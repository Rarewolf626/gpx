"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[95],{5476:(e,a,t)=>{t.r(a),t.d(a,{default:()=>f});var l=t(7829),o=t(3102),n=t(9608),u=t(6685),d={class:"modal-content"},s={class:"modal-header"},i=(0,l.Lk)("button",{type:"button",class:"close","data-dismiss":"modal","aria-label":"Close"},[(0,l.Lk)("span",{"aria-hidden":"true"},"×")],-1),c={class:"icon-box"},r=[(0,l.Lk)("i",{class:"material-icons"},"",-1)],v=["textContent"],m={class:"modal-body"},k=["textContent"],b={key:1},p={class:"modal-footer"},C=["textContent"],h=["disabled"];const f={__name:"DeleteWeek",props:{week_id:{type:Number,required:!0}},setup:function(e){var a=e,t=(0,u.KR)(!1),f=(0,u.KR)(!1),y=(0,u.KR)(""),g=(0,u.KR)(null),x=function(){jQuery(g.value).modal("show")},L=function(){f.value||t.value||(t.value=!0,y.value="",axios.delete("/gpxadmin/room/delete",{params:{id:a.week_id}}).then((function(e){var a,l;e.data.success?(y.value=(null===(a=e.data)||void 0===a?void 0:a.message)||"Room archived Successfully.",f.value=!0,jQuery(g.value).on("hidden.bs.modal",(function(){window.location=e.data.redirect||"/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_all"}))):(y.value=(null===(l=e.data)||void 0===l?void 0:l.message)||"Failed to archive the room.",t.value=!1)})).catch((function(e){var a;y.value=(null===(a=e.response)||void 0===a||null===(a=a.data)||void 0===a?void 0:a.message)||"An error occurred while deleting the room.",t.value=!1})))};return function(e,a){return(0,l.uX)(),(0,l.CE)("div",null,[f.value?(0,l.Q3)("",!0):((0,l.uX)(),(0,l.CE)("button",{key:0,type:"button",class:"btn btn-danger",onClick:(0,o.D$)(x,["prevent"])},"Delete Week")),(0,l.Lk)("div",{ref_key:"modal",ref:g,class:"modal fade",tabindex:"-1",role:"dialog"},[(0,l.Lk)("div",{class:(0,n.C4)(["modal-dialog",{"modal-confirm":f.value}]),role:"document"},[(0,l.Lk)("div",d,[(0,l.Lk)("div",s,[i,(0,l.bo)((0,l.Lk)("div",c,r,512),[[o.aG,f.value]]),(0,l.Lk)("h4",{class:"modal-title",textContent:(0,n.v_)(f.value?"Done!":"Delete Week")},null,8,v)]),(0,l.Lk)("div",m,[f.value?((0,l.uX)(),(0,l.CE)("p",{key:0,class:"text-center",textContent:(0,n.v_)(y.value)},null,8,k)):((0,l.uX)(),(0,l.CE)("p",b,"Are you sure you want to remove this room. This action cannot be undone!"))]),(0,l.Lk)("div",p,[(0,l.Lk)("button",{type:"button",class:(0,n.C4)(["btn btn-default",{"btn-success btn-block":f.value}]),"data-dismiss":"modal",textContent:(0,n.v_)(f.value?"OK":"Close")},null,10,C),f.value?(0,l.Q3)("",!0):((0,l.uX)(),(0,l.CE)("button",{key:0,type:"button",class:"btn btn-danger",onClick:(0,o.D$)(L,["prevent"]),disabled:t.value},"Delete Week ",8,h))])])],2)],512)])}}}}}]);