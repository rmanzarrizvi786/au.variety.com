!function(){"use strict";var e=window.wp.blocks,t=window.wp.i18n,o=window.wp.element,i=window.wp.blockEditor,n=window.wp.components,r=window.wp.compose,l=window.wp.data;const a="pmcAdm",c="locations-by-provider";var s=(0,r.compose)([(0,l.withSelect)(((e,{provider:t})=>{const{getEntityRecords:o,hasFinishedResolution:i}=e("core"),n=o(a,c),r=!i("getEntityRecords",[a,c]),l=[{label:"",value:""}];let s,d,p=t,g=!1;if(!r&&Boolean(n)&&(g=!(r||!Boolean(n))&&1===n.length,g?d=n[0].id:(s=n.map((({id:e,title:t})=>({label:t,value:e}))),s.unshift({label:"",value:""})),Boolean(t))){const e=n.filter((e=>e.id===t));if(Boolean(e.length)){const t=e.shift(),{locations:o}=t;p=t.title,Object.entries(o).forEach((([e,t])=>l.push({label:t,value:e})))}}return{hasSingleProvider:g,isResolving:r,locationOptions:l,providerOptions:s,providerTitle:p,singleProvider:d}}))])((({hasSingleProvider:e,isResolving:i,location:r,locationOptions:l,provider:a,providerOptions:c,providerTitle:s,setAttributes:d,singleProvider:p})=>{if(i)return(0,o.createElement)(n.Spinner,null);let g;return e&&d({provider:p}),g=e?(0,t.__)("Select an ad location to display in this block.","pmc-gutenberg"):(0,t.sprintf)(
/* translators: 1. Provider title. */
(0,t.__)("Select an ad location from those available for the %1$s provider.","pmc-gutenberg"),s),(0,o.createElement)("div",{className:"pmc-ad-select-wrapper"},!e&&(0,o.createElement)(n.SelectControl,{label:(0,t._x)("Provider","PMC Ad providers","pmc-gutenberg"),help:(0,t.__)("Select the ad provider for this block.","pmc-gutenberg"),options:c,value:a,onChange:e=>d({provider:e})}),Boolean(a)&&(0,o.createElement)(n.SelectControl,{label:(0,t._x)("Location","PMC Ad locations for chosen provider","pmc-gutenberg"),help:g,options:l,value:r,onChange:e=>d({location:e})}))})),d=(0,r.compose)([(0,l.withSelect)(((e,{attributes:{location:t,provider:o}})=>{const{getEntityRecords:i,hasFinishedResolution:n}=e("core"),r=i(a,c),l=!n("getEntityRecords",[a,c]);let s=!1,d=t,p=o;if(!l&&Boolean(o)&&Boolean(t)){s=1===r.length;const e=r.filter((e=>o===e.id));if(Boolean(e.length)){const o=e.shift();p=o.title,d=o.locations[t]}}return{hasSingleProvider:s,isResolving:l,locationTitle:d,providerTitle:p}})),(0,l.withDispatch)((e=>{const{addEntities:o}=e("core");o([{name:c,kind:a,baseURL:"/pmc/adm/v2/locations-by-provider",label:(0,t.__)("PMC Ads Providers","pmc-gutenberg"),key:"id"}])}))])((({attributes:{location:e,provider:r},hasSingleProvider:l,isResolving:a,locationTitle:c,providerTitle:d,setAttributes:p})=>{const g=(0,t.__)("Advertisement","pmc-gutenberg");if(r&&e){let e;return e=a?(0,o.createElement)(n.Spinner,null):l?c:`${c} from ${d}`,(0,o.createElement)(o.Fragment,null,(0,o.createElement)(n.Placeholder,{label:g,instructions:e}),(0,o.createElement)(i.BlockControls,{group:"other"},(0,o.createElement)(n.ToolbarButton,{onClick:()=>p({location:"",provider:""})},(0,t.__)("Replace","pmc-gutenberg"))))}const v=(0,o.createElement)(s,{location:e,provider:r,setAttributes:p});return(0,o.createElement)(n.Placeholder,{children:v,label:g})}));const p={title:(0,t.__)("PMC Ad","pmc-gutenberg"),description:(0,t.__)("Insert an ad in content.","pmc-gutenberg"),category:"embed",icon:"money-alt",supports:{anchor:!1,customClassName:!1,html:!1},attributes:{location:{type:"string",default:""},provider:{type:"string",default:""}},edit:d,save:()=>null};(0,e.registerBlockType)("pmc/ad",p)}();