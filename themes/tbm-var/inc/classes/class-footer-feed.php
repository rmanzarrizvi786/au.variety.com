<?php
/**
 * Footer_Feed
 *
 * Used for building the footer feed for posts across the PMC brands.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Footer_Feed
 *
 * @since 2017.1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Footer_Feed {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * @since 2017.1.0
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Initialize Hooks and filters.
	 */
	protected function _setup_hooks() {
		add_filter( 'pmc_footer_list_of_feeds', [ $this, 'set_footer_list' ] );
	}

	/**
	 * Set Footer Feed List
	 *
	 * @since 2017.1.0
	 *
	 * @return array
	 */
	public function set_footer_list() {
		return [
			[
				'feed_source_url' => 'https://rollingstone.com/custom-feed/pmc_footer/',
				'feed_title'      => 'Rolling Stone',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://robbreport.com/custom-feed/pmc-footer-feed/',
				'feed_title'      => 'Robb Report',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://sportico.com/custom-feed/pmc_footer/',
				'feed_title'      => 'Sportico',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://spy.com/custom-feed/pmc-footer-feed/',
				'feed_title'      => 'SPY',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://tvline.com/feed/pmc_footer/',
				'feed_title'      => 'TVLine',
				'css_classes'     => [],
			],
		];
	}
}
