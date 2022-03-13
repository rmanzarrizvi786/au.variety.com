<?php

namespace PMC\SEO_Tweaks;

use \PMC;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Setup
 * @package PMC\SEO_Tweaks
 */
class Setup {

	use Singleton;

	/**
	 * Setup constructor.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		$this->_setup_hooks();
		$this->_unset_hooks();

	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() : void {

		add_action( 'init', [ 'PMC\SEO_Tweaks\Helpers', 'hide_illegal_query_vars_from_bots' ], 0 );
		add_action( 'init', [ 'PMC\SEO_Tweaks\Helpers', 'register_meta' ] );
		add_action( 'wp_head', [ 'PMC\SEO_Tweaks\Helpers', 'wp_head' ] );
		add_action( 'wp_head', [ 'PMC\SEO_Tweaks\Helpers', 'add_image_preview_meta_tag' ] );

		// Priority need to be at 15 for this to work on VY.
		//@see https://wordpressvip.zendesk.com/hc/en-us/requests/72176
		add_filter( 'robots_txt', [ 'PMC\SEO_Tweaks\Helpers', 'seo_robots_txt' ], 15, 2 );
		add_filter( 'mt_seo_fields', [ 'PMC\SEO_Tweaks\Helpers', 'mt_seo_fields' ], 10, 1 );
		//Disallow preview URL's
		add_filter( 'pmc_robots_txt', [ 'PMC\SEO_Tweaks\Helpers', 'disallow_preview_urls' ], 10, 2 );

		// Add filter only in front end not in admin
		if ( ! is_admin() ) {
			/**
			 * @todo update reference to class after updating BGR.
			 */
			add_filter( 'amt_metatags', 'pmc_amt_metatags', 999 );
			add_filter( 'document_title_parts', [ 'PMC\SEO_Tweaks\Helpers', 'co_authors_seo_title' ] );
		}

		if ( is_admin() ) {
			add_filter( 'get_sample_permalink_html', [ 'PMC\SEO_Tweaks\Helpers', 'display_full_permalink' ], 100, 2 );
		}

	}

	/**
	 * Unset hooks.
	 */
	protected function _unset_hooks() : void {

		/**
		 * The following actions are removed because we want to opt out of having these things
		 * rendered in the head of our pages. We used the YOAST SEO plugin prior to the migration
		 * which automatically took care of removing these actions.
		 */
		remove_action( 'wp_head', 'rel_canonical' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'index_rel_link' );
		remove_action( 'wp_head', 'start_post_rel_link' );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

	}

}

// EOF
