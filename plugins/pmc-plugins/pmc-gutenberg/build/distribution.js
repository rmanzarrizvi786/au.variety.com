!function(){"use strict";var e={n:function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,{a:n}),n},d:function(t,n){for(var l in n)e.o(n,l)&&!e.o(t,l)&&Object.defineProperty(t,l,{enumerable:!0,get:n[l]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t=window.wp.element,n=window.lodash,l=window.wp.i18n,r=window.wp.components,o=window.wp.data,i=window.wp.editPost,s=window.wp.primitives,c=(0,t.createElement)(s.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"-2 -2 24 24"},(0,t.createElement)(s.Path,{d:"M9 0C4.03 0 0 4.03 0 9s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9zM1.11 9.68h2.51c.04.91.167 1.814.38 2.7H1.84c-.403-.85-.65-1.764-.73-2.7zm8.57-5.4V1.19c.964.366 1.756 1.08 2.22 2 .205.347.386.708.54 1.08l-2.76.01zm3.22 1.35c.232.883.37 1.788.41 2.7H9.68v-2.7h3.22zM8.32 1.19v3.09H5.56c.154-.372.335-.733.54-1.08.462-.924 1.255-1.64 2.22-2.01zm0 4.44v2.7H4.7c.04-.912.178-1.817.41-2.7h3.21zm-4.7 2.69H1.11c.08-.936.327-1.85.73-2.7H4c-.213.886-.34 1.79-.38 2.7zM4.7 9.68h3.62v2.7H5.11c-.232-.883-.37-1.788-.41-2.7zm3.63 4v3.09c-.964-.366-1.756-1.08-2.22-2-.205-.347-.386-.708-.54-1.08l2.76-.01zm1.35 3.09v-3.04h2.76c-.154.372-.335.733-.54 1.08-.464.92-1.256 1.634-2.22 2v-.04zm0-4.44v-2.7h3.62c-.04.912-.178 1.817-.41 2.7H9.68zm4.71-2.7h2.51c-.08.936-.327 1.85-.73 2.7H14c.21-.87.337-1.757.38-2.65l.01-.05zm0-1.35c-.046-.894-.176-1.78-.39-2.65h2.16c.403.85.65 1.764.73 2.7l-2.5-.05zm1-4H13.6c-.324-.91-.793-1.76-1.39-2.52 1.244.56 2.325 1.426 3.14 2.52h.04zm-9.6-2.52c-.597.76-1.066 1.61-1.39 2.52H2.65c.815-1.094 1.896-1.96 3.14-2.52zm-3.15 12H4.4c.324.91.793 1.76 1.39 2.52-1.248-.567-2.33-1.445-3.14-2.55l-.01.03zm9.56 2.52c.597-.76 1.066-1.61 1.39-2.52h1.76c-.82 1.08-1.9 1.933-3.14 2.48l-.01.04z"})),a=window.wp.plugins,u=window.wp.compose,d=(0,u.compose)((0,o.withSelect)((e=>({canonicalUrl:e("core/editor").getEditedPostAttribute("meta")._pmc_canonical_override}))),(0,o.withDispatch)((e=>({setMetaValue:t=>{e("core/editor").editPost({meta:{_pmc_canonical_override:t}})}}))))((({canonicalUrl:e,setMetaValue:n})=>(0,t.createElement)(r.TextControl,{label:"Canonical Override",help:"The Canonical URL, if different from the posts URL",value:e,onChange:e=>n(e)})));const m="pmcExactTarget",g="BNA",p="pmcEtBnaAck",h=p,b={selectedAlerts:[],sendOverride:!1,subjectOverride:""},w=(e,t=!0)=>{const n=e.sort().join((0,l._x)(", ","Separator used to join BNA list","pmc-gutenberg"));let r="";return r=t?(0,l.sprintf)(// translators: 1. List name or names for BNA.
(0,l._n)("A BNA will be sent to this list: %1$s. Confirm this action below.","A BNA will be sent to these lists: %1$s. Confirm this action below.",e.length,"pmc-gutenberg"),n):(0,l.sprintf)(// translators: 1. List name or names for BNA.
(0,l._n)("A BNA will be sent to this list: %1$s.","A BNA will be sent to these lists: %1$s.",e.length,"pmc-gutenberg"),n),r};var E=(0,u.compose)([(0,o.withSelect)((e=>{const{getEntityRecord:t,getEntityRecordEdits:n,hasFinishedResolution:l}=e("core"),{getCurrentPost:r}=e("core/editor"),{id:o,status:i}=r(),s=[m,g,o],c=t(...s),a=n(...s),u=l("getEntityRecord",s),d="draft"===i||"future"===i;let p=[],h="";return null!=a&&a.selectedAlerts?p=a.selectedAlerts:null!=c&&c.selectedAlerts&&d&&(p=null==c?void 0:c.selectedAlerts),null!=a&&a.subjectOverride?h=null==a?void 0:a.subjectOverride:null!=c&&c.subjectOverride&&d&&(h=c.subjectOverride),{allowCustomSubject:Boolean(null==c?void 0:c.allowSubject),bnas:(null==c?void 0:c.alerts)||[],isResolved:u,log:(null==c?void 0:c.log)||[],selectedAlerts:p,subjectOverride:h,sendOverride:Boolean(null==a?void 0:a.sendOverride)}})),(0,o.withDispatch)(((e,t,{select:r})=>{const{editEntityRecord:o}=e("core"),{lockPostSaving:i,unlockPostSaving:s}=e("core/editor"),{createSuccessNotice:c,createWarningNotice:a,removeNotice:u}=e("core/notices"),{getCurrentPostId:d,isCurrentPostPublished:E,isCurrentPostScheduled:v}=r("core/editor"),_=d(),P=[m,g,_];let S=[];const f={id:h,isDismissible:!1,actions:[{label:(0,l.__)("Acknowledge","pmc-gutenberg"),onClick:()=>{const e=JSON.parse(JSON.stringify(f));e.actions=[],c(w(S,!1),e),s(p)},isPrimary:!0,noDefaultClasses:!0},{label:(0,l.__)("Cancel","pmc-gutenberg"),onClick:()=>{o(...P,b),s(p),u(f.id)}}]};return{unlockPostSaving:s,updateSelections:(e,t,l)=>{var r;const c={};c.selectedAlerts=t?Array.prototype.concat(l,[e]):(0,n.without)(l,e),null!=c&&null!==(r=c.selectedAlerts)&&void 0!==r&&r.length?(i(p),(E()||v())&&(S=c.selectedAlerts,a(w(c.selectedAlerts),f))):(s(p),u(f.id)),o(...P,c)},updateSendOverride:e=>{o(...P,{sendOverride:e})},updateSubject:e=>{o(...P,{subjectOverride:e})}}}))])((({allowCustomSubject:e,bnas:n,isResolved:o,log:i,selectedAlerts:s,sendOverride:c,subjectOverride:a,updateSelections:u,updateSendOverride:d,updateSubject:m})=>o?(0,t.createElement)(t.Fragment,null,(0,t.createElement)(r.PanelRow,null,e&&(0,t.createElement)(r.TextControl,{label:(0,l.__)("Subject","pmc-gutenberg"),value:a,onChange:m})),(0,t.createElement)(r.PanelRow,null,(0,t.createElement)("div",null,n.map(((e,n)=>(0,t.createElement)("div",{key:n,className:"editor-post-taxonomies__hierarchical-terms-choice"},(0,t.createElement)(r.CheckboxControl,{label:e,key:e,checked:s.includes(e),onChange:t=>{u(e,t,s)}})))))),(0,t.createElement)(r.PanelRow,null,(0,t.createElement)(r.CheckboxControl,{label:(0,l.__)("Special Event Coverage Override","pmc-gutenberg"),help:(0,l.__)("Check this box if you want to send breaking news within five minutes of previous send.","pmc-gutenberg"),checked:c,onChange:d})),Boolean(i.length)&&(0,t.createElement)(r.PanelRow,null,(0,t.createElement)("div",null,(0,t.createElement)("p",null,(0,l.__)("Alert Log:","pmc-gutenberg")),(0,t.createElement)("ul",null,i.map(((e,n)=>(0,t.createElement)("li",{key:n},(0,t.createElement)(t.RawHTML,null,(0,l.sprintf)(
/* translators: 1. Timestamp, 2. Username, 3. Lists sent to. */
(0,l.__)("<strong>%1$s</strong> - sent by <strong><em>%2$s</em></strong> to:","pmc-gutenberg"),e.timestamp,e.username)),(0,t.createElement)("ol",null,e.lists.map(((e,n)=>(0,t.createElement)("li",{key:n},e))))))))))):(0,t.createElement)(r.PanelRow,null,(0,t.createElement)(r.Spinner,null))));const v=(0,u.compose)([(0,o.withSelect)((e=>{const{getEntityRecord:t,getEntityRecordEdits:n,hasFinishedResolution:l}=e("core"),{getCurrentPost:r,isCurrentPostPublished:o,isCurrentPostScheduled:i}=e("core/editor"),{id:s,status:c}=r(),a=[m,g,s],u=t(...a),d=n(...a),p=l("getEntityRecord",a);let h=[];return null!=d&&d.selectedAlerts?h=d.selectedAlerts:null!=u&&u.selectedAlerts&&"draft"===c&&(h=null==u?void 0:u.selectedAlerts),{isResolved:p,postId:s,selectedAlerts:h,shouldDisplay:!o()&&!i()&&p&&0!==h.length}})),(0,o.withDispatch)((e=>{const{editEntityRecord:t}=e("core"),{lockPostSaving:n,unlockPostSaving:l}=e("core/editor");return{editEntityRecord:t,lockPostSaving:n,unlockPostSaving:l}}))])((({editEntityRecord:e,lockPostSaving:n,postId:o,selectedAlerts:s,shouldDisplay:c,unlockPostSaving:a})=>{const[u,d]=(0,t.useState)(!1);if(!c)return null;u||n(p);const h=[{label:(0,l.__)("Acknowledge","pmc-gutenberg"),onClick:()=>{d(!0),a(p)},isPrimary:!0,noDefaultClasses:!0},{label:(0,l.__)("Cancel","pmc-gutenberg"),onClick:()=>{e(m,g,o,b),d(!1),a(p)}}];return(0,t.createElement)(i.PluginPrePublishPanel,null,(0,t.createElement)(r.Notice,{status:u?"success":"warning",isDismissible:!1,actions:u?[]:h},(0,t.createElement)("p",null,w(s,!u))))}));(0,a.registerPlugin)("pmc-exacttarget-bna-notice-prepublish",{render:v}),(0,o.subscribe)((()=>{const{isPublishSidebarEnabled:e}=(0,o.select)("core/editor");if(e())return;const{enablePublishSidebar:t}=(0,o.dispatch)("core/editor");t()}));const _=(0,o.subscribe)((()=>{const{addEntities:e}=(0,o.dispatch)("core");_(),e([{name:g,kind:m,baseURL:"pmc/exacttarget/v1/bna",label:(0,l.__)("Breaking News Alerts","pmc-gutenberg"),key:"id"}])}));let P=!1,S=!1,f=!1;(0,o.subscribe)((()=>{if(f)return;const{getCurrentPostId:e,didPostSaveRequestFail:t,isSavingPost:n,isAutosavingPost:l}=(0,o.select)("core/editor"),r=()=>{P=n(),S=l(),f=!1};if(l()||S||n()||!P)return void r();if(t())return void r();const{hasEditsForEntityRecord:i}=(0,o.select)("core"),s=e(),c=[m,g,s];if(!i(...c))return void r();const{saveEditedEntityRecord:a}=(0,o.dispatch)("core"),{removeNotice:u}=(0,o.dispatch)("core/notices");f=!0,a(...c),u(h),r()}));var y=window.wp.wordcount,C=({text:e})=>(0,t.createElement)("div",{className:"pmc-word-count"},(0,t.createElement)("p",null,(0,l._x)("Words","Number of words counted in given string","pmc-gutenberg"),": ",(0,y.count)(e,"words",{}),","," ",(0,l._x)("characters","Number of characters counted in given string","pmc-gutenberg"),": ",(0,y.count)(e,"characters_including_spaces",{}))),A=(0,u.compose)((0,o.withSelect)((e=>({seoTitle:e("core/editor").getEditedPostAttribute("meta").mt_seo_title}))),(0,o.withDispatch)((e=>({setMetaValue:t=>{e("core/editor").editPost({meta:{mt_seo_title:t}})}}))))((({seoTitle:e,setMetaValue:n})=>(0,t.createElement)("div",null,(0,t.createElement)(r.TextControl,{label:(0,l.__)("SEO Title (70 Char max)","pmc-gutenberg"),help:(0,l.__)("The text entered here will alter the <title> tag using the wp_title() function. Use %title% to include the original title or leave empty to keep original title.","pmc-gutenberg"),value:e,onChange:e=>n(e)}),(0,t.createElement)(C,{text:e})))),k=(0,u.compose)((0,o.withSelect)((e=>({seoDescription:e("core/editor").getEditedPostAttribute("meta").mt_seo_description}))),(0,o.withDispatch)((e=>({setMetaValue:t=>{e("core/editor").editPost({meta:{mt_seo_description:t}})}}))))((({seoDescription:e,setMetaValue:n})=>(0,t.createElement)("div",null,(0,t.createElement)(r.TextareaControl,{label:(0,l.__)("SEO Description (200 char max)","pmc-gutenberg"),help:(0,l.__)("This text will be used as description meta information. Left empty, a description is automatically generated.","pmc-gutenberg"),value:e,onChange:e=>n(e)}),(0,t.createElement)(C,{text:e})))),R=window.wp.apiFetch,x=e.n(R),O=(0,u.compose)([(0,u.withState)(),(0,o.withSelect)(((e,{taxonomySlug:t})=>{const{getTaxonomy:n}=e("core"),l=n(t);return l?{currentTerms:e("core/editor").getEditedPostAttribute(l.rest_base),taxonomyRestBase:l.rest_base}:{currentTerms:[],taxonomyRestBase:null}})),(0,o.withDispatch)(((e,{currentTerms:t,taxonomyRestBase:l})=>({updateTerms:r=>{const o=-1!==t.indexOf(r)?(0,n.without)(t,r):[...t,r];e("core/editor").editPost({[l]:o})}})))])((e=>{const{currentTerms:n,help:l,label:o,updateTerms:i,taxonomyRestBase:s,termSlug:c}=e,[a,u]=(0,t.useState)(),[d,m]=(0,t.useState)();return(0,t.useEffect)((()=>{(({currentTerms:e,isChecked:t,taxonomyRestBase:n,termId:l,termSlug:r,setIsChecked:o,setTermId:i})=>{"number"==typeof l&&"boolean"==typeof t||n&&x()({path:`/wp/v2/${n}`}).then((t=>{const n=t.filter((e=>e.slug===r))[0].id;i(n),o(e.includes(n))}))})({currentTerms:n,isChecked:a,taxonomyRestBase:s,termId:d,termSlug:c,setIsChecked:u,setTermId:m})})),"number"!=typeof d&&"boolean"!=typeof a?(0,t.createElement)(r.Spinner,null):(0,t.createElement)(r.ToggleControl,{label:o,help:l[a],checked:a,onChange:e=>{u(e),i(d)}})})),T=()=>{const e={true:(0,l.__)("Will not appear in Google News","pmc-gutenberg"),false:(0,l.__)("Can appear in Google News","pmc-gutenberg")};return(0,t.createElement)(O,{taxonomySlug:"_post-options",termSlug:"exclude-from-google-news",label:(0,l.__)("Google News","pmc-gutenberg"),help:e})};const B=()=>(0,t.createElement)(r.Panel,null,(0,t.createElement)(r.PanelBody,{title:(0,l.__)("Exclusions","pmc-gutenberg"),initialOpen:!1},(0,t.createElement)(r.PanelRow,null,(0,t.createElement)(T,null)))),N=()=>(0,t.createElement)(r.Panel,null,(0,t.createElement)(r.PanelBody,{title:(0,l.__)("SEO","pmc-gutenberg"),initialOpen:!0},(0,t.createElement)(r.PanelRow,null,(0,t.createElement)(A,null)),(0,t.createElement)(r.PanelRow,null,(0,t.createElement)(k,null)),(0,t.createElement)(r.PanelRow,null,(0,t.createElement)(d,null)))),j=()=>{const{getCurrentPost:e}=(0,o.select)("core/editor");return(0,n.get)(e(),["_links","pmc:exact-target-supported"],!1)?(0,t.createElement)(r.Panel,null,(0,t.createElement)(r.PanelBody,{title:(0,l.__)("Breaking News Alerts","pmc-gutenberg"),initialOpen:!1},(0,t.createElement)(E,null))):null};(0,a.registerPlugin)("pmc-distribution",{render:()=>{const e="pmc-distribution",n=(0,l.__)("Distribution","pmc-gutenberg");return(0,t.createElement)(t.Fragment,null,(0,t.createElement)(i.PluginSidebar,{name:e,title:n,icon:c},(0,t.createElement)(N,null),(0,t.createElement)(B,null),(0,t.createElement)(j,null)),(0,t.createElement)(i.PluginSidebarMoreMenuItem,{target:e,icon:c},n))}})}();