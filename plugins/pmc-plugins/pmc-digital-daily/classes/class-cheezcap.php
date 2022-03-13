<?php
/**
 * Add Cheezcap group for Digital Daily settings.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

use CheezCapGroup;
use CheezCapDropdownOption;
use PMC_Cheezcap;
use PMC\Global_Functions\Traits\Singleton;
use Sailthru_Blast_Repeat;

/**
 * Class Cheezcap.
 */
class Cheezcap {
	use Singleton;

	/**
	 * Setting name for Digital Daily-specific newsletter.
	 */
	public const NEWSLETTER = 'pmc_dd_newsletter';

	/**
	 * Retrieve setting value.
	 *
	 * @param string $setting Setting key.
	 * @return mixed
	 */
	public static function get( string $setting ) {
		switch ( $setting ) {
			case 'newsletter':
				$key = static::NEWSLETTER;
				break;

			default:
				$key = null;
				break;
		}

		if ( null === $key ) {
			return null;
		}

		return PMC_Cheezcap::get_instance()->get_option( $key );
	}

	/**
	 * Cheezcap constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	protected function _setup_hooks(): void {
		add_filter( 'pmc_cheezcap_groups', [ $this, 'add_group' ] );
	}

	/**
	 * Add Cheezcap group and settings.
	 *
	 * @param array $groups Registered groups.
	 * @return array
	 */
	public function add_group( array $groups ): array {
		$groups[] = new CheezCapGroup(
			__(
				'Digital Daily',
				'pmc-digital-daily'
			),
			'pmc-digital-daily',
			[
				$this->_get_newsletter_option(),
			]
		);

		return $groups;
	}

	/**
	 * Populate newsletter-related settings.
	 *
	 * @return CheezCapDropdownOption
	 */
	protected function _get_newsletter_option(): CheezCapDropdownOption {
		$newsletters = Sailthru_Blast_Repeat::get_repeats();

		if ( ! is_array( $newsletters ) ) {
			$newsletters = [];
		}

		$values = array_values(
			wp_list_pluck(
				$newsletters,
				'feed_ref'
			)
		);

		$labels = array_values(
			wp_list_pluck(
				$newsletters,
				'name'
			)
		);

		array_unshift( $values, null );
		array_unshift( $labels, '' );

		return new CheezCapDropdownOption(
			__(
				'Newsletter',
				'pmc-digital-daily'
			),
			__(
				'Select newsletter associated with the Digital Daily',
				'pmc-digital-daily'
			),
			static::NEWSLETTER,
			$values,
			0,
			$labels
		);
	}
}
