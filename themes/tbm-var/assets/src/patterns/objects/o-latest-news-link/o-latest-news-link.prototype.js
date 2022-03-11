const clonedeep = require( 'lodash.clonedeep' );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' );

const c_link = clonedeep( c_link_prototype );

c_link.c_link_text = 'Latest News';
c_link.c_link_classes = 'a-hidden@tablet js-LatestNewsButton u-position-fixed u-right-0 u-bottom-20vh u-left-0 u-box-shadow-menu lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-font-size-26 lrv-a-icon-after lrv-a-icon-invert a-icon-down-caret-thin u-background-color-picked-bluewood lrv-u-padding-tb-050 lrv-u-flex lrv-u-align-items-center u-z-index-top lrv-u-justify-content-center lrv-a-unstyle-link lrv-u-color-white lrv-u-overflow-hidden';

module.exports = {
	c_link
};
