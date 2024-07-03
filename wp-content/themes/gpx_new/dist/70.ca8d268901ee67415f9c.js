"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[70],{5130:(e,t,n)=>{n.d(t,{Ft:()=>s,ZP:()=>o});var i=n(6549),a=n.n(i),l=n(661);function o(e){return e?(a()(e)&&(e=function(e,t){return e?t?(0,l.Qc)(e,t,new Date):/^\d{4}-\d{2}-\d{2}$/i.test(e)?(0,l.Qc)(e,"yyyy-MM-dd",new Date):/^\d{2}\/\d{2}\/\d{4}$/i.test(e)?(0,l.Qc)(e,"MM/dd/yyyy",new Date):new Date(Date.parse(e)):""}(e)),new Intl.DateTimeFormat("en-US",{year:"numeric",month:"long",day:"numeric"}).format(e)):""}function s(e){var t=new Date(1e3*e);return new Intl.DateTimeFormat("en-US",{year:"numeric",month:"numeric",day:"numeric"}).format(t)}},7070:(e,t,n)=>{n.r(t),n.d(t,{default:()=>oe});var i=n(9026),a=n(467),l=n(7401),o=n(7885),s=n(5130),r={class:"exchange-credit p-7"},u={class:"exchange-credit-content"},c={key:0,class:"exchange-result"},d=[(0,i._)("h2",null,"Exchange Credit",-1),(0,i._)("p",null," Our records indicate that you do not have a current deposit with GPX; however this exchange will be performed, in good faith, and in-lieu of a deposit/banking of a week. Please select Deposit A Week from your Dashboard after your booking is complete. If you have already deposited your week it can take up to 48-72 hours for our team to verify the transaction. Should GPX have questions we will contact you within 24 business hours. Please note: if a deposit cannot be completed in 5 business days this exchange transaction will be cancelled. ",-1)],p={key:1},_=(0,i._)("hgroup",null,[(0,i._)("h2",null,"Exchange Credit"),(0,i._)("p",null,"Choose an exchange credit to use for this exchange booking.")],-1),h={class:"checkout__exchangelist checkout__exchangelist--deposit"},v=["data-id"],g={class:"bank-row checkout__exchangelist__item__selector"},k=["value","onUpdate:modelValue","aria-describedby"],w=["id"],y={class:"checkout__exchangelist__item__details"},m=(0,i._)("strong",null,"Expires:",-1),f=(0,i._)("strong",null,"Entitlement Year:",-1),b=(0,i._)("strong",null,"Size:",-1),x={key:0,class:"checkout__exchangelist__item__upgradefee"},D=[(0,i._)("div",null,[(0,i._)("strong",null,"Please note:")],-1),(0,i._)("div",{style:{"font-size":"15px"}},"This booking requires an upgrade fee.",-1)],C={key:0},U=["textContent"],q={class:"mt-7"},z=(0,i._)("hgroup",null,[(0,i._)("h2",null,"Use New Deposit"),(0,i._)("p",null,"Select the week you would like to deposit as credit for this exchange.")],-1),P={name:"exchangendeposit",id:"exchangendeposit"},M={ref:"deposits",class:"checkout__exchangelist checkout__exchangelist--deposit"},S={class:"bank-row"},T=["value"],F={class:"bank-row"},Y=["textContent"],G={key:0},I=(0,i._)("a",{href:"tel:+18775667519"},"(877) 566-7519",-1),V={key:1},A=[(0,i._)("div",{class:"bank-row",style:{"margin-bottom":"5px"}},[(0,i._)("span",{class:"dgt-btn bank-select"},"Select")],-1)],H={class:"bank-row"},E=[(0,i._)("option",{value:"studio"}," Studio ",-1),(0,i._)("option",{value:"1"},"1br",-1),(0,i._)("option",{value:"2"},"2br",-1),(0,i._)("option",{value:"3"},"3br",-1)],L=["textContent"],N={key:2,class:"bank-row"},R={key:3,class:"bank-row"},W={key:4,class:"bank-row"},B=["min"],Q=["required"],X={key:0},K=[(0,i._)("div",null,[(0,i._)("strong",null,"Please note:")],-1),(0,i._)("div",{style:{"font-size":"15px"}},"This booking requires an upgrade fee. ",-1)],Z=(0,i._)("p",{id:"floatDisc",style:{"font-size":"18px","margin-top":"35px"}}," *Float reservations must be made with your home resort prior to deposit. Deposit transactions will automatically be system verified. Unverified deposits may result in the cancellation of exchange reservations. ",-1),$={class:"exchange-submit p-7"},j={class:"exchange-submit-content"},O=(0,i._)("h2",{class:"text-center mb-7"},"Let’s Get Started",-1),J={class:"exchange-submit-grid"},ee=(0,i._)("div",{class:"exchange-submit-text p-7"},[(0,i._)("p",null," Give us around 72 hours for your Savings Credits to show up in your account. "),(0,i._)("p",null," A request to convert a week Deposited to GPX Perks Savings Credits is pending confirmation that the Maintenance Fee for the week deposited is paid in full. The amount of Savings Credits awarded is 2x the value of your annual maintenance fee. "),(0,i._)("p",{class:"pb-7"},[(0,i._)("a",{href:"/perksterms/",target:"_blank",rel:"noopener noreferrer"},"Click here"),(0,i.Uk)(" to learn more about the full terms and condition. ")])],-1),te={class:"exchange-submit-agree p-7"},ne=(0,i._)("p",null,"Yes, let’s exchange my Deposit for Savings Credits.",-1),ie={for:"ice-checkbox"},ae=["disabled"],le=(0,i._)("button",{class:"exchange-submit-button p-7",type:"submit"},[(0,i._)("div",null,"Submit"),(0,i._)("div",null,[(0,i._)("figure",null,[(0,i._)("img",{decoding:"async",src:"/wp-content/uploads/2021/01/checkmark-60x60.png",width:"60",height:"60",alt:"checkmark",title:"checkmark",loading:"lazy"})])])],-1);const oe={__name:"ExchangeCredit",props:{credits:Array,ownerships:Array},setup:function(e){var t=(0,o.iH)(!1),n=(0,o.iH)(!1),oe=(0,o.iH)({deposit:null,credit:null,type:null,checkin:null,reservation:null,unit_type:null,coupon:null}),se=(0,o.iH)(!1),re=(0,i.Fl)((function(){return(null!==oe.value.credit||null!==oe.value.deposit)&&!(oe.value.deposit&&!oe.value.checkin)}));(0,i.YP)((function(){return oe.value.credit}),(function(e){e&&ue(e,"credit")})),(0,i.YP)((function(){return oe.value.deposit}),(function(e){e&&ue(e,"deposit")}));var ue=function(e,t){oe.value.checkin=null,oe.value.reservation=null,oe.value.unit_type=null,oe.value.coupon=null,e?"deposit"===t?(oe.value.credit=null,oe.value.type="deposit"):(oe.value.deposit=null,oe.value.type="credit"):(oe.value.credit=null,oe.value.deposit=null,oe.value.type=null,oe.value.tp_fee_enabled=!1)},ce=function(){null!==oe.value.credit||null!==oe.value.deposit?!oe.value.deposit||oe.value.checkin?n.value?(t.value=!0,axios.post(window.gpx_base.url_ajax+"?action=gpx_credit_transfer",oe.value).then((function(e){t.value=!1,e.data.success?window.alertModal.alert("Deposit exchange successful.",!1,(function(){window.location.reload()})):window.alertModal.alert("Deposit exchange failed.")})).catch((function(e){t.value=!1;var n=e.response.data.message||"Could not complete the exchange.";window.alertModal.alert(n)}))):window.alertModal.alert("Please confirm that you agree to the terms and conditions."):window.alertModal.alert("Please select a checkin date for your deposit."):window.alertModal.alert("Please select a deposit to exchange.")};return function(t,ue){return(0,i.wg)(),(0,i.iD)("form",{class:"perks-exchange",onSubmit:(0,a.iM)(ce,["prevent"]),novalidate:""},[(0,i._)("div",null,[(0,i._)("div",r,[(0,i._)("div",u,[0===e.credits.length?((0,i.wg)(),(0,i.iD)("div",c,d)):((0,i.wg)(),(0,i.iD)("div",p,[_,(0,i._)("div",h,[((0,i.wg)(!0),(0,i.iD)(i.HY,null,(0,i.Ko)(e.credits,(function(e){return(0,i.wg)(),(0,i.iD)("label",{key:e.id,"data-id":e.id,class:(0,l.C_)(["checkout__exchangelist__item",{selected:oe.value.credit===e.id}])},[(0,i._)("div",g,[(0,i.wy)((0,i._)("input",{type:"radio",class:"exchange-credit-check if-perks-ownership",value:e.id,"onUpdate:modelValue":function(e){return oe.value.credit=e},name:"deposit[credit]","aria-describedby":"exchange-credit-label-".concat(e.id)},null,8,k),[[a.G2,oe.value.credit,void 0,{number:!0}]]),(0,i._)("span",{class:"checkout__exchangelist__item__label",id:"exchange-credit-label-".concat(e.id)},"Apply Credit",8,w)]),(0,i._)("ul",y,[(0,i._)("li",null,[(0,i._)("strong",null,(0,l.zw)(e.resort),1)]),(0,i._)("li",null,[m,(0,i.Uk)(" "+(0,l.zw)((0,o.SU)(s.ZP)(e.expires)),1)]),(0,i._)("li",null,[f,(0,i.Uk)(" "+(0,l.zw)(e.year),1)]),(0,i._)("li",null,[b,(0,i.Uk)(" "+(0,l.zw)(e.size),1)])]),e.upgradeFee>0?((0,i.wg)(),(0,i.iD)("div",x,D)):(0,i.kq)("",!0)],10,v)})),128))]),e.ownerships.length>0?((0,i.wg)(),(0,i.iD)("div",C,[(0,i.Uk)(" Don't see the credit you want to use? "),(0,i._)("a",{href:"#useDeposit",style:{color:"#009ad6"},onClick:ue[0]||(ue[0]=(0,a.iM)((function(e){return se.value=!se.value}),["prevent"]))}," Click here "),(0,i.Uk)(" to "),(0,i._)("span",{textContent:(0,l.zw)(se.value?"hide":"show")},null,8,U),(0,i.Uk)(" additional weeks to deposit and use for this booking. ")])):(0,i.kq)("",!0)])),(0,i.wy)((0,i._)("div",q,[z,(0,i._)("div",P,[(0,i._)("div",M,[((0,i.wg)(!0),(0,i.iD)(i.HY,null,(0,i.Ko)(e.ownerships,(function(e){return(0,i.wg)(),(0,i.iD)("label",{key:e.id,class:(0,l.C_)(["checkout__exchangelist__item",{selected:oe.value.deposit===e.id}])},[(0,i._)("div",null,[(0,i._)("div",S,[(0,i.wy)((0,i._)("input",{type:"radio",class:"exchange-credit-check if-perks-ownership",value:e.id,"onUpdate:modelValue":ue[1]||(ue[1]=function(e){return oe.value.deposit=e}),name:"deposit[deposit]"},null,8,T),[[a.G2,oe.value.deposit,void 0,{number:!0}]])]),(0,i._)("div",F,[(0,i._)("h3",{textContent:(0,l.zw)(e.ResortName)},null,8,Y)]),e.is_delinquent?((0,i.wg)(),(0,i.iD)("strong",G,[(0,i.Uk)("Please contact us at "),I,(0,i.Uk)(" to use this deposit.")])):((0,i.wg)(),(0,i.iD)("div",V,A)),(0,i._)("div",H,[(0,i.Uk)(" Unit Type: "),e.defaultUpgrade?(0,i.wy)(((0,i.wg)(),(0,i.iD)("select",{key:0,name:"deposit[unit_type]",class:(0,l.C_)(["sel_unit_type doe",{invisible:oe.value.deposit!==e.id}]),"onUpdate:modelValue":ue[2]||(ue[2]=function(e){return oe.value.unit_type=e})},E,2)),[[a.bM,oe.value.unit_type]]):((0,i.wg)(),(0,i.iD)("span",{key:1,textContent:(0,l.zw)(e.Room_Type__c)},null,8,L))]),e.Week_Type__c?((0,i.wg)(),(0,i.iD)("div",N," Week Type: "+(0,l.zw)(e.Week_Type__c),1)):(0,i.kq)("",!0),e.Contract_ID__c?((0,i.wg)(),(0,i.iD)("div",R," Resort Member Number: "+(0,l.zw)(e.Contract_ID__c),1)):(0,i.kq)("",!0),e.Year_Last_Banked__c?((0,i.wg)(),(0,i.iD)("div",W," Last Year Banked: "+(0,l.zw)(e.Year_Last_Banked__c),1)):(0,i.kq)("",!0)]),(0,i._)("div",null,[(0,i._)("div",{class:(0,l.C_)(["bank-row",{invisible:oe.value.deposit!==e.id}]),style:{"margin-top":"10px"}},[e.is_delinquent?(0,i.kq)("",!0):(0,i.wy)(((0,i.wg)(),(0,i.iD)("input",{key:0,type:"date",placeholder:"Check In Date",class:"form-control",name:"deposit[checkin]",min:e.next_year,"onUpdate:modelValue":ue[3]||(ue[3]=function(e){return oe.value.checkin=e}),required:""},null,8,B)),[[a.nr,oe.value.checkin]])],2),(0,i._)("div",{class:(0,l.C_)(["reswrap",{invisible:oe.value.deposit!==e.id}])},[(0,i.wy)((0,i._)("input",{type:"text",placeholder:"Reservation Number",class:"form-control",name:"deposit[reservation]","onUpdate:modelValue":ue[4]||(ue[4]=function(e){return oe.value.reservation=e}),required:!e.gpr},null,8,Q),[[a.nr,oe.value.reservation,void 0,{trim:!0}]])],2),e.upgradeFee>0||e.defaultUpgrade?((0,i.wg)(),(0,i.iD)("div",X,K)):(0,i.kq)("",!0)])],2)})),128))],512)]),Z],512),[[a.F8,se.value||0===e.credits.length]])])]),(0,i._)("div",$,[(0,i._)("div",j,[O,(0,i._)("div",J,[ee,(0,i._)("div",te,[ne,(0,i._)("label",ie,[(0,i.wy)((0,i._)("input",{type:"checkbox",class:"checkbox-agree","onUpdate:modelValue":ue[5]||(ue[5]=function(e){return n.value=e}),disabled:!re.value},null,8,ae),[[a.e8,n.value]]),(0,i.Uk)(" I Agree. ")])]),le])])])])],32)}}}}}]);