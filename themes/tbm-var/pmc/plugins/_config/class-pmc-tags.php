<?php
/**
 * Config for 'pmc-tags' plugin to render required, analytics tags on pages.
 *
 * @package pmc-core-v2
 */

namespace PMC\Core\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Tags {

	use Singleton;

	/**
	 * PMC_Tags constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Defines hooks/filters.
	 *
	 * @return void
	 */
	protected function _setup_hooks() {
		add_filter( 'pmc-tags-filter-tags', [ $this, 'filter_options' ] );
	}

	/**
	 * Filters '$options' for 'pmc-tags' stating which tags to render and where.
	 *
	 * @param array $opts Options for the tags.
	 *
	 * @return array Filtered options array.
	 */
	public function filter_options( $opts ) {

		if ( isset( $opts['comscore'] ) ) {

			$opts['comscore']['enabled']      = true;
			$opts['comscore']['positions']    = [ 'top', 'bottom' ];
			$opts['comscore']['values']['id'] = '6035310';

		}

		if ( isset( $opts['global'] ) ) {

			$opts['global']['enabled']            = true;
			$opts['global']['positions']          = [ 'bottom' ];
			$opts['global']['values']['pmcpcode'] = 'p-31f3D02tYU8zY';

		}

		if ( isset( $opts['keywee'] ) ) {

			$host = str_replace( '.', '', wp_parse_url( home_url(), PHP_URL_HOST ) );

			$opts['keywee']['enabled']      = true;
			$opts['keywee']['values']['id'] = $host; // No need to provide filter here, this can be overridden in child.

		}

		if ( isset( $opts['pingdom'] ) ) {

			$opts['pingdom']['enabled']      = true;
			$opts['pingdom']['positions']    = [ 'bottom' ];
			$opts['pingdom']['values']['id'] = '55133c47abe53d890db40683';

		}

		if ( isset( $opts['pinit'] ) ) {

			$opts['pinit']['enabled']   = true;
			$opts['pinit']['positions'] = [ 'bottom' ];

		}

		if ( isset( $opts['prefetch'] ) ) {

			$opts['prefetch']['enabled'] = true;

		}

		if ( isset( $opts['quantcast'] ) ) {

			$opts['quantcast']['enabled']            = true;
			$opts['quantcast']['positions']          = [ 'top', 'bottom' ];
			$opts['quantcast']['values']['pmcpcode'] = 'p-31f3D02tYU8zY';

		}

		if ( isset( $opts['skimlinks'] ) ) {

			$opts['skimlinks']['enabled']      = true;
			$opts['skimlinks']['positions']    = [ 'bottom' ];
			$opts['skimlinks']['values']['id'] = apply_filters( 'pmc_skimlinks_tag_id', '87443X1540255' );

		}

		return $opts;

	}
}

// EOF
