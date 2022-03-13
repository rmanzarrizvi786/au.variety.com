<?php
/**
 * CSS Template for Single_Post class with core CSS.
 */
?>
html {
	background: #fff;
}
*{
	box-sizing: border-box;
}
#callout {
	background: black;
	font-size: 10pt;
	padding: 5px;
	text-align: center;
	width: 100%;
}

#callout a {
	color: white;
	text-decoration: none;
}

.amp-wp-title {
	font-size: 24px;
}

nav.amp-wp-title-bar {
	padding: 12px 0;
	background: #fff;
	width:100%;
	height: 60px;
	margin: 0 auto;
	z-index: 2147483648;
	max-width: <?php echo intval( $content_max_width ); ?>px;
}

.amp-next-page-container,
.amp-next-page-default-separator {
	max-width: <?php echo intval( $content_max_width ); ?>px;
	margin: 0 auto;
}

.amp-next-page-container div:last-of-type .amp-next-page-default-separator {
	border-bottom: none;
}

.amp-next-page-default-separator {
	margin-bottom: 32px;
	margin-top: 32px;
}

nav.amp-wp-title-bar .amp-wp-site-icon {
	margin: 10px 8px 0 0;
	border-radius: 0;
	height:40px;
}

nav.amp-wp-title-bar div {
	margin: 0 auto;
	text-align:center;
}
amp-sidebar{
	padding-top: 47px;
}
nav.amp-wp-title-bar a {
	background-image: url( <?php echo esc_url( $bg_img_url ); ?> );
	background-repeat: no-repeat;
	background-position: center;
	background-size: contain;
	display: block;
	margin: 0 auto;
	outline: none;
	text-align:center;
	text-indent: -9999px;
	white-space: nowrap;
}

.copyright {
	position: relative;
	margin: 0;
	width: 100%;
	color: #777;
	font-size: 13px;
	text-align: center;
	z-index: 10;
}

.ad-slot{
	clear: both;
	display: block;
	margin: 0 auto;
	max-width: <?php echo intval( $content_max_width ); ?>px;
	padding: 0 16px;
	text-align: center;
	width: auto;
	box-sizing: content-box;
}
.ad-slot amp-ad{
	margin: 0 auto;
}
.pmc-outbrain-amp-widget{
	margin: 0 auto;
	max-width: 568px;
	padding: 16px;
}

.amp-wp-content h1.amp-wp-title{
	margin: 0;
}

.caption {
	font-size: 14px;
	font-style: italic;
	margin: 0 0 7px 0;
}

.thumbnail{
	margin: 0 0 24px 0;
}

.credits{
	font-size: 14px;
	font-style: italic;
}

.pmc-outbrain-amp-widget amp-iframe {
	background: #f3f6f8;
	margin: 0;
}

.amp-social-share-bar {
	display: flex;
	justify-content: center;
}

.amp-social-share-bar amp-social-share {
	margin: 6px 3px;
}

.amp-comments-link {
	border: 2px solid #28b0ea;
	margin: 20px 0 10px;
	padding: 5px;
	text-align: center;
	font-family: 'Open Sans', sans-serif;
}

.amp-comments-link a {
	color: #28b0ea;
	text-decoration: none;
	display: block;
}

.amp-notsupported {
	display: none;
}

.featured-image-container{
	position: relative;
}

.featured-image-container .featured-image-captions{
	position: absolute;
	bottom: 0;
	padding: 0px 5px;
	font-size: 0.75rem;
	line-height: 1.5rem;
	letter-spacing: 0.5px;
}

.featured-image-container .featured-image-captions .image-credit{
	color: #DEDEDE;
}
.amp-wp-title-bar .hamburger{
	position: relative;
	float: left;
	padding: 9px 10px;
	margin-top: 6px;
	margin-right: 15px;
	margin-bottom: 6px;
	background-color: transparent;
	background-image: none;
	border: 1px solid transparent;
	border-radius: 4px;
}

.amp-wp-title-bar .hamburger.left{
	float: left;
}
.amp-wp-title-bar .hamburger.right{
	float: right;
	margin-right: 0;
	margin-left: 15px;
}

.hamburger .icon-bar {
	display: block;
	width: 22px;
	height: 2px;
	border-radius: 1px;
	background-color: #888;
}
.hamburger .icon-bar+.icon-bar{
	margin-top: 4px;
}

amp-sidebar{
	width: 318px;
}

amp-sidebar .menu li{
	border: solid 1px #e9e9e9;
	padding: 10px 30px;
}
amp-sidebar .menu li a{
	height: 15px;
	font-size: 15px;
	text-align: left;
	color: #1e1e1e;
	text-decoration: none;
	line-height: 1;
}
amp-sidebar .menu .sub-menu{
	display: none;
}
.clear{
	clear: both;
}
.amp-category-posts-container{
	padding: 0 30px;
	margin: 0 auto;
	max-width: 568px;
}
.amp-category-posts-container .title {
	padding: 15px 5px;
	margin: 20px 0;
}
.amp-category-posts-container .title h3{
	margin: 0;
	padding-top: 5px;
}

.amp-category-posts-container.with-image .content .list{
	list-style: none;
	margin: 0 -10px;
}

.amp-category-posts-container.with-image .content .list > li {
	display: inline-block;
	width: 49%;
	padding: 0 10px;
	box-sizing: border-box;
	vertical-align: top;
}
.amp-category-posts-container .content .list > li > a{
	color: #353535;
	text-decoration: none;
	line-height: 1.5;
	display: block;
}


.gallery-image-section {
	margin-bottom: 20px;
}

.gallery-image-section > a {
	text-decoration: none;
	margin-bottom: 10px;
	display: block;
}

.gallery-image-section .gallery-img-count {
	background: #d41b21 none repeat scroll 0 0;
	color: #fff;
	display: block;
	padding: 4px;
	text-transform: uppercase;
}

.gallery-image-section .gallery-img-count .icon-photo svg {
	margin-left: 5px;
	margin-top: 3px;
	vertical-align: sub;
}

.gallery-image-section .gallery-thumbnails {
	display: -ms-flexbox;
	display: flex;
	width: 100%;
	margin-bottom: 20px;
}

.gallery-image-section .gallery-thumbnails .gallery-thumbnail {
	margin-left: 10px;
	max-height: 152px;
	overflow: hidden;
}

.gallery-image-section .gallery-thumbnails .gallery-thumbnail:first-child {
	margin-left: 0;
}

.gallery-image-section .gallery-thumbnails .gallery-thumbnail img {
	height: auto;
	max-width: 100%;
}

.pmc-related-link a {
	text-decoration: none;
}
.pmc-related-link.have-image a {
	display: flex;
	align-items: center;
}
.pmc-related-link.have-image a span{
	display: table-cell;
	vertical-align: top;
}
.pmc-related-link.have-image a span.image{
	width: 125px;
	height: auto;
	max-width: 125px;
}
.pmc-related-link.have-image a span.image > amp-img{
	max-width: 100%;
}
.pmc-related-link.have-image a span.text{
	padding: 5px 10px;
}
.amp-social-share-bar-container{
	border-top: 1px solid #dbdbdb;
	border-bottom: solid 2px #000000;
	margin: 10px 0;
	padding: 12px 0;
	display: inline-block;
	width: 100%;
}
.share-this {
	font-weight: bold;
	letter-spacing: 0.6px;
	text-align: left;
	color: #000000;
	float: left;
	margin: 0;
	line-height: 35px;
}

.amp-social-share-bar{
	margin: 0;
}
.amp-social-share-bar > amp-social-share{
	border-radius: 50%;
	background-size: 25px;
	width: 35px;
	height: 35px;
	margin: 0 8px;
}
.article-breadcrumb-container{
	padding: 5px 10px;
	background: #fff;
	width: 100%;
	margin: 0 auto;
	max-width: 568px;
	line-height: 1;
}
.article-header__breadcrumbs {
	align-items: center;
	margin: 0;
}
.article-header__breadcrumbs li{
	align-items: center;
	margin-left: 5px;
	padding-left: 5px;
	position: relative;
	display: inline-block;
}
.article-header__breadcrumbs li:first-child{
	padding:0;
}
.article-header__breadcrumbs li:first-child::before {
	display: none;
}
.article-header__breadcrumbs li::before {
	background: #939393 none repeat scroll 0 0;
	content: "";
	height: 60%;
	width: 1px;
	left: -1px;
	top: 50%;
	position: absolute;
	transform: translateY(-50%) skew(-20deg);
}
.article-header__breadcrumbs li a{
	color: #353535;
	display: block;
	font-size: 10px;
	letter-spacing: 0.5px;
	padding: 2px 0;
	text-decoration: none;
	text-transform: uppercase;
}
*[class*='amp-wp-inline'] {
	margin: 10px auto;
}
.amp-fn-content .pmc-related-link{
	padding: 10px 0;
	border-top: solid 2px #898989;
	border-bottom: solid 1px #dbdbdb;
	margin: 0 0 1em;
	line-height: 1.29;
}
.pmc-related-link .pmc-related-type{
	padding-right: 5px;
	letter-spacing: 0.6px;
	font-weight: 600;
	display: block;
	margin-bottom: 5px;
}
.amp-mode-touch .copyright,
body .copyright{
	z-index: 10;
	position: relative;
	background-color: #000000;
	font-family: Helvetica;
	font-size: 12px;
	color: #ffffff;
	margin: 0;
	line-height: 1;
	padding: 12px;
	box-sizing: border-box;
	font-weight: normal;
	margin-top: 15px;
}
amp-sidebar{
	background-color: #FFF;
}
.amp-wp-byline amp-img{
	display: none;
}
.ad-text::before {
	content: 'ADVERTISEMENT';
	font-size: 9px;
	text-transform: uppercase;
	text-align: center;
	color: #8c8c8c;
	letter-spacing: 1px;
	margin: 3px 0;
	font-family: 'Arial', sans-serif;
	font-weight: normal;
	line-height: 1;
	display: block;
	-webkit-font-smoothing: antialiased;
	-moz-osx-font-smoothing: grayscale;
}

/*
Contain full width images in the content
for Larva and non-Larva sites.
*/
.wp-caption,
.lrv-u-max-width-100p {
	max-width: 100%;
}
