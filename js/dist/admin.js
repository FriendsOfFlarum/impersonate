module.exports=function(e){var t={};function n(o){if(t[o])return t[o].exports;var r=t[o]={i:o,l:!1,exports:{}};return e[o].call(r.exports,r,r.exports,n),r.l=!0,r.exports}return n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)n.d(o,r,function(t){return e[t]}.bind(null,r));return o},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=13)}([function(e,t){e.exports=flarum.core.compat.app},,function(e,t){e.exports=flarum.core.compat.extend},,,function(e,t){e.exports=flarum.extensions["fof-components"]},,,,,,function(e,t){e.exports=flarum.core.compat["components/PermissionGrid"]},,function(e,t,n){"use strict";n.r(t);var o=n(2),r=n(0),a=n.n(r),i=n(11),s=n.n(i),f=n(5),u=f.settings.SettingsModal,l=f.settings.items.BooleanItem;a.a.initializers.add("fof/impersonate",(function(){a.a.extensionSettings["fof-impersonate"]=function(){return a.a.modal.show(u,{title:a.a.translator.trans("fof-impersonate.admin.settings.title"),type:"small",items:function(e){return a.a.forum.attribute("impersonateEnableReason",!1)?[m(l,{setting:e,name:"fof-impersonate.require_reason"},a.a.translator.trans("fof-impersonate.admin.settings.require_reason"))]:[m("p",null,a.a.translator.trans("fof-impersonate.admin.settings.no_settings_available"))]}})},Object(o.extend)(s.a.prototype,"moderateItems",(function(e){e.add("fof-impersonate-login",{icon:"fas fa-id-card",label:a.a.translator.trans("fof-impersonate.admin.permissions.login"),permission:"fof-impersonate.login"})}))}))}]);
//# sourceMappingURL=admin.js.map