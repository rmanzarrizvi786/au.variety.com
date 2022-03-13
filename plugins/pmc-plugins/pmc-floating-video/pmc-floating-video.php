<?php
/**
 * Plugin Name: PMC Floating Video
 * Plugin URI: http://www.pmc.com
 * Description: This plugin was written to keep feature videos within posts fixed in view, floating over the sidebar.
 * Version: 1.2
 * Authors: Mike Auteri, Vinod Tella, PMC
 * License: PMC Proprietary. All rights reserved.
 */

function pmc_floating_video_loader() {
	define( 'PMC_FLOATING_VIDEO_VERSION', '1.2' );
	define( 'PMC_FLOATING_VIDEO_ROOT', __DIR__ );
	require_once PMC_FLOATING_VIDEO_ROOT . '/dependencies.php';
	PMC\Floating_Video\Setup::get_instance();

}

pmc_floating_video_loader();
