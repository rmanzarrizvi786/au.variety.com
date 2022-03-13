<?php
/**
 * Jetpack customizations.
 *
 * @package pmc-global-functions
 */

namespace PMC\Global_Functions;

use BGR_CheezCapGroup;
use CheezCapGroup;
use CheezCapOption;
use CheezCapMultipleCheckboxesOption;
use PMC\Global_Functions\Traits\Singleton;
use PMC_Cheezcap;

class Jetpack_Customizations {
	use Singleton;

	/**
	 * Name of Cheezcap group.
	 */
	protected const CHEEZCAP_GROUP = 'pmc_jetpack';

	/**
	 * Name of Cheezcap field to permit custom roles access to Stats.
	 */
	protected const CHEEZCAP_FIELD_STATS = 'pmc_jetpack_stats_roles';

	/**
	 * Jetpack_Customizations constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_filter( 'jetpack_active_modules', [ $this, 'force_activate_jetpack_modules' ] );

		add_filter( 'pmc_cheezcap_groups', [ $this, 'add_cheezcap' ], 99 );
		add_filter( 'option_stats_options', [ $this, 'set_stats_roles' ] );
	}

	/**
	 * Ensure certain modules are available on all sites.
	 *
	 * @param array $modules
	 * @return array
	 */
	public function force_activate_jetpack_modules( array $modules ): array {
		$forced_modules = [
			'custom-css',
			'shortcodes',
			'sitemaps',
			'publicize',
		];

		return array_unique(
			array_merge(
				$modules,
				$forced_modules
			)
		);
	}

	/**
	 * Register Cheezcap group for Jetpack options.
	 *
	 * @param array $groups Cheezcap groups.
	 * @return array
	 */
	public function add_cheezcap( array $groups ): array {
		$fields = [
			$this->_get_cheezcap_stats_field(),
		];

		if ( class_exists( 'BGR_CheezCapGroup', false ) ) {
			// Cannot cover as this is theme dependent.
			$cheezcap_group_class = BGR_CheezCapGroup::class; // @codeCoverageIgnore
		} else {
			$cheezcap_group_class = CheezCapGroup::class;
		}

		$groups[] = new $cheezcap_group_class(
			'Jetpack',
			static::CHEEZCAP_GROUP,
			$fields
		);

		return $groups;
	}

	/**
	 * Build field for choosing roles that can access Jetpack Stats.
	 *
	 * @return CheezCapOption
	 */
	protected function _get_cheezcap_stats_field(): CheezCapOption {
		$custom_roles = $this->_get_roles_for_stats();

		return new CheezCapMultipleCheckboxesOption(
			'Stats Access',
			"Jetpack's UI does not support custom roles, so stats access is granted to the roles selected here.",
			static::CHEEZCAP_FIELD_STATS,
			// Return type ensures an array is returned.
			// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
			array_keys( $custom_roles ),
			array_values( $custom_roles ),
			'',
			[
				PMC_Cheezcap::class,
				'sanitize_cheezcap_checkboxes',
			]
		);
	}

	/**
	 * Build list of roles that can be granted Stats access.
	 *
	 * @return array
	 */
	protected function _get_roles_for_stats(): array {
		if ( ! is_admin() ) {
			return [];
		}

		// Borrowed from `stats_upgrade_options()`.
		if ( ! function_exists( 'get_editable_roles' ) ) {
			// Cannot cover as there's no way to unload this file.
			require_once ABSPATH . 'wp-admin/includes/user.php'; // @codeCoverageIgnore
		}

		$excluded_roles = [
			'vip_support',
			'vip_support_inactive',
		];

		$roles = array_diff_key(
			get_editable_roles(),
			array_flip( $excluded_roles )
		);

		return wp_list_pluck( $roles, 'name' );
	}

	/**
	 * Modify role-related settings for Jetpack's Stats module.
	 *
	 * @param array|string|false $option Jetpack Stats options.
	 * @return array
	 */
	public function set_stats_roles( $option ): array {
		if ( ! is_array( $option ) ) {
			$option = [];
		}

		// Don't track any logged-in users.
		$option['count_roles'] = [];

		$roles = PMC_Cheezcap::get_instance()->get_option(
			static::CHEEZCAP_FIELD_STATS
		);

		if ( ! is_array( $roles ) || empty( $roles ) ) {
			return $option;
		}

		$option['roles'] = array_unique(
			array_merge(
				$option['roles'] ?? [],
				$roles
			)
		);

		return $option;
	}
}
