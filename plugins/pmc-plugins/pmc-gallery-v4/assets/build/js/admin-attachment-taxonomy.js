!function(n){var r={};function i(e){if(r[e])return r[e].exports;var t=r[e]={i:e,l:!1,exports:{}};return n[e].call(t.exports,t,t.exports,i),t.l=!0,t.exports}i.m=n,i.c=r,i.d=function(e,t,n){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)i.d(n,r,function(e){return t[e]}.bind(null,r));return n},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p="",i(i.s=285)}({285:function(e,t,n){"use strict";n.r(t);n(286)},286:function(e,t){window.pmc_gallery_attachment=new function(){this.tax_settings_element="",this.set_suggest=function(e){jQuery.isFunction(jQuery.suggest)&&jQuery("#"+e).suggest(ajaxurl+"?action=ajax-tag-search&tax=pmc_attachment_tags",{multiple:!0,multipleSep:","}),this.add_checkbox()},this.add_checkbox=function(){var e="pmc_gallery_mediainput",t=jQuery(".attachments-browser .media-toolbar-primary.search-form"),n="";jQuery.each(t,function(e,t){if(!1===jQuery(t).is(":hidden"))return n=jQuery(t)}),""===n||n.find("#tmpl-pmc-gallery-attachment-tax-settings").length||(""===this.tax_settings_element&&(this.tax_settings_element=jQuery("#tmpl-pmc-gallery-attachment-tax-settings")),n.prepend(this.tax_settings_element),jQuery("#pmc-gallery-media-search-input").on("change",function(){this.checked?pmc.cookie.set(e,1):pmc.cookie.expire(e)})),jQuery("#pmc-gallery-media-search-input").prop("checked")?pmc.cookie.set(e,1):pmc.cookie.expire(e)}},jQuery(function(){jQuery("#insert-media-button").on("click",function(){setTimeout(function(){window.pmc_gallery_attachment.add_checkbox(),jQuery(".media-modal-content .media-router .media-menu-item").on("click",function(){window.pmc_gallery_attachment.add_checkbox()})},100)}),setTimeout(function(){jQuery("#pmc-gallery .media-frame-menu .media-menu-item").on("click",function(){setTimeout(function(){window.pmc_gallery_attachment.add_checkbox()},1e3)})},1e3)})}});