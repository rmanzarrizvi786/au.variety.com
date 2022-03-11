<?php

/**
 * Plugin dependencies.
 *
 * @author Divyaraj Masani <divyaraj.masani@rtcamp.com>
 *
 * @package pmc-variety-2017
 */

wpcom_vip_load_plugin('pmc-global-functions', 'pmc-plugins');

// require_once WP_PLUGIN_DIR . '/pmc-global-functions/pmc-global-functions.php';

// Only load ES_WP_Query on Production, and only on Classic VIP. VIP: ES will be needed for some queries
if (
	!defined('PMC_IS_VIP_GO_SITE') || true !== PMC_IS_VIP_GO_SITE
	&& \PMC::is_production()
) {
	pmc_load_plugin('es-wp-query', false, '0.2.0');
}

//EOF
