!function(){"use strict";function t(e){return(t="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(e)}function e(t,e){for(var i=0;i<e.length;i++){var n=e[i];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,n.key,n)}}function i(t,e,i){return e in t?Object.defineProperty(t,e,{value:i,enumerable:!0,configurable:!0,writable:!0}):t[e]=i,t}var n=function(){function n(t){var e;!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,n),i(this,"id",null),i(this,"trackerName","pmcDigitalDaily"),i(this,"title",void 0),i(this,"device",void 0),this.id=t,this.title=window.document.title,this.device=(null===(e=window.pmc_ga_event_tracking)||void 0===e?void 0:e.device)||"[D]",this.initGA()}var r,o;return r=n,(o=[{key:"initGA",value:function(){ga("create",this.id,"auto",this.trackerName),this.setTitle(this.title),"object"===("undefined"==typeof pmcGaCustomDimensions?"undefined":t(pmcGaCustomDimensions))&&ga("".concat(this.trackerName,".set"),pmcGaCustomDimensions)}},{key:"trackPageview",value:function(t,e){this.setTitle(e),ga("".concat(this.trackerName,".send"),"pageview",t)}},{key:"recordClick",value:function(t,e){var i={hitType:"event",eventCategory:e,eventAction:"click",eventLabel:"".concat(this.device," ").concat(t)};this.setTitle(this.title),ga("".concat(this.trackerName,".send"),i)}},{key:"setTitle",value:function(t){ga("".concat(this.trackerName,".set"),"title",t)}}])&&e(r.prototype,o),n}();function r(t){return(r="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function o(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var i=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=i){var n,r,o=[],_n=!0,a=!1;try{for(i=i.call(t);!(_n=(n=i.next()).done)&&(o.push(n.value),!e||o.length!==e);_n=!0);}catch(t){a=!0,r=t}finally{try{_n||null==i.return||i.return()}finally{if(a)throw r}}return o}}(t,e)||function(t,e){if(t){if("string"==typeof t)return a(t,e);var i=Object.prototype.toString.call(t).slice(8,-1);return"Object"===i&&t.constructor&&(i=t.constructor.name),"Map"===i||"Set"===i?Array.from(t):"Arguments"===i||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(i)?a(t,e):void 0}}(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function a(t,e){(null==e||e>t.length)&&(e=t.length);for(var i=0,n=new Array(e);i<e;i++)n[i]=t[i];return n}function l(t,e){for(var i=0;i<e.length;i++){var n=e[i];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,n.key,n)}}function s(t,e,i){return e in t?Object.defineProperty(t,e,{value:i,enumerable:!0,configurable:!0,writable:!0}):t[e]=i,t}var c=function(){function t(e){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),s(this,"anchorClickSelectors",void 0),s(this,"blockClickSelectors",void 0),s(this,"pageviewSelectors",void 0),s(this,"viewType",void 0),s(this,"permalinkAttr","data-permalink"),s(this,"titleAttr","data-title"),s(this,"ga",void 0),s(this,"intersectTimeout",!1),this.anchorClickSelectors=e.anchorClickSelectors,this.blockClickSelectors=e.blockClickSelectors,this.pageviewSelectors=e.pageviewSelectors,this.viewType=e.viewType,this.ga=new n(e.gaId),this.onAnchorSelectorClick=this.onAnchorSelectorClick.bind(this),this.onBlockSelectorClick=this.onBlockSelectorClick.bind(this),this.onIntersect=this.onIntersect.bind(this),this.initObserver=this.initObserver.bind(this),this.recordInitialPageview(),this.initClickTracking(),this.initObserver()}var e,i;return e=t,(i=[{key:"recordInitialPageview",value:function(){this.ga.trackPageview(window.location.href,document.title)}},{key:"initClickTracking",value:function(){var t=this;Object.entries(this.anchorClickSelectors).forEach((function(e){var i=o(e,2),n=i[0],r=i[1];document.querySelectorAll(n).forEach((function(e){return e.addEventListener("click",(function(e){return t.onAnchorSelectorClick(e,r)}))}))})),Object.entries(this.blockClickSelectors).forEach((function(e){var i=o(e,2),n=i[0],r=i[1];document.querySelectorAll(n).forEach((function(e){return e.addEventListener("click",(function(e){return t.onBlockSelectorClick(e,r)}))}))}))}},{key:"onAnchorSelectorClick",value:function(t,e){var i;(i="a"===t.target.nodeName?t.target.href:t.target.closest("a").href).length&&"#"!==i&&this.ga.recordClick(i,e)}},{key:"onBlockSelectorClick",value:function(t,e){var i=t.target.closest("[data-permalink]");i&&this.ga.recordClick(i.getAttribute(this.permalinkAttr),e)}},{key:"initObserver",value:function(){var t=this,e=new IntersectionObserver(this.onIntersect,{root:null,threshold:[0]});this.pageviewSelectors.forEach((function(i){var n;if("object"===r(i)){var a=o(i,2),l=a[0],s=a[1];(n=document.querySelectorAll(l)).forEach((function(e){var i=e.querySelector(s);if(i){var n=i.getAttribute(t.permalinkAttr),r=i.getAttribute(t.titleAttr);e.setAttribute(t.permalinkAttr,n),e.setAttribute(t.titleAttr,r)}}))}else n=document.querySelectorAll(i);n.forEach((function(t){return e.observe(t)}))}))}},{key:"onIntersect",value:function(t){var e=this;"full"===this.viewType&&this.intersectTimeout&&clearTimeout(this.intersectTimeout),this.intersectTimeout=setTimeout((function(){var i=t.filter((function(t){return t.isIntersecting}));"full"===e.viewType&&i.length>1&&(i.sort((function(t,e){return e.intersectionRatio-t.intersectionRatio})),i=i.slice(0,1)),i.forEach((function(t){t.target.hasAttribute(e.permalinkAttr)&&e.ga.trackPageview(t.target.getAttribute(e.permalinkAttr),t.target.getAttribute(e.titleAttr))}))}),500)}}])&&l(e.prototype,i),t}();function u(t,e){for(var i=0;i<e.length;i++){var n=e[i];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,n.key,n)}}function d(t,e,i){return e in t?Object.defineProperty(t,e,{value:i,enumerable:!0,configurable:!0,writable:!0}):t[e]=i,t}var h=function(){function t(e){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),d(this,"config",void 0),d(this,"overflowClass","lrv-u-overflow-hidden"),d(this,"hiddenClass","lrv-a-hidden"),d(this,"overlayElement",null),d(this,"done",!1),this.config=e,this.addEvents=this.addEvents.bind(this),this.showOverlay=this.showOverlay.bind(this),this.hideOverlay=this.hideOverlay.bind(this),this.overlayElement=document.getElementById(this.config.overlayId),null!==this.overlayElement?(this.showOverlay(),this.addEvents()):this.hideOverlay()}var e,i;return e=t,(i=[{key:"addEvents",value:function(){var t=this;blogherads.adq.push((function(){blogherads.addEventListener("gptSlotRenderEnded",(function(e){t.isOverlayUnit(e.slot.domId)&&Boolean(e.isEmpty)&&t.hideOverlay()}))})),blogherads.adq.push((function(){blogherads.addEventListener("gptSlotOnload",(function(e){t.isOverlayUnit(e.slot.domId)&&t.hideOverlay()}))}))}},{key:"isOverlayUnit",value:function(t){return t.indexOf(this.config.adDomSlug)>-1}},{key:"showOverlay",value:function(){document.body.classList.add(this.overflowClass),this.overlayElement.classList.remove(this.hiddenClass),setTimeout(this.hideOverlay,1e3*this.config.timeoutSeconds)}},{key:"hideOverlay",value:function(){this.done||(this.done=!0,null!==this.overlayElement&&this.overlayElement.classList.add(this.hiddenClass),document.body.classList.remove(this.overflowClass),window.pmc.digitalDaily.scrollAssist.instance.doSmoothScroll())}}])&&u(e.prototype,i),t}();function f(t,e){var i="undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(!i){if(Array.isArray(t)||(i=function(t,e){if(t){if("string"==typeof t)return v(t,e);var i=Object.prototype.toString.call(t).slice(8,-1);return"Object"===i&&t.constructor&&(i=t.constructor.name),"Map"===i||"Set"===i?Array.from(t):"Arguments"===i||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(i)?v(t,e):void 0}}(t))||e&&t&&"number"==typeof t.length){i&&(t=i);var n=0,r=function(){};return{s:r,n:function(){return n>=t.length?{done:!0}:{done:!1,value:t[n++]}},e:function(t){throw t},f:r}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var o,a=!0,l=!1;return{s:function(){i=i.call(t)},n:function(){var t=i.next();return a=t.done,t},e:function(t){l=!0,o=t},f:function(){try{a||null==i.return||i.return()}finally{if(l)throw o}}}}function v(t,e){(null==e||e>t.length)&&(e=t.length);for(var i=0,n=new Array(e);i<e;i++)n[i]=t[i];return n}function y(t,e,i,n,r,o,a){try{var l=t[o](a),s=l.value}catch(t){return void i(t)}l.done?e(s):Promise.resolve(s).then(n,r)}function m(t,e){for(var i=0;i<e.length;i++){var n=e[i];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,n.key,n)}}var p=function(){function t(){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),this.print=this.print.bind(this),this.loadLazyImage=this.loadLazyImage.bind(this),this.onBeforePrint=this.onBeforePrint.bind(this),window.addEventListener("beforeprint",this.onBeforePrint),"#print-page"===window.location.hash&&this.print()}var e,i,n,r;return e=t,(i=[{key:"print",value:(n=regeneratorRuntime.mark((function t(){var e,i,n,r;return regeneratorRuntime.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:e=document.querySelectorAll("img[data-lazy-src]"),i=f(e),t.prev=2,i.s();case 4:if((n=i.n()).done){t.next=10;break}return r=n.value,t.next=8,this.loadLazyImage(r);case 8:t.next=4;break;case 10:t.next=15;break;case 12:t.prev=12,t.t0=t.catch(2),i.e(t.t0);case 15:return t.prev=15,i.f(),t.finish(15);case 18:window.print();case 19:case"end":return t.stop()}}),t,this,[[2,12,15,18]])})),r=function(){var t=this,e=arguments;return new Promise((function(i,r){var o=n.apply(t,e);function a(t){y(o,i,r,a,l,"next",t)}function l(t){y(o,i,r,a,l,"throw",t)}a(void 0)}))},function(){return r.apply(this,arguments)})},{key:"loadLazyImage",value:function(t){return new Promise((function(e){t.addEventListener("load",e),t.addEventListener("error",e);var i=t.getAttribute("data-lazy-src"),n=t.getAttribute("data-lazy-srcset"),r=t.getAttribute("data-lazy-sizes");t.setAttribute("data-lazy-loaded","true"),t.removeAttribute("data-lazy-src"),t.removeAttribute("data-lazy-srcset"),t.removeAttribute("data-lazy-sizes"),i?(t.setAttribute("src",i),n&&t.setAttribute("srcset",n),r&&t.setAttribute("sizes",r)):e()}))}},{key:"onBeforePrint",value:function(){document.querySelectorAll("img[data-lazy-src]").forEach(this.loadLazyImage)}}])&&m(e.prototype,i),t}();function g(t,e){for(var i=0;i<e.length;i++){var n=e[i];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,n.key,n)}}var w,b,k,S,A=function(){function t(){var e,i=this;!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),(e="tracker")in this?Object.defineProperty(this,e,{value:null,enumerable:!0,configurable:!0,writable:!0}):this[e]=null,this.dispatchEvent=this.dispatchEvent.bind(this),this.doSmoothScroll=this.doSmoothScroll.bind(this),this.dispatchEventAfterScroll=this.dispatchEventAfterScroll.bind(this),this.startTrackingScroll=this.startTrackingScroll.bind(this),this.dispatchEventOnScrollStop=this.dispatchEventOnScrollStop.bind(this),window.pmc.digitalDaily.issueHasCoverAd()||window.addEventListener("DOMContentLoaded",(function(){setTimeout(i.doSmoothScroll,5)})),this.showOverlay()}var e,i;return e=t,(i=[{key:"dispatchEvent",value:function(){var t=new Event(window.pmc.digitalDaily.scrollAssist.event);window.dispatchEvent(t)}},{key:"doSmoothScroll",value:function(){if(window.location.hash){var t=window.location.hash.substring(1).split("&");if(t){var e=document.getElementById(t[0]);if(window.location.hash="",e){var i=e.closest(".article-block-outer"),n=i.offsetTop;if(i){var r=i.getBoundingClientRect(),o=window.getComputedStyle(i),a=r.top+parseInt(o.marginTop,10),l=window.pmc.digitalDaily.getTopOffset(!1);!document.querySelector("html").classList.contains("is-sticky")&&n>50&&(l+=document.querySelector(".js-Header-contents").offsetHeight),this.dispatchEventAfterScroll(),window.scrollTo({top:a-l,left:0,behavior:"smooth"})}else this.dispatchEvent()}else this.dispatchEvent()}else this.dispatchEvent()}else this.dispatchEvent()}},{key:"dispatchEventAfterScroll",value:function(){window.addEventListener("scroll",this.startTrackingScroll,{passive:!0})}},{key:"startTrackingScroll",value:function(){null!==this.tracker&&clearTimeout(this.tracker),this.tracker=setTimeout(this.dispatchEventOnScrollStop,50)}},{key:"dispatchEventOnScrollStop",value:function(){window.removeEventListener("scroll",this.startTrackingScroll,{passive:!0}),this.tracker=null,this.dispatchEvent()}},{key:"showOverlay",value:function(){if(window.pmc.digitalDaily.config.isFullView){var t="lrv-a-hidden",e=document.getElementById("pmc-digital-daily-cover-ad-overlay");""!==window.location.hash.substring(1)&&e.classList.remove(t),window.addEventListener(window.pmc.digitalDaily.scrollAssist.event,(function(){setTimeout((function(){e.classList.add(t)}),250)}))}}}])&&g(e.prototype,i),t}();window.pmc=window.pmc||{},window.pmc.digitalDaily={config:pmcDigitalDailyConfig,scrollAssist:{event:"pmc.digitalDaily.scrollAssist.afterScroll",instance:null}},window.pmc.digitalDaily.issueHasCoverAd=function(){return!Boolean(window.pmc.digitalDaily.config.isFullView)&&Boolean(window.pmc.digitalDaily.config.coverAd.has)},window.pmc.digitalDaily.getTopOffset=function(){var t=!(arguments.length>0&&void 0!==arguments[0])||arguments[0],e=document.querySelectorAll("#wpadminbar, div.js-Header-contents, div.header-sticky-nav, div.digital-daily-navigation"),i=0;return e.forEach((function(e){var n=window.getComputedStyle(e);"none"!==n.display&&(t?i+=e.scrollHeight:(i+=e.offsetHeight,i+=parseInt(n.marginTop,10),i+=parseInt(n.marginBottom,10)))})),i},b=null!==(w=pmcDigitalDailyAnalyticsConfig)&&void 0!==w?w:{},"function"==typeof ga&&b.gaId&&(null!==(k=b.blockClickSelectors)&&void 0!==k&&k.length||null!==(S=b.pageviewSelectors)&&void 0!==S&&S.length)&&new c(b),window.pmc.digitalDaily.scrollAssist.instance=new A,window.pmc.digitalDaily.issueHasCoverAd()&&new h(window.pmc.digitalDaily.config.coverAd),window.pmc.printPreparer=new p}();