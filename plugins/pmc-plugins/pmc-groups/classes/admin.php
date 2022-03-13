<?php namespace PMC\Groups;

use PMC;
use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class to handle wp-admin side of the plugin
 *
 * @author Javier Martinez <jmartinez@pmc.com>
 * @since 2015-07-08
 * TODO: Add docblocks for all methods
 */
class Admin {

	use Singleton;

	const CACHE_KEY = 'pmc_groups';
	const OPTION_KEY = 'pmc_groups';
	const CACHE_GROUP = 'pmc_groups_cache';
	const WP_USERS_CACHE_LIFE = 15 * MINUTE_IN_SECONDS;
	const PMC_GROUPS_BODY_CLASS = "pmc-group-";

	protected $_groups = array();

	// default groups to add
	protected $_groups_to_add = array(

		/*
		 * User group for PMC Developers with list of default
		 * users that are to be a part of this group.
		 */
		'pmc-dev'     => array(
			'description' => 'Group for all PMC Developers',
			'users'       => array(
				'agupta@pmc.com',
				'asannad@pmc.com',
				'amandhare@pmc.com',
				'bcamenisch@pmc.com',
				'cgilmore@pmc.com',
				'dsingh@pmc.com',
				'hvong@pmc.com',
				'james.mehorter@pmc.com',
				'michael.auteri@pmc.com',
				'vsharma@pmc.com',
				'aescobar@pmc.com',
				'mjzorick@pmc.com',
				'vtella@pmc.com',
			),
		),

		/*
		 * User group for PMC Product/Project Managers
		 * with list of default users that are to be a part
		 * of this group.
		 */
		'pmc-product' => array(
			'description' => 'Group for all PMC Product team members',
			'users'       => array(
				'dramsay@pmc.com',
				'gkoen@pmc.com',
				'ncatton@pmc.com',
				'spipershimizu@pmc.com',
			),
		),

		/*
		 * User group to control access to Getty/pre-2016 images
		 * on a LoB. There are no default users here, each LoB
		 * defines its own access list.
		 */
		'pmc-getty-access' => array(
			'description' => 'Group which allows access to Getty image attachments',
			'users'       => array(),
		),
	);

	/**
	 * Set up hooks
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	protected function __construct() {
		// Actions
		add_action( 'init', array( $this, 'init_actions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'setup_assets' ) );
		add_action( 'admin_menu', array( $this, 'admin_settings_menu') );
		add_action( 'wp_ajax_groups_crud', array( $this, 'handle_form' ) );

		// Filters
	}

	/**
	 * Fire functions we need during ini hook
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	public function init_actions(){
		$this->_register_groups();
		$this->_add_body_class();
	}

	/**
	 * Registers default + custom groups through filter
	 * All groups will be stored in $this->_groups as objects
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 * @version 2016-03-29 Archana Mandhare PMCVIP-1074 - Add default users to the pmc-dev group
	 */
	protected function _register_groups() {
		$default_config = array(
			'slug'        => '',
			'description' => '',
			'ticket'      => '',
			'users'       => array(),
		);

		$groups = array();

		//setup default groups
		foreach ( $this->_groups_to_add as $group_slug => $group_config ) {

			$groups[ $group_slug ] = array(
				'slug'        => $group_slug,
				'description' => $group_config['description'],
				'ticket'      => '',
				'users'       => $group_config['users'],
			);

		}
		$groups = apply_filters( 'pmc_groups_register_group', $groups );

		foreach( $groups as $slug => $config ) {
			$config = array_merge( $default_config, $config );

			if ( ! is_array( $config['users'] ) ) {
				$config['users'] = array();
			}
			$config['slug'] = $slug;

			// resolve email addresses to user accounts
			$config['users'] = $this->_get_default_user_list( $config['users'], $slug );

			$groups[$slug] = $config;
		}

		$final_list = array();
		foreach( $groups as $slug => $config ) {
			if( !is_string( $slug ) || !is_array( $config ) || empty( $slug ) ) {
				continue;
			}
			$final_list[ $slug ] = new Group( $config );
		}

		$this->_groups = $final_list;

	}

	/**
	 * Adds body classes for each group the current user is in
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	protected function _add_body_class(){

		global $current_user;

		foreach( $this->_groups as $slug => $group ){
			$users = $group->get_users();
			if( ! empty( $users ) && in_array( $current_user->user_login, $users ) ){
				pmc_add_body_class( self::PMC_GROUPS_BODY_CLASS . $slug );
			}
		}
	}

	/**
	 * Set up assets
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 * @version 2016-01-08 Amit Gupta - PMCVIP-772 - load assets only on relevant page
	 */
	public function setup_assets( $hook = '' ) {

		if ( strtolower( $hook ) !== 'settings_page_pmc-groups' ) {
			//not our admin page, bail out
			return;
		}

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );

		wp_enqueue_style( 'pmc-groups-manager-css', plugins_url( 'pmc-groups/assets/css/screen.css', PMC_GROUPS_ROOT ) );
		wp_enqueue_style( 'pmc-jqueryui-core', plugins_url( 'pmc-groups/assets/css/jquery-ui/core.css', PMC_GROUPS_ROOT ) );
		wp_enqueue_style( 'pmc-jqueryui-resizable', plugins_url( 'pmc-groups/assets/css/jquery-ui/resizable.css', PMC_GROUPS_ROOT ) );

		wp_enqueue_script( 'pmc-groups-manager', plugins_url( 'pmc-groups/assets/js/admin.js', PMC_GROUPS_ROOT ), array( 'jquery' ) );

		wp_localize_script( 'pmc-groups-manager', 'pmc_groups_options', array(
			'url'              => admin_url( 'admin-ajax.php' ),
			'pmc_groups'       => $this->groups_to_array(),
			'wp_users'         => $this->get_wp_users( true ),
			'admin_url'        => admin_url("users.php?s="),
			'pmc_groups_nonce' => wp_create_nonce( 'pmc-groups' ),
		) );

	}

	/**
	 * Register the settings page
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	public function admin_settings_menu(){
		add_options_page(
			'PMC Groups',       // Page Title
			'PMC Groups',       // Menu Title
			'manage_options',   // Capability
			'pmc-groups',       // Menu Slug
			array( $this, 'render_admin_page' )           // Callback function
		);
	}

	/**
	 * Render our admin page template
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	public function render_admin_page () {

		echo PMC::render_template( PMC_GROUPS_ROOT . '/templates/admin.php', array(
			'pmc_groups'    => $this->groups_to_array(),
		) );
	}

	/**
	 * Simple getter
	 *
	 * @return array
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	public function get_groups(){
		return $this->_groups;
	}

	/**
	 * Accepts array of group keys.
	 * Returns TRUE if current user is in any of those groups
	 *
	 * @param $user
	 * @internal param $group
	 * @internal param array $groups
	 * @return bool
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	public function get_user_groups( $user ){
		$groups = $this->get_groups();
		$user_groups = array();

		foreach( $groups as $slug => $group ){

			if( in_array( $user->data->user_login, $group->get_users() )){
				$user_groups[] = $slug;
			}
		}

		return $user_groups;
	}

	/**
	 * Returns wp_users from either cache or DB
	 * @param $keyed boolean Default false. If true, will return an array of user objects keyed by username. Otherwise numerical index.
	 *
	 * @return array $wp_users
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 * @version 2016-03-31 Archana Mandhare PMCVIP-1074 - Added user_email to the fields to get email for each user.
	 * @version 2017-02-14 Corey Gilmore Supported
	 */
	public function get_wp_users( $keyed = false ){
		$wp_users = wp_cache_get( md5( self::CACHE_KEY . '_wp_users' ), self::CACHE_GROUP );

		if ( 'empty' === $wp_users ) {
			return array();
		}

		if ( false === $wp_users ) {
			$wp_users = get_users( array(
				'orderby' => 'display_name',
				'order'   => 'ASC',
				'role__not_in' => 'subscriber',
				'fields'  => array(
					'ID',
					'user_login',
					'display_name',
					'user_email',
				)
			) );

			if ( empty( $wp_users ) ) {
				$wp_users = 'empty';
			}

			wp_cache_set( md5( self::CACHE_KEY . '_wp_users' ), $wp_users, self::CACHE_GROUP, self::WP_USERS_CACHE_LIFE );

			if ( 'empty' === $wp_users ) {
				return array();
			}
		}

		if ( $keyed ) {
			$keyed_wp_users = array();
			foreach ( $wp_users as $user ) {
				$keyed_wp_users[$user->user_login] = $user;
			}
			$wp_users = $keyed_wp_users;
		}

		return $wp_users;

	}

	/**
	 * Handle AJAX form submission: create ad, update ad, etc.
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	public function handle_form() {

		check_ajax_referer( 'pmc-groups', 'pmc_groups_nonce' );

		if( !current_user_can('edit_posts') || ! is_user_member_of_blog() ) {
			wp_send_json( array(
				'success' => false,
				'message' => "Your permissions do not allow this action.",
				'method'  => "NA",
			) );
			exit();
		}

		$success = true;
		$message = null;
		$method  = sanitize_text_field( $_GET['method'] );
		$valid_methods = array( 'save', 'add' );

		if( ! in_array( $method, $valid_methods, true ) ){
			wp_send_json( array(
				'success' => false,
				'message' => 'Invalid form method',
				'method'  => 'INVALID',
			) );
			exit();
		}

		// Get group and users
		$data['group'] = sanitize_text_field( $_GET['data']['group'] );

		if( ! empty($_GET['data']['users'] ) ){
			foreach( $_GET['data']['users'] as $username ){
				$data['users'][] = sanitize_user( $username, true );
			}
		}else{
			// We need to allow a group to be completely cleared of users;
			$data['users'] = array();
		}

		// Make sure we have a group
		if( empty( $data['group'] ) ){
			wp_send_json( array(
				'success' => false,
				'message' => "Group was not provided.",
				'method'  => $method,
			) );
			exit();
		}else{
			$data['group'] = sanitize_text_field( $data['group'] );
		}


		// Prune invalid usernames
		$cached_usernames = $this->get_wp_users();

		foreach( $data['users'] as $key => $username ){
			$username_is_valid = validate_username( $username );
			$username_exist = username_exists( $username );
			$username_is_allowed = false;

			// Verify user is in our cached list
			foreach( $cached_usernames as $cached_username ){
				if( $cached_username->user_login === $username ){
					$username_is_allowed = true;
					break;
				}
			}

			// Username needs to be valid, exist and be in our allowed wp_users list
			if ( ! $username_is_valid || ! $username_exist || ! $username_is_allowed ) {
				unset( $data['users'][ $key ] );
			}
		}
		unset($cached_usernames );

		try {
			switch ( $method ) {
				case 'save':
					$success = $this->save( $data );
					break;
				case 'add':
					break;
			}

		} catch (Exception $e) {
			$success = false;
			$message = $e->getMessage();
		}

		// Output response
		wp_send_json( array(
			'success' => $success,
			'message' => $message,
			'method'  => $method,
		) );

		exit();
	}

	/**
	 * Return array version of Group collection. Useful localizing data.
	 *
	 * @return array
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	public function groups_to_array(){

		$data = array();
		foreach( $this->_groups as $slug => $group ){

			$data[ $slug ] = array(
				'ticket'        => $group->get_ticket(),
				'description'   => $group->get_description(),
				'users'         => $group->get_users( true ),
				'user_count'    => 0,
			);
			$data[$slug]['user_count'] = count( $data[$slug]['users'] );

		}

		return $data;
	}

	/**
	 * Add a user to a group only if the user does not already exist in the given group
	 *
	 * @param array $data
	 * @internal param $group
	 * @internal param $user
	 * @return bool
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 *
	 */
	protected function save( array $data ){

		try{

			pmc_update_option($data['group'], $data['users'], self::OPTION_KEY);

			$this->_prune_groups();

		}catch (\Exception $e ){
			return false;
		}

		return true;

	}

	/**
	 * Deletes old groups existing within stored meta data but that are not currently being registered via filter.
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	protected function _prune_groups(){
		$pmc_options_groups = pmc_get_options( self::OPTION_KEY );

		foreach ( $pmc_options_groups as $slug => $data ){

			if( !array_key_exists( $slug, $this->_groups ) ){
				pmc_delete_option( $slug, self::OPTION_KEY );
			}
		}

	}

	/**
	 * Get the default users list for pmc-dev default group
	 *
	 * @since 2016-03-28
	 * @version 2016-03-28 Archana Mandhare  PMCVIP-1074
	 *
	 * @param $user_list list of emails of the users
	 * @param $group_name the groupname that the users should belong to
	 *
	 * @return array list of users part of this group
	 *
	 */
	protected function _get_default_user_list( $user_list, $group_name ) {

		$default_users = array();

		// get_wp_users() is a function that returns cached list of users. Cache time is 1 hour
		$cached_usernames = $this->get_wp_users();

		foreach ( $user_list as $email ) {

			$username_is_allowed = false;

			// Verify user is in our cached list
			foreach ( $cached_usernames as $cached_username ) {
				if ( $cached_username->user_email === $email ) {
					$username_is_allowed = true;
					break;
				}
			}

			// Username needs to be valid, exists and be in our allowed wp_users list
			if ( ! $username_is_allowed ) {
				$not_allowed[] = $email;
			}
		}

		// Filter out the users that are not registered WP users from the $this->_dev_list
		if ( ! empty( $not_allowed ) ) {
			$user_list = array_diff( $user_list, $not_allowed );
			foreach ( $user_list as $email ) {
				$user = get_user_by( 'email', $email );
				$default_users[] = $user->user_login;
			}
		}

		$group_users = pmc_get_option( $group_name, Admin::OPTION_KEY );

		if ( ! is_array( $group_users ) ) {
			$group_users = array();
		}

		// Check if we have any users not already saved as part of the group
		$diff = array_diff(  $default_users, $group_users );

		if ( ! empty( $diff ) ) {
			// We have found users that were not part of the group, hence adding them to the group
			$group_users = array_unique( array_merge( $default_users, $group_users ), SORT_REGULAR );
			pmc_update_option( $group_name, $group_users, self::OPTION_KEY );
		}

		return $group_users;
	}

}
