!function(e){var t={};function i(a){if(t[a])return t[a].exports;var s=t[a]={i:a,l:!1,exports:{}};return e[a].call(s.exports,s,s.exports,i),s.l=!0,s.exports}i.m=e,i.c=t,i.d=function(e,t,a){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:a})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var a=Object.create(null);if(i.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var s in e)i.d(a,s,function(t){return e[t]}.bind(null,s));return a},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p="",i(i.s=28)}({2:function(e,t,i){"use strict";i.d(t,"a",(function(){return a}));class a{constructor(e){this.el=e,this.toggleEl=this.el.querySelectorAll("[data-collapsible-toggle]"),this.panels=[...this.el.querySelectorAll("[data-collapsible-panel]")],this.group=this.findGroup(),this.toggle=this.toggle.bind(this),this.onClick=this.onClick.bind(this),this.toggleEl.forEach(e=>e.addEventListener("click",this.onClick))}destroy(){this.toggleEl.forEach(e=>e.removeEventListener("click",this.onClick)),this.isCollapsed||this.toggle()}get state(){return this.el.dataset.collapsible}set state(e){this.el.dataset.collapsible=e,this.isCollapsed?this.el.classList.remove("is-expanded"):this.el.classList.add("is-expanded")}get isCollapsed(){return"collapsed"===this.state}get container(){return document.querySelector(this.el.dataset.collapsibleContainer)}get closeOnClick(){return void 0!==this.el.dataset.collapsibleCloseOnClick}findGroup(){return[...document.body.querySelectorAll("[data-collapsible-group]")].find(e=>e.contains(this.el))||null}onClick(e){e.preventDefault(),this.toggle()}toggle(){this.state=this.isCollapsed?"expanded":"collapsed",this.maybeRepositionPanel(),this.maybeCloseOnClick(),"expanded"===this.state&&null!==this.group&&this.closeOthersInGroup()}collapse(){"expanded"===this.state&&this.toggle()}closeOthersInGroup(){[...this.group.querySelectorAll("[data-collapsible]")].forEach(e=>{e!==this.el&&e.pmcCollapsible.collapse()})}maybeRepositionPanel(){if(this.container)if(this.isCollapsed)this.panels.forEach(e=>e.style.marginLeft="");else{const e=this.container.getBoundingClientRect().left;this.panels.forEach(t=>{const i=t.getBoundingClientRect();if(0===i.width&&0===i.height)return;const a=parseInt(window.getComputedStyle(t).marginLeft,10),s=i.left-2*a;s<e&&(t.style.marginLeft=e-s+"px")})}}maybeCloseOnClick(){this.closeOnClick&&(this.isCollapsed?document.body.removeEventListener("click",this.toggle):setTimeout(()=>document.body.addEventListener("click",this.toggle),1))}}},28:function(e,t,i){"use strict";i.r(t);var a=i(3);window.addEventListener("load",(function(){Object(a.a)()}))},3:function(e,t,i){"use strict";i.d(t,"a",(function(){return r}));var a=i(2);class s{constructor(e){var t,i,a,s;this.el=e,this.triggers=[...e.querySelectorAll("[data-video-showcase-trigger]")],this.player=e.querySelector("[data-video-showcase-player]"),this.elementsToHide=[...this.el.querySelectorAll(".is-to-be-hidden")],this.attributesToRemoveFromPlayer=["data-video-showcase-trigger","data-video-showcase-title","data-video-showcase-dek","data-video-showcase-permalink","data-video-showcase-type","href"],this.state={isPlayerSetup:!1,hasSocialShare:!1,videoID:"",videoType:""},this.playerUI={heading:e.querySelector("[data-video-showcase-player-heading], .js-VideoShowcasePlayerHeading"),sponsoredBadge:e.querySelector(".js-video-showcase-sponsored-badge"),dek:e.querySelector("[data-video-showcase-player-dek], .js-VideoShowcasePlayerDek"),iframe:e.querySelector("[data-video-showcase-iframe], .js-VideoShowcasePlayerIframe"),jwplayerContainer:e.querySelector("#jwplayerContainer"),social:e.querySelector("[data-video-showcase-player-social-share], .js-VideoShowcasePlayerSocialShare"),oembedContainer:e.querySelector("[data-video-showcase-oembed], .js-VideoShowcasePlayerOembed"),time:e.querySelector(".js-VideoShowcasePlayerTime")},this.init(),this.player.dataset.videoShowcaseAutoplay?this.handleTriggerClick(null,this.triggers[0]):(t=this.el,i="click",a="[data-video-showcase-trigger]",s=this.handleTriggerClick.bind(this),t.addEventListener(i,e=>{const t=((e,t)=>t.matches&&t.matches(e))(a,e.target)?e.target:e.target.closest(a);t&&s(e,t)}))}init(){null!==this.playerUI.social&&(this.state.hasSocialShare=!0)}getPlayerCardData(e){const t=e.dataset.videoShowcaseTrigger,i=this.state.hasSocialShare;return{title:e.dataset.videoShowcaseTitle,sponsored:e.dataset.videoShowcaseSponsored,dek:e.dataset.videoShowcaseDek,permalink:e.dataset.videoShowcasePermalink,time:e.dataset.videoShowcaseTime,socialString:function(e){if(window.wp&&i){return wp.template("trigger-social-share-"+t)(void 0)}}()}}updatePlayerCardData(e,t){this.playerUI.heading&&t.title&&(this.playerUI.heading.innerText=t.title),this.playerUI.heading&&t.permalink&&this.playerUI.heading.setAttribute("href",t.permalink),this.playerUI.dek&&t.dek&&(this.playerUI.dek.innerText=t.dek),this.playerUI.time&&t.time&&(this.playerUI.time.innerText=t.time),t.socialString&&this.state.hasSocialShare&&this.updateCardSocialShare(t.socialString),this.playerUI.sponsoredBadge&&(t.sponsored?this.playerUI.sponsoredBadge.classList.remove("u-hidden"):this.playerUI.sponsoredBadge.classList.add("u-hidden"))}updateCardSocialShare(e){this.playerUI.social.removeChild(this.playerUI.social.querySelector("ul")),this.playerUI.social.insertAdjacentHTML("beforeend",e),this.initCollapsible(this.playerUI.social.querySelector("[data-collapsible]"))}initCollapsible(e){e.pmcCollapsible=new a.a(e)}returnUrl(e,t){return"youtube"===t?"https://www.youtube.com/embed/"+e:"jwplayer"===t?`https://content.jwplatform.com/feeds/${e}.json`:"oembed"===t?e:void 0}playYoutube(e){this.playerUI.iframe.removeAttribute("hidden"),this.playerUI.iframe.setAttribute("src",e+"?rel=0&autoplay=1&showinfo=0&controls=2&rel=0&modestbranding=0"),this.playerUI.iframe.setAttribute("allow","accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture")}playJW(e){let t;this.playerUI.jwplayerContainer.removeAttribute("hidden"),window.pmc_jwplayer?t=window.pmc_jwplayer(this.playerUI.jwplayerContainer.id,"default"):window.jwplayer&&(t=window.jwplayer(this.playerUI.jwplayerContainer.id)),t&&(t.setup({playlist:e,aspectratio:"16:9"}),t.play())}playEmbed(e){this.playerUI.oembedContainer.removeAttribute("hidden"),this.playerUI.oembedContainer.innerHTML="",this.playerUI.oembedContainer.insertAdjacentHTML("beforeend",e)}handleTriggerClick(e,t){e&&e.preventDefault();const i=this.state.videoType;this.state.videoType=t.dataset.videoShowcaseType,this.state.videoID=t.dataset.videoShowcaseTrigger,this.resetPlayer(i),this.playVideo(this.state.videoID,this.state.videoType),this.updatePlayerUI(this.state.videoID),this.onFirstTimePlay()}playVideo(e,t){const i=this.returnUrl(e,t);"youtube"===t&&this.playYoutube(i),"jwplayer"===t&&this.playJW(i),"oembed"===t&&this.playEmbed(i)}onFirstTimePlay(){!1===this.state.isPlayerSetup&&(this.elementsToHide.forEach(e=>e.setAttribute("hidden","")),this.attributesToRemoveFromPlayer.forEach(e=>this.player.parentNode.removeAttribute(e)),this.state.isPlayerSetup=!0)}updatePlayerUI(e){const t=this.el.querySelector(`[data-video-showcase-trigger="${e}"]`),i=this.getPlayerCardData(t);this.setActiveTrigger(e),this.updatePlayerCardData(t,i)}resetPlayer(e){"jwplayer"===e&&window.jwplayer&&(window.jwplayer("jwplayerContainer").remove(),this.playerUI.jwplayerContainer.setAttribute("hidden","")),"youtube"===e&&(this.playerUI.iframe.setAttribute("src",""),this.playerUI.iframe.setAttribute("hidden",""))}resetAllTriggers(){this.triggers.forEach(e=>e.classList.remove("is-playing"))}setActiveTrigger(e){const t=this.el.querySelector(`.related-videos [data-video-showcase-trigger="${e}"]`);this.resetAllTriggers(),null!==t&&t.classList.add("is-playing")}}function r(){const e=[...document.querySelectorAll("[data-video-showcase]")];e.length&&e.forEach(e=>e.pmcVideoShowcase=new s(e))}}});