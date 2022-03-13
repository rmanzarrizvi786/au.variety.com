!function(){"use strict";var e=window.wp.blocks,t=window.wp.i18n,l=window.wp.element,a=window.lodash,r=window.wp.blockEditor,n=window.wp.components,o=window.wp.compose,c=window.wp.data,s=window.wp.editPost,i=window.wp.plugins;const p="_pmc_digital_daily_print_support_pdf_id",m="pmc-story-digital-daily-document-settings",u=(0,o.compose)((0,c.withSelect)((e=>{var t;const{getMedia:l}=e("core"),{getCurrentPostType:a,getEditedPostAttribute:r}=e("core/editor"),n=null!==(t=r("meta"))&&void 0!==t?t:{},{[p]:o}=n;return{media:o?l(o,{context:"view"}):null,mediaId:o,postType:a()}})),(0,c.withDispatch)((e=>{const{editPost:t}=e("core/editor");return{onRemove:()=>{t({meta:{[p]:null}})},onUpdate:e=>{const l={[p]:e.id};t({meta:l})}}})))((({media:e,mediaId:o,onRemove:c,onUpdate:i,postType:p})=>{if("digital-daily"!==p)return null;const u=["application/pdf"];let d,g;return e&&(d=(0,a.get)(e,["source_url"]),g=(0,a.get)(e,["title","rendered"])),(0,l.createElement)(s.PluginDocumentSettingPanel,{name:m,title:(0,t.__)("Print PDF","pmc-gutenberg"),className:m,icon:"printer",opened:!0},(0,l.createElement)(n.PanelRow,null,!!o&&!e&&(0,l.createElement)(n.Spinner,null),!!o&&e&&(0,l.createElement)("a",{href:d,target:"_blank",rel:"noreferrer"},(0,l.createElement)(n.Dashicon,{icon:"media-document"}),(0,l.createElement)("br",null),g),!o&&!e&&(0,l.createElement)(r.MediaUploadCheck,null,(0,l.createElement)(r.MediaUpload,{title:(0,t.__)("Print PDF","pmc-gutenberg"),onSelect:i,allowedTypes:u,value:null==e?void 0:e.id,render:({open:e})=>(0,l.createElement)(n.Button,{onClick:e,isSecondary:!0},(0,t.__)("Select or Upload PDF","pmc-gutenberg"))}))),!!o&&e&&(0,l.createElement)(n.PanelRow,null,(0,l.createElement)(r.MediaUploadCheck,null,(0,l.createElement)(r.MediaUpload,{title:(0,t.__)("Print PDF","pmc-gutenberg"),onSelect:i,allowedTypes:u,value:null==e?void 0:e.id,render:({open:e})=>(0,l.createElement)(l.Fragment,null,(0,l.createElement)(n.Button,{onClick:e,isLink:!0},(0,t.__)("Replace","pmc-gutenberg")),(0,l.createElement)(n.Button,{onClick:c,isLink:!0,isDestructive:!0},(0,t.__)("Remove","pmc-gutenberg")))}))))}));(0,i.registerPlugin)(m,{render:u});var d=window.wp.hooks;const g=[{value:null,label:(0,t.__)("Default","pmc-gutenberg")},{value:"lrv-a-crop-1x1",label:(0,t.__)("1x1","pmc-gutenberg")},{value:"lrv-a-crop-2x1",label:(0,t.__)("2x1","pmc-gutenberg")},{value:"lrv-a-crop-2x3",label:(0,t.__)("2x3","pmc-gutenberg")},{value:"lrv-a-crop-3x4",label:(0,t.__)("3x4","pmc-gutenberg")},{value:"lrv-a-crop-4x3",label:(0,t.__)("4x3","pmc-gutenberg")},{value:"lrv-a-crop-5x1",label:(0,t.__)("5x1","pmc-gutenberg")},{value:"lrv-a-crop-5x2",label:(0,t.__)("5x2","pmc-gutenberg")},{value:"lrv-a-crop-16x9",label:(0,t.__)("16x9","pmc-gutenberg")}],_="pmc/story-digital-daily";(0,d.addFilter)("pmcGutenberg.storyBlock.additionalDisplayControls",_,(e=>a=>{const{attributes:{imageCropClass:r},name:o,setAttributes:c}=a;return _!==o?null:(0,l.createElement)(l.Fragment,null,(0,l.createElement)(e,a),(0,l.createElement)(n.PanelRow,null,(0,l.createElement)(n.SelectControl,{label:(0,t.__)("Override image crop","pmc-gutenberg"),help:(0,t.__)("Change the crop applied to the story's featured image.","pmc-gutenberg"),value:r,options:g,onChange:e=>{c({imageCropClass:e})}})))}));const h={className:{type:"string",default:""},postType:{type:"string",default:"post"},postID:{type:"number",default:null},taxonomySlug:{type:"string",default:null},viewMoreText:{type:"string",default:null},hasDisplayedExcerpt:{type:"boolean",default:!0},hasDisplayedByline:{type:"boolean",default:!0},hasDisplayedPrimaryTerm:{type:"boolean",default:!0},hasFullWidthImage:{type:"boolean",default:!1},alignment:{type:"string",default:"none"},title:{type:"string",default:null},excerpt:{type:"string",default:null},featuredImageID:{type:"number",default:null},contentOverride:{type:"string",default:null},hasContentOverride:{type:"boolean",default:!1},backgroundColor:{type:"string",default:null}};var y=window.wp.primitives,b=(0,l.createElement)(y.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,l.createElement)(y.Path,{d:"M4 19.8h8.9v-1.5H4v1.5zm8.9-15.6H4v1.5h8.9V4.2zm-8.9 7v1.5h16v-1.5H4z"})),v=(0,l.createElement)(y.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,l.createElement)(y.Path,{d:"M11.1 19.8H20v-1.5h-8.9v1.5zm0-15.6v1.5H20V4.2h-8.9zM4 12.8h16v-1.5H4v1.5z"})),w=(0,l.createElement)(y.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,l.createElement)(y.Path,{d:"M16.4 4.2H7.6v1.5h8.9V4.2zM4 11.2v1.5h16v-1.5H4zm3.6 8.6h8.9v-1.5H7.6v1.5z"}));const E=({onChange:e})=>{const a=(0,o.useInstanceId)(E);return(0,l.createElement)("div",null,(0,l.createElement)("label",{htmlFor:`pmc-story-card__search-${a}`},(0,t.__)("Next, search for a post by title: ","pmc-gutenberg")),(0,l.createElement)("input",{style:{width:"100%"},id:`pmc-story-card__search-${a}`,type:"search",placeholder:(0,t.__)("Search for a post","pmc-gutenberg"),onChange:t=>e(t.target.value),autoComplete:"off"}))},f=({postType:e,onChangePostID:r,onMorePosts:o,maxPosts:s,keywords:i})=>{const{posts:p,morePostsAvailable:m,isLoading:u}=(0,c.useSelect)((t=>{const l="postType",r=e,n={per_page:100,orderby:"date",order:"desc",status:["publish","draft","future"],search:i},{getEntityRecords:o,getMedia:c}=t("core"),{isResolving:p}=t("core/data");let m=[];for(let e=1;e<=Math.ceil(s/100);e++){const t=o(l,r,{...n,page:e});t&&(m=m.concat(t))}return{posts:Array.isArray(m)?m.slice(0,s).map((e=>(({post:e,getMedia:t})=>{if(e&&e.featured_media){const l=t(e.featured_media);let r=(0,a.get)(l,["media_details","sizes","large","source_url"],null);return r||(r=(0,a.get)(l,"source_url",null)),{...e,featuredImageSourceUrl:r,featuredImageAltText:l&&l.alt_text?l.alt_text:""}}return e})({post:e,getMedia:c}))):m,morePostsAvailable:()=>m.length>s,isLoading:()=>{const e=[!1];for(let t=1;t<=Math.ceil(s/100);t++)e.push(p("core","getEntityRecords",[l,r,{...n,page:t}]));for(const t of m.slice(0,s))t&&e.push(p("core","getMedia",[t.featured_media]));return e.reduce(((e,t)=>e||t))}}}),[i,s]);return p&&p.length>0?(0,l.createElement)("fieldset",{className:"pmc-story-card-search-results__fieldset"},(0,l.createElement)(n.VisuallyHidden,{as:"legend"},(0,t.__)("Choose a post","pmc-gutenberg")),(0,l.createElement)("ol",{className:"pmc-story-card-search-results__list"},p.map((e=>e?(0,l.createElement)("li",{className:"pmc-story-card-search-results__item",key:e.id},(0,l.createElement)(n.Button,{isSecondary:!0,onClick:()=>{r(e.id)},className:"pmc-story-card-search-results__button"},(0,l.createElement)("span",{className:"pmc-story-card-search-results__title"},e.title.raw,(0,l.createElement)("i",{className:"pmc-story-card-search-results__title-label"},"publish"!==e.status?" - "+e.status:"")),(0,l.createElement)("span",{className:"pmc-story-card-search-results__image-container"},(0,l.createElement)("img",{className:"pmc-story-card-search-results__image",src:e.featuredImageSourceUrl,alt:e.featuredImageAltText})))):""))),m()&&(0,l.createElement)(n.Button,{className:"pmc-story-card-search-results__more-button",isPrimary:!0,disabled:u(),isBusy:u(),onClick:()=>{o()}},"More Posts")):u()?(0,l.createElement)(n.Spinner,null):i?(0,l.createElement)("p",{className:"pmc-story-card-search-results__note"},`No posts found for “${i}”.`):(0,l.createElement)("p",{className:"pmc-story-card-search-results__note"},"No posts found.")},C=({onChangePostType:e,postType:a,onChangePostID:r,postTypeSelectOptions:o})=>{const[c,s]=(0,l.useState)(null),[i,p]=(0,l.useState)(5);return(0,l.createElement)(n.Placeholder,{className:"pmc-story-card-setup",icon:"format-aside",label:(0,t.__)("Story Setup","pmc-gutenberg"),instructions:(0,t.__)("Choose a post to display in this story card.","pmc-gutenberg"),isColumnLayout:!0},(0,l.createElement)(n.SelectControl,{label:(0,t.__)("First, select a post type to search: ","pmc-gutenberg"),value:a,onChange:e,options:o}),(0,l.createElement)(E,{onChange:s}),(0,l.createElement)("div",{style:{minHeight:"200px"}},(0,l.createElement)(f,{keywords:c,maxPosts:i,postType:a,onChangePostID:r,onMorePosts:function(){p(i+5)}})))},x=({imageID:e,hasFullWidthImage:t})=>{const{src:r,alt:o}=(0,c.useSelect)((t=>{const l={src:null,alt:null};if(e<1)return l;const{getMedia:r}=t("core"),n=r(e);let o=(0,a.get)(n,["media_details","sizes","large","source_url"],null);return o||(o=(0,a.get)(n,"source_url",null)),l.src=o,l.alt=n&&n.alt_text?n.alt_text:"",l}),[e]);return r?(0,l.createElement)("img",{src:r,alt:o,style:{width:t?"auto":"50%"},className:"pmc-story-card-preview__featured-image"}):(0,l.createElement)(n.Spinner,null)},T=({value:e,onChange:t})=>(0,l.createElement)(r.RichText,{value:e,onChange:t,allowedFormats:["core/bold","core/italic"],className:"pmc-story-card-preview__title"}),P=({value:e,onChange:t})=>(0,l.createElement)(r.RichText,{value:e,onChange:t,allowedFormats:[],className:"pmc-story-card-preview__excerpt"}),k=({postType:e,postID:a,contentOverride:o,hasContentOverride:s,hasDisplayedExcerpt:i,hasFullWidthImage:p,alignment:m,title:u,excerpt:d,featuredImageID:g,onContentOverrideUpdate:_,onChangeTitle:h,onChangeExcerpt:y,viewMoreText:b})=>{var v;const w=(0,c.useSelect)((t=>{const{getEntityRecord:l}=t("core");return l("postType",e,a)}),[a]);if(!w)return(0,l.createElement)(n.Spinner,null);const E=null!==(v=null!=g?g:w.featured_media)&&void 0!==v&&v;return d||(d=w.excerpt?w.excerpt.rendered:""),(0,l.createElement)("div",{className:"pmc-story-card-edit",style:{textAlign:m}},(0,l.createElement)(T,{value:u||w.title.raw,onChange:h}),Boolean(E)&&(0,l.createElement)(x,{imageID:E,hasFullWidthImage:p}),Boolean(i)&&(0,l.createElement)(P,{value:d,onChange:y}),(0,l.createElement)("p",{className:"pmc-story-card-preview__link"},(0,l.createElement)("a",{href:w.link},b)),a&&s&&(0,l.createElement)(r.RichText,{value:o,placeholder:(0,t.__)("Enter excerpt override…","pmc-gutenberg"),onChange:_,tagName:"p"}))},D=(0,r.withColors)("backgroundColor",{backgroundColor:"color"})((a=>{const{attributes:{postType:o,postID:s,contentOverride:i,hasContentOverride:p,hasDisplayedExcerpt:m,hasDisplayedByline:u,hasDisplayedPrimaryTerm:d,hasFullWidthImage:g,alignment:_,title:y,excerpt:E,featuredImageID:f,viewMoreText:x,className:T},backgroundColor:P,name:D,setAttributes:I,setBackgroundColor:M}=a,{__unstableMarkNextChangeAsNotPersistent:B}=(0,c.useDispatch)("core/block-editor"),N=window[(e=>`pmc_${e.split("/")[1].replace(/-/g,"_")}_block_config`)(D)],O=S(N),F=(0,e.hasBlockSupport)(D,"pmc.colors.background",!1),R=(0,e.hasBlockSupport)(D,"pmc.contentOverride",!1),A=(0,e.hasBlockSupport)(D,"pmc.fullWidthImage",!1),z=e=>{I({taxonomySlug:N[e].taxonomySlug,viewMoreText:N[e].viewMoreText})};B(),z(o);const U=e=>{I({alignment:void 0===e?"none":e})},H=[{icon:b,title:(0,t.__)("Align left","pmc-gutenberg"),align:"left"}];(null===T||void 0!==T&&-1!==T.indexOf("horizontal"))&&(H.push({icon:v,title:(0,t.__)("Align right","pmc-gutenberg"),align:"right"}),"center"===_&&U("right")),void 0!==T&&-1!==T.indexOf("vertical")&&(H.push({icon:w,title:(0,t.__)("Align center","pmc-gutenberg"),align:"center"}),"right"===_&&U("center"));const W=(0,n.withFilters)("pmcGutenberg.storyBlock.additionalDisplayControls")((e=>(0,l.createElement)(l.Fragment,null)));return(0,l.createElement)(l.Fragment,null,s&&(0,l.createElement)(r.BlockControls,null,(0,l.createElement)(r.AlignmentToolbar,{value:_,onChange:U,alignmentControls:H}),(0,l.createElement)(n.ToolbarButton,{label:(0,t.__)("Replace","pmc-gutenberg"),onClick:()=>{const e={};for(const[t,l]of Object.entries(h))e[t]=l.default;e.postType=o,e.taxonomySlug=N[o].taxonomySlug,e.viewMoreText=N[o].viewMoreText,I(e)}},(0,t.__)("Replace","pmc-gutenberg")),(0,l.createElement)(r.MediaReplaceFlow,{allowedTypes:["image"],accept:"image/*",onSelect:e=>{I({featuredImageID:void 0===e?null:e.id})},name:(0,t.__)("Override Image","pmc-gutenberg")})),s?(0,l.createElement)(k,{postType:o,postID:s,contentOverride:i,hasContentOverride:p,hasDisplayedExcerpt:m,hasFullWidthImage:g,alignment:_,title:y,excerpt:E,featuredImageID:f,onChangeTitle:e=>{I({title:void 0===e?null:e})},onChangeExcerpt:e=>{I({excerpt:void 0===e?null:e})},onContentOverrideUpdate:e=>{I({contentOverride:e})},viewMoreText:x}):(0,l.createElement)(C,{postType:o,onChangePostID:e=>{I({postID:void 0===e?null:e})},onChangePostType:e=>{I({postType:e}),z(e)},placeholderTitle:(0,t.__)("Select a Story","pmc-gutenberg"),postTypeSelectOptions:O}),(0,l.createElement)(r.InspectorControls,null,(0,l.createElement)(n.Panel,null,(0,l.createElement)(n.PanelBody,{title:(0,t.__)("Display Settings","pmc-gutenberg"),initialOpen:!0},(0,l.createElement)(n.PanelRow,null,(0,l.createElement)(n.ToggleControl,{label:(0,t.__)("Display dek?","pmc-gutenberg"),help:m?(0,t.__)("Dek will be shown (if design includes it).","pmc-gutenberg"):(0,t.__)("Dek will be hidden (if design includes it).","pmc-gutenberg"),checked:m,onChange:e=>{I({hasDisplayedExcerpt:void 0===e||e})}})),(0,l.createElement)(n.PanelRow,null,(0,l.createElement)(n.ToggleControl,{label:(0,t.__)("Display byline?","pmc-gutenberg"),help:u?(0,t.__)("Byline will be shown (if design includes it).","pmc-gutenberg"):(0,t.__)("Byline will be hidden (if design includes it).","pmc-gutenberg"),checked:u,onChange:e=>{I({hasDisplayedByline:void 0===e||e})}})),(0,l.createElement)(n.PanelRow,null,(0,l.createElement)(n.ToggleControl,{label:(0,t.__)("Display breadcrumb?","pmc-gutenberg"),help:d?(0,t.__)("Taxonomy term (breadcrumb) will be shown (if design includes it).","pmc-gutenberg"):(0,t.__)("Taxonomy term (breadcrumb) will be hidden (if design includes it).","pmc-gutenberg"),checked:d,onChange:e=>{I({hasDisplayedPrimaryTerm:void 0===e||e})}})),A&&(0,l.createElement)(n.PanelRow,null,(0,l.createElement)(n.ToggleControl,{label:(0,t.__)("Make image full-width","pmc-gutenberg"),help:g?(0,t.__)("Image fills the width of the story card.","pmc-gutenberg"):(0,t.__)("Image is thumbnail size.","pmc-gutenberg"),checked:g,onChange:e=>{I({hasFullWidthImage:void 0!==e&&e})}})),R&&(0,l.createElement)(n.PanelRow,null,(0,l.createElement)(n.ToggleControl,{label:(0,t.__)("Override excerpt?","pmc-gutenberg"),help:p?(0,t.__)("Use customized post excerpt.","pmc-gutenberg"):(0,t.__)("Use automatically-generated excerpt.","pmc-gutenberg"),checked:p,onChange:e=>{I({hasContentOverride:e})}})),(0,l.createElement)(W,a)),F&&(0,l.createElement)(r.PanelColorSettings,{title:(0,t.__)("Colors","pmc-gutenberg"),colorSettings:[{value:P.color,onChange:M,label:(0,t.__)("Background Color","pmc-gutenberg")}]}))))})),S=e=>{const{getPostTypes:t}=(0,c.select)("core"),l=t({per_page:-1});return Object.keys(e).map((t=>{const a=e[t].postType,r=null==l?void 0:l.filter((e=>a===e.slug));return{value:a,label:void 0===r||1!==r.length?a:r[0].labels.name}}))},I=((e={})=>(0,a.merge)({},{title:(0,t.__)("PMC Story","pmc-gutenberg"),description:(0,t.__)("Show a post summary and a link","pmc-gutenberg"),category:"embed",icon:"format-aside",supports:{anchor:!1,customClassName:!1,html:!1,pmc:{colors:{background:!1},contentOverride:!1,fullWidthImage:!1}},attributes:h,edit:D,save:()=>null},e))({title:(0,t.__)("PMC Digital Daily Story","pmc-gutenberg"),description:(0,t.__)("Embed an article in a Digital Daily issue.","pmc-gutenberg"),attributes:{imageCropClass:{type:"string",default:null}},supports:{pmc:{colors:{background:!0},contentOverride:!0}}});(0,e.registerBlockType)("pmc/story-digital-daily",I)}();