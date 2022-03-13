"use strict";function _classCallCheck(e,r){if(!(e instanceof r))throw new TypeError("Cannot call a class as a function")}function _defineProperty(e,r,o){return r in e?Object.defineProperty(e,r,{value:o,enumerable:!0,configurable:!0,writable:!0}):e[r]=o,e}var PMCRedirectOverlay=function e(){var o=this;_classCallCheck(this,e),_defineProperty(this,"setup_hooks",function(){o.$(".pmc-reg-rd-overlay-banner .btn-close").on("click",o.hide_banner),o.$(".pmc-reg-rd-overlay-banner").on("click","a",o.expire_session)}),_defineProperty(this,"hide_banner",function(e){e.preventDefault(),o.$(e.target).parent().hide()}),_defineProperty(this,"expire_session",function(){pmc.cookie.expire(o.short_term_cookie_name)}),_defineProperty(this,"should_display_banner",function(){var e=pmc.cookie.get(o.long_term_cookie_name),r=pmc.is_empty(e),e=pmc.cookie.get(o.short_term_cookie_name);return r=!pmc.is_empty(e)||r}),_defineProperty(this,"set_cookies",function(){var e=pmc.cookie.get(o.long_term_cookie_name);pmc.is_empty(e)&&(e=30,"undefined"!=typeof pmc_region_redirect_overlay&&void 0!==pmc_region_redirect_overlay.dnd_duration&&0<parseInt(pmc_region_redirect_overlay.dnd_duration)&&(e=parseInt(pmc_region_redirect_overlay.dnd_duration)),e*=86400,pmc.cookie.set(o.long_term_cookie_name,"hide",e,"/"),pmc.cookie.set(o.short_term_cookie_name,"show"))}),_defineProperty(this,"get_current_country",function(){return("undefined"!=typeof pmc_fastly_geo_data&&void 0!==pmc_fastly_geo_data.country?pmc_fastly_geo_data.country:"").toLowerCase()}),_defineProperty(this,"has_local_site",function(){var e=0<arguments.length&&void 0!==arguments[0]?arguments[0]:"";return void 0!==e&&!pmc.is_empty(e)&&pmc_region_redirect_overlay.countries.includes(e)}),_defineProperty(this,"maybe_display_banner",function(){var e,r=o.get_current_country();o.should_display_banner()&&o.has_local_site(r)&&(e=pmc_region_redirect_overlay.overlay_html[r],(r=o.$("#pmc-reg-rd-overlay-banner")).find(".message").html(e),r.show(),o.set_cookies())}),this.$=jQuery,this.long_term_cookie_name="pmc_reg_rd_overlay_banner",this.short_term_cookie_name="pmc_reg_rd_overlay_banner_sesn",this.setup_hooks(),this.maybe_display_banner()};new PMCRedirectOverlay;