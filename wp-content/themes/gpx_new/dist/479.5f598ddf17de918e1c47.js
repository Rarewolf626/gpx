(self.webpackChunk=self.webpackChunk||[]).push([[479],{4073:(t,e,r)=>{var n=r(8453).Symbol;t.exports=n},6624:(t,e,r)=>{var n=r(4073),a=r(7915),i=r(4478),o=n?n.toStringTag:void 0;t.exports=function(t){return null==t?void 0===t?"[object Undefined]":"[object Null]":o&&o in Object(t)?a(t):i(t)}},8928:(t,e,r)=>{var n="object"==typeof r.g&&r.g&&r.g.Object===Object&&r.g;t.exports=n},7915:(t,e,r)=>{var n=r(4073),a=Object.prototype,i=a.hasOwnProperty,o=a.toString,s=n?n.toStringTag:void 0;t.exports=function(t){var e=i.call(t,s),r=t[s];try{t[s]=void 0;var n=!0}catch(t){}var a=o.call(t);return n&&(e?t[s]=r:delete t[s]),a}},4478:t=>{var e=Object.prototype.toString;t.exports=function(t){return e.call(t)}},8453:(t,e,r)=>{var n=r(8928),a="object"==typeof self&&self&&self.Object===Object&&self,i=n||a||Function("return this")();t.exports=i},6521:t=>{var e=Array.isArray;t.exports=e},2050:t=>{t.exports=function(t){return null!=t&&"object"==typeof t}},4239:(t,e,r)=>{var n=r(6624),a=r(6521),i=r(2050);t.exports=function(t){return"string"==typeof t||!a(t)&&i(t)&&"[object String]"==n(t)}},4377:(t,e,r)=>{"use strict";r.d(e,{qg:()=>Ot});var n=r(8449);let a={};function i(){return a}function o(){return Object.assign({},i())}const s={lessThanXSeconds:{one:"less than a second",other:"less than {{count}} seconds"},xSeconds:{one:"1 second",other:"{{count}} seconds"},halfAMinute:"half a minute",lessThanXMinutes:{one:"less than a minute",other:"less than {{count}} minutes"},xMinutes:{one:"1 minute",other:"{{count}} minutes"},aboutXHours:{one:"about 1 hour",other:"about {{count}} hours"},xHours:{one:"1 hour",other:"{{count}} hours"},xDays:{one:"1 day",other:"{{count}} days"},aboutXWeeks:{one:"about 1 week",other:"about {{count}} weeks"},xWeeks:{one:"1 week",other:"{{count}} weeks"},aboutXMonths:{one:"about 1 month",other:"about {{count}} months"},xMonths:{one:"1 month",other:"{{count}} months"},aboutXYears:{one:"about 1 year",other:"about {{count}} years"},xYears:{one:"1 year",other:"{{count}} years"},overXYears:{one:"over 1 year",other:"over {{count}} years"},almostXYears:{one:"almost 1 year",other:"almost {{count}} years"}};function u(t){return(e={})=>{const r=e.width?String(e.width):t.defaultWidth;return t.formats[r]||t.formats[t.defaultWidth]}}const d={date:u({formats:{full:"EEEE, MMMM do, y",long:"MMMM do, y",medium:"MMM d, y",short:"MM/dd/yyyy"},defaultWidth:"full"}),time:u({formats:{full:"h:mm:ss a zzzz",long:"h:mm:ss a z",medium:"h:mm:ss a",short:"h:mm a"},defaultWidth:"full"}),dateTime:u({formats:{full:"{{date}} 'at' {{time}}",long:"{{date}} 'at' {{time}}",medium:"{{date}}, {{time}}",short:"{{date}}, {{time}}"},defaultWidth:"full"})},c={lastWeek:"'last' eeee 'at' p",yesterday:"'yesterday at' p",today:"'today at' p",tomorrow:"'tomorrow at' p",nextWeek:"eeee 'at' p",other:"P"};function l(t){return(e,r)=>{let n;if("formatting"===(r?.context?String(r.context):"standalone")&&t.formattingValues){const e=t.defaultFormattingWidth||t.defaultWidth,a=r?.width?String(r.width):e;n=t.formattingValues[a]||t.formattingValues[e]}else{const e=t.defaultWidth,a=r?.width?String(r.width):t.defaultWidth;n=t.values[a]||t.values[e]}return n[t.argumentCallback?t.argumentCallback(e):e]}}function h(t){return(e,r={})=>{const n=r.width,a=n&&t.matchPatterns[n]||t.matchPatterns[t.defaultMatchWidth],i=e.match(a);if(!i)return null;const o=i[0],s=n&&t.parsePatterns[n]||t.parsePatterns[t.defaultParseWidth],u=Array.isArray(s)?function(t,e){for(let r=0;r<t.length;r++)if(e(t[r]))return r;return}(s,(t=>t.test(o))):function(t,e){for(const r in t)if(Object.prototype.hasOwnProperty.call(t,r)&&e(t[r]))return r;return}(s,(t=>t.test(o)));let d;d=t.valueCallback?t.valueCallback(u):u,d=r.valueCallback?r.valueCallback(d):d;return{value:d,rest:e.slice(o.length)}}}var m;const w={code:"en-US",formatDistance:(t,e,r)=>{let n;const a=s[t];return n="string"==typeof a?a:1===e?a.one:a.other.replace("{{count}}",e.toString()),r?.addSuffix?r.comparison&&r.comparison>0?"in "+n:n+" ago":n},formatLong:d,formatRelative:(t,e,r,n)=>c[t],localize:{ordinalNumber:(t,e)=>{const r=Number(t),n=r%100;if(n>20||n<10)switch(n%10){case 1:return r+"st";case 2:return r+"nd";case 3:return r+"rd"}return r+"th"},era:l({values:{narrow:["B","A"],abbreviated:["BC","AD"],wide:["Before Christ","Anno Domini"]},defaultWidth:"wide"}),quarter:l({values:{narrow:["1","2","3","4"],abbreviated:["Q1","Q2","Q3","Q4"],wide:["1st quarter","2nd quarter","3rd quarter","4th quarter"]},defaultWidth:"wide",argumentCallback:t=>t-1}),month:l({values:{narrow:["J","F","M","A","M","J","J","A","S","O","N","D"],abbreviated:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],wide:["January","February","March","April","May","June","July","August","September","October","November","December"]},defaultWidth:"wide"}),day:l({values:{narrow:["S","M","T","W","T","F","S"],short:["Su","Mo","Tu","We","Th","Fr","Sa"],abbreviated:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],wide:["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]},defaultWidth:"wide"}),dayPeriod:l({values:{narrow:{am:"a",pm:"p",midnight:"mi",noon:"n",morning:"morning",afternoon:"afternoon",evening:"evening",night:"night"},abbreviated:{am:"AM",pm:"PM",midnight:"midnight",noon:"noon",morning:"morning",afternoon:"afternoon",evening:"evening",night:"night"},wide:{am:"a.m.",pm:"p.m.",midnight:"midnight",noon:"noon",morning:"morning",afternoon:"afternoon",evening:"evening",night:"night"}},defaultWidth:"wide",formattingValues:{narrow:{am:"a",pm:"p",midnight:"mi",noon:"n",morning:"in the morning",afternoon:"in the afternoon",evening:"in the evening",night:"at night"},abbreviated:{am:"AM",pm:"PM",midnight:"midnight",noon:"noon",morning:"in the morning",afternoon:"in the afternoon",evening:"in the evening",night:"at night"},wide:{am:"a.m.",pm:"p.m.",midnight:"midnight",noon:"noon",morning:"in the morning",afternoon:"in the afternoon",evening:"in the evening",night:"at night"}},defaultFormattingWidth:"wide"})},match:{ordinalNumber:(m={matchPattern:/^(\d+)(th|st|nd|rd)?/i,parsePattern:/\d+/i,valueCallback:t=>parseInt(t,10)},(t,e={})=>{const r=t.match(m.matchPattern);if(!r)return null;const n=r[0],a=t.match(m.parsePattern);if(!a)return null;let i=m.valueCallback?m.valueCallback(a[0]):a[0];return i=e.valueCallback?e.valueCallback(i):i,{value:i,rest:t.slice(n.length)}}),era:h({matchPatterns:{narrow:/^(b|a)/i,abbreviated:/^(b\.?\s?c\.?|b\.?\s?c\.?\s?e\.?|a\.?\s?d\.?|c\.?\s?e\.?)/i,wide:/^(before christ|before common era|anno domini|common era)/i},defaultMatchWidth:"wide",parsePatterns:{any:[/^b/i,/^(a|c)/i]},defaultParseWidth:"any"}),quarter:h({matchPatterns:{narrow:/^[1234]/i,abbreviated:/^q[1234]/i,wide:/^[1234](th|st|nd|rd)? quarter/i},defaultMatchWidth:"wide",parsePatterns:{any:[/1/i,/2/i,/3/i,/4/i]},defaultParseWidth:"any",valueCallback:t=>t+1}),month:h({matchPatterns:{narrow:/^[jfmasond]/i,abbreviated:/^(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)/i,wide:/^(january|february|march|april|may|june|july|august|september|october|november|december)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^j/i,/^f/i,/^m/i,/^a/i,/^m/i,/^j/i,/^j/i,/^a/i,/^s/i,/^o/i,/^n/i,/^d/i],any:[/^ja/i,/^f/i,/^mar/i,/^ap/i,/^may/i,/^jun/i,/^jul/i,/^au/i,/^s/i,/^o/i,/^n/i,/^d/i]},defaultParseWidth:"any"}),day:h({matchPatterns:{narrow:/^[smtwf]/i,short:/^(su|mo|tu|we|th|fr|sa)/i,abbreviated:/^(sun|mon|tue|wed|thu|fri|sat)/i,wide:/^(sunday|monday|tuesday|wednesday|thursday|friday|saturday)/i},defaultMatchWidth:"wide",parsePatterns:{narrow:[/^s/i,/^m/i,/^t/i,/^w/i,/^t/i,/^f/i,/^s/i],any:[/^su/i,/^m/i,/^tu/i,/^w/i,/^th/i,/^f/i,/^sa/i]},defaultParseWidth:"any"}),dayPeriod:h({matchPatterns:{narrow:/^(a|p|mi|n|(in the|at) (morning|afternoon|evening|night))/i,any:/^([ap]\.?\s?m\.?|midnight|noon|(in the|at) (morning|afternoon|evening|night))/i},defaultMatchWidth:"any",parsePatterns:{any:{am:/^a/i,pm:/^p/i,midnight:/^mi/i,noon:/^no/i,morning:/morning/i,afternoon:/afternoon/i,evening:/evening/i,night:/night/i}},defaultParseWidth:"any"})},options:{weekStartsOn:0,firstWeekContainsDate:1}};var f=r(2849);const g=(t,e)=>{switch(t){case"P":return e.date({width:"short"});case"PP":return e.date({width:"medium"});case"PPP":return e.date({width:"long"});default:return e.date({width:"full"})}},y=(t,e)=>{switch(t){case"p":return e.time({width:"short"});case"pp":return e.time({width:"medium"});case"ppp":return e.time({width:"long"});default:return e.time({width:"full"})}},p={p:y,P:(t,e)=>{const r=t.match(/(P+)(p+)?/)||[],n=r[1],a=r[2];if(!a)return g(t,e);let i;switch(n){case"P":i=e.dateTime({width:"short"});break;case"PP":i=e.dateTime({width:"medium"});break;case"PPP":i=e.dateTime({width:"long"});break;default:i=e.dateTime({width:"full"})}return i.replace("{{date}}",g(n,e)).replace("{{time}}",y(a,e))}},b=/^D+$/,x=/^Y+$/,v=["D","DD","YY","YYYY"];function k(t){return b.test(t)}function T(t){return x.test(t)}function M(t,e,r){const n=function(t,e,r){const n="Y"===t[0]?"years":"days of the month";return`Use \`${t.toLowerCase()}\` instead of \`${t}\` (in \`${e}\`) for formatting ${n} to the input \`${r}\`; see: https://github.com/date-fns/date-fns/blob/master/docs/unicodeTokens.md`}(t,e,r);if(console.warn(n),v.includes(t))throw new RangeError(n)}function P(t,e){const r=e instanceof Date?(0,n.w)(e,0):new e(0);return r.setFullYear(t.getFullYear(),t.getMonth(),t.getDate()),r.setHours(t.getHours(),t.getMinutes(),t.getSeconds(),t.getMilliseconds()),r}class D{subPriority=0;validate(t,e){return!0}}class Y extends D{constructor(t,e,r,n,a){super(),this.value=t,this.validateValue=e,this.setValue=r,this.priority=n,a&&(this.subPriority=a)}validate(t,e){return this.validateValue(t,this.value,e)}set(t,e,r){return this.setValue(t,e,this.value,r)}}class S extends D{priority=10;subPriority=-1;set(t,e){return e.timestampIsSet?t:(0,n.w)(t,P(t,Date))}}class W{run(t,e,r,n){const a=this.parse(t,e,r,n);return a?{setter:new Y(a.value,this.validate,this.set,this.priority,this.subPriority),rest:a.rest}:null}validate(t,e,r){return!0}}var H=r(449);const q=/^(1[0-2]|0?\d)/,N=/^(3[0-1]|[0-2]?\d)/,C=/^(36[0-6]|3[0-5]\d|[0-2]?\d?\d)/,O=/^(5[0-3]|[0-4]?\d)/,F=/^(2[0-3]|[0-1]?\d)/,E=/^(2[0-4]|[0-1]?\d)/,j=/^(1[0-1]|0?\d)/,Q=/^(1[0-2]|0?\d)/,L=/^[0-5]?\d/,I=/^[0-5]?\d/,A=/^\d/,R=/^\d{1,2}/,X=/^\d{1,3}/,B=/^\d{1,4}/,G=/^-?\d+/,$=/^-?\d/,J=/^-?\d{1,2}/,V=/^-?\d{1,3}/,z=/^-?\d{1,4}/,K=/^([+-])(\d{2})(\d{2})?|Z/,Z=/^([+-])(\d{2})(\d{2})|Z/,U=/^([+-])(\d{2})(\d{2})((\d{2}))?|Z/,_=/^([+-])(\d{2}):(\d{2})|Z/,tt=/^([+-])(\d{2}):(\d{2})(:(\d{2}))?|Z/;function et(t,e){return t?{value:e(t.value),rest:t.rest}:t}function rt(t,e){const r=e.match(t);return r?{value:parseInt(r[0],10),rest:e.slice(r[0].length)}:null}function nt(t,e){const r=e.match(t);if(!r)return null;if("Z"===r[0])return{value:0,rest:e.slice(1)};const n="+"===r[1]?1:-1,a=r[2]?parseInt(r[2],10):0,i=r[3]?parseInt(r[3],10):0,o=r[5]?parseInt(r[5],10):0;return{value:n*(a*H.s0+i*H.Cg+o*H._m),rest:e.slice(r[0].length)}}function at(t){return rt(G,t)}function it(t,e){switch(t){case 1:return rt(A,e);case 2:return rt(R,e);case 3:return rt(X,e);case 4:return rt(B,e);default:return rt(new RegExp("^\\d{1,"+t+"}"),e)}}function ot(t,e){switch(t){case 1:return rt($,e);case 2:return rt(J,e);case 3:return rt(V,e);case 4:return rt(z,e);default:return rt(new RegExp("^-?\\d{1,"+t+"}"),e)}}function st(t){switch(t){case"morning":return 4;case"evening":return 17;case"pm":case"noon":case"afternoon":return 12;default:return 0}}function ut(t,e){const r=e>0,n=r?e:1-e;let a;if(n<=50)a=t||100;else{const e=n+50;a=t+100*Math.trunc(e/100)-(t>=e%100?100:0)}return r?a:1-a}function dt(t){return t%400==0||t%4==0&&t%100!=0}function ct(t,e){const r=i(),n=e?.weekStartsOn??e?.locale?.options?.weekStartsOn??r.weekStartsOn??r.locale?.options?.weekStartsOn??0,a=(0,f.a)(t),o=a.getDay(),s=(o<n?7:0)+o-n;return a.setDate(a.getDate()-s),a.setHours(0,0,0,0),a}function lt(t,e){const r=(0,f.a)(t),a=r.getFullYear(),o=i(),s=e?.firstWeekContainsDate??e?.locale?.options?.firstWeekContainsDate??o.firstWeekContainsDate??o.locale?.options?.firstWeekContainsDate??1,u=(0,n.w)(t,0);u.setFullYear(a+1,0,s),u.setHours(0,0,0,0);const d=ct(u,e),c=(0,n.w)(t,0);c.setFullYear(a,0,s),c.setHours(0,0,0,0);const l=ct(c,e);return r.getTime()>=d.getTime()?a+1:r.getTime()>=l.getTime()?a:a-1}function ht(t){return ct(t,{weekStartsOn:1})}function mt(t,e){const r=i(),a=e?.firstWeekContainsDate??e?.locale?.options?.firstWeekContainsDate??r.firstWeekContainsDate??r.locale?.options?.firstWeekContainsDate??1,o=lt(t,e),s=(0,n.w)(t,0);s.setFullYear(o,0,a),s.setHours(0,0,0,0);return ct(s,e)}function wt(t,e){const r=(0,f.a)(t),n=+ct(r,e)-+mt(r,e);return Math.round(n/H.my)+1}function ft(t,e,r){const n=(0,f.a)(t),a=wt(n,r)-e;return n.setDate(n.getDate()-7*a),n}function gt(t){const e=(0,f.a)(t),r=e.getFullYear(),a=(0,n.w)(t,0);a.setFullYear(r+1,0,4),a.setHours(0,0,0,0);const i=ht(a),o=(0,n.w)(t,0);o.setFullYear(r,0,4),o.setHours(0,0,0,0);const s=ht(o);return e.getTime()>=i.getTime()?r+1:e.getTime()>=s.getTime()?r:r-1}function yt(t){const e=gt(t),r=(0,n.w)(t,0);return r.setFullYear(e,0,4),r.setHours(0,0,0,0),ht(r)}function pt(t){const e=(0,f.a)(t),r=+ht(e)-+yt(e);return Math.round(r/H.my)+1}function bt(t,e){const r=(0,f.a)(t),n=pt(r)-e;return r.setDate(r.getDate()-7*n),r}const xt=[31,28,31,30,31,30,31,31,30,31,30,31],vt=[31,29,31,30,31,30,31,31,30,31,30,31];var kt=r(7434);function Tt(t,e,r){const n=i(),a=r?.weekStartsOn??r?.locale?.options?.weekStartsOn??n.weekStartsOn??n.locale?.options?.weekStartsOn??0,o=(0,f.a)(t),s=o.getDay(),u=7-a,d=e<0||e>6?e-(s+u)%7:((e%7+7)%7+u)%7-(s+u)%7;return(0,kt.f)(o,d)}function Mt(t){let e=(0,f.a)(t).getDay();return 0===e&&(e=7),e}function Pt(t,e){const r=(0,f.a)(t),n=e-Mt(r);return(0,kt.f)(r,n)}function Dt(t){const e=(0,f.a)(t),r=new Date(Date.UTC(e.getFullYear(),e.getMonth(),e.getDate(),e.getHours(),e.getMinutes(),e.getSeconds(),e.getMilliseconds()));return r.setUTCFullYear(e.getFullYear()),+t-+r}const Yt={G:new class extends W{priority=140;parse(t,e,r){switch(e){case"G":case"GG":case"GGG":return r.era(t,{width:"abbreviated"})||r.era(t,{width:"narrow"});case"GGGGG":return r.era(t,{width:"narrow"});default:return r.era(t,{width:"wide"})||r.era(t,{width:"abbreviated"})||r.era(t,{width:"narrow"})}}set(t,e,r){return e.era=r,t.setFullYear(r,0,1),t.setHours(0,0,0,0),t}incompatibleTokens=["R","u","t","T"]},y:new class extends W{priority=130;incompatibleTokens=["Y","R","u","w","I","i","e","c","t","T"];parse(t,e,r){const n=t=>({year:t,isTwoDigitYear:"yy"===e});switch(e){case"y":return et(it(4,t),n);case"yo":return et(r.ordinalNumber(t,{unit:"year"}),n);default:return et(it(e.length,t),n)}}validate(t,e){return e.isTwoDigitYear||e.year>0}set(t,e,r){const n=t.getFullYear();if(r.isTwoDigitYear){const e=ut(r.year,n);return t.setFullYear(e,0,1),t.setHours(0,0,0,0),t}const a="era"in e&&1!==e.era?1-r.year:r.year;return t.setFullYear(a,0,1),t.setHours(0,0,0,0),t}},Y:new class extends W{priority=130;parse(t,e,r){const n=t=>({year:t,isTwoDigitYear:"YY"===e});switch(e){case"Y":return et(it(4,t),n);case"Yo":return et(r.ordinalNumber(t,{unit:"year"}),n);default:return et(it(e.length,t),n)}}validate(t,e){return e.isTwoDigitYear||e.year>0}set(t,e,r,n){const a=lt(t,n);if(r.isTwoDigitYear){const e=ut(r.year,a);return t.setFullYear(e,0,n.firstWeekContainsDate),t.setHours(0,0,0,0),ct(t,n)}const i="era"in e&&1!==e.era?1-r.year:r.year;return t.setFullYear(i,0,n.firstWeekContainsDate),t.setHours(0,0,0,0),ct(t,n)}incompatibleTokens=["y","R","u","Q","q","M","L","I","d","D","i","t","T"]},R:new class extends W{priority=130;parse(t,e){return ot("R"===e?4:e.length,t)}set(t,e,r){const a=(0,n.w)(t,0);return a.setFullYear(r,0,4),a.setHours(0,0,0,0),ht(a)}incompatibleTokens=["G","y","Y","u","Q","q","M","L","w","d","D","e","c","t","T"]},u:new class extends W{priority=130;parse(t,e){return ot("u"===e?4:e.length,t)}set(t,e,r){return t.setFullYear(r,0,1),t.setHours(0,0,0,0),t}incompatibleTokens=["G","y","Y","R","w","I","i","e","c","t","T"]},Q:new class extends W{priority=120;parse(t,e,r){switch(e){case"Q":case"QQ":return it(e.length,t);case"Qo":return r.ordinalNumber(t,{unit:"quarter"});case"QQQ":return r.quarter(t,{width:"abbreviated",context:"formatting"})||r.quarter(t,{width:"narrow",context:"formatting"});case"QQQQQ":return r.quarter(t,{width:"narrow",context:"formatting"});default:return r.quarter(t,{width:"wide",context:"formatting"})||r.quarter(t,{width:"abbreviated",context:"formatting"})||r.quarter(t,{width:"narrow",context:"formatting"})}}validate(t,e){return e>=1&&e<=4}set(t,e,r){return t.setMonth(3*(r-1),1),t.setHours(0,0,0,0),t}incompatibleTokens=["Y","R","q","M","L","w","I","d","D","i","e","c","t","T"]},q:new class extends W{priority=120;parse(t,e,r){switch(e){case"q":case"qq":return it(e.length,t);case"qo":return r.ordinalNumber(t,{unit:"quarter"});case"qqq":return r.quarter(t,{width:"abbreviated",context:"standalone"})||r.quarter(t,{width:"narrow",context:"standalone"});case"qqqqq":return r.quarter(t,{width:"narrow",context:"standalone"});default:return r.quarter(t,{width:"wide",context:"standalone"})||r.quarter(t,{width:"abbreviated",context:"standalone"})||r.quarter(t,{width:"narrow",context:"standalone"})}}validate(t,e){return e>=1&&e<=4}set(t,e,r){return t.setMonth(3*(r-1),1),t.setHours(0,0,0,0),t}incompatibleTokens=["Y","R","Q","M","L","w","I","d","D","i","e","c","t","T"]},M:new class extends W{incompatibleTokens=["Y","R","q","Q","L","w","I","D","i","e","c","t","T"];priority=110;parse(t,e,r){const n=t=>t-1;switch(e){case"M":return et(rt(q,t),n);case"MM":return et(it(2,t),n);case"Mo":return et(r.ordinalNumber(t,{unit:"month"}),n);case"MMM":return r.month(t,{width:"abbreviated",context:"formatting"})||r.month(t,{width:"narrow",context:"formatting"});case"MMMMM":return r.month(t,{width:"narrow",context:"formatting"});default:return r.month(t,{width:"wide",context:"formatting"})||r.month(t,{width:"abbreviated",context:"formatting"})||r.month(t,{width:"narrow",context:"formatting"})}}validate(t,e){return e>=0&&e<=11}set(t,e,r){return t.setMonth(r,1),t.setHours(0,0,0,0),t}},L:new class extends W{priority=110;parse(t,e,r){const n=t=>t-1;switch(e){case"L":return et(rt(q,t),n);case"LL":return et(it(2,t),n);case"Lo":return et(r.ordinalNumber(t,{unit:"month"}),n);case"LLL":return r.month(t,{width:"abbreviated",context:"standalone"})||r.month(t,{width:"narrow",context:"standalone"});case"LLLLL":return r.month(t,{width:"narrow",context:"standalone"});default:return r.month(t,{width:"wide",context:"standalone"})||r.month(t,{width:"abbreviated",context:"standalone"})||r.month(t,{width:"narrow",context:"standalone"})}}validate(t,e){return e>=0&&e<=11}set(t,e,r){return t.setMonth(r,1),t.setHours(0,0,0,0),t}incompatibleTokens=["Y","R","q","Q","M","w","I","D","i","e","c","t","T"]},w:new class extends W{priority=100;parse(t,e,r){switch(e){case"w":return rt(O,t);case"wo":return r.ordinalNumber(t,{unit:"week"});default:return it(e.length,t)}}validate(t,e){return e>=1&&e<=53}set(t,e,r,n){return ct(ft(t,r,n),n)}incompatibleTokens=["y","R","u","q","Q","M","L","I","d","D","i","t","T"]},I:new class extends W{priority=100;parse(t,e,r){switch(e){case"I":return rt(O,t);case"Io":return r.ordinalNumber(t,{unit:"week"});default:return it(e.length,t)}}validate(t,e){return e>=1&&e<=53}set(t,e,r){return ht(bt(t,r))}incompatibleTokens=["y","Y","u","q","Q","M","L","w","d","D","e","c","t","T"]},d:new class extends W{priority=90;subPriority=1;parse(t,e,r){switch(e){case"d":return rt(N,t);case"do":return r.ordinalNumber(t,{unit:"date"});default:return it(e.length,t)}}validate(t,e){const r=dt(t.getFullYear()),n=t.getMonth();return r?e>=1&&e<=vt[n]:e>=1&&e<=xt[n]}set(t,e,r){return t.setDate(r),t.setHours(0,0,0,0),t}incompatibleTokens=["Y","R","q","Q","w","I","D","i","e","c","t","T"]},D:new class extends W{priority=90;subpriority=1;parse(t,e,r){switch(e){case"D":case"DD":return rt(C,t);case"Do":return r.ordinalNumber(t,{unit:"date"});default:return it(e.length,t)}}validate(t,e){return dt(t.getFullYear())?e>=1&&e<=366:e>=1&&e<=365}set(t,e,r){return t.setMonth(0,r),t.setHours(0,0,0,0),t}incompatibleTokens=["Y","R","q","Q","M","L","w","I","d","E","i","e","c","t","T"]},E:new class extends W{priority=90;parse(t,e,r){switch(e){case"E":case"EE":case"EEE":return r.day(t,{width:"abbreviated",context:"formatting"})||r.day(t,{width:"short",context:"formatting"})||r.day(t,{width:"narrow",context:"formatting"});case"EEEEE":return r.day(t,{width:"narrow",context:"formatting"});case"EEEEEE":return r.day(t,{width:"short",context:"formatting"})||r.day(t,{width:"narrow",context:"formatting"});default:return r.day(t,{width:"wide",context:"formatting"})||r.day(t,{width:"abbreviated",context:"formatting"})||r.day(t,{width:"short",context:"formatting"})||r.day(t,{width:"narrow",context:"formatting"})}}validate(t,e){return e>=0&&e<=6}set(t,e,r,n){return(t=Tt(t,r,n)).setHours(0,0,0,0),t}incompatibleTokens=["D","i","e","c","t","T"]},e:new class extends W{priority=90;parse(t,e,r,n){const a=t=>{const e=7*Math.floor((t-1)/7);return(t+n.weekStartsOn+6)%7+e};switch(e){case"e":case"ee":return et(it(e.length,t),a);case"eo":return et(r.ordinalNumber(t,{unit:"day"}),a);case"eee":return r.day(t,{width:"abbreviated",context:"formatting"})||r.day(t,{width:"short",context:"formatting"})||r.day(t,{width:"narrow",context:"formatting"});case"eeeee":return r.day(t,{width:"narrow",context:"formatting"});case"eeeeee":return r.day(t,{width:"short",context:"formatting"})||r.day(t,{width:"narrow",context:"formatting"});default:return r.day(t,{width:"wide",context:"formatting"})||r.day(t,{width:"abbreviated",context:"formatting"})||r.day(t,{width:"short",context:"formatting"})||r.day(t,{width:"narrow",context:"formatting"})}}validate(t,e){return e>=0&&e<=6}set(t,e,r,n){return(t=Tt(t,r,n)).setHours(0,0,0,0),t}incompatibleTokens=["y","R","u","q","Q","M","L","I","d","D","E","i","c","t","T"]},c:new class extends W{priority=90;parse(t,e,r,n){const a=t=>{const e=7*Math.floor((t-1)/7);return(t+n.weekStartsOn+6)%7+e};switch(e){case"c":case"cc":return et(it(e.length,t),a);case"co":return et(r.ordinalNumber(t,{unit:"day"}),a);case"ccc":return r.day(t,{width:"abbreviated",context:"standalone"})||r.day(t,{width:"short",context:"standalone"})||r.day(t,{width:"narrow",context:"standalone"});case"ccccc":return r.day(t,{width:"narrow",context:"standalone"});case"cccccc":return r.day(t,{width:"short",context:"standalone"})||r.day(t,{width:"narrow",context:"standalone"});default:return r.day(t,{width:"wide",context:"standalone"})||r.day(t,{width:"abbreviated",context:"standalone"})||r.day(t,{width:"short",context:"standalone"})||r.day(t,{width:"narrow",context:"standalone"})}}validate(t,e){return e>=0&&e<=6}set(t,e,r,n){return(t=Tt(t,r,n)).setHours(0,0,0,0),t}incompatibleTokens=["y","R","u","q","Q","M","L","I","d","D","E","i","e","t","T"]},i:new class extends W{priority=90;parse(t,e,r){const n=t=>0===t?7:t;switch(e){case"i":case"ii":return it(e.length,t);case"io":return r.ordinalNumber(t,{unit:"day"});case"iii":return et(r.day(t,{width:"abbreviated",context:"formatting"})||r.day(t,{width:"short",context:"formatting"})||r.day(t,{width:"narrow",context:"formatting"}),n);case"iiiii":return et(r.day(t,{width:"narrow",context:"formatting"}),n);case"iiiiii":return et(r.day(t,{width:"short",context:"formatting"})||r.day(t,{width:"narrow",context:"formatting"}),n);default:return et(r.day(t,{width:"wide",context:"formatting"})||r.day(t,{width:"abbreviated",context:"formatting"})||r.day(t,{width:"short",context:"formatting"})||r.day(t,{width:"narrow",context:"formatting"}),n)}}validate(t,e){return e>=1&&e<=7}set(t,e,r){return(t=Pt(t,r)).setHours(0,0,0,0),t}incompatibleTokens=["y","Y","u","q","Q","M","L","w","d","D","E","e","c","t","T"]},a:new class extends W{priority=80;parse(t,e,r){switch(e){case"a":case"aa":case"aaa":return r.dayPeriod(t,{width:"abbreviated",context:"formatting"})||r.dayPeriod(t,{width:"narrow",context:"formatting"});case"aaaaa":return r.dayPeriod(t,{width:"narrow",context:"formatting"});default:return r.dayPeriod(t,{width:"wide",context:"formatting"})||r.dayPeriod(t,{width:"abbreviated",context:"formatting"})||r.dayPeriod(t,{width:"narrow",context:"formatting"})}}set(t,e,r){return t.setHours(st(r),0,0,0),t}incompatibleTokens=["b","B","H","k","t","T"]},b:new class extends W{priority=80;parse(t,e,r){switch(e){case"b":case"bb":case"bbb":return r.dayPeriod(t,{width:"abbreviated",context:"formatting"})||r.dayPeriod(t,{width:"narrow",context:"formatting"});case"bbbbb":return r.dayPeriod(t,{width:"narrow",context:"formatting"});default:return r.dayPeriod(t,{width:"wide",context:"formatting"})||r.dayPeriod(t,{width:"abbreviated",context:"formatting"})||r.dayPeriod(t,{width:"narrow",context:"formatting"})}}set(t,e,r){return t.setHours(st(r),0,0,0),t}incompatibleTokens=["a","B","H","k","t","T"]},B:new class extends W{priority=80;parse(t,e,r){switch(e){case"B":case"BB":case"BBB":return r.dayPeriod(t,{width:"abbreviated",context:"formatting"})||r.dayPeriod(t,{width:"narrow",context:"formatting"});case"BBBBB":return r.dayPeriod(t,{width:"narrow",context:"formatting"});default:return r.dayPeriod(t,{width:"wide",context:"formatting"})||r.dayPeriod(t,{width:"abbreviated",context:"formatting"})||r.dayPeriod(t,{width:"narrow",context:"formatting"})}}set(t,e,r){return t.setHours(st(r),0,0,0),t}incompatibleTokens=["a","b","t","T"]},h:new class extends W{priority=70;parse(t,e,r){switch(e){case"h":return rt(Q,t);case"ho":return r.ordinalNumber(t,{unit:"hour"});default:return it(e.length,t)}}validate(t,e){return e>=1&&e<=12}set(t,e,r){const n=t.getHours()>=12;return n&&r<12?t.setHours(r+12,0,0,0):n||12!==r?t.setHours(r,0,0,0):t.setHours(0,0,0,0),t}incompatibleTokens=["H","K","k","t","T"]},H:new class extends W{priority=70;parse(t,e,r){switch(e){case"H":return rt(F,t);case"Ho":return r.ordinalNumber(t,{unit:"hour"});default:return it(e.length,t)}}validate(t,e){return e>=0&&e<=23}set(t,e,r){return t.setHours(r,0,0,0),t}incompatibleTokens=["a","b","h","K","k","t","T"]},K:new class extends W{priority=70;parse(t,e,r){switch(e){case"K":return rt(j,t);case"Ko":return r.ordinalNumber(t,{unit:"hour"});default:return it(e.length,t)}}validate(t,e){return e>=0&&e<=11}set(t,e,r){return t.getHours()>=12&&r<12?t.setHours(r+12,0,0,0):t.setHours(r,0,0,0),t}incompatibleTokens=["h","H","k","t","T"]},k:new class extends W{priority=70;parse(t,e,r){switch(e){case"k":return rt(E,t);case"ko":return r.ordinalNumber(t,{unit:"hour"});default:return it(e.length,t)}}validate(t,e){return e>=1&&e<=24}set(t,e,r){const n=r<=24?r%24:r;return t.setHours(n,0,0,0),t}incompatibleTokens=["a","b","h","H","K","t","T"]},m:new class extends W{priority=60;parse(t,e,r){switch(e){case"m":return rt(L,t);case"mo":return r.ordinalNumber(t,{unit:"minute"});default:return it(e.length,t)}}validate(t,e){return e>=0&&e<=59}set(t,e,r){return t.setMinutes(r,0,0),t}incompatibleTokens=["t","T"]},s:new class extends W{priority=50;parse(t,e,r){switch(e){case"s":return rt(I,t);case"so":return r.ordinalNumber(t,{unit:"second"});default:return it(e.length,t)}}validate(t,e){return e>=0&&e<=59}set(t,e,r){return t.setSeconds(r,0),t}incompatibleTokens=["t","T"]},S:new class extends W{priority=30;parse(t,e){return et(it(e.length,t),(t=>Math.trunc(t*Math.pow(10,3-e.length))))}set(t,e,r){return t.setMilliseconds(r),t}incompatibleTokens=["t","T"]},X:new class extends W{priority=10;parse(t,e){switch(e){case"X":return nt(K,t);case"XX":return nt(Z,t);case"XXXX":return nt(U,t);case"XXXXX":return nt(tt,t);default:return nt(_,t)}}set(t,e,r){return e.timestampIsSet?t:(0,n.w)(t,t.getTime()-Dt(t)-r)}incompatibleTokens=["t","T","x"]},x:new class extends W{priority=10;parse(t,e){switch(e){case"x":return nt(K,t);case"xx":return nt(Z,t);case"xxxx":return nt(U,t);case"xxxxx":return nt(tt,t);default:return nt(_,t)}}set(t,e,r){return e.timestampIsSet?t:(0,n.w)(t,t.getTime()-Dt(t)-r)}incompatibleTokens=["t","T","X"]},t:new class extends W{priority=40;parse(t){return at(t)}set(t,e,r){return[(0,n.w)(t,1e3*r),{timestampIsSet:!0}]}incompatibleTokens="*"},T:new class extends W{priority=20;parse(t){return at(t)}set(t,e,r){return[(0,n.w)(t,r),{timestampIsSet:!0}]}incompatibleTokens="*"}},St=/[yYQqMLwIdDecihHKkms]o|(\w)\1*|''|'(''|[^'])+('|$)|./g,Wt=/P+p+|P+|p+|''|'(''|[^'])+('|$)|./g,Ht=/^'([^]*?)'?$/,qt=/''/g,Nt=/\S/,Ct=/[a-zA-Z]/;function Ot(t,e,r,a){const i=o(),s=a?.locale??i.locale??w,u=a?.firstWeekContainsDate??a?.locale?.options?.firstWeekContainsDate??i.firstWeekContainsDate??i.locale?.options?.firstWeekContainsDate??1,d=a?.weekStartsOn??a?.locale?.options?.weekStartsOn??i.weekStartsOn??i.locale?.options?.weekStartsOn??0;if(""===e)return""===t?(0,f.a)(r):(0,n.w)(r,NaN);const c={firstWeekContainsDate:u,weekStartsOn:d,locale:s},l=[new S],h=e.match(Wt).map((t=>{const e=t[0];if(e in p){return(0,p[e])(t,s.formatLong)}return t})).join("").match(St),m=[];for(let i of h){!a?.useAdditionalWeekYearTokens&&T(i)&&M(i,e,t),!a?.useAdditionalDayOfYearTokens&&k(i)&&M(i,e,t);const o=i[0],u=Yt[o];if(u){const{incompatibleTokens:e}=u;if(Array.isArray(e)){const t=m.find((t=>e.includes(t.token)||t.token===o));if(t)throw new RangeError(`The format string mustn't contain \`${t.fullToken}\` and \`${i}\` at the same time`)}else if("*"===u.incompatibleTokens&&m.length>0)throw new RangeError(`The format string mustn't contain \`${i}\` and any other token at the same time`);m.push({token:o,fullToken:i});const a=u.run(t,i,s.match,c);if(!a)return(0,n.w)(r,NaN);l.push(a.setter),t=a.rest}else{if(o.match(Ct))throw new RangeError("Format string contains an unescaped latin alphabet character `"+o+"`");if("''"===i?i="'":"'"===o&&(i=i.match(Ht)[1].replace(qt,"'")),0!==t.indexOf(i))return(0,n.w)(r,NaN);t=t.slice(i.length)}}if(t.length>0&&Nt.test(t))return(0,n.w)(r,NaN);const g=l.map((t=>t.priority)).sort(((t,e)=>e-t)).filter(((t,e,r)=>r.indexOf(t)===e)).map((t=>l.filter((e=>e.priority===t)).sort(((t,e)=>e.subPriority-t.subPriority)))).map((t=>t[0]));let y=(0,f.a)(r);if(isNaN(y.getTime()))return(0,n.w)(r,NaN);const b={};for(const t of g){if(!t.validate(y,c))return(0,n.w)(r,NaN);const e=t.set(y,b,c);Array.isArray(e)?(y=e[0],Object.assign(b,e[1])):y=e}return(0,n.w)(r,y)}}}]);