<?php

namespace PMC\SEO_Tweaks;

use PMC\Global_Functions\Traits\Singleton;

class Canonical_Redirect {
	use Singleton;

	protected function __construct() {
		add_action( 'template_redirect', [ $this, 'maybe_canonical_redirect' ] );
	}

	public function maybe_canonical_redirect() {
		global $wp_query;

		if ( is_singular( 'post' ) ) {
			/**
			 * SADE-34: Fix numbering on article page, issue caused by wp not doing canonical redirect
			 * when numeric page is request against article where content does not support pagination
			 */
			// @see function redirect_canonical
			if ( get_query_var( 'page' ) && $wp_query->post && false === strpos( $wp_query->post->post_content, '<!--nextpage-->' ) ) {
				wp_safe_redirect( get_permalink(), 301 );
				exit();
			}
		}

	}

}
