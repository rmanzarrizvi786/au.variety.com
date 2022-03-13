!function(){"use strict";var e=window.wp.blocks,t=window.wp.i18n;const l=e=>`pmc_${e.split("/")[1].replace(/-/g,"_")}_block_config`;var a=window.lodash,r=window.wp.data;const n={className:{type:"string",default:""},postType:{type:"string",default:"post"},postID:{type:"number",default:null},taxonomySlug:{type:"string",default:null},viewMoreText:{type:"string",default:null},hasDisplayedExcerpt:{type:"boolean",default:!0},hasDisplayedByline:{type:"boolean",default:!0},hasDisplayedPrimaryTerm:{type:"boolean",default:!0},hasFullWidthImage:{type:"boolean",default:!1},alignment:{type:"string",default:"none"},title:{type:"string",default:null},excerpt:{type:"string",default:null},featuredImageID:{type:"number",default:null},contentOverride:{type:"string",default:null},hasContentOverride:{type:"boolean",default:!1},backgroundColor:{type:"string",default:null}};var o=window.wp.element,s=window.wp.blockEditor,c=window.wp.components,i=window.wp.primitives,m=(0,o.createElement)(i.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,o.createElement)(i.Path,{d:"M4 19.8h8.9v-1.5H4v1.5zm8.9-15.6H4v1.5h8.9V4.2zm-8.9 7v1.5h16v-1.5H4z"})),p=(0,o.createElement)(i.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,o.createElement)(i.Path,{d:"M11.1 19.8H20v-1.5h-8.9v1.5zm0-15.6v1.5H20V4.2h-8.9zM4 12.8h16v-1.5H4v1.5z"})),u=(0,o.createElement)(i.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,o.createElement)(i.Path,{d:"M16.4 4.2H7.6v1.5h8.9V4.2zM4 11.2v1.5h16v-1.5H4zm3.6 8.6h8.9v-1.5H7.6v1.5z"})),d=window.wp.compose;const g=({onChange:e})=>{const l=(0,d.useInstanceId)(g);return(0,o.createElement)("div",null,(0,o.createElement)("label",{htmlFor:`pmc-story-card__search-${l}`},(0,t.__)("Next, search for a post by title: ","pmc-gutenberg")),(0,o.createElement)("input",{style:{width:"100%"},id:`pmc-story-card__search-${l}`,type:"search",placeholder:(0,t.__)("Search for a post","pmc-gutenberg"),onChange:t=>e(t.target.value),autoComplete:"off"}))},h=({postType:e,onChangePostID:l,onMorePosts:n,maxPosts:s,keywords:i})=>{const{posts:m,morePostsAvailable:p,isLoading:u}=(0,r.useSelect)((t=>{const l="postType",r=e,n={per_page:100,orderby:"date",order:"desc",status:["publish","draft","future"],search:i},{getEntityRecords:o,getMedia:c}=t("core"),{isResolving:m}=t("core/data");let p=[];for(let e=1;e<=Math.ceil(s/100);e++){const t=o(l,r,{...n,page:e});t&&(p=p.concat(t))}return{posts:Array.isArray(p)?p.slice(0,s).map((e=>(({post:e,getMedia:t})=>{if(e&&e.featured_media){const l=t(e.featured_media);let r=(0,a.get)(l,["media_details","sizes","large","source_url"],null);return r||(r=(0,a.get)(l,"source_url",null)),{...e,featuredImageSourceUrl:r,featuredImageAltText:l&&l.alt_text?l.alt_text:""}}return e})({post:e,getMedia:c}))):p,morePostsAvailable:()=>p.length>s,isLoading:()=>{const e=[!1];for(let t=1;t<=Math.ceil(s/100);t++)e.push(m("core","getEntityRecords",[l,r,{...n,page:t}]));for(const t of p.slice(0,s))t&&e.push(m("core","getMedia",[t.featured_media]));return e.reduce(((e,t)=>e||t))}}}),[i,s]);return m&&m.length>0?(0,o.createElement)("fieldset",{className:"pmc-story-card-search-results__fieldset"},(0,o.createElement)(c.VisuallyHidden,{as:"legend"},(0,t.__)("Choose a post","pmc-gutenberg")),(0,o.createElement)("ol",{className:"pmc-story-card-search-results__list"},m.map((e=>e?(0,o.createElement)("li",{className:"pmc-story-card-search-results__item",key:e.id},(0,o.createElement)(c.Button,{isSecondary:!0,onClick:()=>{l(e.id)},className:"pmc-story-card-search-results__button"},(0,o.createElement)("span",{className:"pmc-story-card-search-results__title"},e.title.raw,(0,o.createElement)("i",{className:"pmc-story-card-search-results__title-label"},"publish"!==e.status?" - "+e.status:"")),(0,o.createElement)("span",{className:"pmc-story-card-search-results__image-container"},(0,o.createElement)("img",{className:"pmc-story-card-search-results__image",src:e.featuredImageSourceUrl,alt:e.featuredImageAltText})))):""))),p()&&(0,o.createElement)(c.Button,{className:"pmc-story-card-search-results__more-button",isPrimary:!0,disabled:u(),isBusy:u(),onClick:()=>{n()}},"More Posts")):u()?(0,o.createElement)(c.Spinner,null):i?(0,o.createElement)("p",{className:"pmc-story-card-search-results__note"},`No posts found for “${i}”.`):(0,o.createElement)("p",{className:"pmc-story-card-search-results__note"},"No posts found.")},_=({onChangePostType:e,postType:l,onChangePostID:a,postTypeSelectOptions:r})=>{const[n,s]=(0,o.useState)(null),[i,m]=(0,o.useState)(5);return(0,o.createElement)(c.Placeholder,{className:"pmc-story-card-setup",icon:"format-aside",label:(0,t.__)("Story Setup","pmc-gutenberg"),instructions:(0,t.__)("Choose a post to display in this story card.","pmc-gutenberg"),isColumnLayout:!0},(0,o.createElement)(c.SelectControl,{label:(0,t.__)("First, select a post type to search: ","pmc-gutenberg"),value:l,onChange:e,options:r}),(0,o.createElement)(g,{onChange:s}),(0,o.createElement)("div",{style:{minHeight:"200px"}},(0,o.createElement)(h,{keywords:n,maxPosts:i,postType:l,onChangePostID:a,onMorePosts:function(){m(i+5)}})))},y=({imageID:e,hasFullWidthImage:t})=>{const{src:l,alt:n}=(0,r.useSelect)((t=>{const l={src:null,alt:null};if(e<1)return l;const{getMedia:r}=t("core"),n=r(e);let o=(0,a.get)(n,["media_details","sizes","large","source_url"],null);return o||(o=(0,a.get)(n,"source_url",null)),l.src=o,l.alt=n&&n.alt_text?n.alt_text:"",l}),[e]);return l?(0,o.createElement)("img",{src:l,alt:n,style:{width:t?"auto":"50%"},className:"pmc-story-card-preview__featured-image"}):(0,o.createElement)(c.Spinner,null)},b=({value:e,onChange:t})=>(0,o.createElement)(s.RichText,{value:e,onChange:t,allowedFormats:["core/bold","core/italic"],className:"pmc-story-card-preview__title"}),v=({value:e,onChange:t})=>(0,o.createElement)(s.RichText,{value:e,onChange:t,allowedFormats:[],className:"pmc-story-card-preview__excerpt"}),w=({postType:e,postID:l,contentOverride:a,hasContentOverride:n,hasDisplayedExcerpt:i,hasFullWidthImage:m,alignment:p,title:u,excerpt:d,featuredImageID:g,onContentOverrideUpdate:h,onChangeTitle:_,onChangeExcerpt:w,viewMoreText:E})=>{var f;const C=(0,r.useSelect)((t=>{const{getEntityRecord:a}=t("core");return a("postType",e,l)}),[l]);if(!C)return(0,o.createElement)(c.Spinner,null);const x=null!==(f=null!=g?g:C.featured_media)&&void 0!==f&&f;return d||(d=C.excerpt?C.excerpt.rendered:""),(0,o.createElement)("div",{className:"pmc-story-card-edit",style:{textAlign:p}},(0,o.createElement)(b,{value:u||C.title.raw,onChange:_}),Boolean(x)&&(0,o.createElement)(y,{imageID:x,hasFullWidthImage:m}),Boolean(i)&&(0,o.createElement)(v,{value:d,onChange:w}),(0,o.createElement)("p",{className:"pmc-story-card-preview__link"},(0,o.createElement)("a",{href:C.link},E)),l&&n&&(0,o.createElement)(s.RichText,{value:a,placeholder:(0,t.__)("Enter excerpt override…","pmc-gutenberg"),onChange:h,tagName:"p"}))},E=(0,s.withColors)("backgroundColor",{backgroundColor:"color"})((a=>{const{attributes:{postType:i,postID:d,contentOverride:g,hasContentOverride:h,hasDisplayedExcerpt:y,hasDisplayedByline:b,hasDisplayedPrimaryTerm:v,hasFullWidthImage:E,alignment:C,title:x,excerpt:T,featuredImageID:k,viewMoreText:I,className:S},backgroundColor:D,name:P,setAttributes:M,setBackgroundColor:N}=a,{__unstableMarkNextChangeAsNotPersistent:O}=(0,r.useDispatch)("core/block-editor"),B=window[l(P)],R=f(B),F=(0,e.hasBlockSupport)(P,"pmc.colors.background",!1),z=(0,e.hasBlockSupport)(P,"pmc.contentOverride",!1),A=(0,e.hasBlockSupport)(P,"pmc.fullWidthImage",!1),H=e=>{M({taxonomySlug:B[e].taxonomySlug,viewMoreText:B[e].viewMoreText})};O(),H(i);const W=e=>{M({alignment:void 0===e?"none":e})},V=[{icon:m,title:(0,t.__)("Align left","pmc-gutenberg"),align:"left"}];(null===S||void 0!==S&&-1!==S.indexOf("horizontal"))&&(V.push({icon:p,title:(0,t.__)("Align right","pmc-gutenberg"),align:"right"}),"center"===C&&W("right")),void 0!==S&&-1!==S.indexOf("vertical")&&(V.push({icon:u,title:(0,t.__)("Align center","pmc-gutenberg"),align:"center"}),"right"===C&&W("center"));const U=(0,c.withFilters)("pmcGutenberg.storyBlock.additionalDisplayControls")((e=>(0,o.createElement)(o.Fragment,null)));return(0,o.createElement)(o.Fragment,null,d&&(0,o.createElement)(s.BlockControls,null,(0,o.createElement)(s.AlignmentToolbar,{value:C,onChange:W,alignmentControls:V}),(0,o.createElement)(c.ToolbarButton,{label:(0,t.__)("Replace","pmc-gutenberg"),onClick:()=>{const e={};for(const[t,l]of Object.entries(n))e[t]=l.default;e.postType=i,e.taxonomySlug=B[i].taxonomySlug,e.viewMoreText=B[i].viewMoreText,M(e)}},(0,t.__)("Replace","pmc-gutenberg")),(0,o.createElement)(s.MediaReplaceFlow,{allowedTypes:["image"],accept:"image/*",onSelect:e=>{M({featuredImageID:void 0===e?null:e.id})},name:(0,t.__)("Override Image","pmc-gutenberg")})),d?(0,o.createElement)(w,{postType:i,postID:d,contentOverride:g,hasContentOverride:h,hasDisplayedExcerpt:y,hasFullWidthImage:E,alignment:C,title:x,excerpt:T,featuredImageID:k,onChangeTitle:e=>{M({title:void 0===e?null:e})},onChangeExcerpt:e=>{M({excerpt:void 0===e?null:e})},onContentOverrideUpdate:e=>{M({contentOverride:e})},viewMoreText:I}):(0,o.createElement)(_,{postType:i,onChangePostID:e=>{M({postID:void 0===e?null:e})},onChangePostType:e=>{M({postType:e}),H(e)},placeholderTitle:(0,t.__)("Select a Story","pmc-gutenberg"),postTypeSelectOptions:R}),(0,o.createElement)(s.InspectorControls,null,(0,o.createElement)(c.Panel,null,(0,o.createElement)(c.PanelBody,{title:(0,t.__)("Display Settings","pmc-gutenberg"),initialOpen:!0},(0,o.createElement)(c.PanelRow,null,(0,o.createElement)(c.ToggleControl,{label:(0,t.__)("Display dek?","pmc-gutenberg"),help:y?(0,t.__)("Dek will be shown (if design includes it).","pmc-gutenberg"):(0,t.__)("Dek will be hidden (if design includes it).","pmc-gutenberg"),checked:y,onChange:e=>{M({hasDisplayedExcerpt:void 0===e||e})}})),(0,o.createElement)(c.PanelRow,null,(0,o.createElement)(c.ToggleControl,{label:(0,t.__)("Display byline?","pmc-gutenberg"),help:b?(0,t.__)("Byline will be shown (if design includes it).","pmc-gutenberg"):(0,t.__)("Byline will be hidden (if design includes it).","pmc-gutenberg"),checked:b,onChange:e=>{M({hasDisplayedByline:void 0===e||e})}})),(0,o.createElement)(c.PanelRow,null,(0,o.createElement)(c.ToggleControl,{label:(0,t.__)("Display breadcrumb?","pmc-gutenberg"),help:v?(0,t.__)("Taxonomy term (breadcrumb) will be shown (if design includes it).","pmc-gutenberg"):(0,t.__)("Taxonomy term (breadcrumb) will be hidden (if design includes it).","pmc-gutenberg"),checked:v,onChange:e=>{M({hasDisplayedPrimaryTerm:void 0===e||e})}})),A&&(0,o.createElement)(c.PanelRow,null,(0,o.createElement)(c.ToggleControl,{label:(0,t.__)("Make image full-width","pmc-gutenberg"),help:E?(0,t.__)("Image fills the width of the story card.","pmc-gutenberg"):(0,t.__)("Image is thumbnail size.","pmc-gutenberg"),checked:E,onChange:e=>{M({hasFullWidthImage:void 0!==e&&e})}})),z&&(0,o.createElement)(c.PanelRow,null,(0,o.createElement)(c.ToggleControl,{label:(0,t.__)("Override excerpt?","pmc-gutenberg"),help:h?(0,t.__)("Use customized post excerpt.","pmc-gutenberg"):(0,t.__)("Use automatically-generated excerpt.","pmc-gutenberg"),checked:h,onChange:e=>{M({hasContentOverride:e})}})),(0,o.createElement)(U,a)),F&&(0,o.createElement)(s.PanelColorSettings,{title:(0,t.__)("Colors","pmc-gutenberg"),colorSettings:[{value:D.color,onChange:N,label:(0,t.__)("Background Color","pmc-gutenberg")}]}))))})),f=e=>{const{getPostTypes:t}=(0,r.select)("core"),l=t({per_page:-1});return Object.keys(e).map((t=>{const a=e[t].postType,r=null==l?void 0:l.filter((e=>a===e.slug));return{value:a,label:void 0===r||1!==r.length?a:r[0].labels.name}}))},C="pmc/story-runway-review",x=window[l(C)],T=((e={})=>(0,a.merge)({},{title:(0,t.__)("PMC Story","pmc-gutenberg"),description:(0,t.__)("Show a post summary and a link","pmc-gutenberg"),category:"embed",icon:"format-aside",supports:{anchor:!1,customClassName:!1,html:!1,pmc:{colors:{background:!1},contentOverride:!1,fullWidthImage:!1}},attributes:n,edit:E,save:()=>null},e))({title:(0,t.__)("PMC Runway Review","pmc-gutenberg"),description:(0,t.__)("Embed a Runway Review.","pmc-gutenberg"),icon:"camera",attributes:{postType:{default:Object.keys(x)[0]}},supports:{pmc:{colors:{background:!0},contentOverride:!0}}});(0,e.registerBlockType)(C,T)}();