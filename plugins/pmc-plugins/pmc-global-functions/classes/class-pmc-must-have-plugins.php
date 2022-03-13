<?php
/**
 * All must-have plugins (both VIP & PMC Plugins) which have
 * to be loaded on all sites.
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since 2015-07-06
 */


class PMC_Must_Have_Plugins {

	/**
	 * This function loads all the must have VIP plugins
	 *
	 * @return void
	 */
	public static function load_vip_plugins() {
		pmc_load_plugin( 'cheezcap' );
		pmc_load_plugin( 'pmc-geo-uniques', 'pmc-plugins' );
	}

	/**
	 * This function loads all the must have PMC plugins
	 *
	 * @return void
	 * @codeCoverageIgnore There is no good way to properly unit test this method
	 */
	public static function load_pmc_plugins() {

		// Load the pmc-wp-cli plugin before the must have plugins in case they need it.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			// We can't cover this code here because of WP_CLI constant
			pmc_load_plugin( 'pmc-wp-cli', 'pmc-plugins' ); // @codeCoverageIgnore
		}

		// note: keep this list alphabetic order unless where needed
		pmc_load_plugin( 'pmc-410', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-admantx', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-ccpa', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-compliance', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-contentdial', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-dateless-link', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-disable-live-chat', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-ecomm', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-export', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-facebook-instant-articles', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-geo-restricted-content', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-google-tagmanager', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-http-headers', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-js-libraries', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-krux-tag', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-linkcount', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-omni', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-options', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-page-meta', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-performance-metrics', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-post-options', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-post-reviewer', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-preload', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-pwa', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-roles-capabilities', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-safe-redirect-manager', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-structured-data', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-taxonomy-export', 'pmc-plugins' );
		pmc_load_plugin( 'pmc-video-player', 'pmc-plugins' );

		// Load this plugin last
		pmc_load_plugin( 'onesignal-free-web-push-notifications', 'pmc-plugins' );

	}

} // end of class

//EOF
