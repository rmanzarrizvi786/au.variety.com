const clonedeep = require( 'lodash.clonedeep' );

const c_heading = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' ) );
const c_tagline = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' ) );
const c_link__email = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' ) );
const c_link__twitter = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' ) );
const c_icon__twitter = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype' ) );


c_heading.c_heading_classes = 'lrv-u-font-family-primary lrv-u-font-size-28 u-font-size-42@tablet lrv-u-font-weight-bold lrv-u-margin-b-050';
c_heading.c_heading_text = 'Mike Fleming Jr';

c_tagline.c_tagline_classes = 'u-font-family-body lrv-u-font-size-15 u-font-size-18@tablet';
c_tagline.c_tagline_text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. At quis risus sed vulputate odio ut enim blandit. Aliquam sem et tortor consequat id. Convallis aenean et tortor at risus viverra. Sagittis aliquam malesuada bibendum arcu vitae elementum curabitur vitae. Id aliquet lectus proin nibh nisl. Id diam maecenas ultricies mi. Luctus venenatis lectus magna fringilla urna porttitor rhoncus.';

c_link__email.c_link_text = 'mike.fleming@pmcqa.com';
c_link__email.c_link_classes = 'lrv-a-unstyle-link u-font-family-basic lrv-u-color-grey-dark:hover lrv-a-hover-effect lrv-u-whitespace-nowrap lrv-a-icon-before u-text-transform-lowercase';
c_link__email.c_link_classes += ' lrv-a-icon-before lrv-a-icon-envelope u-color-pale-sky-2 u-text-transform-lowercase';

c_icon__twitter.c_icon_link_classes = '';
c_icon__twitter.c_icon_url = '#';
c_icon__twitter.c_icon_name = 'twitter';
c_icon__twitter.c_icon_classes = 'lrv-u-height-16 lrv-u-width-16 u-color-twitter u-color-twitter:hover lrv-u-margin-r-025';

c_link__twitter.c_link_text = 'mike_fleming';
c_link__twitter.c_link_classes = 'lrv-a-unstyle-link u-font-family-basic u-color-pale-sky-2 lrv-u-color-grey-dark:hover lrv-a-hover-effect lrv-u-whitespace-nowrap u-text-transform-lowercase';

module.exports = {
	author_blurb_social_list__classes: 'lrv-a-unstyle-list lrv-u-flex@tablet lrv-a-space-children-horizontal@tablet lrv-a-space-children-vertical@mobile-max lrv-a-space-children--1 a-space-children--050@mobile-max lrv-u-font-family-primary lrv-u-font-size-15 u-font-size-18@tablet',
	author_blurb_social_list_item__classes: 'lrv-u-flex',
	c_heading: c_heading,
	c_tagline: c_tagline,
	c_link__email: c_link__email,
	c_icon__twitter: c_icon__twitter,
	c_link__twitter: c_link__twitter,

};

