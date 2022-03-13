<?php

namespace PMC\Piano;

use PMC\Global_Functions\Traits\Singleton;

/**
 * This class is responsible to create separate subscribe landing pages by using  WP re-write rule .
 *
 * Class Subscribe Rewrite
 * @package PMC\Piano
 */
class Subscribe_Rewrite {

	use Singleton;

	/**
	 * Hook into WordPress
	 */
	public function __construct() {
		// Initialization function to add custom re-write rule.
		add_action( 'init', [ $this, 'init' ] );

		// Filters the query variables for subscribe page allowed before processing.
		add_filter( 'query_vars', [ $this, 'query_vars' ] );

		// Render Subscribe Landing Page Template.
		add_filter( 'template_include', [ $this, 'template_include' ] );
	}

	/**
	 * Set Custom re-write rule for SubscribeLanding Page.
	 *
	 * The URL having /subscribe/anytext will open the separate landing pages, that have the same template as the subscribe landing page
	 */
	public function init(): void {
		add_rewrite_rule( 'subscribe/([a-z0-9-]+)[/]?$', 'index.php?subscribepage=$matches[1]', 'top' );
	}

	/**
	 * Register our custom query var
	 *
	 * @param $query_vars
	 *
	 * @return mixed
	 */
	public function query_vars( $query_vars ): array {

		$query_vars[] = 'subscribepage';

		return $query_vars;
	}

	/**
	 * Render Subscribe Landing Page Template.
	 *
	 * @param $template
	 *
	 * @return string
	 */
	public function template_include( $template ): string {

		if ( ! get_query_var( 'subscribepage' ) ) {
			return $template;
		}

		return apply_filters( 'pmc_piano_subscribe_template', locate_template( [ 'page-subscribe.php' ] ) );
	}
}
