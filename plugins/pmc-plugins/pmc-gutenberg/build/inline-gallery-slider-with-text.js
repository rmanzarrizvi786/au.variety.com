!function(){"use strict";var e={n:function(t){var l=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(l,{a:l}),l},d:function(t,l){for(var r in l)e.o(l,r)&&!e.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:l[r]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t=window.wp.blocks,l=window.wp.i18n;function r(){return r=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var l=arguments[t];for(var r in l)Object.prototype.hasOwnProperty.call(l,r)&&(e[r]=l[r])}return e},r.apply(this,arguments)}var n=window.wp.element,a=window.wp.blockEditor,c=window.wp.components,o=window.wp.primitives,i=(0,n.createElement)(o.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},(0,n.createElement)(o.Path,{d:"M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM5 4.5h14c.3 0 .5.2.5.5v8.4l-3-2.9c-.3-.3-.8-.3-1 0L11.9 14 9 12c-.3-.2-.6-.2-.8 0l-3.6 2.6V5c-.1-.3.1-.5.4-.5zm14 15H5c-.3 0-.5-.2-.5-.5v-2.4l4.1-3 3 1.9c.3.2.7.2.9-.1L16 12l3.5 3.4V19c0 .3-.2.5-.5.5z"})),s=window.wp.serverSideRender,g=e.n(s);const u={title:(0,l.__)("PMC Inline Gallery Slider with Text","pmc-gutenberg"),description:(0,l.__)("Create an inline gallery slider accompanied by several paragraphs of text.","pmc-gutenberg"),category:"theme",icon:"welcome-widgets-menus",keywords:["inline","gallery","text"],attributes:{svgSlug:{type:"string",default:""},heading:{type:"string",default:""},text:{type:"string",default:""}},supports:{anchor:!1,customClassName:!1,html:!1},edit:({attributes:{svgSlug:e,heading:t,text:o},isSelected:s,setAttributes:u})=>{const m="lrv-a-grid-item",p=(0,a.useBlockProps)();let d="lrv-u-margin-t-150";return s&&(d+=" lrv-u-border-a-1 lrv-u-border-color-grey-light"),(0,n.createElement)("div",{className:"lrv-a-grid lrv-a-cols lrv-a-cols2@desktop lrv-a-cols2@tablet"},(0,n.createElement)("div",{className:m},(0,n.createElement)("div",{className:"lrv-a-grid lrv-a-cols lrv-a-cols3@desktop lrv-a-cols3@tablet lrv-u-align-items-center"},(0,n.createElement)("div",{className:m},(({attributes:{slug:e},name:t,setAttributes:r})=>Boolean(e)?(0,n.createElement)(n.Fragment,null,(0,n.createElement)(g(),{attributes:{slug:e},block:t}),(0,n.createElement)(a.BlockControls,{group:"other"},(0,n.createElement)(c.ToolbarButton,{onClick:()=>{r({slug:""})}},(0,l.__)("Replace","pmc-gutenberg")))):1===pmcGutenbergSvgOptions.length?(0,n.createElement)(n.Fragment,null,(0,l.__)("There are no SVGs configured for this post type.","pmc-gutenberg")):(0,n.createElement)(c.Placeholder,{icon:i,label:(0,l.__)("SVG","pmc-gutenberg")},(0,n.createElement)(c.SelectControl,{value:e,options:pmcGutenbergSvgOptions,onChange:e=>{r({slug:e})}})))({name:"pmc/svg",attributes:{slug:e},setAttributes:({slug:e})=>{u({svgSlug:e})}})),(0,n.createElement)("div",{className:"lrv-a-grid-item lrv-a-span2"},!s&&(0,n.createElement)("h3",null,t),s&&(0,n.createElement)(c.TextControl,{value:t,placeholder:(0,l.__)("Enter title","pmc-gutenberg"),label:(0,l.__)("Enter title","pmc-gutenberg"),hideLabelFromVision:!0,onChange:e=>{u({heading:e})}}))),(0,n.createElement)(a.RichText,{value:o,placeholder:(0,l.__)("Enter review","pmc-gutenberg"),onChange:e=>{u({text:e})},tagName:"div",multiline:"p",className:d})),(0,n.createElement)("div",r({className:m},p),(0,n.createElement)(a.InnerBlocks,{allowedBlocks:["core/gallery"],template:[["core/gallery",{}]],templateLock:"all",renderAppender:!1})))},save:()=>(0,n.createElement)(a.InnerBlocks.Content,null)};(0,t.registerBlockType)("pmc/inline-gallery-slider-with-text",u)}();