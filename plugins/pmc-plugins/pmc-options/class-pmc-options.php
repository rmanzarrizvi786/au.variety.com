<?php
/**
 * Version 2015-08-5, Hau - Fix issue where _populate_data is called before post type registered
 */

class PMC_Options {
	private static $_instance = array();

	//Post slug
	private $_post_name = 'general';

	private $_post_id = false;

	// Post type name
	const post_type_name = 'pmc-long-options';

	/**
	 * Return single instance of a class
	 * @static
	 * @param string $group_name
	 * @return mixed
	 */
	public static function get_instance( $group_name = 'general' ) {

		if( ! isset( self::$_instance[ $group_name ] ) ) {
			self::$_instance[ $group_name ] = new PMC_Options( $group_name );
			self::$_instance[ $group_name ]->init();
			// NOTE: can't call _populate_data here since post type have not been register via init function
		}

		return self::$_instance[ $group_name ];
	}

	/**
	 * Private constructor since this class is singleton
	 */
	private function __construct( $group_name ) {
		//Adding hash to post name, so it is unique and while creating post, the slug
		//does not conflict with other post's slug in the table
		$this->_post_name = $group_name.md5($group_name);
	}


	/**
	 * Function to add action on init
	 */
	public function init() {

		add_action( 'init', array( $this, 'register_post_type' ) );

	}

	/**
	 * Populate data on init
	 */
	private function _populate_data(){

		if ( ! empty( $this->_post_id ) ) {
			return;
		}

		$this->_post_id = wp_cache_get( $this->_post_name, 'pmc-long-options' );

		if ( ! $this->_post_id ) {
			global $wpdb;

			//Using direct query so its lightweight, getting just ID of the post.
			$this->_post_id = $wpdb->get_var( $wpdb->prepare(
				"select ID from $wpdb->posts
				where post_type = %s
				and post_name = %s
				order by id desc
				limit 1", self::post_type_name, $this->_post_name
			) );

			if( !$this->_post_id ){
				$this->_post_id = $this->insert_post();
			}

			if( $this->_post_id > 0 ){

				wp_cache_set( $this->_post_name, $this->_post_id, 'pmc-long-options' );

			}
		}

		//Pre-populate post meta cache for further use
		get_post_custom( $this->_post_id );
	}

	/**
	 * Register post type
	 */
	public function register_post_type(){
		// if post type already exist, do nothing
		if ( post_type_exists( self::post_type_name ) ) {
			return;
		}
		register_post_type(
			self::post_type_name,
			array(
				'label'   => __('PMC Long Options', 'pmc-long-options' ),
				'public'  => false,
				'rewrite' => false,
			)
		);
	}

	/**
	 * Get insert of custom options post type.
	 * Insert post if not present already
	 * @return int|WP_Error
	 */
	public function insert_post(){

		$args=array(
			'name' => $this->_post_name,
			'post_name' => $this->_post_name,
			'post_type' => self::post_type_name,
			'post_status' => 'publish',
			'post_title' => $this->_post_name,
			'post_content' => '',
			'numberposts' => 1,
			'post_date' => current_time( 'mysql' )
		);

		$post_id = wp_insert_post( $args );

		if ( is_wp_error( $post_id ) ) {

			$post_id = 0;
		}

		return $post_id;

	}

	/**
	 * Add option as post_meta
	 * @param $name
	 * @param $value
	 */
	public function add_option( $name, $value ){

		$this->_populate_data();
		//use update_post_meta to avoid duplicates
		return update_post_meta( $this->_post_id, $name, $value );

	}

	/**
	 * Update option
	 * @param $name
	 * @param $value
	 */
	public function update_option( $name, $value ){

		$this->_populate_data();
		return update_post_meta( $this->_post_id, $name, $value );

	}

	/**
	 * Delete Option
	 * @param $name
	 */
	public function delete_option( $name ){

		$this->_populate_data();
		return delete_post_meta( $this->_post_id, $name );

	}

	/**
	 * Get Option
	 * @param $name
	 */
	public function get_option( $name ){

		$this->_populate_data();
		return get_post_meta( $this->_post_id, $name, true );

	}

	public function get_options(){

		$this->_populate_data();
		return get_post_meta( $this->_post_id );
	}

}
//EOF