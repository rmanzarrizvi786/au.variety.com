!function(e){var t={};function r(o){if(t[o])return t[o].exports;var i=t[o]={i:o,l:!1,exports:{}};return e[o].call(i.exports,i,i.exports,r),i.l=!0,i.exports}r.m=e,r.c=t,r.d=function(e,t,o){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(r.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var i in e)r.d(o,i,function(t){return e[t]}.bind(null,i));return o},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="",r(r.s=34)}({34:function(e,t,r){"use strict";r.r(t);const o={has_cookie:!1,authorized:!1,has_digital_access:!1,secured_url_path:e=>`https://${window.location.host}${e}`,init(){const e=this;jQuery(()=>{e.update()})},update(){const e=this;"undefined"!=typeof uls&&(uls.session.can_access("vy-digital")?this.set_html_authorized():this.set_html_not_authorized(),jQuery("a[esp-promo-suffix]").each((function(){jQuery(this).attr("href","https://www.pubservice.com/variety/?PC=VY&PK="+e.current_promocode("M",jQuery(this).attr("esp-promo-suffix")))})))},set_html_authorized(){const e=this;jQuery("body").addClass(["authenticated","authenticated-pp"].join(" ")),jQuery(".vy-logout").attr("href",this.secured_url_path("/digital-subscriber-access/#action=logout")),jQuery("#digital-link-text").attr("title","View Print Edition").attr("href",this.secured_url_path("/access-digital/")).attr("target","_blank").show(),jQuery("#subscribe-link-section #subscribe-link-text").text("Access Premier").attr("href",this.secured_url_path("/print-plus/")).show(),jQuery.cookie("uls3_username")&&jQuery(".vy-username").text(jQuery.cookie("uls3_username")),"undefined"!=typeof Variety_Authentication&&jQuery(".vy-logout").on("click",t=>(e.set_overlay_processing(!0),t.preventDefault(),jQuery(".vy-logout").unbind("click"),Variety_Authentication.logout(()=>{e.set_html_not_authorized(),window.location.reload()}),!1)),jQuery(".variety-story-issue-login").show(),jQuery(".variety-story-issue-logout").hide()},set_html_not_authorized(){jQuery("#sign-in-my-account").remove(),jQuery("#subscribe-link-text").html("Subscribe Today!").attr("href","/subscribe-us/").attr("target","self").show(),jQuery("#digital-link-text").attr("title","Subscribe Today!").attr("href","/subscribe-us/").removeAttr("target").show(),jQuery(".variety-story-issue-login").hide(),jQuery(".variety-story-issue-logout").show()},current_promocode(e,t){const r=new Date,o=r.getMonth()+1;return e+(r.getFullYear()%10).toString()+o.toString(16).toUpperCase()+t},set_overlay_processing(e){let t=jQuery("#overlay_ajax_loading");e?(t.length||(t=jQuery("<div></div>").attr("id","overlay_ajax_loading"),jQuery("body").append(t)),jQuery(t).show()):t.length&&jQuery(t).hide()}};void 0!==window.pmc&&void 0!==jQuery&&(pmc.hooks.add_filter("pmc-adm-set-targeting-keywords",e=>{const t=e;return void 0===t.kw&&(t.kw=[]),t}),pmc.hooks.add_filter("pmc-adm-set-targeting-keywords-kw",e=>{let t=e,r="logged-out-subscriber";return void 0===jQuery.cookie||(uls.session.is_valid()&&(r="logged-in-subscriber"),t instanceof Array?t.push(r):t+=(t?",":"")+r),t}),pmc.hooks.add_action("uls.ping.refresh",()=>{o.update()}));var i=o;window.addEventListener("load",(function(){void 0!==window.pmc&&void 0!==jQuery&&i.init()}))}});