/*
 * Copyright (c) 2011, Yahoo! Inc.  All rights reserved.
 * Copyrights licensed under the BSD License. See the accompanying LICENSE file for terms.
 */
(function(a){var e,c,b,g=a.document;if(typeof BOOMR==="undefined"){BOOMR={}}if(BOOMR.version){return}BOOMR.version="0.9";e={beacon_url:"",site_domain:a.location.hostname.replace(/.*?([^.]+\.[^.]+)\.?$/,"$1").toLowerCase(),user_ip:"",events:{page_ready:[],page_unload:[],before_beacon:[]},vars:{},disabled_plugins:{},fireEvent:function(d,l){var j,k,m;if(!this.events.hasOwnProperty(d)){return false}m=this.events[d];for(j=0;j<m.length;j++){k=m[j];k[0].call(k[2],l,k[1])}return true},addListener:function(i,j,h,d){if(i.addEventListener){i.addEventListener(j,h,(d))}else{if(i.attachEvent){i.attachEvent("on"+j,h)}}}};c={utils:{getCookie:function(d){if(!d){return null}d=" "+d+"=";var h,j;j=" "+g.cookie+";";if((h=j.indexOf(d))>=0){h+=d.length;j=j.substring(h,j.indexOf(";",h));return j}return null},setCookie:function(h,d,n,r,l,m){var q="",j,p,o,i="";if(!h){return false}for(j in d){if(d.hasOwnProperty(j)){q+="&"+encodeURIComponent(j)+"="+encodeURIComponent(d[j])}}q=q.replace(/^&/,"");if(n){i=new Date();i.setTime(i.getTime()+n*1000);i=i.toGMTString()}p=h+"="+q;o=p+((n)?"; expires="+i:"")+((r)?"; path="+r:"")+((typeof l!=="undefined")?"; domain="+(l!==null?l:e.site_domain):"")+((m)?"; secure":"");if(p.length<4000){g.cookie=o;return(q===this.getCookie(h))}return false},getSubCookies:function(k){var j,h,d,n,m={};if(!k){return null}j=k.split("&");if(j.length===0){return null}for(h=0,d=j.length;h<d;h++){n=j[h].split("=");n.push("");m[decodeURIComponent(n[0])]=decodeURIComponent(n[1])}return m},removeCookie:function(d){return this.setCookie(d,{},0,"/",null)},pluginConfig:function(m,d,k,j){var h,l=0;if(!d||!d[k]){return false}for(h=0;h<j.length;h++){if(typeof d[k][j[h]]!=="undefined"){m[j[h]]=d[k][j[h]];l++}}return(l>0)}},init:function(h){var l,d,j=["beacon_url","site_domain","user_ip"];if(!h){h={}}for(l=0;l<j.length;l++){if(typeof h[j[l]]!=="undefined"){e[j[l]]=h[j[l]]}}if(typeof h.log!=="undefined"){this.log=h.log}if(!this.log){this.log=function(i,k,n){}}for(d in this.plugins){if(h[d]&&typeof h[d].enabled!=="undefined"&&h[d].enabled===false){e.disabled_plugins[d]=1;continue}else{if(e.disabled_plugins[d]){delete e.disabled_plugins[d]}}if(this.plugins.hasOwnProperty(d)&&typeof this.plugins[d].init==="function"){this.plugins[d].init(h)}}if(typeof h.autorun==="undefined"||h.autorun!==false){e.addListener(a,"load",function(){e.fireEvent("page_ready")})}e.addListener(a,"unload",function(){a=null});return this},page_ready:function(){e.fireEvent("page_ready");return this},subscribe:function(d,m,j,o){var k,l,n;if(!e.events.hasOwnProperty(d)){return this}n=e.events[d];for(k=0;k<n.length;k++){l=n[k];if(l[0]===m&&l[1]===j&&l[2]===o){return this}}n.push([m,j||{},o||null]);if(d==="page_unload"){e.addListener(a,"unload",function(){if(m){m.call(o,null,j)}m=o=j=null});e.addListener(a,"beforeunload",function(){if(m){m.call(o,null,j)}m=o=j=null})}return this},addVar:function(h,i){if(typeof h==="string"){e.vars[h]=i}else{if(typeof h==="object"){var j=h,d;for(d in j){if(j.hasOwnProperty(d)){e.vars[d]=j[d]}}}}return this},removeVar:function(){var d,h;if(!arguments.length){return this}if(arguments.length===1&&Object.prototype.toString.apply(arguments[0])==="[object Array]"){h=arguments[0]}else{h=arguments}for(d=0;d<h.length;d++){if(e.vars.hasOwnProperty(h[d])){delete e.vars[h[d]]}}return this},sendBeacon:function(){var i,j,h,d=0;for(i in this.plugins){if(this.plugins.hasOwnProperty(i)){if(e.disabled_plugins[i]){continue}if(!this.plugins[i].is_complete()){return this}}}e.fireEvent("before_beacon",e.vars);if(!e.beacon_url){return this}j=e.beacon_url+"?v="+encodeURIComponent(BOOMR.version);for(i in e.vars){if(e.vars.hasOwnProperty(i)){d++;j+="&"+encodeURIComponent(i)+"="+encodeURIComponent(e.vars[i])}}if(d){h=new Image();h.src=j}return this}};var f=function(d){return function(h,i){this.log(h,d,"boomerang"+(i?"."+i:""));return this}};c.debug=f("debug");c.info=f("info");c.warn=f("warn");c.error=f("error");if(a.YAHOO&&a.YAHOO.widget&&a.YAHOO.widget.Logger){c.log=a.YAHOO.log}else{if(typeof a.Y!=="undefined"&&typeof a.Y.log!=="undefined"){c.log=a.Y.log}else{if(typeof console!=="undefined"&&typeof console.log!=="undefined"){c.log=function(d,h,i){console.log(i+": ["+h+"] ",d)}}}}for(b in c){if(c.hasOwnProperty(b)){BOOMR[b]=c[b]}}BOOMR.plugins=BOOMR.plugins||{}}(window));(function(a){var c=a.document;BOOMR=BOOMR||{};BOOMR.plugins=BOOMR.plugins||{};var b={complete:false,timers:{},cookie:"RT",cookie_exp:600,strict_referrer:true,start:function(){var e,d=new Date().getTime();if(!BOOMR.utils.setCookie(b.cookie,{s:d,r:c.URL.replace(/#.*/,"")},b.cookie_exp,"/",null)){BOOMR.error("cannot set start cookie","rt");return this}e=new Date().getTime();if(e-d>50){BOOMR.utils.removeCookie(b.cookie);BOOMR.error("took more than 50ms to set cookie... aborting: "+d+" -> "+e,"rt")}return this}};BOOMR.plugins.RT={init:function(d){b.complete=false;b.timers={};BOOMR.utils.pluginConfig(b,d,"RT",["cookie","cookie_exp","strict_referrer"]);BOOMR.subscribe("page_ready",this.done,null,this);BOOMR.subscribe("page_unload",b.start,null,this);return this},startTimer:function(d){if(d){b.timers[d]={start:new Date().getTime()};b.complete=false}return this},endTimer:function(d,e){if(d){b.timers[d]=b.timers[d]||{};if(typeof b.timers[d].end==="undefined"){b.timers[d].end=(typeof e==="number"?e:new Date().getTime())}}return this},setTimer:function(d,e){if(d){b.timers[d]={delta:e}}return this},done:function(){var l,o,d,j,e,k={t_done:1,t_resp:1,t_page:1},i=0,m,h,n=[],f,g;if(b.complete){return this}this.endTimer("t_done");o=c.URL.replace(/#.*/,"");d=j=c.referrer.replace(/#.*/,"");e=BOOMR.utils.getSubCookies(BOOMR.utils.getCookie(b.cookie));BOOMR.utils.removeCookie(b.cookie);if(e!==null&&typeof e.s!=="undefined"&&typeof e.r!=="undefined"){d=e.r;if(!b.strict_referrer||d===j){l=parseInt(e.s,10)}}if(!l){BOOMR.warn("start cookie not set, trying WebTiming API","rt");g=a.performance||a.msPerformance||a.webkitPerformance||a.mozPerformance;if(g&&g.timing){f=g.timing}else{if(a.chrome&&a.chrome.csi){f={requestStart:a.chrome.csi().startE}}}if(f){l=f.requestStart||f.fetchStart||f.navigationStart||undefined}else{BOOMR.warn("This browser doesn't support the WebTiming API","rt")}}BOOMR.removeVar("t_done","t_page","t_resp","u","r","r2");for(m in b.timers){if(!b.timers.hasOwnProperty(m)){continue}h=b.timers[m];if(typeof h.delta!=="number"){if(typeof h.start!=="number"){h.start=l}h.delta=h.end-h.start}if(isNaN(h.delta)){continue}if(k.hasOwnProperty(m)){BOOMR.addVar(m,h.delta)}else{n.push(m+"|"+h.delta)}i++}if(i){BOOMR.addVar({u:o,r:d});if(j!==d){BOOMR.addVar("r2",j)}if(n.length){BOOMR.addVar("t_other",n.join(","))}}b.timers={};b.complete=true;BOOMR.sendBeacon();return this},is_complete:function(){return b.complete}}}(window));(function(b){var e=b.document;BOOMR=BOOMR||{};BOOMR.plugins=BOOMR.plugins||{};var a=[{name:"image-0.png",size:11483,timeout:1400},{name:"image-1.png",size:40658,timeout:1200},{name:"image-2.png",size:164897,timeout:1300},{name:"image-3.png",size:381756,timeout:1500},{name:"image-4.png",size:1234664,timeout:1200},{name:"image-5.png",size:4509613,timeout:1200},{name:"image-6.png",size:9084559,timeout:1200}];a.end=a.length;a.start=0;a.l={name:"image-l.gif",size:35,timeout:1000};var c={base_url:"images/",timeout:15000,nruns:5,latency_runs:10,user_ip:"",cookie_exp:7*86400,cookie:"BA",results:[],latencies:[],latency:null,runs_left:0,aborted:false,complete:false,running:false,ncmp:function(f,d){return(f-d)},iqr:function(h){var g=h.length-1,f,m,k,d=[],j;f=(h[Math.floor(g*0.25)]+h[Math.ceil(g*0.25)])/2;m=(h[Math.floor(g*0.75)]+h[Math.ceil(g*0.75)])/2;k=(m-f)*1.5;g++;for(j=0;j<g&&h[j]<m+k;j++){if(h[j]>f-k){d.push(h[j])}}return d},calc_latency:function(){var h,f,j=0,g=0,k,m,d,o,l;l=this.iqr(this.latencies.sort(this.ncmp));f=l.length;BOOMR.debug(l,"bw");for(h=1;h<f;h++){j+=l[h];g+=l[h]*l[h]}f--;k=Math.round(j/f);d=Math.sqrt(g/f-j*j/(f*f));o=(1.96*d/Math.sqrt(f)).toFixed(2);d=d.toFixed(2);f=l.length-1;m=Math.round((l[Math.floor(f/2)]+l[Math.ceil(f/2)])/2);return{mean:k,median:m,stddev:d,stderr:o}},calc_bw:function(){var y,x,t=0,p,g=[],v=[],f=0,o=0,C=0,u=0,q,A,B,h,d,w,k,m,l,z,s;for(y=0;y<this.nruns;y++){if(!this.results[y]||!this.results[y].r){continue}p=this.results[y].r;l=0;for(x=p.length-1;x>=0&&l<3;x--){if(typeof p[x]==="undefined"){break}if(p[x].t===null){continue}t++;l++;z=a[x].size*1000/p[x].t;g.push(z);s=a[x].size*1000/(p[x].t-this.latency.mean);v.push(s)}}BOOMR.debug("got "+t+" readings","bw");BOOMR.debug("bandwidths: "+g,"bw");BOOMR.debug("corrected: "+v,"bw");if(g.length>3){g=this.iqr(g.sort(this.ncmp));v=this.iqr(v.sort(this.ncmp))}else{g=g.sort(this.ncmp);v=v.sort(this.ncmp)}BOOMR.debug("after iqr: "+g,"bw");BOOMR.debug("corrected: "+v,"bw");t=Math.max(g.length,v.length);for(y=0;y<t;y++){if(y<g.length){f+=g[y];o+=Math.pow(g[y],2)}if(y<v.length){C+=v[y];u+=Math.pow(v[y],2)}}t=g.length;q=Math.round(f/t);A=Math.sqrt(o/t-Math.pow(f/t,2));B=Math.round(1.96*A/Math.sqrt(t));A=Math.round(A);t=g.length-1;h=Math.round((g[Math.floor(t/2)]+g[Math.ceil(t/2)])/2);t=v.length;d=Math.round(C/t);w=Math.sqrt(u/t-Math.pow(C/t,2));k=(1.96*w/Math.sqrt(t)).toFixed(2);w=w.toFixed(2);t=v.length-1;m=Math.round((v[Math.floor(t/2)]+v[Math.ceil(t/2)])/2);BOOMR.debug("amean: "+q+", median: "+h,"bw");BOOMR.debug("corrected amean: "+d+", median: "+m,"bw");return{mean:q,stddev:A,stderr:B,median:h,mean_corrected:d,stddev_corrected:w,stderr_corrected:k,median_corrected:m}},defer:function(f){var d=this;return setTimeout(function(){f.call(d);d=null},10)},load_img:function(g,k,m){var f=this.base_url+a[g].name+"?t="+(new Date().getTime())+Math.random(),l=0,j=0,d=new Image(),h=this;d.onload=function(){d.onload=d.onerror=null;d=null;clearTimeout(l);if(m){m.call(h,g,j,k,true)}h=m=null};d.onerror=function(){d.onload=d.onerror=null;d=null;clearTimeout(l);if(m){m.call(h,g,j,k,false)}h=m=null};l=setTimeout(function(){if(m){m.call(h,g,j,k,null)}},a[g].timeout+Math.min(400,this.latency?this.latency.mean:400));j=new Date().getTime();d.src=f},lat_loaded:function(d,f,h,j){if(h!==this.latency_runs+1){return}if(j!==null){var g=new Date().getTime()-f;this.latencies.push(g)}if(this.latency_runs===0){this.latency=this.calc_latency()}this.defer(this.iterate)},img_loaded:function(f,g,h,j){if(h!==this.runs_left+1){return}if(this.results[this.nruns-h].r[f]){return}if(j===null){this.results[this.nruns-h].r[f+1]={t:null,state:null,run:h};return}var d={start:g,end:new Date().getTime(),t:null,state:j,run:h};if(j){d.t=d.end-d.start}this.results[this.nruns-h].r[f]=d;if(f>=a.end-1||typeof this.results[this.nruns-h].r[f+1]!=="undefined"){BOOMR.debug(this.results[this.nruns-h],"bw");if(h===this.nruns){a.start=f}this.defer(this.iterate)}else{this.load_img(f+1,h,this.img_loaded)}},finish:function(){if(!this.latency){this.latency=this.calc_latency()}var f=this.calc_bw(),d={bw:f.median_corrected,bw_err:parseFloat(f.stderr_corrected,10),lat:this.latency.mean,lat_err:parseFloat(this.latency.stderr,10),bw_time:Math.round(new Date().getTime()/1000)};BOOMR.addVar(d);if(!isNaN(d.bw)){BOOMR.utils.setCookie(this.cookie,{ba:Math.round(d.bw),be:d.bw_err,l:d.lat,le:d.lat_err,ip:this.user_ip,t:d.bw_time},(this.user_ip?this.cookie_exp:0),"/",null)}this.complete=true;BOOMR.sendBeacon();this.running=false},iterate:function(){if(this.aborted){return false}if(!this.runs_left){this.finish()}else{if(this.latency_runs){this.load_img("l",this.latency_runs--,this.lat_loaded)}else{this.results.push({r:[]});this.load_img(a.start,this.runs_left--,this.img_loaded)}}},setVarsFromCookie:function(l){var i=parseInt(l.ba,10),k=parseFloat(l.be,10),j=parseInt(l.l,10)||0,f=parseFloat(l.le,10)||0,d=l.ip.replace(/\.\d+$/,"0"),m=parseInt(l.t,10),h=this.user_ip.replace(/\.\d+$/,"0"),g=Math.round((new Date().getTime())/1000);if(d===h&&m>=g-this.cookie_exp){this.complete=true;BOOMR.addVar({bw:i,lat:j,bw_err:k,lat_err:f});return true}return false}};BOOMR.plugins.BW={init:function(d){var f;BOOMR.utils.pluginConfig(c,d,"BW",["base_url","timeout","nruns","cookie","cookie_exp"]);if(d&&d.user_ip){c.user_ip=d.user_ip}a.start=0;c.runs_left=c.nruns;c.latency_runs=10;c.results=[];c.latencies=[];c.latency=null;c.complete=false;c.aborted=false;BOOMR.removeVar("ba","ba_err","lat","lat_err");f=BOOMR.utils.getSubCookies(BOOMR.utils.getCookie(c.cookie));if(!f||!f.ba||!c.setVarsFromCookie(f)){BOOMR.subscribe("page_ready",this.run,null,this)}return this},run:function(){if(c.running||c.complete){return this}if(b.location.protocol==="https:"){BOOMR.info("HTTPS detected, skipping bandwidth test","bw");c.complete=true;return this}c.running=true;setTimeout(this.abort,c.timeout);c.defer(c.iterate);return this},abort:function(){c.aborted=true;c.finish();return this},is_complete:function(){return c.complete}}}(window));
