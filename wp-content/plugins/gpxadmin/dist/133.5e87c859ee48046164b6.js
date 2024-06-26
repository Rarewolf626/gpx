"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[133],{3133:(e,n,t)=>{t.r(n),t.d(n,{default:()=>Z});var r=t(9026),o=t(467),l=t(7401),a=t(7885);function i(e){return i="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},i(e)}function u(e,n){var t=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);n&&(r=r.filter((function(n){return Object.getOwnPropertyDescriptor(e,n).enumerable}))),t.push.apply(t,r)}return t}function s(e){for(var n=1;n<arguments.length;n++){var t=null!=arguments[n]?arguments[n]:{};n%2?u(Object(t),!0).forEach((function(n){d(e,n,t[n])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(t)):u(Object(t)).forEach((function(n){Object.defineProperty(e,n,Object.getOwnPropertyDescriptor(t,n))}))}return e}function d(e,n,t){var r;return r=function(e,n){if("object"!=i(e)||!e)return e;var t=e[Symbol.toPrimitive];if(void 0!==t){var r=t.call(e,n||"default");if("object"!=i(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===n?String:Number)(e)}(n,"string"),(n="symbol"==i(r)?r:String(r))in e?Object.defineProperty(e,n,{value:t,enumerable:!0,configurable:!0,writable:!0}):e[n]=t,e}var c=["disabled"],f={class:"col-md-6 col-md-offset-3"},v={class:"form-group"},p=(0,r._)("label",{for:"region-CountryID",class:"control-label required"},"Country",-1),m=["value","textContent"],g=["textContent"],y=["for"],_=["id","onChange"],b=(0,r._)("option",{value:""},null,-1),w=["value","textContent","selected"],C=["textContent"],D={class:"row"},h={class:"col-md-6"},x={class:"form-group"},k=(0,r._)("label",{for:"region-name",class:"control-label required"},"Region Name",-1),O=["textContent"],H={class:"col-md-6"},j={class:"form-group"},q=(0,r._)("label",{for:"region-displayName",class:"control-label"},"Display Name",-1),I=["textContent"],N={class:"row"},S={class:"col-md-4"},P={class:"form-group"},z=(0,r._)("label",{for:"region-featured",class:"control-label"},"Featured",-1),U=[(0,r._)("option",{value:!0},"Yes",-1),(0,r._)("option",{value:!1},"No",-1)],V=["textContent"],Y={class:"col-md-4"},M={class:"form-group"},R=(0,r._)("label",{for:"region-ddHidden",class:"control-label"},"Hidden",-1),E=[(0,r._)("option",{value:!0},"Yes",-1),(0,r._)("option",{value:!1},"No",-1)],T=["textContent"],F={class:"col-md-4"},K={class:"form-group"},A=(0,r._)("label",{for:"region-show_resort_fees",class:"control-label"},"Show Resort Fees",-1),B=[(0,r._)("option",{value:!0},"Yes",-1),(0,r._)("option",{value:!1},"No",-1)],G=["textContent"],J=(0,r._)("div",{class:"form-help"},"This will affect all resorts in this region and its children.",-1),L=(0,r._)("div",{class:"ln_solid"},null,-1),Q={class:"col-md-6 col-md-offset-3"},W=["disabled"],X=["textContent"];const Z={__name:"RegionEdit",props:{region:{type:Object,required:!0},countries:{type:Array,required:!0},regions:{type:Array,required:!0}},setup:function(e){var n=e,t=(0,a.iH)(!1),i=(0,a.iH)(!1),u=(0,a.iH)({name:n.region.name,CountryID:n.region.CountryID,parent:n.region.parent,displayName:n.region.displayName,ddHidden:n.region.ddHidden,featured:n.region.featured,show_resort_fees:n.region.show_resort_fees}),d=(0,a.iH)({}),Z=(0,a.iH)(""),$=(0,a.iH)(n.region.ancestors.map((function(e){return{id:e.id,name:e.name,parent:e.parent,siblings:n.regions.filter((function(t){return t.id!==n.region.id&&(1===e.parent?t.CountryID===e.CountryID&&1===t.parent:t.parent===e.parent)})).sort((function(e,n){var t=e.lft;return n.lft-t}))}}))),ee=function(e){var t=n.countries.find((function(n){return n.id==e.target.value}));$.value=[{id:e.target.value,name:null==t?void 0:t.name,parent:null,siblings:n.regions.filter((function(t){return t.id!==n.region.id&&(1===t.parent&&t.CountryID==e.target.value)})).sort((function(e,n){var t=e.lft;return n.lft-t}))}],u.value.parent=null},ne=function(){t.value=!0,i.value=!1,Z.value="",d.value={},axios.post("/gpxadmin/region/update/",s(s({},u.value),{},{id:n.region.id})).then((function(e){console.log(e.data),Z.value=e.data.message||"Region was updated.",i.value=e.data.success,t.value=!1,setTimeout((function(){Z.value=""}),4e3)})).catch((function(e){console.log(e),i.value=!1,422===e.response.status?d.value=e.response.data.errors:(Z.value=e.response.data.message||"Failed to save region.",setTimeout((function(){Z.value=""}),4e3)),t.value=!1}))};return function(a,s){return(0,r.wg)(),(0,r.iD)("form",{onSubmit:(0,o.iM)(ne,["prevent"]),novalidate:""},[(0,r._)("fieldset",{class:"row",disabled:t.value},[(0,r._)("div",f,[(0,r._)("div",v,[p,(0,r._)("div",null,[(0,r.wy)((0,r._)("select",{id:"region-CountryID",name:"CountryID",class:"form-control","onUpdate:modelValue":s[0]||(s[0]=function(e){return u.value.CountryID=e}),onChange:ee},[((0,r.wg)(!0),(0,r.iD)(r.HY,null,(0,r.Ko)(e.countries,(function(e){return(0,r.wg)(),(0,r.iD)("option",{key:e.id,value:e.id,textContent:(0,l.zw)(e.name)},null,8,m)})),128))],544),[[o.bM,u.value.CountryID,void 0,{number:!0}]])]),d.value.CountryID?((0,r.wg)(),(0,r.iD)("div",{key:0,textContent:(0,l.zw)(d.value.CountryID[0]),class:"form-error"},null,8,g)):(0,r.kq)("",!0)]),((0,r.wg)(!0),(0,r.iD)(r.HY,null,(0,r.Ko)($.value,(function(e,t){return(0,r.wg)(),(0,r.iD)("div",{key:e.id,class:"form-group"},[(0,r._)("label",{for:"region-parent-".concat(e.id),class:(0,l.C_)(["control-label",{required:0===t}])},"Parent Region",10,y),(0,r._)("div",null,[(0,r._)("select",{id:"region-parent-".concat(e.id),class:"form-control",onChange:function(t){return function(e,t){var r=e?n.regions.find((function(n){return n.id==e})):n.regions.find((function(e){return e.id==t})),o=[],l=r;do{o.push({id:l.id,name:l.name,parent:l.parent,siblings:n.regions.filter((function(e){return e.id!==n.region.id&&(1===l.parent?e.CountryID===l.CountryID&&1===e.parent:e.parent===l.parent)})).sort((function(e,n){var t=e.lft;return n.lft-t}))}),l=n.regions.find((function(e){return e.id==l.parent}))}while(l);o.reverse(),l=r,n.regions.find((function(e){return e.parent==l.id}))&&o.push({id:t,name:null,parent:l.id,siblings:n.regions.filter((function(e){return e.id!==n.region.id&&1!==e.parent&&l.id===e.parent})).sort((function(e,n){var t=e.lft;return n.lft-t}))}),$.value=o,u.value.parent=e||null}(parseInt(t.target.value),e.parent)}},[b,((0,r.wg)(!0),(0,r.iD)(r.HY,null,(0,r.Ko)(e.siblings,(function(n){return(0,r.wg)(),(0,r.iD)("option",{key:n.id,value:n.id,textContent:(0,l.zw)(n.name),selected:n.id===e.id},null,8,w)})),128))],40,_)])])})),128)),d.value.parent?((0,r.wg)(),(0,r.iD)("div",{key:0,textContent:(0,l.zw)(d.value.parent[0]),class:"form-error"},null,8,C)):(0,r.kq)("",!0),(0,r._)("div",D,[(0,r._)("div",h,[(0,r._)("div",x,[k,(0,r._)("div",null,[(0,r.wy)((0,r._)("input",{type:"text",id:"region-name",name:"name",class:"form-control","onUpdate:modelValue":s[1]||(s[1]=function(e){return u.value.name=e}),required:"",maxlength:"255"},null,512),[[o.nr,u.value.name]]),d.value.name?((0,r.wg)(),(0,r.iD)("div",{key:0,textContent:(0,l.zw)(d.value.name[0]),class:"form-error"},null,8,O)):(0,r.kq)("",!0)])])]),(0,r._)("div",H,[(0,r._)("div",j,[q,(0,r._)("div",null,[(0,r.wy)((0,r._)("input",{type:"text",id:"region-displayName",name:"displayName",class:"form-control","onUpdate:modelValue":s[2]||(s[2]=function(e){return u.value.displayName=e}),maxlength:"255"},null,512),[[o.nr,u.value.displayName]]),d.value.displayName?((0,r.wg)(),(0,r.iD)("div",{key:0,textContent:(0,l.zw)(d.value.displayName[0]),class:"form-error"},null,8,I)):(0,r.kq)("",!0)])])])]),(0,r._)("div",N,[(0,r._)("div",S,[(0,r._)("div",P,[z,(0,r._)("div",null,[(0,r.wy)((0,r._)("select",{id:"region-featured",name:"featured",class:"form-control","onUpdate:modelValue":s[3]||(s[3]=function(e){return u.value.featured=e})},U,512),[[o.bM,u.value.featured]])]),d.value.featured?((0,r.wg)(),(0,r.iD)("div",{key:0,textContent:(0,l.zw)(d.value.featured[0]),class:"form-error"},null,8,V)):(0,r.kq)("",!0)])]),(0,r._)("div",Y,[(0,r._)("div",M,[R,(0,r._)("div",null,[(0,r.wy)((0,r._)("select",{id:"region-ddHidden",name:"ddHidden",class:"form-control","onUpdate:modelValue":s[4]||(s[4]=function(e){return u.value.ddHidden=e})},E,512),[[o.bM,u.value.ddHidden]])]),d.value.ddHidden?((0,r.wg)(),(0,r.iD)("div",{key:0,textContent:(0,l.zw)(d.value.show_resort_fees[0]),class:"form-error"},null,8,T)):(0,r.kq)("",!0)])]),(0,r._)("div",F,[(0,r._)("div",K,[A,(0,r._)("div",null,[(0,r.wy)((0,r._)("select",{id:"region-show_resort_fees",name:"show_resort_fees",class:"form-control","onUpdate:modelValue":s[5]||(s[5]=function(e){return u.value.show_resort_fees=e})},B,512),[[o.bM,u.value.show_resort_fees]])]),d.value.show_resort_fees?((0,r.wg)(),(0,r.iD)("div",{key:0,textContent:(0,l.zw)(d.value.show_resort_fees[0]),class:"form-error"},null,8,G)):(0,r.kq)("",!0),J])])])])],8,c),L,(0,r._)("div",Q,[(0,r._)("button",{type:"submit",class:"btn btn-success",disabled:t.value},"Save",8,W),Z.value?((0,r.wg)(),(0,r.iD)("div",{key:0,class:(0,l.C_)(["alert",{"alert-success":i.value,"alert-danger":!i.value}]),textContent:(0,l.zw)(Z.value)},null,10,X)):(0,r.kq)("",!0)])],32)}}}}}]);