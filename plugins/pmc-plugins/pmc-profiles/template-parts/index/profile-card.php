<?php

use PMC\PMC_Profiles\PMC_Profiles;
use PMC\PMC_Profiles\Post_Type;

$profile_blurb = PMC\Larva\Json::get_instance()->get_json_data( 'modules/profile-blurb.card' );

$profile_blurb['c_title']['c_title_text']            = '{{{data.name}}}'; // phpcs:ignore WordPressVIPMinimum.Security.Mustache.OutputNotation
$profile_blurb['c_tagline']['c_tagline_text']        = '{{{data.secondary_taxonomy_terms}}}'; // phpcs:ignore WordPressVIPMinimum.Security.Mustache.OutputNotation
$profile_blurb['c_tagline_second']['c_tagline_text'] = '{{{data.tertiary_taxonomy_terms}}}'; // phpcs:ignore WordPressVIPMinimum.Security.Mustache.OutputNotation

$profile_blurb['c_dek']['c_dek_markup'] = '{{{data.quaternary_taxonomy_terms}}}'; // phpcs:ignore WordPressVIPMinimum.Security.Mustache.OutputNotation
$profile_blurb['c_dek']['c_dek_text']   = false;

$thumbnail_id = get_post_thumbnail_id( get_the_ID() );

$profile_blurb['c_lazy_image']['c_lazy_image_markup'] = '<img class="lrv-u-background-color-grey-lightest lrv-u-width-100p lrv-u-display-block lrv-u-height-auto" src="{{data.image_src}}" alt="{{data.image_alt}}" />';

// Note about data.url: This needs to be updated in the template/parser
// to be an _attr so that it uses esc_attr. Right now we are adding the
// href with JS. See note in ProfileFilter.js in updateResults func.
$profile_blurb['c_lazy_image']['c_lazy_image_link_url'] = '{{data.url}}';


$profile_blurb['o_social_list']             = false;
$profile_blurb['c_button']['c_button_text'] = esc_html__( 'View More', 'pmc-profiles' );

// See above note.
$profile_blurb['c_button']['c_button_url'] = '{{data.url}}';

echo '<script type="text/html" id="tmpl-profile-card">';

\PMC::render_template(
	sprintf( '%s/build/patterns/modules/profile-blurb.php', \PMC\Larva\Config::get_instance()->get( 'core_directory' ) ),
	$profile_blurb,
	true
);

echo '</script>';
