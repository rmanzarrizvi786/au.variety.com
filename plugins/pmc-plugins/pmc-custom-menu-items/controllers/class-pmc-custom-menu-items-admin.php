<?php

/**
 * PMC Custom Menu Items admin class
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since 2014-08-18
 */

class PMC_Custom_Menu_Items_Admin extends PMC_Custom_Menu_Items {

	/**
	 * Nonce field data
	 */
	private $_nonce = array(
		'action' => 'pmc-custom-menu-item-add_edit',
		'field'  => 'pmc-cmi-nonce',
	);

	/**
	 * Post status blocklist
	 *
	 * Array containing post statuses which are not to be allowed
	 */
	private $_post_status_blocklist = array( 'pending', 'private' );

	/**
	 * Array in which admin notices are stored
	 */
	private $_admin_notices = array();


	/**
	 * Setup hooks for admin UI
	 *
	 * @return void
	 */
	protected function _setup_child_hooks() {
		/**
		 * actions
		 */
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );	//setup display of error messages if any
		add_action( 'admin_footer', array( $this, 'clear_admin_notices' ) );	//clear out error messages which have been displayed already
		add_action( 'shutdown', array( $this, 'save_admin_notices' ) );			//save error messages for current page

		add_action( 'add_meta_boxes_' . parent::POST_TYPE, array( $this, 'setup_metaboxes' ) );		//add metabox(es) only for our post type

		/**
		 * filters
		 */
		add_filter( 'post_updated_messages', array( $this, 'set_messages' ) );		//custom messages for our post type
		add_filter( 'wp_insert_post_data', array( $this, 'save_hook_callback' ), 99, 2 );		//intercept post data before save
	}


	/**
	 * Accepts a string and returns a sanitized version
	 *
	 * @param string $value String to sanitize
	 * @return string Sanitized $value
	 */
	private function _sanitize_parameter( $value ) {
		return sanitize_text_field( strip_tags( $value ) );
	}


	/**
	 * Determine if current admin screen is our post type's screen or not
	 *
	 * @param string $post_type Post type against which current screen is to be checked
	 * @return bool TRUE if current screen is for $post_type post type else FALSE
	 */
	private function _is_our_admin_screen( $post_type = '' ) {
		if ( ! empty( $post_type ) ) {
			return (bool) ( $post_type == parent::POST_TYPE );
		}

		if ( get_post_type() ) {
			return (bool) ( get_post_type() == parent::POST_TYPE );
		}

		return false;
	}


	/**
	 * This function is hooked on 'post_updated_messages' filter
	 * to set custom messages for our post type which are displayed whenever
	 * a post of our custom post type is saved.
	 *
	 * @return array $messages
	 */
	public function set_messages( $messages ) {
		$entity = 'Custom Menu Item';

		$messages[ parent::POST_TYPE ] = array(
			0  => '',
			1  => sprintf( '%s updated', $entity ),
			2  => false,
			3  => false,
			4  => sprintf( '%s updated', $entity ),
			5  => false,
			6  => sprintf( '%s published', $entity ),
			7  => sprintf( '%s saved', $entity ),
			8  => sprintf( '%s submitted', $entity ),
			9  => false,
			10 => sprintf( '%s draft updated', $entity ),
		);

		return $messages;
	}


	/**
	 * This function accepts a message which is to be displayed after
	 * current post is saved.
	 *
	 * @return void
	 */
	private function _add_admin_notice( $message, $type = 'error' ) {
		if ( empty( $message ) || ! is_string( $message ) ) {
			return;
		}

		$key = md5( $message );

		if ( array_key_exists( $key, $this->_admin_notices ) ) {
			return;
		}

		$type = ( $type !== 'success' ) ? 'error' : 'updated';

		$this->_admin_notices[ $key ] = array(
			'type' => $type,
			'message' => wp_kses_post( $message ),
		);
	}


	/**
	 * This function is called on shutdown and it saves $this->_admin_notices in
	 * WP Cache if its not empty and if current screen is add/edit screen for our post type
	 *
	 * @return void
	 */
	public function save_admin_notices() {
		if ( ! $this->_is_our_admin_screen() || empty( $this->_admin_notices ) || ! get_the_ID() ) {
			return;
		}

		wp_cache_set( get_the_ID(), $this->_admin_notices, parent::POST_TYPE, 300 );
	}

	/**
	 * This function is called on admin_footer and it clears out notices saved in
	 * WP Cache if current screen is add/edit screen of our post type
	 *
	 * @return void
	 */
	public function clear_admin_notices() {
		if ( ! $this->_is_our_admin_screen() || ! get_the_ID() ) {
			return;
		}

		wp_cache_delete( get_the_ID(), parent::POST_TYPE );
	}

	/**
	 * This function is called on admin_notices and it displays all notices saved in
	 * WP Cache if current screen is add/edit screen of our post type
	 *
	 * @return void
	 */
	public function show_admin_notices() {
		if ( ! $this->_is_our_admin_screen() || ! get_the_ID() ) {
			return;
		}

		$notices = wp_cache_get( get_the_ID(), parent::POST_TYPE );

		if ( empty( $notices ) ) {
			return;
		}

		foreach( $notices as $notice ) {
			//load the template to display admin notice
			echo PMC::render_template( PMC_CUSTOM_MENU_ITEMS_DIR . '/views/admin-notice.php', $notice );
		}
	}


	/**
	 * This function sets up metaboxes for our custom post type
	 *
	 * @return void
	 */
	public function setup_metaboxes() {
		add_meta_box( parent::PLUGIN_ID . '-hook-name', 'Filter Hook to Callback', array( $this, 'render_metabox' ), parent::POST_TYPE, 'normal', 'high' );
	}


	/**
	 * This function renders the Callback Filter metabox for our custom post type
	 *
	 * @param WP_Post $post Object of the post for which metabox is rendered
	 * @return void
	 */
	public function render_metabox( $post ) {
		if ( ! empty( $post->post_content ) ) {
			//grab data for current post to populate callback fields
			$callback_data = $this->_get_callback_data( $post );
		}

		if ( empty( $callback_data ) ) {
			$callback_data = array(
				'filter' => '',
				'param1' => '',
				'param2' => '',
			);
		}

		echo PMC::render_template( PMC_CUSTOM_MENU_ITEMS_DIR . '/views/admin-menu-item-metabox-ui.php', array(
			'plugin_id'     => parent::PLUGIN_ID,
			'filter_prefix' => parent::FILTER_PREFIX,
			'nonce'         => $this->_nonce,
			'callback_data' => $callback_data,
		) );
	}


	/**
	 * This function intercepts post data before it is saved by hooking into 'wp_insert_post_data'
	 * filter. If the post is of our custom post type then it runs few validations and re-organizes
	 * data before returning it back to WP for saving in database

	 * @param array $data Data that is to be saved in database
	 * @param array $posted_data Data that was sent by the add/edit post screen
	 * @return array $data
	 */
	public function save_hook_callback( array $data = array(), array $posted_data = array() ) {
		//run through some basic checks to make sure we're intercepting the correct post type's data
		if (
			empty( $data ) || empty( $posted_data ) || $data['post_type'] !== parent::POST_TYPE
			|| empty( $posted_data[ $this->_nonce['field'] ] )
			|| ! wp_verify_nonce( $posted_data[ $this->_nonce['field'] ], $this->_nonce['action'] )
		) {
			//not our post type, bail out
			return $data;
		}

		$callback_data = array(
			'filter' => ( ! empty( $posted_data[ parent::PLUGIN_ID . '-filter' ] ) ) ? sanitize_title( $posted_data[ parent::PLUGIN_ID . '-filter' ] ) : '',
			'param1' => ( ! empty( $posted_data[ parent::PLUGIN_ID . '-param1' ] ) ) ? $this->_sanitize_parameter( $posted_data[ parent::PLUGIN_ID . '-param1' ] ) : '',
			'param2' => ( ! empty( $posted_data[ parent::PLUGIN_ID . '-param2' ] ) ) ? $this->_sanitize_parameter( $posted_data[ parent::PLUGIN_ID . '-param2' ] ) : '',
		);

		//json encode the callback data array and store in post content
		$data['post_content'] = json_encode( $callback_data );

		//override post_status if its blocklisted
		if ( in_array( $data['post_status'], (array) $this->_post_status_blocklist, true ) ) {
			$data['post_status'] = 'draft';
		}

		/*
		 * If filter is not set then force the post_status to draft,
		 * because we cannot allow a custom menu item to be placed in
		 * a menu without a callback filter defined for it
		 */
		if ( $data['post_status'] !== 'draft' && empty( $callback_data['filter'] ) ) {
			$this->_add_admin_notice( 'Callback Filter for this Custom Menu Item must be set' );

			$data['post_status'] = 'draft';
		}

		return $data;
	}

}	//end of class


//EOF
