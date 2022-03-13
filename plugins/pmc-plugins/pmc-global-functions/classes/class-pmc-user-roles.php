<?php

/**
 * This class adds custom user roles for PMC sites
 *
 * IMPORTANT: User custom role creation needs some extra steps for integration testing
 * @see http://docs.pmc.com/2014/12/03/create-qa-logins-for-custom-roles/
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since 2014-02-28
 * @version 2014-03-04
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_User_Roles {

	use Singleton;

	/**
	 * @var array The common capabilities shared between multiple roles of same types
	 */
	protected $_common_capabilities = array(
		'manager' => array(
			'edit_theme_options' => true,
		),
	);

	/**
	 * @var array The custom roles
	 */
	protected $_roles = array(
		'pmc-editorial-manager' => array(
			'type' => 'manager',
			'label' => 'PMC Editorial Manager',
			'base_role' => 'editor',
			'additional_capabilities' => array(
				'list_users' => true,
				'edit_themes' => true, /* Allow Appearance menu */
				'edit_posts' => true, /* Polldaddy v2.0.23 is_author() */
				'delete_others_pages' => true /* Polldaddy v2.0.23 is_editor() */
			),
		),
		'pmc-adops-manager' => array(
			'type' => 'manager',
			'label' => 'PMC AdOps Manager',
			'base_role' => 'editor',
			'additional_capabilities' => array(
				'upload_files' => true /* Specifically allow access to media tab */
			),
		),
		'pmc-reporter' => array(
			'type' => 'reporter',
			'label' => 'PMC Reporter',
			'base_role' => 'editor',
			'additional_capabilities' => array(),
		),
		'pmc-audience-marketing' => array(
			'type' => 'manager',
			'label' => 'PMC Audience Marketing',
			'base_role' => 'contributor',
			'additional_capabilities' => array(
				'upload_files' => true, /* Specifically allow access to media tab */
			),
		),
		'pmc-international-site-translator' => array(
			'type'                    => 'translator',
			'label'                   => 'PMC International Site Translator',
			'base_role'               => 'subscriber',
			'additional_capabilities' => array(),
		),
	);

	/**
	 * @var array The exclusive capabilities of each role defined separately as all these would be added to administrator role as well
	 */
	protected $_exclusive_capabilities = array(
		'pmc-editorial-manager' => array(
			'pmc_safe_redirect_manager_cap' => true,
			'pmc_encoding_cap' => true,
		),
		'pmc-reporter' => array(
			'pmc_editorial_reports_cap' => true,
		),
		'pmc-international-site-translator' => array(
			'pmc_view_custom_feeds_for_translators' => true,
		),
	);


	/**
	 * Class init function called when object is created
	 */
	protected function __construct() {
		//override plugin capabilities
		$this->_override_plugin_capabilities();

		add_action( 'init', array( $this, 'init_roles' ), 0 ); //03MAY14 Previously level was 20 but custom roles weren't being pushed out on time
	}

	/**
	 * This function is called on WordPress 'init' and sets
	 * the ball rolling
	 */
	public function init_roles() {
		//initial Voodoo for the people who want options in life & their plugins
		$this->_allow_override_on_roles();
		$this->_allow_override_on_common_capabilities();

		//time to add 'em custom user roles
		$this->_add_custom_roles();
	}

	/**
	 * This function accepts an array and returns the keys of all second
	 * dimension arrays
	 *
	 * @param array $capabilities_haystack
	 * @return array
	 */
	protected function _get_capability_names( array $capabilities_haystack = array() ) {
		if ( empty( $capabilities_haystack ) ) {
			return array();
		}

		$capability_names = array();

		foreach ( $capabilities_haystack as $key => $value ) {
			if ( ! is_array( $value ) ) {
				continue;
			}

			$capability_names = array_merge( $capability_names, array_keys( $value ) );
		}

		return array_filter( array_unique( $capability_names ) );
	}

	/**
	 * This function applies a filter on $_roles to allow addition/modification/removal of any custom role
	 *
	 * @return void
	 */
	protected function _allow_override_on_roles() {
		//allow role override
		$this->_roles = apply_filters( 'pmc_user_roles_override', $this->_roles );
	}

	/**
	 * This function applies a filter on capabilities of each role type in $_common_capabilities
	 * to allow addition/modification/removal of any custom capability which is applicable to
	 * every custom role of that particular type (instead of doing them for every role)
	 *
	 * @return void
	 */
	protected function _allow_override_on_common_capabilities() {
		if ( empty( $this->_roles ) || ! is_array( $this->_roles ) ) {
			//the roles var needs to be an array, someone screwed up on filter, bail out
			return;
		}

		$types = array_filter( array_unique( wp_list_pluck( $this->_roles, 'type' ) ) );

		foreach ( $types as $type ) {
			$capabilities = array();

			if ( array_key_exists( $type, $this->_common_capabilities ) ) {
				$capabilities = $this->_common_capabilities[ $type ];
			}

			$capabilities = apply_filters( 'pmc_user_roles_override_' . $type . '_capabilities', $capabilities );

			if ( ! empty( $capabilities ) && is_array( $capabilities ) ) {
				$this->_common_capabilities[ $type ] = $capabilities;
			}

			unset( $capabilities );
		}
	}

	/**
	 * This function loops over all the roles defined in $_roles and adds them with their
	 * capabilities.
	 *
	 * @return void
	 */
	protected function _add_custom_roles() {
		if ( empty( $this->_roles ) || ! is_array( $this->_roles ) ) {
			//the roles var needs to be an array, someone screwed up on filter, bail out
			return false;
		}

		$admin_capabilities = array();

		foreach ( $this->_roles as $role_slug => $role ) {
			$capabilities = $role['additional_capabilities'];

			//if current role's type has any common capabilities then get those
			if ( array_key_exists( $role['type'], $this->_common_capabilities ) ) {
				$capabilities = array_merge( $capabilities, $this->_common_capabilities[ $role['type'] ] );
			}

			//if current role's type has any exclusive capabilities then get those
			if ( array_key_exists( $role_slug, $this->_exclusive_capabilities ) ) {
				$capabilities = array_merge( $capabilities, $this->_exclusive_capabilities[ $role_slug ] );
			}

			//allow capability override for this role
			$capabilities = apply_filters( 'pmc_user_roles_override_capabilities_' . $role_slug, $capabilities );

			if ( empty( $capabilities ) || ! is_array( $capabilities ) ) {
				//no capabilities, someone screwed up on filter, move to next role
				continue;
			}

			if ( function_exists( 'wpcom_vip_duplicate_role' ) ) {
				//add this role
				wpcom_vip_duplicate_role( $role['base_role'], $role_slug, $role['label'], $capabilities );
			}

			//merge capabilities with admin capabilities
			$admin_capabilities = array_merge( $admin_capabilities, array_diff( array_keys( $capabilities, true ), array_keys( $role['additional_capabilities'] ) ) );

			unset( $capabilities );
		}

		//add additional capabilities to admins
		$this->_add_additional_capabilities_to( 'administrator', $admin_capabilities );
	}

	/**
	 * This function accepts a role slug and an array of capabilities and assigns those capabilities
	 * to the role.
	 *
	 * @param string $role Role slug to which capabilities are to be added
	 * @param array $capabilities Capabilities that are to be added to the role
	 * @return void
	 */
	protected function _add_additional_capabilities_to( $role = 'administrator', $capabilities = array() ) {
		if ( empty( $role ) || ! is_string( $role ) || empty( $capabilities ) || ! is_array( $capabilities ) ) {
			//one thing or other not right, bail out
			return;
		}

		$capabilities = array_filter( array_unique( $capabilities ) );

		if ( function_exists( 'wpcom_vip_add_role_caps' ) ) {
			wpcom_vip_add_role_caps( $role, $capabilities );
		}
	}

	/**
	 * This function overrides capabilities for certain plugins
	 * with custom PMC capabilities
	 *
	 * @todo Filters and capabilities are hardcoded here for now. Need to implement in a more elegant way.
	 *
	 * @return void
	 */
	protected function _override_plugin_capabilities() {
		add_filter( 'srm_restrict_to_capability', function() {
			return 'pmc_safe_redirect_manager_cap';
		} );

		add_filter( 'pmc_encoding_capability_override', function() {
			return 'pmc_encoding_cap';
		} );

		add_filter( 'pmc_editorial_reports_capability_override', function() {
			return 'pmc_editorial_reports_cap';
		} );
	}

//end of class
}

PMC_User_Roles::get_instance();

//EOF
