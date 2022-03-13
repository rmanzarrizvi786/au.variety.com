<?php

/**
 * PMC_Not_For_Publication class
 *
 * @author Amit Gupta
 * @since 2013-09-12
 * @version 2013-09-16
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Not_For_Publication {

	use Singleton;

	/**
	 * Custom post type name
	 */
	const post_type = 'pmc-nfp';

	/**
	 * Plugin ID used to prefix unique identifiers
	 */
	const plugin_id = 'pmc-nfp';

	/**
	 * prefix used for custom fields
	 */
	const field_prefix = 'pmc_nfp_';

	/**
	 * Post meta key used to store post ID to which NFP Article is copied
	 */
	const post_meta_key = 'pmc_nfp_post_copy';

	/**
	 * Array in which admin notices are stored
	 */
	protected $_admin_notices = array();

	/**
	 * Array in which supported taxonomies are stored
	 */
	protected $_taxonomies = array();


	/**
	 * Initialization routine
	 */
	protected function __construct() {
		$this->_setup_taxonomies();		//setup allowed taxonomies
		$this->_register_post_type();	//register our custom post type

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_stuff' ) );
		add_filter( 'pmc_inappropriate_for_syndication_exclude_types', array( $this, 'exclude_post_type_from_syndication_flag' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'prevent_post_publishing' ), 999, 2 );
		add_action( 'post_submitbox_misc_actions', array( $this, 'show_copy_post_button' ) );

		//setup hook for nfp article copy
		$this->_hook_unhook_copy_to_post( true );

		//setup display of error messages if any
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
		//clear out error messages which have been displayed already
		add_action( 'admin_footer', array( $this, 'clear_admin_notices' ) );
		//save error messages for current page
		add_action( 'shutdown', array( $this, 'save_admin_notices' ) );
	}

	/**
	 * This function hooks/unhooks the nfp article to post copy handler
	 */
	protected function _hook_unhook_copy_to_post( $hook = true ) {

		if ( $hook === false ) {
			//unhook copy to post function
			remove_action( 'save_post', array( $this, 'copy_to_post' ) );
		} else {
			//hook in copy to post function
			add_action( 'save_post', array( $this, 'copy_to_post' ) );
		}
	}

	/**
	 * This function returns TRUE if current admin page is the add/edit screen
	 * for NFP Articles else it returns FALSE
	 */
	protected function _is_nfp_post_page() {
		$is_it = false;		//assume its not our page

		if ( ! is_admin() ) {
			return $is_it;
		}

		if (
			! empty( $GLOBALS['pagenow'] ) && ( $GLOBALS['pagenow'] == 'post.php' || $GLOBALS['pagenow'] == 'post-new.php' )
			&& ! empty( $GLOBALS['post']->post_type ) && $GLOBALS['post']->post_type == self::post_type
		) {
			$is_it = true;
		}

		return $is_it;
	}

	/**
	 * This function fetches all registered taxonomies, weeds out the ones
	 * not needed for posts and stores them in $_taxonomies class var
	 */
	protected function _setup_taxonomies() {
		$taxonomies = get_taxonomies();

		//remove unwanted taxonomies
		unset( $taxonomies['nav_menu'], $taxonomies['link_category'], $taxonomies['post_format'], $taxonomies['pmc_carousel_modules'] );
		unset( $taxonomies['vcategory'], $taxonomies['post_status'], $taxonomies['ef_editorial_meta'], $taxonomies['following_users'] );
		unset( $taxonomies['ef_usergroup'], $taxonomies['hollywood_exec_company'], $taxonomies['author'] );

		//pass through filter to allow a site an override
		$this->_taxonomies = apply_filters( 'pmc_not_for_publication_taxonomies', $taxonomies );

		if ( empty( $this->_taxonomies ) ) {
			$this->_taxonomies = array();
		}

		if ( ! is_array( $this->_taxonomies ) ) {
			$this->_taxonomies = (array) $this->_taxonomies;
		}

		unset( $taxonomies );
	}

	/**
	 * This function registers the custom post type
	 */
	protected function _register_post_type() {
		register_post_type( self::post_type, array(
			'labels' => array(
				'name' => 'NFP Articles',
				'singular_name' => 'NFP Article',
			),
			'description' => 'A custom post type to store articles in advance without having them published by accident',
			'public' => false,					//private post type
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true,					//enable wp-admin UI
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => false,		//no need to put in admin bar
			'menu_position' => 5,				//show below Posts menu in wp-admin
			'hierarchical' => false,
			'supports' => array(
				'title', 'editor', 'excerpt','author',
				'custom-fields', 'revisions',
				'thumbnail'
			),
			'taxonomies' => $this->_taxonomies,
			'rewrite' => false,					//url rewrites not needed for this
			'can_export' => true,				//allow post export
		) );
	}

	/**
	 * This function accepts a message which is to be displayed after
	 * current post/page is saved.
	 */
	protected function _add_admin_notice( $message, $type = 'error' ) {
		if( empty( $message ) || ! is_string( $message ) ) {
			return;
		}

		$key = md5( $message );

		if( array_key_exists( $key, $this->_admin_notices ) ) {
			return;
		}

		$type = ( $type !== 'success' ) ? 'error' : 'updated';

		$this->_admin_notices[$key] = array(
			'type' => $type,
			'message' => $message
		);

		return true;
	}

	/**
	 * This function is called on shutdown and it saves $this->_admin_notices in
	 * WP Cache if its not empty and if current screen is post/page add/edit screen
	 */
	public function save_admin_notices() {
		global $post;

		if( empty( $this->_admin_notices ) || empty( $post ) || ! isset( $post->ID ) || intval( $post->ID ) < 1 ) {
			return;
		}

		wp_cache_set( $post->ID, $this->_admin_notices, self::post_type, 300 );
	}

	/**
	 * This function is called on admin_footer and it clears out notices saved in
	 * WP Cache if current screen is post/page add/edit screen
	 */
	public function clear_admin_notices() {
		global $post;

		if( empty( $post ) || ! is_object( $post ) || ! isset( $post->ID ) || intval( $post->ID ) < 1 ) {
			return;
		}

		wp_cache_delete( $post->ID, self::post_type );
	}

	/**
	 * This function is called on admin_notices and it displays all notices saved in
	 * WP Cache if current screen is post/page add/edit screen
	 */
	public function show_admin_notices() {
		global $post;

		if( empty( $post ) || ! is_object( $post ) || ! isset( $post->ID ) || intval( $post->ID ) < 1 ) {
			return;
		}

		$notices = wp_cache_get( $post->ID, self::post_type );

		if( empty( $notices ) ) {
			return;
		}

		foreach( $notices as $notice ) {
			//load the view to display admin notice
			$type = $notice['type'];
			$message = $notice['message'];
			require __DIR__ . '/views/admin-notice-view.php';
		}
	}

	/**
	 * This function enqueues our JS etc to HTML Head
	 */
	public function enqueue_stuff() {
		if ( ! $this->_is_nfp_post_page() ) {
			return;
		}

		wp_enqueue_script( self::plugin_id . '-admin', plugins_url( 'js/pmc-nfp-admin.js', __FILE__ ), array( 'jquery' ) );
		wp_localize_script( self::plugin_id . '-admin', 'pmc_nfp', array(
			'field_prefix' => self::field_prefix,
		) );
	}

	/**
	 * This function adds NFP post type to post syndication exclusion list
	 */
	public function exclude_post_type_from_syndication_flag( $post_types ) {
		if ( ! is_array( $post_types ) ) {
			return $post_types;
		}

		if ( ! in_array( self::post_type, $post_types ) ) {
			//add our post type for exclusion
			$post_types[] = self::post_type;
		}

		return $post_types;
	}

	/**
	 * This function prevents any NFP article from getting published and keeps them
	 * always as drafts
	 */
	public function prevent_post_publishing( $data, $postarr ) {
		if ( ! empty( $data['post_type'] ) && $data['post_type'] == self::post_type ) {
			$statuses_to_revert = array(
				'publish', 'future', 'pending', 'private',
			);

			if ( ! empty( $data['post_status'] ) && in_array( $data['post_status'], $statuses_to_revert ) ) {
				//NFP article can't be anything but a draft
				$data['post_status'] = 'draft';
			}
		}

		return $data;
	}

	/**
	 * This function shows the Copy to Post button on a NFP article if it hasn't
	 * been copied to a post already
	 */
	public function show_copy_post_button() {
		if ( ! $this->_is_nfp_post_page() ) {
			//not our post page, bail out
			return;
		}

		$copied_to_post_id = get_post_meta( $GLOBALS['post']->ID, self::post_meta_key, true );

		if ( ! empty( $copied_to_post_id ) ) {
			//we've already copied this to a post so no need to show copy button now, bail out
			return;
		}

		$div_id = self::plugin_id . '-copy-post';
		$input_hidden_name = self::field_prefix . 'do_copy';
		$input_submit_name = self::field_prefix . 'copy_post';
		//load the view with HTML to show the button
		require ( __DIR__ . '/views/copy-to-post-button-view.php' );
	}

	/**
	 * This function accepts Post IDs of the new post and the old post and it copies
	 * all meta from old post to new post
	 */
	public function copy_post_meta( $new_post_id, $old_post_id, $exclude_keys = false ) {
		$new_post_id = intval( $new_post_id );
		$old_post_id = intval( $old_post_id );

		if ( empty( $new_post_id ) || empty( $old_post_id ) ) {
			//bail out
			return;
		}

		$post_custom = get_post_custom( $old_post_id );

		if ( empty( $post_custom ) ) {
			//no meta to copy over, bail out
			return;
		}

		foreach ( $post_custom as $key => $values ) {

			if ( !empty( $exclude_keys ) && in_array(  $key, $exclude_keys ) ) {
				continue;
			}

			$values_count = count( $values );

			//copy over all values for this meta key
			for ( $i = 0; $i < $values_count; $i++ ) {
				$value = maybe_unserialize( $values[ $i ] );
				add_post_meta( $new_post_id, $key, $value );
				unset( $value );
			}

			unset( $values_count );
		}

		unset( $post_custom );
	}

	/**
	 * This function handles the Copy to Post functionality for NFP Articles
	 * and copies the current NFP article to a new post along with all categories,
	 * post tags and attachments
	 */
	public function copy_to_post( $post_id ) {
		if ( wp_is_post_revision( $post_id ) !== false ) {
			//post is a revision, bail out
			return;
		}

		if ( empty( $_POST[ self::field_prefix . 'do_copy' ] ) || $_POST[ self::field_prefix . 'do_copy' ] !== 'yes' || get_post_type( $post_id ) !== self::post_type ) {
			//not our custom post, bail out
			return;
		}

		$copied_to_post_id = get_post_meta( $post_id, self::post_meta_key, true );

		if ( ! empty( $copied_to_post_id ) ) {
			//we've already copied this to a post so no need to copy again, bail out
			return;
		}

		unset( $copied_to_post_id );

		//unhook this function - to prevent any loops (though unlikely)
		$this->_hook_unhook_copy_to_post( false );

		$nfp_post = get_post( $post_id, ARRAY_A );
		$new_post_data = $nfp_post;

		//remove unnecessary stuff
		unset( $new_post_data['ID'], $new_post_data['comment_status'], $new_post_data['ping_status'], $new_post_data['post_name'], $new_post_data['post_content_filtered'] );
		unset( $new_post_data['post_parent'], $new_post_data['guid'], $new_post_data['menu_order'], $new_post_data['post_mime_type'], $new_post_data['comment_count'] );
		unset( $new_post_data['filter'], $new_post_data['format_content'], $new_post_data['ancestors'] );

		//set straight few things
		$new_post_data['post_status'] = 'draft';
		$new_post_data['post_type'] = 'post';

		//create the new post with NFP article data
		$new_post_id = wp_insert_post( $new_post_data );

		if ( ! is_numeric( $new_post_id ) || $new_post_id < 1 ) {
			//bail out, something went wrong
			return;
		}

		$new_post_id = intval( $new_post_id );

		// copy post meta excluding: _edit_*
		$this->copy_post_meta( $new_post_id, $post_id, array( '_edit_last', '_edit_lock' ) );

		//loop through all supported taxonomies and copy them over
		foreach ( $this->_taxonomies as $taxonomy ) {
			//grab all terms for the taxonomy and copy them over
			$taxonomy_terms = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );

			if ( ! empty( $taxonomy_terms ) ) {
				$taxonomy_terms = array_map( 'intval', $taxonomy_terms );
				wp_set_post_terms( $new_post_id, $taxonomy_terms, $taxonomy );
			}

			unset( $taxonomy_terms );
		}


		/*
		 * grab all attachments and copy them over
		 *
		 * this is like a normal post, it is unlikely it will have any more
		 * than 10-12 attachments at most, but keeping this flexible just in
		 * case a post gets insane and ends up with many attachments
		 */
		$batch_size = 50;
		$children_migrated = 0;
		$total_children = $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare( "SELECT COUNT( ID ) FROM " . $GLOBALS['wpdb']->posts . " WHERE post_type='attachment' AND post_parent='%d'", $post_id ) );
		$total_children = intval( $total_children );

		if ( $total_children > 0 ) {
			while ( $children_migrated < $total_children ) {
				$post_attachments = get_children( array(
					'post_parent' => $post_id,
					'post_type' => 'attachment',
					'numberposts' => $batch_size,
					'offset' => $children_migrated,
				), ARRAY_A );

				if ( ! empty( $post_attachments ) ) {
					foreach ( $post_attachments as $attachment ) {
						$new_attachment = $attachment;

						//remove unnecessary stuff
						unset( $new_attachment['ID'], $new_attachment['comment_status'], $new_attachment['ping_status'], $new_attachment['post_name'] );
						unset( $new_attachment['post_content_filtered'], $new_attachment['comment_count'], $new_attachment['filter'], $new_attachment['format_content'] );

						//add necessary stuff
						$new_attachment['post_parent'] = $new_post_id;
						$new_attachment_id = wp_insert_post( $new_attachment );

						if ( is_numeric( $new_attachment_id ) && intval( $new_attachment_id ) > 1 ) {
							//copy over all post meta (if any)
							$this->copy_post_meta( $new_attachment_id, $attachment['ID'] );
						}

						unset( $new_attachment_id, $new_attachment );
					}

					$children_migrated += $batch_size;

					/*
					 * sleep only if its 2nd batch or later
					 * we will bail out after first batch in almost all cases
					 * so no need to sleep and increasing the time in that case
					 */
					if ( $children_migrated > $batch_size ) {
						sleep( 1 );		//no need to hammer the db
					}
				}

				unset( $post_attachments );
			}
		}

		unset( $total_children, $children_migrated, $batch_size );

		//get URL to edit screen of the newly created post
		$new_post_edit_url = admin_url( 'post.php?post=' . $new_post_id . '&action=edit' );

		//save in NFP article meta
		add_post_meta( $post_id, self::post_meta_key, $new_post_id, true );

		//setup message to display
		$this->_add_admin_notice( 'This article has been copied to a post. <a href="' . esc_url( $new_post_edit_url ) . '">Click here</a> to edit or publish it.' , 'success' );

		unset( $post_tags, $post_categories, $nfp_post );
		unset( $new_post_data, $new_post_id, $new_post_edit_url );

		//hook this function back in
		$this->_hook_unhook_copy_to_post( true );
	}

//end of class
}


//EOF
