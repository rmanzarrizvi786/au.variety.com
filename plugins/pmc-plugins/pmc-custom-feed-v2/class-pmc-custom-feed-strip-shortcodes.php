<?php

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Feed_Strip_Shortcodes {

	use Singleton;

	protected function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
	}

	public function action_init() {
		add_action( 'pmc_custom_feed_start', array( $this, 'action_pmc_custom_feed_start' ), 10, 3 );
	}

	public function action_pmc_custom_feed_start( $feed, $feed_options, $template_name ) {
		if( empty( $feed_options['strip_related_links'] ) ) {
			add_filter( 'pmc_strip_shortcode', array( $this, 'filter_pmc_strip_shortcode' ), 10, 3 );
		}
	}

	public function filter_pmc_strip_shortcode( $content, $shortcode, $origin_content ) {
		if ( $shortcode == 'pmc-related-link' ) {
			return $origin_content;
		}
		return $content;
	}

}

PMC_Custom_Feed_Strip_Shortcodes::get_instance();

// EOF
