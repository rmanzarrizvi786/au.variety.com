!function(e){var t={};function n(i){if(t[i])return t[i].exports;var o=t[i]={i:i,l:!1,exports:{}};return e[i].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,i){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:i})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var i=Object.create(null);if(n.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(i,o,function(t){return e[t]}.bind(null,o));return i},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=9)}({10:function(e,t,n){"use strict";!function(e){const t=function(){const e=this,t=document.querySelector("#category-all");e.init=function(){const n=t.querySelectorAll("input");null!==n&&n.forEach((function(t){t.addEventListener("change",e.injectBoxVisibilityCSSClass.bind(this))})),this.injectBoxVisibilityCSSClass()},e.injectBoxVisibilityCSSClass=function(){e.shouldShow()?document.body.classList.add("pmc-review-metabox-visible"):document.body.classList.remove("pmc-review-metabox-visible")},e.shouldShow=function(){return Object.keys(pmcReviewData.reviewCategories).includes(this.getSelectedCategory())},e.getSelectedCategory=function(){const e=t.querySelectorAll("input:checked");let n="",i="";return e.forEach((function(e){i=_.find(pmcReviewData.reviewCategories,{term_id:Number(e.value)}),void 0!==i&&(n=i.slug)})),n},e.init()};e.addEventListener("load",(function(){new t}))}(window,jQuery)},11:function(e,t){var n,i;n=jQuery,i={terms:{cat:"",tag:""},isCat:!1,isTag:!1,listen:{},contentEditor:!1,l10n:{googleAlert:"Please note that the Google review snippet has not been set.",googleWarning:"Google review snippet is missing.",snippetLength:"The snippet length ( %s ) exceeds the maximum length of 200 characters.",selectionLength:"Selection length:"}},"undefined"!=typeof _varietyFilmReviewAdminExports&&n.extend(i,_varietyFilmReviewAdminExports),i.init=function(){n(document).ready((function(){i.listen.cat=n('select[name="relationships[category][categories]"]'),i.listen.tag=".fm-post_tag",i.targets=n("#film-review-grp, #variety_review_credit"),i.watchEditor(),i.watchPublish(),i.initChecks()}))},i.initChecks=function(){i.checkCat(),i.checkTag(),i.listen.cat.on("change",(function(){i.checkCat()})),n(i.listen.tag).on("click",".fmjs-remove",(function(){setTimeout((function(){i.checkTag()}),500)})),n(i.listen.tag).on("change","select",(function(){i.checkTag()}))},i.checkCat=function(){i.isCat=i.terms.cat.toString()===i.listen.cat.val(),i.triggerButton()},i.checkTag=function(){var e=n(i.listen.tag).find("select");i.isTag=!1,e.each((function(){i.terms.tag.toString()===n(this).val().toString()&&(i.isTag=!0)})),i.triggerButton()},i.isReview=function(){return i.isCat||i.isTag},i.triggerButton=function(){i.contentEditor&&(n(i.contentEditor).trigger("pmc_film_review_snippet_btn",i.isReview()),i.isReview()?n("#feedback-review-snippet").show():n("#feedback-review-snippet").hide()),i.isReview()?i.targets.show():i.targets.hide()},i.watchEditor=function(){try{tinymce.onAddEditor.add((function(e,t){"content"===t.id&&(i.contentEditor=t,n(i.contentEditor).on("pmc_film_review_snippet",(function(e,t){var o="";0===t.length?o=i.l10n.googleWarning:200<t.length&&(o=i.l10n.snippetLength.replace("%s",t.length)),0<o.length?1>n("#feedback-review-snippet").length?(n("#major-publishing-actions").after(n('<div class="feedback" id="feedback-review-snippet">'+o+"</div>")),200<t.length&&alert(o)):n("#feedback-review-snippet").text(o):n("#feedback-review-snippet").remove(),n("#pmc-film-review-snippet").val(t)})),n(i.contentEditor).on("pmc_selection_length",(function(e,t){var o=n("#pmc-selection-length");1>o.length&&n("#wp-word-count").append(" | "+i.l10n.selectionLength+'<span id="pmc-selection-length"></span>'),o.html(t)})),t.on("PostRender",(function(){i.initChecks()})))}))}catch(e){}},i.watchPublish=function(){n("#publish").on("click",(function(){try{i.activate&&0<n("#feedback-review-snippet").length&&alert(i.l10n.googleAlert)}catch(e){}}))}},9:function(e,t,n){"use strict";n.r(t);n(10),n(11)}});