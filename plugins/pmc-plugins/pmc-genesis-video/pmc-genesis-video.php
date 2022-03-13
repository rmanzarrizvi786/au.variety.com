<?php
/*
Plugin Name: PMC Genesis Video
Description: AdGenesis is an ad product that uses Genesis Attention Platform™ and Page Attention Rank™ to create out-stream video ad inventory adjacent to editorial content including article pages and photo galleries.
Version: 1.0
Author: PMC, Archana Mandhare
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_GENESIS_VIDEO_ROOT', __DIR__ );

/**
 * Initialize Classes
 *
 * @since 2016-05-26
 * @version 2016-05-26 Archana Mandhare PMCVIP-1636
 *
 */
function pmc_genesis_video_loader() {
	PMC\Genesis_Video\Frontend::get_instance();
	PMC\Genesis_Video\Admin::get_instance();
}

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
pmc_genesis_video_loader();

//EOF
