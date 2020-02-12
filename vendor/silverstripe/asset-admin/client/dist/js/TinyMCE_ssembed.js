!function(e){function t(r){if(n[r])return n[r].exports;var i=n[r]={i:r,l:!1,exports:{}};return e[r].call(i.exports,i,i.exports,t),i.l=!0,i.exports}var n={};t.m=e,t.c=n,t.i=function(e){return e},t.d=function(e,n,r){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:r})},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s="./client/src/entwine/TinyMCE_ssembed.js")}({"./client/src/components/InsertEmbedModal/InsertEmbedModal.js":function(e,t,n){"use strict";function r(e){return e&&e.__esModule?e:{default:e}}function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function a(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}function o(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t)}function s(e,t){var n=e.config.sections.find(function(e){return e.name===E}),r=t.fileAttributes?t.fileAttributes.Url:"",i=n.form.remoteEditForm.schemaUrl,a=r&&i+"/?embedurl="+encodeURIComponent(r),o=n.form.remoteCreateForm.schemaUrl;return{sectionConfig:n,schemaUrl:a||o,targetUrl:r}}function l(e){return{actions:{schema:(0,m.bindActionCreators)(_,e)}}}Object.defineProperty(t,"__esModule",{value:!0}),t.Component=void 0;var d=function(){function e(e,t){var n=[],r=!0,i=!1,a=void 0;try{for(var o,s=e[Symbol.iterator]();!(r=(o=s.next()).done)&&(n.push(o.value),!t||n.length!==t);r=!0);}catch(e){i=!0,a=e}finally{try{!r&&s.return&&s.return()}finally{if(i)throw a}}return n}return function(t,n){if(Array.isArray(t))return t;if(Symbol.iterator in Object(t))return e(t,n);throw new TypeError("Invalid attempt to destructure non-iterable instance")}}(),u=function(){function e(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}return function(t,n,r){return n&&e(t.prototype,n),r&&e(t,r),t}}(),c=n(2),f=r(c),p=n(0),h=r(p),m=n(5),g=n(4),b=n(11),v=r(b),y=n(19),_=function(e){if(e&&e.__esModule)return e;var t={};if(null!=e)for(var n in e)Object.prototype.hasOwnProperty.call(e,n)&&(t[n]=e[n]);return t.default=e,t}(y),C=n(1),A=r(C),E="SilverStripe\\AssetAdmin\\Controller\\AssetAdmin",w=function(e){function t(e){i(this,t);var n=a(this,(t.__proto__||Object.getPrototypeOf(t)).call(this,e));return n.handleSubmit=n.handleSubmit.bind(n),n}return o(t,e),u(t,[{key:"componentWillMount",value:function(){this.setOverrides(this.props)}},{key:"componentWillReceiveProps",value:function(e){e.isOpen&&!this.props.isOpen&&this.setOverrides(e)}},{key:"componentWillUnmount",value:function(){this.clearOverrides()}},{key:"setOverrides",value:function(e){if(this.props.schemaUrl!==e.schemaUrl&&this.clearOverrides(),e.schemaUrl){var t=Object.assign({},e.fileAttributes);delete t.ID;var n={fields:Object.entries(t).map(function(e){var t=d(e,2);return{name:t[0],value:t[1]}})};this.props.actions.schema.setSchemaStateOverrides(e.schemaUrl,n)}}},{key:"getModalProps",value:function(){var e=Object.assign({onSubmit:this.handleSubmit,onLoadingError:this.handleLoadingError,showErrorMessage:!0,responseClassBad:"alert alert-danger",identifier:"AssetAdmin.InsertEmbedModal"},this.props,{className:"insert-embed-modal "+this.props.className,size:"lg",onClosed:this.props.onClosed,title:this.props.targetUrl?f.default._t("AssetAdmin.EditTitle","Media from the web"):f.default._t("AssetAdmin.CreateTitle","Insert new media from the web")});return delete e.sectionConfig,delete e.onInsert,delete e.fileAttributes,e}},{key:"clearOverrides",value:function(){this.props.actions.schema.setSchemaStateOverrides(this.props.schemaUrl,null)}},{key:"handleLoadingError",value:function(e){"function"==typeof this.props.onLoadingError&&this.props.onLoadingError(e)}},{key:"handleSubmit",value:function(e,t){switch(t){case"action_addmedia":this.props.onCreate(e);break;case"action_insertmedia":this.props.onInsert(e);break;case"action_cancel":this.props.onClosed()}return Promise.resolve()}},{key:"render",value:function(){return h.default.createElement(v.default,this.getModalProps())}}]),t}(p.Component);w.propTypes={sectionConfig:A.default.shape({url:A.default.string,form:A.default.object}),isOpen:A.default.bool,onInsert:A.default.func.isRequired,onCreate:A.default.func.isRequired,fileAttributes:A.default.shape({Url:A.default.string,CaptionText:A.default.string,PreviewUrl:A.default.string,Placement:A.default.string,Width:A.default.number,Height:A.default.number}),onClosed:A.default.func.isRequired,className:A.default.string,actions:A.default.object,schemaUrl:A.default.string.isRequired,targetUrl:A.default.string,onLoadingError:A.default.func},w.defaultProps={className:"",fileAttributes:{}},t.Component=w,t.default=(0,g.connect)(s,l)(w)},"./client/src/entwine/TinyMCE_ssembed.js":function(e,t,n){"use strict";function r(e){return e&&e.__esModule?e:{default:e}}var i=n(7),a=r(i),o=n(0),s=r(o),l=n(6),d=r(l),u=n(3),c=n(8),f=r(c),p=n("./client/src/components/InsertEmbedModal/InsertEmbedModal.js"),h=r(p),m=n(2),g=r(m),b=(0,u.loadComponent)(h.default),v='div[data-shortcode="embed"]';!function(){var e={init:function(e){var t=g.default._t("AssetAdmin.INSERT_VIA_URL","Insert media via URL"),n=g.default._t("AssetAdmin.EDIT_MEDIA","Edit media"),r=g.default._t("AssetAdmin.MEDIA","Media");e.addButton("ssembed",{title:t,icon:"media",cmd:"ssembed",stateSelector:v}),e.addMenuItem("ssembed",{text:r,icon:"media",cmd:"ssembed"}),e.addButton("ssembededit",{title:n,icon:"editimage",cmd:"ssembed"}),e.addContextToolbar(function(t){return e.dom.is(t,v)},"alignleft aligncenter alignright | ssembededit"),e.addCommand("ssembed",function(){(0,a.default)("#"+e.id).entwine("ss").openEmbedDialog()}),e.on("BeforeExecCommand",function(t){var n=t.command,r=t.ui,i=t.value;"mceAdvMedia"!==n&&"mceAdvMedia"!==n||(t.preventDefault(),e.execCommand("ssembed",r,i))}),e.on("SaveContent",function(e){var t=(0,a.default)("<div>"+e.content+"</div>");t.find(v).each(function(){var e=(0,a.default)(this),t=e.find("img.placeholder");if(0===t.length)return e.removeAttr("data-url"),void e.removeAttr("data-shortcode");var n=e.find(".caption").text(),r=parseInt(t.attr("width"),10),i=parseInt(t.attr("height"),10),o=e.data("url"),s=(0,c.sanitiseShortCodeProperties)({url:o,thumbnail:t.prop("src"),class:e.prop("class"),width:isNaN(r)?null:r,height:isNaN(i)?null:i,caption:n}),l=f.default.serialise({name:"embed",properties:s,wrapped:!0,content:s.url});e.replaceWith(l)}),e.content=t.html()}),e.on("BeforeSetContent",function(e){for(var t=e.content,n=f.default.match("embed",!0,t);n;){var r=n.properties,i=(0,a.default)("<div/>").attr("data-url",r.url||n.content).attr("data-shortcode","embed").addClass(r.class).addClass("ss-htmleditorfield-file embed"),o=(0,a.default)("<img />").attr("src",r.thumbnail).addClass("placeholder");if(r.width&&o.attr("width",r.width),r.height&&o.attr("height",r.height),i.append(o),r.caption){var s=(0,a.default)("<p />").addClass("caption").text(r.caption);i.append(s)}t=t.replace(n.original,(0,a.default)("<div/>").append(i).html()),n=f.default.match("embed",!0,t)}e.content=t})}};tinymce.PluginManager.add("ssembed",function(t){return e.init(t)})}(),a.default.entwine("ss",function(e){e(".js-injector-boot #insert-embed-react__dialog-wrapper").entwine({Element:null,Data:{},onunmatch:function(){this._clearModal()},_clearModal:function(){d.default.unmountComponentAtNode(this[0])},open:function(){this._renderModal(!0)},close:function(){this.setData({}),this._renderModal(!1)},_renderModal:function(e){var t=this,n=function(){return t.close()},r=function(){return t._handleInsert.apply(t,arguments)},i=function(){return t._handleCreate.apply(t,arguments)},a=function(){return t._handleLoadingError.apply(t,arguments)},o=this.getOriginalAttributes();d.default.render(s.default.createElement(b,{isOpen:e,onCreate:i,onInsert:r,onClosed:n,onLoadingError:a,bodyClassName:"modal__dialog",className:"insert-embed-react__dialog-wrapper",fileAttributes:o}),this[0])},_handleLoadingError:function(){this.setData({}),this.open()},_handleInsert:function(e){var t=this.getData();this.setData(Object.assign({Url:t.Url},e)),this.insertRemote(),this.close()},_handleCreate:function(e){this.setData(Object.assign({},this.getData(),e)),this.open()},getOriginalAttributes:function(){var t=this.getData(),n=this.getElement();if(!n)return t;var r=e(n.getEditor().getSelectedNode());if(!r.length)return t;var i=r.closest(v).add(r.filter(v));if(!i.length)return t;var a=i.find("img.placeholder");if(0===a.length)return t;var o=i.find(".caption").text(),s=parseInt(a.width(),10),l=parseInt(a.height(),10);return{Url:i.data("url")||t.Url,CaptionText:o,PreviewUrl:a.attr("src"),Width:isNaN(s)?null:s,Height:isNaN(l)?null:l,Placement:this.findPosition(i.prop("class"))}},findPosition:function(e){var t=["leftAlone","center","rightAlone","left","right"];if("string"!=typeof e)return"";var n=e.split(" ");return t.find(function(e){return n.indexOf(e)>-1})},insertRemote:function(){var t=this.getElement();if(!t)return!1;var n=t.getEditor();if(!n)return!1;var r=this.getData(),i=(0,a.default)("<div/>").attr("data-url",r.Url).attr("data-shortcode","embed").addClass(r.Placement).addClass("ss-htmleditorfield-file embed"),o=(0,a.default)("<img />").attr("src",r.PreviewUrl).addClass("placeholder");if(r.Width&&o.attr("width",r.Width),r.Height&&o.attr("height",r.Height),i.append(o),r.CaptionText){var s=(0,a.default)("<p />").addClass("caption").text(r.CaptionText);i.append(s)}var l=e(n.getSelectedNode()),d=e(null);return l.length&&(d=l.filter(v),0===d.length&&(d=l.closest(v)),0===d.length&&(d=l.filter("img.placeholder"))),d.length?d.replaceWith(i):(n.repaint(),n.insertContent(e("<div />").append(i.clone()).html(),{skip_undo:1})),n.addUndo(),n.repaint(),!0}})})},0:function(e,t){e.exports=React},1:function(e,t){e.exports=PropTypes},11:function(e,t){e.exports=FormBuilderModal},19:function(e,t){e.exports=SchemaActions},2:function(e,t){e.exports=i18n},3:function(e,t){e.exports=Injector},4:function(e,t){e.exports=ReactRedux},5:function(e,t){e.exports=Redux},6:function(e,t){e.exports=ReactDom},7:function(e,t){e.exports=jQuery},8:function(e,t){e.exports=ShortcodeSerialiser}});