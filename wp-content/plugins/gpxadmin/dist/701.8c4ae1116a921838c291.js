"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[701],{4701:(e,a,t)=>{t.r(a),t.d(a,{default:()=>c});var n=t(9026),i=t(467),u=t(7401),l=t(7885),s=["disabled"],d=["disabled"],o={type:"submit",class:"btn btn-primary"},r=["textContent"];const c={__name:"RegionFeatured",props:{region:{type:Object,required:!0}},setup:function(e){var a=e,t=(0,l.iH)(a.region.featured),c=(0,l.iH)(a.region.hidden),v=(0,l.iH)(!1),f=(0,l.iH)(""),p=function(){v.value||(v.value=!0,f.value="",axios.post("/gpxadmin/region/featured/",{id:a.region.id}).then((function(e){t.value=e.data.featured,f.value=e.data.message})).catch((function(e){var a;f.value=(null===(a=e.response)||void 0===a?void 0:a.data.message)||"Failed to update featured status"})).finally((function(){v.value=!1,setTimeout((function(){f.value=""}),4e3)})))},b=function(){v.value||(v.value=!0,f.value="",axios.post("/gpxadmin/region/hidden/",{id:a.region.id}).then((function(e){c.value=e.data.hidden,f.value=e.data.message})).catch((function(e){var a;f.value=(null===(a=e.response)||void 0===a?void 0:a.data.message)||"Failed to update hidden status"})).finally((function(){v.value=!1,setTimeout((function(){f.value=""}),4e3)})))};return function(e,a){return(0,n.wg)(),(0,n.iD)("div",null,[(0,n._)("div",null,[(0,n._)("form",{onSubmit:(0,i.iM)(p,["prevent"]),class:"d-inline-block"},[(0,n._)("button",{type:"submit",class:"btn btn-primary",disabled:v.value},[(0,n.Uk)(" Featured "),(0,n._)("i",{class:(0,u.C_)(["hidden-status fa",t.value?"fa-check-square":"fa-square"]),"aria-hidden":"true"},null,2)],8,s)],32),(0,n._)("form",{onSubmit:(0,i.iM)(b,["prevent"]),class:"d-inline-block",disabled:v.value},[(0,n._)("button",o,[(0,n.Uk)(" Hidden "),(0,n._)("i",{class:(0,u.C_)(["hidden-status fa",c.value?"fa-check-square":"fa-square"]),"aria-hidden":"true"},null,2)])],40,d)]),f.value?((0,n.wg)(),(0,n.iD)("div",{key:0,class:"alert alert-success",textContent:(0,u.zw)(f.value)},null,8,r)):(0,n.kq)("",!0)])}}}}}]);