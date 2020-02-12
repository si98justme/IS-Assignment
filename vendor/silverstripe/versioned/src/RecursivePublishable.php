<?php


namespace SilverStripe\Versioned;

use InvalidArgumentException;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\Queries\SQLUpdate;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\Tests\MySQLDatabaseTest\Data;

/**
 * Provides owns / owned_by and recursive publishing API for all objects.
 * This extension is added to DataObject by default
 *
 * @property DataObject|RecursivePublishable $owner
 */
class RecursivePublishable extends DataExtension
{
    /**
     * List of relationships on this object that are "owned" by this object.
     * Owership in the context of versioned objects is a relationship where
     * the publishing of owning objects requires the publishing of owned objects.
     *
     * E.g. A page owns a set of banners, as in order for the page to be published, all
     * banners on this page must also be published for it to be visible.
     *
     * Typically any object and its owned objects should be visible in the same edit view.
     * E.g. a page and {@see GridField} of banners.
     *
     * Page hierarchy is typically not considered an ownership relationship.
     *
     * Ownership is recursive; If A owns B and B owns C then A owns C.
     *
     * @config
     * @var array List of has_many or many_many relationships owned by this object.
     */
    private static $owns = [];

    /**
     * Opposing relationship to owns config; Represents the objects which
     * own the current object.
     *
     * @var array
     */
    private static $owned_by = [];

    /**
     * Publish this object and all owned objects to Live
     *
     * @return bool
     */
    public function publishRecursive()
    {
        // Create a new changeset for this item and publish it
        $changeset = ChangeSet::create();
        $changeset->IsInferred = true;
        $changeset->Name = _t(
            __CLASS__ . '.INFERRED_TITLE',
            "Generated by publish of '{title}' at {created}",
            [
                'title' => $this->owner->Title,
                'created' => DBDatetime::now()->Nice()
            ]
        );
        $changeset->write();
        $changeset->addObject($this->owner);
        return $changeset->publish(true);
    }

    /**
     * Rollback all related objects on this stage.
     *
     * Note: This method should be called on the source object queried in the appropriate "from"
     * for this rollback, as it will rely on the parent object's query parameters to return
     * nested objects.
     *
     * @internal Do not call this directly! This should only be invoked by Versioned::rollbackRecursive()
     * @param int|string $version Parent version / stage to rollback from
     */
    public function rollbackRelations($version)
    {
        $owner = $this->owner;
        // Rollback recursively
        foreach ($owner->findOwned(false) as $object) {
            if ($object->hasExtension(Versioned::class)) {
                // Pass in null to rollback to self version
                /** @var Versioned $object */
                $object->rollbackRecursive(null);
            } else {
                // Rollback unversioned record (inherits parent query parameters)
                $object->rollbackRelations($version);
            }
        }
    }

    /**
     * Remove this item from any changesets
     *
     * @return bool
     */
    public function deleteFromChangeSets()
    {
        $changeSetIDs = [];

        // Remove all ChangeSetItems matching this record
        /** @var ChangeSetItem $changeSetItem */
        foreach (ChangeSetItem::get_for_object($this->owner) as $changeSetItem) {
            $changeSetIDs[$changeSetItem->ChangeSetID] = $changeSetItem->ChangeSetID;
            $changeSetItem->delete();
        }

        // Sync all affected changesets
        if ($changeSetIDs) {
            /** @var ChangeSet $changeSet */
            foreach (ChangeSet::get()->byIDs($changeSetIDs) as $changeSet) {
                $changeSet->sync();
            }
        }
        return true;
    }

    /**
     * Find all objects owned by the current object.
     * Note that objects will only be searched in the same stage as the given record.
     *
     * @param bool $recursive True if recursive
     * @param ArrayList $list Optional list to add items to
     * @return ArrayList list of objects
     */
    public function findOwned($recursive = true, $list = null)
    {
        // Find objects in these relationships
        return $this->owner->findRelatedObjects('owns', $recursive, $list);
    }

    /**
     * Returns true if the record has any owned relationships that exist
     * @return bool
     */
    public function hasOwned()
    {
        if (!$this->owner->isInDB()) {
            return false;
        }

        $ownedRelationships = $this->owner->config()->get('owns') ?: [];
        foreach ($ownedRelationships as $relationship) {
            /* @var DataObject|SS_List $result */
            $result = $this->owner->{$relationship}();
            if ($result->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find objects which own this object.
     * Note that objects will only be searched in the same stage as the given record.
     *
     * @param bool $recursive True if recursive
     * @param ArrayList $list Optional list to add items to
     * @return ArrayList list of objects
     */
    public function findOwners($recursive = true, $list = null)
    {
        if (!$list) {
            $list = new ArrayList();
        }

        // Build reverse lookup for ownership
        // @todo - Cache this more intelligently
        $rules = $this->lookupReverseOwners();

        // Hand off to recursive method
        return $this->findOwnersRecursive($recursive, $list, $rules);
    }

    /**
     * Find objects which own this object.
     * Note that objects will only be searched in the same stage as the given record.
     *
     * @param bool $recursive True if recursive
     * @param ArrayList $list List to add items to
     * @param array $lookup List of reverse lookup rules for owned objects
     * @return ArrayList list of objects
     */
    public function findOwnersRecursive($recursive, $list, $lookup)
    {
        // First pass: find objects that are explicitly owned_by (e.g. custom relationships)
        /** @var DataObject $owner */
        $owner = $this->owner;
        $owners = $owner->findRelatedObjects('owned_by', false);

        // Second pass: Find owners via reverse lookup list if possible
        if ($owner->isInDB()) {
            foreach ($lookup as $ownedClass => $classLookups) {
                // Skip owners of other objects
                if (!is_a($owner, $ownedClass)) {
                    continue;
                }
                foreach ($classLookups as $classLookup) {
                    // Merge new owners into this object's owners
                    $ownerClass = $classLookup['class'];
                    $ownerRelation = $classLookup['relation'];
                    $result = $owner->inferReciprocalComponent($ownerClass, $ownerRelation);
                    $owner->mergeRelatedObjects($owners, $result);
                }
            }
        }

        // Merge all objects into the main list
        $newItems = $owner->mergeRelatedObjects($list, $owners);

        // If recursing, iterate over all newly added items
        if ($recursive) {
            foreach ($newItems as $item) {
                /** @var RecursivePublishable|DataObject $item */
                $item->findOwnersRecursive(true, $list, $lookup);
            }
        }

        return $list;
    }

    /**
     * Find a list of classes, each of which with a list of methods to invoke
     * to lookup owners.
     *
     * @return array
     */
    protected function lookupReverseOwners()
    {
        // Find all classes with 'owns' config
        $lookup = [];
        $classes = ClassInfo::subclassesFor(DataObject::class);
        array_shift($classes); // skip DataObject
        foreach ($classes as $class) {
            // Ensure this class is RecursivePublishable
            if (!DataObject::has_extension($class, self::class)) {
                continue;
            }

            // Check owned objects for this class
            $owns = Config::inst()->get($class, 'owns', Config::UNINHERITED);
            if (empty($owns)) {
                continue;
            }

            $instance = DataObject::singleton($class);
            foreach ($owns as $owned) {
                // Find owned class
                $ownedClass = $instance->getRelationClass($owned);
                // Skip custom methods that don't have db relations, or cannot be inferred
                if (!$ownedClass || $ownedClass === DataObject::class) {
                    continue;
                }

                // Add lookup for owned class
                if (!isset($lookup[$ownedClass])) {
                    $lookup[$ownedClass] = [];
                }
                $lookup[$ownedClass][] = [
                    'class' => $class,
                    'relation' => $owned
                ];
            }
        }
        return $lookup;
    }

    /**
     * Set foreign keys of has_many objects to 0 where those objects were
     * disowned as a result of a partial publish / unpublish.
     * I.e. this object and its owned objects were recently written to $targetStage,
     * but deleted objects were not.
     *
     * Note that this operation does not create any new Versions
     *
     * @param string|int|DataObject $source Objects in this stage / version / record will not be unlinked.
     * Provide number if saving records from a version, or string if saving records from a stage
     * @param string $targetStage Objects which exist in this stage but not $sourceVersion
     * will be unlinked. This parameter only supports stage name, as you cannot modify specific versions
     * @throws InvalidArgumentException
     */
    public function unlinkDisownedObjects($source, $targetStage)
    {
        $owner = $this->owner;

        // after publishing, objects which used to be owned need to be
        // dis-connected from this object (set ForeignKeyID = 0)
        $owns = $owner->config()->get('owns');
        $hasMany = $owner->config()->get('has_many');
        $ownedHasMany = array_intersect($owns, array_keys($hasMany));
        if (empty($ownedHasMany)) {
            return;
        }

        // Get exclusion list based on parent object
        /** @var Versioned|DataObject $sourceOwner */
        $sourceOwner = null;
        if ($source instanceof DataObject) {
            $sourceOwner = $source;
        } elseif (is_numeric($source)) {
            $sourceOwner = Versioned::get_version($owner->baseClass(), $owner->ID, $source);
        } elseif (is_string($source)) {
            ReadingMode::validateStage($source);
            $sourceOwner = Versioned::get_by_stage($owner->baseClass(), $source)->byID($owner->ID);
        }

        // Make sure the record exists at all
        if (!$sourceOwner || !$sourceOwner->isInDB()) {
            throw new InvalidArgumentException(
                '$source parameter provided was not a valid stage, version, or saved record'
            );
        }

        // Unlink each relationship
        foreach ($ownedHasMany as $relationship) {
            $sourceOwner->unlinkDisownedRelationship($source, $targetStage, $relationship);
        }
    }

    /**
     * Unlink an object with a specific named relationship against the owner.
     * Note: The owner object should be queried in the correct stage / view mode
     * that includes objects that should NOT be unlinked.
     * E.g. if disowning objects from live, the owner object should be queried
     * in draft, and vice versa (or some other source version ID)
     *
     * @param string|int|DataObject $source Objects in this stage / version / record will not be unlinked.
     * Provide number if saving records from a version, or string if saving records from a stage
     * @param string $targetStage Objects which exist in this stage but not $sourceVersion
     * will be unlinked. This parameter only supports stage name, as you cannot modify specific versions
     * @param string $relationship Name of has_many relationship to unlink
     */
    public function unlinkDisownedRelationship($source, $targetStage, $relationship)
    {
        $owner = $this->owner;

        // Check the owned object is actually versioned and staged
        $schema = DataObject::getSchema();
        $joinClass = $schema->hasManyComponent(get_class($owner), $relationship);
        $joinInstance = DataObject::singleton($joinClass);

        // Skip unversioned relationships
        /** @var Versioned $versioned */
        $versioned = $joinInstance->getExtensionInstance(Versioned::class);
        if (!$versioned) {
            return;
        }

        // Find table and field to join on
        $joinField = $schema->getRemoteJoinField(get_class($owner), $relationship, 'has_many', $polymorphic);
        $joinTable = DataObject::getSchema()->tableForField(
            $joinClass,
            $polymorphic ? "{$joinField}ID" : $joinField
        );

        // Generate update query which will unlink disowned objects
        $targetTable = $versioned->stageTable($joinTable, $targetStage);
        $disowned = new SQLUpdate("\"{$targetTable}\"");
        if ($polymorphic) {
            $disowned
                ->assign("\"{$joinField}ID\"", 0)
                ->assign("\"{$joinField}Class\"", null)
                ->addWhere([
                    "\"{$targetTable}\".\"{$joinField}ID\"" => $owner->ID,
                    "\"{$targetTable}\".\"{$joinField}Class\"" => get_class($owner),
                ]);
        } else {
            $disowned
                ->assign("\"{$joinField}\"", 0)
                ->addWhere([
                    "\"{$targetTable}\".\"{$joinField}\"" => $owner->ID
                ]);
        }

        // Query the source for the list of items to NOT remove
        $ownedSQL = $owner->getComponents($relationship)->sql($ownedParams);
        $disowned->addWhere([
            "\"{$targetTable}\".\"ID\" NOT IN (SELECT \"Source\".\"ID\" FROM ({$ownedSQL}) AS \"Source\")" => $ownedParams
        ]);

        $owner->extend('updateDisownershipQuery', $disowned, $source, $targetStage, $relationship);

        $disowned->execute();
    }

    /**
     * If `cascade_duplications` is empty, default to `owns` config
     *
     * @param DataObject $original
     * @param bool $doWrite
     * @param array|null|false $relations
     */
    public function onBeforeDuplicate($original, &$doWrite, &$relations)
    {
        // If relations to duplicate are declared (or forced off) don't rewrite
        if ($relations || $relations === false) {
            return;
        }

        // Only duplicate owned relationships that are either exclusively owned,
        // or require additional writes. Also exclude any custom non-relation ownerships.
        $allowed = array_merge(
            array_keys($this->owner->manyMany()), // Require mapping table duplications
            array_keys($this->owner->belongsTo()), // Exclusive record must be duplicated
            array_keys($this->owner->hasMany()) // Exclusive records should be duplicated
        );
        // Note: don't assume that owned has_one needs duplication, as these can be
        // shared non-exclusively by both clone and original.
        // Get candidates from ownership and intersect
        $owns = $this->owner->config()->get('owns');
        $relations = array_intersect($allowed, $owns);
    }
}
