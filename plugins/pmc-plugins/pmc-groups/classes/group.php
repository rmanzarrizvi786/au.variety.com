<?php namespace PMC\Groups;

/**
 * Class to handle Group objects for PMC_Groups
 *
 * @author Javier Martinez <jmartinez@pmc.com>
 * @since 2015-07-13
 */
class Group {

	protected $_slug;
	protected $_description;
	protected $_ticket;

	protected $_users = array();

	const TICKET_URL = "https://jira.pmcdev.io/browse/";

	/**
	 * Accepts a config array.
	 * $config['slug'] = required
	 *
	 * @param array $config
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	public function __construct( array $config ){

		if( !$this->validate_config( $config ) ){
			return false;
		}

		$this->_slug = sanitize_text_field( $config['slug'] );

		if( isset( $config['description'] ) ) {
			$this->_description = sanitize_text_field( $config['description'] );
		}

		if( isset( $config['ticket'] ) ) {
			$this->_ticket = sanitize_text_field( $config['ticket'] );
		}

		// Assigns users to the group's _users attribute
		$this->get_users();

		return $this;

	}

	/**
	 *
	 * Validates the config array
	 *
	 * @param $config
	 * @return bool
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	protected function validate_config( $config ){

		$valid = true;

		if( empty( $config['slug'] ) ) {
			$valid = false;
		}

		return $valid;
	}

	/**
	 * Returns users for the current group.
	 *
	 * @return array
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	public function get_users(){

		if( isset( $this->_slug ) ){
			$group_users = pmc_get_option( $this->_slug, Admin::OPTION_KEY );

			if( is_array( $group_users ) ){
				$this->_users = $group_users;
				return $this->_users;
			}

			// Either the key didn't exist, or its value wasn't an array
			return array();
		}
	}

	/**
	 * Simple getter
	 *
	 * @return mixed
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	public function get_description(){
		return $this->_description;
	}

	/**
	 * Simple getter
	 *
	 * @return bool
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	public function get_ticket(){

		if( trim( $this->_ticket ) !== '' ){
			return $this->_ticket;
		}else{
			return false;
		}
	}

	/**
	 * Simple getter
	 *
	 * @return mixed
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	public function get_slug(){
		return $this->_slug;
	}

	/**
	 * Returns TRUE if current user is a member of $group_name, FALSE if not
	 *
	 * @param $group_name
	 * @return bool
	 *
	 */
	public static function pmc_current_user_is_member_of( $group_name ) : bool {

		$current_user = wp_get_current_user();

		$is_user_member_of_blog = apply_filters( 'pmc_group_is_user_member_of_blog', is_user_member_of_blog() );

		if ( 0 === $current_user->ID && current_user_can( 'edit_posts' ) && $is_user_member_of_blog ) {
			return false;
		} else {
			return self::pmc_user_is_member_of( $current_user, $group_name );
		}
	}

	/**
	 * Returns TRUE is given $user is member of given $group_name
	 *
	 * @param $user
	 * @param $group_name
	 * @return bool
	 *
	 */
	public static function pmc_user_is_member_of( $user, $group_name ) : bool {

		if ( ! current_user_can( 'edit_posts' ) || ! is_user_member_of_blog( $user->ID ) ) {
			return false;
		}

		$groups = Admin::get_instance()->get_user_groups( $user );

		return in_array( $group_name, (array) $groups, true );
	}
}
