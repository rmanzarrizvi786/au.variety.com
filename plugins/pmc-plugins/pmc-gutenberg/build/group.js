!function(){"use strict";var e=window.wp.element,o=window.lodash,t=window.wp.blockEditor,n=window.wp.components,l=window.wp.compose,r=window.wp.hooks,c=window.wp.i18n;const i="blocks.registerBlockType",a="pmc-gutenberg/builtin/group",d=(0,l.createHigherOrderComponent)((o=>l=>{const{attributes:{backgroundColor:r,fullBleedBackgroundColor:i},setAttributes:a}=l;return(0,e.createElement)(e.Fragment,null,(0,e.createElement)(o,l),(0,e.createElement)(t.InspectorControls,null,(0,e.createElement)(n.Panel,null,(0,e.createElement)(n.PanelBody,{title:(0,c.__)("Display options","pmc-gutenberg"),initialOpen:!1},(0,e.createElement)(n.PanelRow,null,r&&(0,e.createElement)(n.ToggleControl,{label:(0,c.__)("Extend background color to edges of screen.","pmc-gutenberg"),help:(0,c.__)("Applies only if a background color is chosen.","pmc-gutengerg"),checked:i,onChange:e=>{a({fullBleedBackgroundColor:e})}}))))))}),"extendEdit");(0,r.addFilter)(i,a,((e,t)=>"core/group"!==t?e:((0,r.removeFilter)(i,a),(0,o.merge)({},e,{attributes:{fullBleedBackgroundColor:{type:"boolean",default:!1}},supports:{color:{text:!1}},edit:d(e.edit)}))))}();