!function(){"use strict";var e=window.wp.element,t=window.lodash,n=window.wp.blockEditor,l=window.wp.components,o=window.wp.compose,r=window.wp.hooks,a=window.wp.i18n;const i="blocks.registerBlockType",p="pmc-gutenberg/builtin/paragraph",c=(0,o.createHigherOrderComponent)((t=>o=>{const{attributes:{typographyFontSize:r},setAttributes:i}=o,p=[{value:null,label:(0,a.__)("Medium (default)","pmc-gutenberg")},{value:"body-s",label:(0,a.__)("Small","pmc-gutenberg")}];return(0,e.createElement)(e.Fragment,null,(0,e.createElement)(t,o),(0,e.createElement)(n.InspectorControls,null,(0,e.createElement)(l.Panel,null,(0,e.createElement)(l.PanelBody,{title:(0,a.__)("Typography","pmc-gutenberg"),initialOpen:!1},(0,e.createElement)(l.PanelRow,null,(0,e.createElement)(l.SelectControl,{label:(0,a.__)("Font Size","pmc-gutenberg"),value:r,options:p,onChange:e=>{i({typographyFontSize:e})}}))))))}),"extendEdit");(0,r.addFilter)(i,p,((e,n)=>"core/paragraph"!==n?e:((0,r.removeFilter)(i,p),(0,t.merge)({},e,{attributes:{typographyFontSize:{type:"string",default:null}},edit:c(e.edit)}))))}();