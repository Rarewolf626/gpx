"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[925],{5925:(e,a,t)=>{t.r(a),t.d(a,{default:()=>w});var l=t(9026),n=t(467),o=t(7401),i=t(7885),d={class:"modal-content"},s={class:"modal-header"},u=(0,l._)("button",{type:"button",class:"close","data-dismiss":"modal","aria-label":"Close"},[(0,l._)("span",{"aria-hidden":"true"},"×")],-1),c={class:"icon-box"},r=[(0,l._)("i",{class:"material-icons"},"",-1)],v=["textContent"],m={class:"modal-body"},b=["textContent"],p={key:1},k={class:"modal-footer"},_=["textContent"],g=["disabled"];const w={__name:"DeleteWeek",props:{week_id:{type:Number,required:!0}},setup:function(e){var a=e,t=(0,i.iH)(!1),w=(0,i.iH)(!1),h=(0,i.iH)(""),f=(0,i.iH)(null),y=function(){jQuery(f.value).modal("show")},C=function(){w.value||t.value||(t.value=!0,h.value="",axios.delete("/gpxadmin/room/delete",{params:{id:a.week_id}}).then((function(e){var a,l;e.data.success?(h.value=(null===(a=e.data)||void 0===a?void 0:a.message)||"Room archived Successfully.",w.value=!0,jQuery(f.value).on("hidden.bs.modal",(function(){window.location=e.data.redirect||"/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_all"}))):(h.value=(null===(l=e.data)||void 0===l?void 0:l.message)||"Failed to archive the room.",t.value=!1)})).catch((function(e){var a;h.value=(null===(a=e.response)||void 0===a||null===(a=a.data)||void 0===a?void 0:a.message)||"An error occurred while deleting the room.",t.value=!1})))};return function(e,a){return(0,l.wg)(),(0,l.iD)("div",null,[w.value?(0,l.kq)("",!0):((0,l.wg)(),(0,l.iD)("button",{key:0,type:"button",class:"btn btn-danger",onClick:(0,n.iM)(y,["prevent"])},"Delete Week")),(0,l._)("div",{ref_key:"modal",ref:f,class:"modal fade",tabindex:"-1",role:"dialog"},[(0,l._)("div",{class:(0,o.C_)(["modal-dialog",{"modal-confirm":w.value}]),role:"document"},[(0,l._)("div",d,[(0,l._)("div",s,[u,(0,l.wy)((0,l._)("div",c,r,512),[[n.F8,w.value]]),(0,l._)("h4",{class:"modal-title",textContent:(0,o.zw)(w.value?"Done!":"Delete Week")},null,8,v)]),(0,l._)("div",m,[w.value?((0,l.wg)(),(0,l.iD)("p",{key:0,class:"text-center",textContent:(0,o.zw)(h.value)},null,8,b)):((0,l.wg)(),(0,l.iD)("p",p,"Are you sure you want to remove this room. This action cannot be undone!"))]),(0,l._)("div",k,[(0,l._)("button",{type:"button",class:(0,o.C_)(["btn btn-default",{"btn-success btn-block":w.value}]),"data-dismiss":"modal",textContent:(0,o.zw)(w.value?"OK":"Close")},null,10,_),w.value?(0,l.kq)("",!0):((0,l.wg)(),(0,l.iD)("button",{key:0,type:"button",class:"btn btn-danger",onClick:(0,n.iM)(C,["prevent"]),disabled:t.value},"Delete Week ",8,g))])])],2)],512)])}}}}}]);