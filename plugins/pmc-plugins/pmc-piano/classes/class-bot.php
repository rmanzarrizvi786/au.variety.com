<?php

namespace PMC\Piano;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Global_Functions\Utility\Device;

/**
 * This class contains functionality for things specific to the cxensebot.
 * Original source : PMC\Cxense\Bot
 */
class Bot {
	use Singleton;

	/**
	 * Class initialization
	 */
	protected function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_assets' ], 11 );
		add_action( 'init', [ $this, 'disable_onetrust' ] ); // must be called before Onetrust
		add_filter( 'pmc-tags-filter-tags', [ $this, 'filter_tags' ] );
		add_filter( 'pmc_robots_txt', [ $this, 'set_user_agent' ], 10, 2 );
	}

	/**
	 * Disable Onetrust for Cxensebot
	 */
	public function disable_onetrust(): void {
		if ( Device::get_instance()->is_bot( 'cxensebot' ) ) {
			add_filter( 'pmc_onetrust', '__return_false' );
		}
	}

	/**
	 * Remove unneeded scripts so the cxensebot can crawl pages faster
	 */
	public function dequeue_assets(): void {
		if ( Device::get_instance()->is_bot( 'cxensebot' ) ) {
			$scripts = $this->scripts_to_dequeue();
			if ( ! empty( $scripts ) && is_array( $scripts ) ) {
				foreach ( $scripts as $script ) {
					wp_dequeue_script( $script );
				}
			}
		}
	}

	/**
	 * Gets all the scripts to dequeue.
	 * Themes can override the scripts using the filter below.
	 * @return array
	 */
	public function scripts_to_dequeue(): array {
		return apply_filters( 'pmc_cxense_scripts_to_dequeue_for_bot', $this->get_default_scripts_to_dequeue() );
	}

	/**
	 * Default scripts to dequeue.
	 * The scripts below were chosen based on:
	 * 1) How long they take to load, on average
	 * 2) How little impact they have on the bot crawling pages correctly
	 * @return array
	 */
	public function get_default_scripts_to_dequeue(): array {
		return [
			'qc-cmp-init',
			'pmc-cmp-report-js',
		];
	}

	public function filter_tags( $tags ) {
		if ( Device::get_instance()->is_bot( 'cxensebot' ) && isset( $tags['quantcast'] ) ) {
			$tags['quantcast']['enabled'] = false;
		}

		return $tags;
	}

	/**
	 * Allow cxense bot to crawl pages
	 *
	 * @param $output string
	 * @param $public bool
	 * @return string
	 */
	public function set_user_agent( $output, $public ): string {
		if ( $public ) {
			$output .= 'User-agent: cXensebot' . PHP_EOL;
			$output .= 'Crawl-delay: 5' . PHP_EOL;
		}

		return $output;
	}

	/**
	 * Iterates through the list of bots allowed access to paywall, provided from default list and filter.
	 *
	 * @return bool
	 */
	public function check_if_allowed_bot(): bool {
		$bots = (array) apply_filters( 'pmc_cxense_paywall_allowed_bots', $this->get_default_allowed_bots() );
		foreach ( $bots as $bot ) {
			if ( Device::get_instance()->is_bot( $bot ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets default list of bots allowed access to paywall.
	 *
	 * @return array
	 */
	public function get_default_allowed_bots(): array {
		return [
			'cxensebot',
			'googlebot',
			'googlebot-news',
			'outbrain',
		];
	}
}
