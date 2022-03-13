<?php
/**
 * Provide role based access to ads of different providers - Add PMC Audience Marketing role and its capabilities
 * Using filters from Existing Role Class PMC_User_Roles
 *
 * @see pmc-global-functions/classes/class-pmc-user-roles.php
 * @author Archana Mandhare <amandhare@pmc.com>
 * @since 2017-11-21
 * @version 2017-11-21 Archana Mandhare PMCRS-986
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Ads_Role {

	use Singleton;

	/**
	 * @var array
	 * All these would be added to administrator role as well
	 * The exclusive capabilities of each role defined separately.
	 * The providers each role will support.
	 */
	protected $_adm_roles = [
		'pmc-audience-marketing' => [
			'capabilities' => [
				'pmc_manage_site_served_ads_cap' => true, /* Specifically allow access to site served ads only */
				'pmc_manage_ads_cap'             => true,
			],
			'providers'    => [ 'site-served' ],
		],
		'pmc-adops-manager'      => [
			'capabilities' => [
				'pmc_manage_google_publisher_ads_cap' => true, /* Specifically allow access to all adops ads */
				'pmc_manage_ads_cap'                  => true,
			],
			'providers'    => [ 'google-publisher' ],
		],
	];

	/**
	 * Class constructor
	 * Add all the hooks and filters
	 */
	protected function __construct() {
		add_filter( 'pmc_user_roles_override_capabilities_pmc-audience-marketing', [ $this, 'add_capabilities_for_audience_marketing' ] );
		add_filter( 'pmc_user_roles_override_capabilities_pmc-adops-manager', [ $this, 'add_capabilities_for_adops' ] );
	}

	/**
	 * Filter hook to add exclusive capabilities to audience_marketing
	 * @param array
	 * @return array
	 */
	public function add_capabilities_for_audience_marketing( $capabilities = array() ) {
		if ( is_array( $capabilities ) ) {
			$capabilities = array_merge( $capabilities, $this->_adm_roles['pmc-audience-marketing']['capabilities'] );
		}

		return $capabilities;
	}

	/**
	 * Filter hook to add exclusive capabilities to adops
	 * @param array
	 * @return array
	 */
	public function add_capabilities_for_adops( $capabilities = array() ) {
		if ( is_array( $capabilities ) ) {
			$capabilities = array_merge( $capabilities, $this->_adm_roles['pmc-adops-manager']['capabilities'] );
		}

		return $capabilities;
	}

	/**
	 * Get all the providers that this role supports
	 *
	 * @param string $role
	 *
	 * @return mixed
	 */
	public function get_providers_for_role( $role ) {

		$current_providers = [];

		$providers = PMC_Ads::get_instance()->get_providers();

		if ( empty( $providers ) || ! is_array( $providers ) ) {
			return [];
		}

		// If an admin then return all providers
		if ( 'administrator' === $role ) {
			return $providers;
		}

		if ( ! array_key_exists( $role, $this->_adm_roles ) || ! array_key_exists( 'providers', $this->_adm_roles[ $role ] ) ) {
			return [];
		}

		$role_providers = $this->_adm_roles[ $role ]['providers'];

		if ( empty( $role_providers ) || ! is_array( $role_providers ) ) {
			return [];
		}

		foreach ( $role_providers as $role_provider ) {
			if ( array_key_exists( $role_provider, $providers ) ) {
				$current_providers = array_merge( $current_providers, [ $role_provider => $providers[ $role_provider ] ] );
			}
		}

		return $current_providers;
	}

	/**
	 * Get all the roles declared in this class
	 * @return array
	 */
	public function get_adm_roles() {
		return array_keys( $this->_adm_roles );
	}

}
