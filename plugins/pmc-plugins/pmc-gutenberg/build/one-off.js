!function(){"use strict";var e=window.wp.element,t=window.wp.components,n=window.wp.blocks,o=window.wp.i18n;const s=void 0!==window.pmc_theme_one_offs?window.pmc_theme_one_offs:[],a={title:(0,o.__)("PMC One Off","pmc-gutenberg"),description:(0,o.__)("A block for handling custom interface provided by the theme.","pmc-gutenberg"),attributes:{oneOffTemplate:{type:"string"}},category:"design",icon:"screenoptions",supports:{anchor:!1,customClassName:!1,html:!1},keywords:s.map((e=>e.name)),edit:({attributes:n,setAttributes:a})=>{const l=(e=>{try{return e.map((e=>({label:e.name,value:e.slug})))}catch{return[]}})(s);return l.unshift({label:"Please Select a One Off",value:""}),(0,e.createElement)(t.SelectControl,{label:(0,o.__)("Select a One Off template: ","pmc-gutenberg"),value:n.oneOffTemplate,onChange:e=>a({oneOffTemplate:e}),options:l})},save:()=>null};void 0!==window.pmc_theme_one_offs&&(0,n.registerBlockType)("pmc/one-off",a)}();