"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[508],{9508:(e,n,a)=>{a.r(n),a.d(n,{default:()=>d});var t=a(9026),i=a(467),o=a(7401),l=a(7885),s=["disabled"],r={class:"fa fa-circle-o-notch fa-spin fa-fw",style:{display:"none"}};const d={__name:"RegionDelete",props:{region:{type:Object,required:!0}},setup:function(e){var n=e,a=(0,l.iH)(!1),d=function(){a.value||confirm("Are you sure you want to remove this record?\nAll associated data will be moved to the parent region.\nThis action cannot be undone!")&&(a.value=!0,axios.post("/gpxadmin/region/delete/",{id:n.region.id}).then((function(e){if(e.data.success)window.location=e.data.redirect;else{var n=e.data.message||"Failed to delete region";a.value=!1,alert(n)}})).catch((function(e){var n,t=(null===(n=e.response)||void 0===n?void 0:n.data.message)||"Failed to delete region";a.value=!1,alert(t)})))};return function(n,l){return(0,t.wg)(),(0,t.iD)("form",{onSubmit:(0,i.iM)(d,["prevent"]),class:"d-inline-block"},[(0,t._)("button",{type:"submit",class:"btn btn-danger",disabled:a.value},[(0,t.Uk)(" Remove "+(0,o.zw)(e.region.name)+" ",1),(0,t.wy)((0,t._)("i",r,null,512),[[i.F8,a.value]])],8,s)],32)}}}}}]);