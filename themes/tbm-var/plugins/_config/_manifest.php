<?php

/**
 * Manifest for plugin configurations.
 *
 * All plugin configurations are loaded/initialized in here
 *
 * @since   2018-12-18
 *
 * @package pmc-variety
 */

use Variety\Plugins\Config;

/*
 * Unless there's specific reason, load 410 & redirection
 * stuff before anything else.
 */

\Variety\Plugins\Config\Cheezcap::get_instance();
\Variety\Plugins\Config\JW_Player::get_instance();
\Variety\Plugins\Config\Outbrain::get_instance();
// \Variety\Plugins\Config\PMC_Adm::get_instance();
// \Variety\Plugins\Config\PMC_Apple_News::get_instance();
\Variety\Plugins\Config\PMC_Automated_Related_Links::get_instance();
\Variety\Plugins\Config\PMC_Buy_Now::get_instance();
\Variety\Plugins\Config\PMC_Content::get_instance();
\Variety\Plugins\Config\PMC_Content_Publishing::get_instance();
// \Variety\Plugins\Config\PMC_Custom_Feed_V2::get_instance();
\Variety\Plugins\Config\PMC_Custom_Metadata::get_instance();
\Variety\Plugins\Config\PMC_Cxense::get_instance();
// \Variety\Plugins\Config\PMC_Disable_Getty_Images::get_instance();
\Variety\Plugins\Config\PMC_Event_Tracking::get_instance();
\Variety\Plugins\Config\PMC_Exacttarget::get_instance();
\Variety\Plugins\Config\PMC_Facebook_Instant_Articles::get_instance();
\Variety\Plugins\Config\PMC_Floating_Player::get_instance();
\Variety\Plugins\Config\PMC_Gallery::get_instance();
\Variety\Plugins\Config\PMC_Genre::get_instance();
\Variety\Plugins\Config\PMC_Geo_Restricted_Content::get_instance();
\Variety\Plugins\Config\PMC_GetEmails::get_instance();
\Variety\Plugins\Config\PMC_Global_Functions::get_instance();
\Variety\Plugins\Config\PMC_Google_AMP::get_instance();
\Variety\Plugins\Config\PMC_Google_Universal_Analytics::get_instance();
\Variety\Plugins\Config\PMC_Groups::get_instance();
\Variety\Plugins\Config\PMC_Gutenberg::get_instance();
\Variety\Plugins\Config\PMC_Larva::get_instance();
\Variety\Plugins\Config\PMC_LinkContent::get_instance();
\Variety\Plugins\Config\PMC_Page_Meta::get_instance();
\Variety\Plugins\Config\PMC_Post_Listing_Filters::get_instance();
\Variety\Plugins\Config\PMC_PWA::get_instance();
\Variety\Plugins\Config\PMC_Seo_Tweaks::get_instance();
\Variety\Plugins\Config\PMC_Sitemaps::get_instance();
\Variety\Plugins\Config\PMC_Social_Share_Bar::get_instance();
\Variety\Plugins\Config\PMC_Store_Products::get_instance();
\Variety\Plugins\Config\PMC_Structured_Data::get_instance();
\Variety\Plugins\Config\PMC_Swiftype::get_instance();
\Variety\Plugins\Config\PMC_Tags::get_instance();
\Variety\Plugins\Config\PMC_Vertical::get_instance();
\Variety\Plugins\Config\PMC_Video_Player::get_instance();
\Variety\Plugins\Config\PMC_Video_Playlist_Manager::get_instance();
\Variety\Plugins\Config\PMC_Post_Options::get_instance();
// \Variety\Plugins\Config\PMC_Related_Link::get_instance();
\Variety\Plugins\Config\WPCOM_Legacy_Redirector::get_instance();
\Variety\Plugins\Config\Zoninator::get_instance();
\Variety\Plugins\Config\PMC_Subscription_V2::get_instance();
\Variety\Plugins\Config\PMC_Onetrust::get_instance();
\Variety\Plugins\Config\Yappa_Comments::get_instance();
\Variety\Plugins\Config\Ecomm_Disclaimer::get_instance();
//EOF
