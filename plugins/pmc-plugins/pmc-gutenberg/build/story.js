!function(){"use strict";var e=window.wp.blocks,t=window.lodash,l=window.wp.data,a=window.wp.i18n;const r={className:{type:"string",default:""},postType:{type:"string",default:"post"},postID:{type:"number",default:null},taxonomySlug:{type:"string",default:null},viewMoreText:{type:"string",default:null},hasDisplayedExcerpt:{type:"boolean",default:!0},hasDisplayedByline:{type:"boolean",default:!0},hasDisplayedPrimaryTerm:{type:"boolean",default:!0},hasFullWidthImage:{type:"boolean",default:!1},alignment:{type:"string",default:"none"},title:{type:"string",default:null},excerpt:{type:"string",default:null},featuredImageID:{type:"number",default:null},contentOverride:{type:"string",default:null},hasContentOverride:{type:"boolean",default:!1},backgroundColor:{type:"string",default:null}};var n=window.wp.element,o=window.wp.blockEditor,s=window.wp.components,c=window.wp.primitives,i=(0,n.createElement)(c.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,n.createElement)(c.Path,{d:"M4 19.8h8.9v-1.5H4v1.5zm8.9-15.6H4v1.5h8.9V4.2zm-8.9 7v1.5h16v-1.5H4z"})),m=(0,n.createElement)(c.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,n.createElement)(c.Path,{d:"M11.1 19.8H20v-1.5h-8.9v1.5zm0-15.6v1.5H20V4.2h-8.9zM4 12.8h16v-1.5H4v1.5z"})),p=(0,n.createElement)(c.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,n.createElement)(c.Path,{d:"M16.4 4.2H7.6v1.5h8.9V4.2zM4 11.2v1.5h16v-1.5H4zm3.6 8.6h8.9v-1.5H7.6v1.5z"}));var u=window.wp.compose;const d=({onChange:e})=>{const t=(0,u.useInstanceId)(d);return(0,n.createElement)("div",null,(0,n.createElement)("label",{htmlFor:`pmc-story-card__search-${t}`},(0,a.__)("Next, search for a post by title: ","pmc-gutenberg")),(0,n.createElement)("input",{style:{width:"100%"},id:`pmc-story-card__search-${t}`,type:"search",placeholder:(0,a.__)("Search for a post","pmc-gutenberg"),onChange:t=>e(t.target.value),autoComplete:"off"}))},g=({postType:e,onChangePostID:r,onMorePosts:o,maxPosts:c,keywords:i})=>{const{posts:m,morePostsAvailable:p,isLoading:u}=(0,l.useSelect)((l=>{const a="postType",r=e,n={per_page:100,orderby:"date",order:"desc",status:["publish","draft","future"],search:i},{getEntityRecords:o,getMedia:s}=l("core"),{isResolving:m}=l("core/data");let p=[];for(let e=1;e<=Math.ceil(c/100);e++){const t=o(a,r,{...n,page:e});t&&(p=p.concat(t))}return{posts:Array.isArray(p)?p.slice(0,c).map((e=>(({post:e,getMedia:l})=>{if(e&&e.featured_media){const a=l(e.featured_media);let r=(0,t.get)(a,["media_details","sizes","large","source_url"],null);return r||(r=(0,t.get)(a,"source_url",null)),{...e,featuredImageSourceUrl:r,featuredImageAltText:a&&a.alt_text?a.alt_text:""}}return e})({post:e,getMedia:s}))):p,morePostsAvailable:()=>p.length>c,isLoading:()=>{const e=[!1];for(let t=1;t<=Math.ceil(c/100);t++)e.push(m("core","getEntityRecords",[a,r,{...n,page:t}]));for(const t of p.slice(0,c))t&&e.push(m("core","getMedia",[t.featured_media]));return e.reduce(((e,t)=>e||t))}}}),[i,c]);return m&&m.length>0?(0,n.createElement)("fieldset",{className:"pmc-story-card-search-results__fieldset"},(0,n.createElement)(s.VisuallyHidden,{as:"legend"},(0,a.__)("Choose a post","pmc-gutenberg")),(0,n.createElement)("ol",{className:"pmc-story-card-search-results__list"},m.map((e=>e?(0,n.createElement)("li",{className:"pmc-story-card-search-results__item",key:e.id},(0,n.createElement)(s.Button,{isSecondary:!0,onClick:()=>{r(e.id)},className:"pmc-story-card-search-results__button"},(0,n.createElement)("span",{className:"pmc-story-card-search-results__title"},e.title.raw,(0,n.createElement)("i",{className:"pmc-story-card-search-results__title-label"},"publish"!==e.status?" - "+e.status:"")),(0,n.createElement)("span",{className:"pmc-story-card-search-results__image-container"},(0,n.createElement)("img",{className:"pmc-story-card-search-results__image",src:e.featuredImageSourceUrl,alt:e.featuredImageAltText})))):""))),p()&&(0,n.createElement)(s.Button,{className:"pmc-story-card-search-results__more-button",isPrimary:!0,disabled:u(),isBusy:u(),onClick:()=>{o()}},"More Posts")):u()?(0,n.createElement)(s.Spinner,null):i?(0,n.createElement)("p",{className:"pmc-story-card-search-results__note"},`No posts found for “${i}”.`):(0,n.createElement)("p",{className:"pmc-story-card-search-results__note"},"No posts found.")},h=({onChangePostType:e,postType:t,onChangePostID:l,postTypeSelectOptions:r})=>{const[o,c]=(0,n.useState)(null),[i,m]=(0,n.useState)(5);return(0,n.createElement)(s.Placeholder,{className:"pmc-story-card-setup",icon:"format-aside",label:(0,a.__)("Story Setup","pmc-gutenberg"),instructions:(0,a.__)("Choose a post to display in this story card.","pmc-gutenberg"),isColumnLayout:!0},(0,n.createElement)(s.SelectControl,{label:(0,a.__)("First, select a post type to search: ","pmc-gutenberg"),value:t,onChange:e,options:r}),(0,n.createElement)(d,{onChange:c}),(0,n.createElement)("div",{style:{minHeight:"200px"}},(0,n.createElement)(g,{keywords:o,maxPosts:i,postType:t,onChangePostID:l,onMorePosts:function(){m(i+5)}})))},_=({imageID:e,hasFullWidthImage:a})=>{const{src:r,alt:o}=(0,l.useSelect)((l=>{const a={src:null,alt:null};if(e<1)return a;const{getMedia:r}=l("core"),n=r(e);let o=(0,t.get)(n,["media_details","sizes","large","source_url"],null);return o||(o=(0,t.get)(n,"source_url",null)),a.src=o,a.alt=n&&n.alt_text?n.alt_text:"",a}),[e]);return r?(0,n.createElement)("img",{src:r,alt:o,style:{width:a?"auto":"50%"},className:"pmc-story-card-preview__featured-image"}):(0,n.createElement)(s.Spinner,null)},y=({value:e,onChange:t})=>(0,n.createElement)(o.RichText,{value:e,onChange:t,allowedFormats:["core/bold","core/italic"],className:"pmc-story-card-preview__title"}),b=({value:e,onChange:t})=>(0,n.createElement)(o.RichText,{value:e,onChange:t,allowedFormats:[],className:"pmc-story-card-preview__excerpt"}),v=({postType:e,postID:t,contentOverride:r,hasContentOverride:c,hasDisplayedExcerpt:i,hasFullWidthImage:m,alignment:p,title:u,excerpt:d,featuredImageID:g,onContentOverrideUpdate:h,onChangeTitle:v,onChangeExcerpt:w,viewMoreText:E})=>{var f;const C=(0,l.useSelect)((l=>{const{getEntityRecord:a}=l("core");return a("postType",e,t)}),[t]);if(!C)return(0,n.createElement)(s.Spinner,null);const x=null!==(f=null!=g?g:C.featured_media)&&void 0!==f&&f;return d||(d=C.excerpt?C.excerpt.rendered:""),(0,n.createElement)("div",{className:"pmc-story-card-edit",style:{textAlign:p}},(0,n.createElement)(y,{value:u||C.title.raw,onChange:v}),Boolean(x)&&(0,n.createElement)(_,{imageID:x,hasFullWidthImage:m}),Boolean(i)&&(0,n.createElement)(b,{value:d,onChange:w}),(0,n.createElement)("p",{className:"pmc-story-card-preview__link"},(0,n.createElement)("a",{href:C.link},E)),t&&c&&(0,n.createElement)(o.RichText,{value:r,placeholder:(0,a.__)("Enter excerpt override…","pmc-gutenberg"),onChange:h,tagName:"p"}))},w=(0,o.withColors)("backgroundColor",{backgroundColor:"color"})((t=>{const{attributes:{postType:c,postID:u,contentOverride:d,hasContentOverride:g,hasDisplayedExcerpt:_,hasDisplayedByline:y,hasDisplayedPrimaryTerm:b,hasFullWidthImage:w,alignment:f,title:C,excerpt:x,featuredImageID:T,viewMoreText:I,className:k},backgroundColor:S,name:D,setAttributes:P,setBackgroundColor:M}=t,{__unstableMarkNextChangeAsNotPersistent:N}=(0,l.useDispatch)("core/block-editor"),B=window[(H=D,`pmc_${H.split("/")[1].replace(/-/g,"_")}_block_config`)],O=E(B),F=(0,e.hasBlockSupport)(D,"pmc.colors.background",!1),R=(0,e.hasBlockSupport)(D,"pmc.contentOverride",!1),z=(0,e.hasBlockSupport)(D,"pmc.fullWidthImage",!1),A=e=>{P({taxonomySlug:B[e].taxonomySlug,viewMoreText:B[e].viewMoreText})};var H;N(),A(c);const W=e=>{P({alignment:void 0===e?"none":e})},V=[{icon:i,title:(0,a.__)("Align left","pmc-gutenberg"),align:"left"}];(null===k||void 0!==k&&-1!==k.indexOf("horizontal"))&&(V.push({icon:m,title:(0,a.__)("Align right","pmc-gutenberg"),align:"right"}),"center"===f&&W("right")),void 0!==k&&-1!==k.indexOf("vertical")&&(V.push({icon:p,title:(0,a.__)("Align center","pmc-gutenberg"),align:"center"}),"right"===f&&W("center"));const U=(0,s.withFilters)("pmcGutenberg.storyBlock.additionalDisplayControls")((e=>(0,n.createElement)(n.Fragment,null)));return(0,n.createElement)(n.Fragment,null,u&&(0,n.createElement)(o.BlockControls,null,(0,n.createElement)(o.AlignmentToolbar,{value:f,onChange:W,alignmentControls:V}),(0,n.createElement)(s.ToolbarButton,{label:(0,a.__)("Replace","pmc-gutenberg"),onClick:()=>{const e={};for(const[t,l]of Object.entries(r))e[t]=l.default;e.postType=c,e.taxonomySlug=B[c].taxonomySlug,e.viewMoreText=B[c].viewMoreText,P(e)}},(0,a.__)("Replace","pmc-gutenberg")),(0,n.createElement)(o.MediaReplaceFlow,{allowedTypes:["image"],accept:"image/*",onSelect:e=>{P({featuredImageID:void 0===e?null:e.id})},name:(0,a.__)("Override Image","pmc-gutenberg")})),u?(0,n.createElement)(v,{postType:c,postID:u,contentOverride:d,hasContentOverride:g,hasDisplayedExcerpt:_,hasFullWidthImage:w,alignment:f,title:C,excerpt:x,featuredImageID:T,onChangeTitle:e=>{P({title:void 0===e?null:e})},onChangeExcerpt:e=>{P({excerpt:void 0===e?null:e})},onContentOverrideUpdate:e=>{P({contentOverride:e})},viewMoreText:I}):(0,n.createElement)(h,{postType:c,onChangePostID:e=>{P({postID:void 0===e?null:e})},onChangePostType:e=>{P({postType:e}),A(e)},placeholderTitle:(0,a.__)("Select a Story","pmc-gutenberg"),postTypeSelectOptions:O}),(0,n.createElement)(o.InspectorControls,null,(0,n.createElement)(s.Panel,null,(0,n.createElement)(s.PanelBody,{title:(0,a.__)("Display Settings","pmc-gutenberg"),initialOpen:!0},(0,n.createElement)(s.PanelRow,null,(0,n.createElement)(s.ToggleControl,{label:(0,a.__)("Display dek?","pmc-gutenberg"),help:_?(0,a.__)("Dek will be shown (if design includes it).","pmc-gutenberg"):(0,a.__)("Dek will be hidden (if design includes it).","pmc-gutenberg"),checked:_,onChange:e=>{P({hasDisplayedExcerpt:void 0===e||e})}})),(0,n.createElement)(s.PanelRow,null,(0,n.createElement)(s.ToggleControl,{label:(0,a.__)("Display byline?","pmc-gutenberg"),help:y?(0,a.__)("Byline will be shown (if design includes it).","pmc-gutenberg"):(0,a.__)("Byline will be hidden (if design includes it).","pmc-gutenberg"),checked:y,onChange:e=>{P({hasDisplayedByline:void 0===e||e})}})),(0,n.createElement)(s.PanelRow,null,(0,n.createElement)(s.ToggleControl,{label:(0,a.__)("Display breadcrumb?","pmc-gutenberg"),help:b?(0,a.__)("Taxonomy term (breadcrumb) will be shown (if design includes it).","pmc-gutenberg"):(0,a.__)("Taxonomy term (breadcrumb) will be hidden (if design includes it).","pmc-gutenberg"),checked:b,onChange:e=>{P({hasDisplayedPrimaryTerm:void 0===e||e})}})),z&&(0,n.createElement)(s.PanelRow,null,(0,n.createElement)(s.ToggleControl,{label:(0,a.__)("Make image full-width","pmc-gutenberg"),help:w?(0,a.__)("Image fills the width of the story card.","pmc-gutenberg"):(0,a.__)("Image is thumbnail size.","pmc-gutenberg"),checked:w,onChange:e=>{P({hasFullWidthImage:void 0!==e&&e})}})),R&&(0,n.createElement)(s.PanelRow,null,(0,n.createElement)(s.ToggleControl,{label:(0,a.__)("Override excerpt?","pmc-gutenberg"),help:g?(0,a.__)("Use customized post excerpt.","pmc-gutenberg"):(0,a.__)("Use automatically-generated excerpt.","pmc-gutenberg"),checked:g,onChange:e=>{P({hasContentOverride:e})}})),(0,n.createElement)(U,t)),F&&(0,n.createElement)(o.PanelColorSettings,{title:(0,a.__)("Colors","pmc-gutenberg"),colorSettings:[{value:S.color,onChange:M,label:(0,a.__)("Background Color","pmc-gutenberg")}]}))))})),E=e=>{const{getPostTypes:t}=(0,l.select)("core"),a=t({per_page:-1});return Object.keys(e).map((t=>{const l=e[t].postType,r=null==a?void 0:a.filter((e=>l===e.slug));return{value:l,label:void 0===r||1!==r.length?l:r[0].labels.name}}))},f=((e={})=>(0,t.merge)({},{title:(0,a.__)("PMC Story","pmc-gutenberg"),description:(0,a.__)("Show a post summary and a link","pmc-gutenberg"),category:"embed",icon:"format-aside",supports:{anchor:!1,customClassName:!1,html:!1,pmc:{colors:{background:!1},contentOverride:!1,fullWidthImage:!1}},attributes:r,edit:w,save:()=>null},e))();(0,e.registerBlockType)("pmc/story",f)}();