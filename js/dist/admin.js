(()=>{var e={n:o=>{var r=o&&o.__esModule?()=>o.default:()=>o;return e.d(r,{a:r}),r},d:(o,r)=>{for(var t in r)e.o(r,t)&&!e.o(o,t)&&Object.defineProperty(o,t,{enumerable:!0,get:r[t]})},o:(e,o)=>Object.prototype.hasOwnProperty.call(e,o),r:e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})}},o={};(()=>{"use strict";e.r(o),e.d(o,{extend:()=>O});const r=flarum.reg.get("core","admin/app");var t=e.n(r);const n=flarum.reg.get("core","common/extend"),a=flarum.reg.get("core","admin/components/UserListPage");var s=e.n(a);const i=flarum.reg.get("core","common/app");var l=e.n(i);const c=flarum.reg.get("core","common/components/Button");var u=e.n(c);function d(e){return d="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},d(e)}function f(e,o,r){return(o=function(e){var o=function(e){if("object"!=d(e)||!e)return e;var o=e[Symbol.toPrimitive];if(void 0!==o){var r=o.call(e,"string");if("object"!=d(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return String(e)}(e);return"symbol"==d(o)?o:o+""}(o))in e?Object.defineProperty(e,o,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[o]=r,e}const p=flarum.reg.get("core","common/components/Form");var g=e.n(p);const b=flarum.reg.get("core","common/components/FormModal");var h=e.n(b);const v=flarum.reg.get("core","common/helpers/username");var y=e.n(v);const w=flarum.reg.get("core","common/utils/Stream");var S=e.n(w);const _=flarum.reg.get("core","common/utils/withAttr");var M=e.n(_);class x extends(h()){constructor(){super(...arguments),f(this,"user",void 0),f(this,"reason",void 0),f(this,"loading",void 0),f(this,"reasonEnabled",void 0),f(this,"reasonRequired",void 0)}oninit(e){super.oninit(e),this.user=this.attrs.user,this.reason=S()(""),this.loading=!1,this.reasonEnabled=l().initializers.has("fof-moderator-notes"),this.reasonRequired=this.user.impersonateReasonRequired()}className(){return"ImpersonateModal Modal--medium"}title(){return l().translator.trans("fof-impersonate.lib.modal.title")}content(){return m("div",{className:"Modal-body"},m("div",null,m("p",null,l().translator.trans("fof-impersonate.lib.modal.label",{username:y()(this.user)}))),m(g(),{className:"Form--centered"},this.reasonEnabled?m("div",{className:"Form-group"},m("textarea",{className:"FormControl",value:this.reason(),placeholder:this.reasonRequired?l().translator.trans("fof-impersonate.lib.modal.placeholder_required"):l().translator.trans("fof-impersonate.lib.modal.placeholder_optional"),oninput:M()("value",this.reason),rows:"4"})):"",m("div",{className:"Form-group"},u().component({className:"Button Button--primary Button--block",type:"submit",loading:this.loading},l().translator.trans("fof-impersonate.lib.modal.impersonate_username",{username:y()(this.user)})))))}onsubmit(e){e.preventDefault(),this.loading=!0,l().store.createRecord("impersonate").save({userId:this.user.id(),reason:this.reason()}).then(this.attrs.callback).catch((()=>{this.loading=!1}))}}flarum.reg.add("fof-impersonate","common/components/ImpersonateModal",x);class q extends(u()){view(){return m(u(),{icon:"fas fa-id-card",onclick:this.loginAsUser.bind(this)},l().translator.trans("fof-impersonate.lib.user_controls.impersonate_button"))}loginAsUser(){const{user:e,redirectTo:o}=this.attrs;l().modal.show(x,{callback:()=>{o?window.location.href=o:window.location.reload()},user:e})}}flarum.reg.add("fof-impersonate","common/components/LoginAsUserButton",q);const F=flarum.reg.get("core","common/extenders");var P=e.n(F);const R=flarum.reg.get("core","common/models/User");var j=e.n(R);const N=flarum.reg.get("core","common/Model");var A=e.n(N);class I extends(A()){reason(){return A().attribute("reason").call(this)}}flarum.reg.add("fof-impersonate","common/models/Impersonate",I);const O=[(new(P().Store)).add("impersonate",I),new(P().Model)(j()).attribute("canFoFImpersonate").attribute("impersonateReasonRequired"),(new(P().Admin)).permission((()=>({icon:"fas fa-id-card",label:t().translator.trans("fof-impersonate.admin.permissions.login"),permission:"fof-impersonate.login"})),"moderate").setting((()=>({setting:"fof-impersonate.require_reason",type:"boolean",label:t().translator.trans("fof-impersonate.admin.settings.require_reason")})))];t().initializers.add("fof-impersonate",(()=>{(0,n.extend)(s().prototype,"userActionItems",(function(e,o){const r=t().forum.attribute("baseUrl");e.add("impersonate",m(q,{user:o,redirectTo:`${r}/u/${o.slug()}`}))}))}))})(),module.exports=o})();
//# sourceMappingURL=admin.js.map