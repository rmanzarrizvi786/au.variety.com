<?php

/**
 * Class PMC_Custom_Feed
 *
 * @author PMC, Amit Sannad
 * @since  2013-08-20
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Feed {

	use Singleton;

	const post_type_name   = "_pmc-custom-feed";
	const taxonomy_name    = "_pmc-custom-feed-option";
	const meta_custom_name = "_pmc_custom_feed_";
	const cache_key        = "pmc_custom_feed_cache";
	const rewrite_slug     = "custom-feed";

	const CURATED_POST_META_GROUP = '_pmc_curated_custom_feed_';
	const MAX_POST_COUNT          = 100;

	private $_custom_feed_template_list = array();

	/**
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		/*
		 * Actions
		 */
		add_action( 'wp', [ $this, 'wp' ] );
		add_action( 'wp', [ $this, 'set_cache_maxage_for_feeds' ] );
		add_action( 'init', array( $this, 'init' ) );

		add_action( 'pmc_custom_feed_start', array( $this, 'pmc_custom_feed_start' ), 10, 3 );
		add_action( 'save_post_' . self::post_type_name, array( $this, 'prevent_meta_deletion' ) );
		add_action( 'pmc_custom_feed_start', [ $this, 'action_pmc_custom_feed_start' ], 10, 3 );
		add_action( 'async_transition_post_status', [ $this, 'flush_edge_cache' ], 10, 3 );

		/*
		 * Filters
		 */
		add_filter( 'custom_metadata_manager_field_types_that_support_default_value', array( $this, 'custom_metadata_manager_field_types_that_support_default_value' ) );
		add_filter( 'single_template', array( $this, 'get_single_template_file' ) );
		add_filter( 'pmc_strip_shortcode', [ $this, 'maybe_override_caption_shortcode_strip_for_feed' ], 10, 3 );

		/*
		 * Conditional hooks
		 */
		if ( is_admin() ) {
			add_action( 'custom_metadata_manager_init_metadata', array( $this, 'metabox_manager' ) );
		}
	}

	/*
	 * Remove actions that are adding double link headers on custom feeds
	 * ref: https://wordpressvip.zendesk.com/hc/en-us/requests/113598
	 */
	public function wp() {

		if ( is_singular( self::post_type_name ) ) {
			remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
			remove_action( 'template_redirect', 'rest_output_link_header', 11 );
		}
	}

	/**
	* Hooks the wp action to insert some cache control
	* max-age headers.
	*
	* @param Object wp The WP object, passed by reference
	* @return void
	*/
	public function set_cache_maxage_for_feeds( $wp ) : void {
		if ( is_feed() ) {
			// Set the max age for feeds to 1 minute
			if ( ! is_user_logged_in() ) {
				header( 'Cache-Control: max-age=60' );
			}
		}
	}

	public function init() {
		$this->register_post_type();
		$this->add_rewrite_endpoint();
	}

	public function register_post_type() {
		register_post_type(
			self::post_type_name,
			[
				'labels'              => [
					'name'               => __( 'PMC Feeds', 'pmc-custom-feed' ),
					'singular_name'      => __( 'PMC Feed', 'pmc-custom-feed' ),
					'add_new'            => __( 'Add New', 'pmc-custom-feed' ),
					'add_new_item'       => __( 'Add New PMC Feed', 'pmc-custom-feed' ),
					'edit_item'          => __( 'Edit PMC Feeds', 'pmc-custom-feed' ),
					'new_item'           => __( 'New PMC Feed', 'pmc-custom-feed' ),
					'view_item'          => __( 'View PMC Feed', 'pmc-custom-feed' ),
					'search_items'       => __( 'Search PMC Feeds', 'pmc-custom-feed' ),
					'not_found'          => __( 'No PMC Feeds found.', 'pmc-custom-feed' ),
					'not_found_in_trash' => __( 'No PMC Feeds found in Trash.', 'pmc-custom-feed' ),
					'all_items'          => __( 'PMC Feeds', 'pmc-custom-feed' ),
				],
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'exclude_from_search' => true,
				'supports'            => [ 'title', 'revisions' ],
				'has_archive'         => false,
				'rewrite'             => [
					'slug'  => self::rewrite_slug,
					'feeds' => false,  // Our custom feed does not support native wp feed endpoint
				],
			]
		);

		// Add private taxonomy for Custom Feed only.
		// this is to facilitate certain on/off type of feed configuration, which is easily managed this way.
		register_taxonomy(
			self::taxonomy_name,
			self::post_type_name,
			[
				'hierarchical' => true,
				'public'       => false,
				'show_ui'      => true,
				'labels'       => [
					'name'          => __( 'Custom Feed Option', 'pmc-custom-feed' ),
					'singular_name' => __( 'Custom Feed Option', 'pmc-custom-feed' ),
					'search_items'  => __( 'Custom Feed Options', 'pmc-custom-feed' ),
					'all_items'     => __( 'All Custom Feed Options', 'pmc-custom-feed' ),
					'edit_item'     => __( 'Edit Custom Feed Option', 'pmc-custom-feed' ),
					'update_item'   => __( 'Update Custom Feed Option', 'pmc-custom-feed' ),
					'add_new_item'  => __( 'Add New Custom Feed Option', 'pmc-custom-feed' ),
					'new_item_name' => __( 'New Custom Feed Option Name', 'pmc-custom-feed' ),
				],
				// admins only
				'capabilities' => [
					'manage_terms' => 'manage_options', // admin+
					'edit_terms'   => 'manage_options', // admin+
					'delete_terms' => 'manage_options', // admin+
					'assign_terms' => 'edit_posts', // contributor+
				],
				'rewrite'      => false, // This custom taxonomy is specific to our custom feed, avoid any rewrite add by wp
			]
		);

	}

	/**
	 * Starts the feed rendering for a custom feed
	 *
	 * @version 2015-08-03
	 * @since 2015-08-03 - Mike Auteri - PPT-5181: Support URL allowed in Feeds
	 */
	public function pmc_custom_feed_start() {
		global $post;

		$disable_auto_clickable = false;

		add_filter( 'pmc_strip_disallowed_urls', array( $this, 'pmc_feeds_external_url_allowed' ), 10, 2 );

		$terms = get_the_terms( $post->ID, self::taxonomy_name );
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				switch( $term->slug ) {
					case 'external-urls-link-to-source-post':
						add_filter( 'pmc_strip_disallowed_urls', function( $args, $post_id ) {
							$args['external_url_link_source_boolean'] = true;
							return $args;
						}, 10, 2 );
						$disable_auto_clickable = true;
						break;
					// @TODO: To be remove
					case 'external-urls-whitelist-only':
					case 'external-urls-allowlist-only':
						add_filter( 'pmc_strip_disallowed_urls', function( $args, $post_id ) {
							// @TODO: To be remove
							$args['external_url_whitelist_boolean'] = true;
							$args['external_url_allowlist_boolean'] = true;
							return $args;
						}, 10, 2 );
						$disable_auto_clickable = true;
						break;
					case 'external-urls-strip-all':
						add_filter( 'pmc_strip_disallowed_urls', function( $args, $post_id ) {
							$args['external_strip_all_boolean'] = true;
							return $args;
						}, 10, 2 );
						$disable_auto_clickable = true;
						break;
				}
			}
		}

		// We do not want these filters to turn non clickable link into clickable link
		if ( $disable_auto_clickable ) {
			remove_filter( 'the_content', 'wpcom_make_content_clickable', 120 );
			remove_filter( 'the_excerpt', 'wpcom_make_content_clickable', 120 );
		}

		add_filter( 'the_content', [ PMC::class, 'strip_disallowed_urls' ] );
		add_filter( 'the_excerpt', [ PMC::class, 'strip_disallowed_urls' ] );
		add_filter( 'the_excerpt_rss', [ PMC::class, 'strip_disallowed_urls' ] );
		add_filter( 'the_content_rss', [ PMC::class, 'strip_disallowed_urls' ] );

		$image_size = self::get_instance()->get_feed_image_size();

		if ( !is_array( $image_size ) ) {
			return;
		}

		$image_size = array_filter( $image_size, function ( $val ) {
			return ( $val > 1 );
		} );

		if ( isset( $image_size['height'] ) && isset( $image_size['width'] ) ) {
			$image_name = PMC_Custom_Feed::get_instance()->get_feed_config( 'image_size' );
			add_image_size( 'pmc_custom_image' . $image_name, $image_size['width'], $image_size['height'], true );
		}

	}

	/**
	 * Allowed URLs present in the CheezCap option Feeds Pages: Allowed Hosts:
	 *
	 * @param array $sites
	 * @return array
	 */
	public function pmc_feeds_external_url_allowed( $args, $post_id ) {
		$hosts = cheezcap_get_option( 'pmc_feeds_external_url_allowlist', false );

		// @TODO: SADE-517 to be removed
		if ( empty( $hosts ) ) {
			$hosts = cheezcap_get_option( 'pmc_feeds_external_url_whitelist', false );
		}

		if ( ! empty( $hosts ) ) {
			return $args;
		}

		$hosts = empty( $hosts ) ? '' : str_replace( array( "\r\n", "\n", "\r" ), "\n", $hosts );
		$hosts = explode( "\n", $hosts );

		$args['feeds_external_url_allowlist'] = array_merge( (array) $hosts, (array) $args['feeds_external_url_allowlist'] );

		return $args;
	}

	public function custom_metadata_manager_field_types_that_support_default_value( $fields ) {
		if ( ! is_array( $fields ) ) {
			return $fields;
		}

		$fields[] = 'checkbox';

		return array_filter( array_unique( $fields ) );
	}

	public function add_rewrite_endpoint() {
		add_rewrite_endpoint( 'fpid', EP_PERMALINK );
	}

	/**
	 * Handles registering the metaboxes for config.
	 *
	 * @codeCoverageIgnore  Ignoring since it just registers metaboxes.
	 */
	public function metabox_manager() {

		//Check if custom metadata manager plugin is there or not.
		if ( !function_exists( 'x_add_metadata_field' ) ) {
			return;
		}

		$grp_args = array(
			'label' => 'PMC Feed Options',
		);

		$post_array = array( self::post_type_name );

		$grp_name = self::meta_custom_name . 'grp';

		/**
		 * This function is hooked on and executed a bit too early (damn)
		 * and $post object is not available, so checking for post ID in querystring.
		 * If post ID in querystring exists then its existing post else its new post.
		 *
		 * @ticket PPT-2556
		 * @since 2014-07-14 Amit Gupta
		 */
		if ( ! empty( $_GET['post'] ) && intval( $_GET['post'] ) > 0 ) {
			$tracking = get_post_meta( intval( $_GET['post'] ), self::meta_custom_name . 'tracking', true );
		}

		$tracking = ( empty( $tracking ) || ! in_array( $tracking, array( 'on', 'off' ) ) ) ? 'on' : strtolower( $tracking );

		/*
		 * Filter to enable selection of curated post modules.
		 *
		 * @param bool $feed_curation The default value is passed as false to keep it disabled by default.
		 */
		$feed_curation        = apply_filters( 'pmc_custom_feed_enable_curation', false );
		$curated_feed_mb_name = sprintf( '%sgrp', self::CURATED_POST_META_GROUP );

		if ( class_exists( 'PMC_Carousel' ) && true === $feed_curation ) {

			x_add_metadata_group(
				$curated_feed_mb_name,
				$post_array,
				[
					'label'       => 'PMC Feed Curated Post Options',
					'description' => '<strong>Note:</strong> If any of these curated post options is selected then Taxonomy, Feed Post Type and Feed Query String fields will have no effect.',
				]
			);

			x_add_metadata_field(
				sprintf( '%scuration_1', self::meta_custom_name ),
				$post_array,
				[
					'group'      => $curated_feed_mb_name,
					'field_type' => 'taxonomy_select',
					'taxonomy'   => \PMC_Carousel::modules_taxonomy_name,
					'label'      => 'Select 1st Curation Module',
				]
			);

			x_add_metadata_field(
				sprintf( '%scuration_2', self::meta_custom_name ),
				$post_array,
				[
					'group'      => $curated_feed_mb_name,
					'field_type' => 'taxonomy_select',
					'taxonomy'   => \PMC_Carousel::modules_taxonomy_name,
					'label'      => 'Select 2nd Curation Module',
				]
			);

			x_add_metadata_field(
				sprintf( '%scuration_3', self::meta_custom_name ),
				$post_array,
				[
					'group'      => $curated_feed_mb_name,
					'field_type' => 'taxonomy_select',
					'taxonomy'   => \PMC_Carousel::modules_taxonomy_name,
					'label'      => 'Select 3rd Curation Module',
				]
			);

		}    // end if PMC_Carousel

		//taxonomy query params
		x_add_metadata_group( $grp_name, $post_array, $grp_args );

		x_add_metadata_field( self::meta_custom_name . 'taxonomy', $post_array,
			array(
				'group'       => $grp_name,
				'label'       => 'Taxonomy',
				'description' => 'Usage: tax_query1 | tax_query2. | is AND relation. Where tax_query = taxonomy: comma separated term slug : operators( IN, NOT IN, AND ). Ex: category:film,tv:IN. Details => https://confluence.pmcdev.io/x/jYBlAQ',
			) );

		// Count of posts to show.
		x_add_metadata_field(
			self::meta_custom_name . 'count',
			$post_array,
			array(
				'group'             => $grp_name,
				'label'             => 'Post Count',
				'field_type'        => 'number',
				'default_value'     => 10,
				'display_column'    => 'Count',
				'sanitize_callback' => array(
					$this,
					'sanitize_post_count',
				),
				'description'       => 'Valid values are between 1 and ' . self::MAX_POST_COUNT,
			)
		);

		//which template to use for feed
		x_add_metadata_field( self::meta_custom_name . 'template', $post_array,
			array(
				 'group'          => $grp_name,
				 'label'          => 'Feed Template',
				 'field_type'     => 'select',
				 'values'         => $this->_get_feed_template_list(),
				 'display_column' => 'Feed Template'
			) );

		//custom image size for feed type
		x_add_metadata_field( self::meta_custom_name . 'image_size', $post_array,
			array(
				 'group'             => $grp_name,
				 'label'             => 'Feed Image Size',
				 'description'       => 'Usage is img_w x img_h eg: 100x50 ; or image name eg: thumbnail, river-image, etc',
				 'sanitize_callback' => array( $this, 'sanitize_image_size' ),
			) );

		//for feeds some time n campaigns we want to add query string in all anchor tags
		// add option for it
		x_add_metadata_field( self::meta_custom_name . 'query_string', $post_array,
			array(
				 'group'       => $grp_name,
				 'label'       => 'Feed Query String',
				 'description' => 'Usage => qs1=1&qs2=2'
			) );

		//Slug
		x_add_metadata_field( self::meta_custom_name . 'slug', $post_array,
			array(
				 'group' => $grp_name,
				 'label' => 'Feed Slug'
			) );

		//show related posts inside the feed posts or not
		x_add_metadata_field( self::meta_custom_name . 'related', $post_array,
			array(
				 'group'      => $grp_name,
				 'label'      => 'Related Article In Post',
				 'field_type' => 'checkbox',
			) );

		//include tracking beacons inside the feed posts or not
		x_add_metadata_field( self::meta_custom_name . 'tracking', $post_array,
			array(
				'group'      => $grp_name,
				'label'      => 'Include Tracking Beacon(s) in Post Content',
				'field_type' => 'checkbox',
				'values' => array( $tracking ),
				'default_value' => 'on',
			) );

		//filter for post type
		x_add_metadata_field( self::meta_custom_name . 'post_type', $post_array,
			array(
				 'group'             => $grp_name,
				 'label'             => 'Feed Post Type (use comas to separate multiple types)',
				 'sanitize_callback' => array( $this, 'sanitize_post_type' ),
			) );

		//static html at the start of posts
		x_add_metadata_field( self::meta_custom_name . 'prehtml', $post_array,
			array(
				 'group'      => $grp_name,
				 'label'      => 'Feed HTML before post content',
				 'field_type' => 'textarea',
			) );

		// prepend a text string before first paragraph.
		x_add_metadata_field( self::meta_custom_name . 'prepend_text_p1', $post_array,
			array(
				 'group'      => $grp_name,
				 'label'      => 'Prepend text before first paragraph',
				 'field_type' => 'text',
			) );

		//static html at the end of posts
		x_add_metadata_field( self::meta_custom_name . 'html', $post_array,
			array(
				 'group'      => $grp_name,
				 'label'      => 'Feed HTML after post content',
				 'field_type' => 'textarea',
			) );

		//Notes for the feeds
		x_add_metadata_field( self::meta_custom_name . 'notes', $post_array,
			array(
				 'group'          => $grp_name,
				 'label'          => 'Feed notes',
				 'field_type'     => 'textarea',
				 'display_column' => 'Notes'
			) );

		//token for security, if token is present then only show the feed
		x_add_metadata_field( self::meta_custom_name . 'token', $post_array,
			array(
				 'group' => $grp_name,
				 'label' => 'Feed Token'
			) );

		// Affiliate Code for amazon.
		x_add_metadata_field(
			self::meta_custom_name . 'affiliate_code',
			$post_array,
			[
				'group'       => $grp_name,
				'label'       => __( 'Overwrite Amazon Affiliate Code', 'pmc-custom-feed-v2' ),
				'description' => __( 'Overwrites the tag parameter value in Amazon links added to posts using [pmc-store-products] shortcode. For example, ?tag=spyrss-20 value will get overwritten by the value set here.', 'pmc-custom-feed-v2' ),
			]
		);

		// utm_name for the feeds
		x_add_metadata_field(
			self::meta_custom_name . 'utm_campaign',
			$post_array,
			[
				'group'       => $grp_name,
				'label'       => 'UTM Campaign',
				'field_type'  => 'text',
				'description' => 'Product, promo code, slogan (eg spring_sale).',
			]
		);

		// utm_source for the feeds
		x_add_metadata_field(
			self::meta_custom_name . 'utm_source',
			$post_array,
			[
				'group'       => $grp_name,
				'label'       => 'UTM Source',
				'field_type'  => 'text',
				'description' => 'The referrer (eg google, amazon, newsletter).',
			]
		);

		// utm_medium for the feeds
		x_add_metadata_field(
			self::meta_custom_name . 'utm_medium',
			$post_array,
			[
				'group'       => $grp_name,
				'label'       => 'UTM Medium',
				'field_type'  => 'text',
				'description' => 'Marketing medium (eg banner, email).',
			]
		);

	}

	/**
	 * Called on 'save_post' hook this function makes sure certain ON/OFF flags don't
	 * get deleted from meta when turned OFF because Custom Metadata Manager plugin
	 * is stupid and deletes meta if its an unchecked checkbox without providing
	 * any easy way to override the action.
	 *
	 * @return void
	 *
	 * @since 2014-07-16 Amit Gupta
	 * @ticket PPT-2556
	 */
	public function prevent_meta_deletion() {
		if ( empty( $_POST['_pmc_custom_feed_tracking'] ) ) {
			/*
			 * just set the value in $_POST super global,
			 * we don't care if its auto-save or not, let WordPress
			 * and Custom Metadata Manager plugin worry about all that
			 */
			$_POST['_pmc_custom_feed_tracking'] = 'off';
		}
	}

	/**
	 * Override function to use our custom template to render single posts(feed)
	 *
	 * @version 2015-08-03
	 * @since 2015-08-03 - Mike Auteri - PPT-5125: Update wp_query variables to prevent logic errors.
	 *
	 * @param string $single_template
	 * @return string
	 */
	public function get_single_template_file( $single_template ) {

		$queried_object = get_queried_object();

		if ( $queried_object->post_type == self::post_type_name ) {

			global $feed, $wp_query;
			/* Faking single as feed and therefore setting $feed and is_feed.
			 */

			$feed                         = $queried_object->post_name;
			$wp_query->query['feed']      = 'feed';
			$wp_query->query_vars['feed'] = 'feed';
			$wp_query->is_singular        = false;
			$wp_query->is_single          = false;
			$wp_query->is_feed            = true;
			$template                     = $this->get_feed_config( 'template' );
			$template                     = sanitize_file_name( $template );
			$single_template              = __DIR__ . "/templates/{$template}.php";
		}

		//Check to see if the feed requires token or not.
		//If token is required and is not present bail.
		$feed_token = $this->get_feed_config( "token" );

		if ( !empty( $feed_token ) && !PMC_Custom_Feed_Helper::is_current_feed_auth( $queried_object->post_name, $feed_token ) ) {
			//Its feed URI
			//It requires  a token
			//Token in URI doesn't exist or doesn't match
			//Show 403 forbidden error
			header( "HTTP/1.1 403 Forbidden" );
			exit;
		}

		return $single_template;

	}

	/**
	 * Get Feed configuration by key.
	 *
	 * @param string $key
	 *
	 * @return array|mixed
	 */
	public function get_feed_config( $key = "" ) {

		$queried_id = get_queried_object_id();

		if ( empty( $queried_id ) ) {
			return;
		}

		if ( !empty( $key ) ) {
			return apply_filters( 'pmc_custom_feed_config', get_post_meta( $queried_id, self::meta_custom_name . $key, true ), $key );
		}

		return apply_filters( 'pmc_custom_feed_config', $this->_get_all_feed_configs( $queried_id ), $key );

	}

	/**
	 * Return image dimension for the feed.
	 */
	public function get_feed_image_size() {

		$img = $this->get_feed_config( "image_size" );

		if ( !empty( $img ) ) {
			if (has_image_size( $img )) {
				return $img;
			} else {
				$img              = explode( "x", $img );
				$img_arr["width"] = $img[0];
				if ( isset( $img[1] ) ) {
					$img_arr["height"] = $img[1];
				}

				return $img_arr;
			}
		}
	}

	public function get_feed_image_src() {
		$queried_id = get_queried_object_id();

		$thumbnail_id = get_post_thumbnail_id( $queried_id );

		if ( empty( $thumbnail_id ) ) {
			return;
		}

		$url = wp_get_attachment_image_src( $thumbnail_id, 'full' );
		if ( ! empty( $url[0] ) ) {
			return $url[0];
		}

	}

	/**
	 * Add PMC Custom Feed Option terms
	 *
	 * @param array $terms An array of term slugs and term names,
	 *                     e.g. array( 'term-a' => 'Term A', 'term-b' => 'Term B', ... )
	 *
	 * @return null
	 */
	public function add_taxonomy_term_if_not_exist( $terms, $force = false ) {

		// Bail if there are no terms given, or if the current user isn't an admin
		if ( empty( $terms ) || ! is_array( $terms ) || ! current_user_can( 'manage_options') ) {
			return;
		}

		// Loop through each term..
		foreach ( $terms as $term_slug => $term_name ) {

			// Check if the term exists..
			if ( function_exists( 'wpcom_vip_term_exists' ) ) {
				$term_exists = wpcom_vip_term_exists( $term_slug, self::taxonomy_name );
			} else {
				$term_exists = term_exists( $term_slug, self::taxonomy_name );
			}

			// Create the term if it does not already exist
			if ( empty( $term_exists ) || ! is_array( $term_exists ) ) {
				wp_insert_term( $term_name, self::taxonomy_name, array( 'slug' => $term_slug ) );
			}
		}
	}

	/**
	 * Prepare config data for feed
	 */
	private function _get_all_feed_configs( $post_id = "" ) {

		if ( empty( $post_id ) ) {
			return;
		}

		$post_meta = get_post_meta( $post_id, "", false );

		$meta = array( 'feed_id' => $post_id );

		foreach ( $post_meta as $key => $value ) {

			if ( 0 === stripos( $key, self::meta_custom_name ) ) {
				$key        = str_replace( self::meta_custom_name, "", $key );
				$meta[$key] = $value[0];
			}
		}

		//ADD configuration based on terms too.
		$terms = get_the_terms( $post_id, self::taxonomy_name );

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$meta[$term->slug] = true;
			}
		}

		/**
		 * Consider tracking beacon off only if it is specifically turned OFF
		 * else consider it ON by default. This is so the existing feeds don't
		 * find themselves lost without tracking beacons.
		 *
		 * @since 2014-07-16 Amit Gupta
		 * @ticket PPT-2556
		 */
		$meta['tracking'] = ( ! empty( $meta['tracking'] ) && strtolower( $meta['tracking'] ) == 'off'  ) ? 'off' : 'on';

		return $meta;
	}

	/**
	 * Get list of all registered templates
	 * @return array
	 */
	private function _get_feed_template_list() {
		if ( !empty( $this->_custom_feed_template_list ) ) {
			return $this->_custom_feed_template_list;
		}

		if ( $handle = opendir( __DIR__ . '/templates' ) ) {
			while ( false !== ( $file = readdir( $handle ) ) ) {
				if ( preg_match( "/.php/", $file ) ) {
					//<select> option should have filename without .php
					$value                                    = str_replace( ".php", "", $file );
					$this->_custom_feed_template_list[$value] = $value;
				}
			}
			closedir( $handle );
		}

		// sort the result so it easier to select from the dropdown
		asort( $this->_custom_feed_template_list );

		return $this->_custom_feed_template_list;
	}

	public function sanitize_post_count( $field_slug, $field, $object_type, $object_id, $value ) {

		$value = intval( $value );

		if ( 0 > $value ) {
			$value = 1;
		} elseif ( self::MAX_POST_COUNT < $value ) {
			$value = self::MAX_POST_COUNT;
		}
		return $value;
	}

	public function sanitize_post_type( $field_slug, $field, $object_type, $object_id, $value ) {

		$post_types = PMC_Custom_Feed_Helper::validate_post_types( $value );
		return implode(',', $post_types );

	}

	public function sanitize_image_size( $field_slug, $field, $object_type, $object_id, $value ) {

		if ( has_image_size($value) ) {
			return $value;
		} else {
			$img_size = explode( "x", $value );

			if ( empty($img_size[0])  || empty($img_size[1])
				|| !is_numeric($img_size[0]) || !is_numeric($img_size[1])
				|| $img_size[0] < 1 || $img_size[1] < 1 ) {
				return;
			}
		}
		return $value;
	}

	public function is_feed() {

		$feed_post_id = get_queried_object_id();
		if ( !empty( $feed_post_id ) && self::post_type_name == get_post_type( $feed_post_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Don't strip caption shortcode from PMC custom feeds.
	 *
	 * @param string $content        empty string
	 * @param string $shortcode      shortcode name
	 * @param string $origin_content origin shortcode tag
	 *
	 * @return string returns shortcode tag if its 'caption' and option enables else returns empty string( $content )
	 */
	public function maybe_override_caption_shortcode_strip_for_feed( $content, $shortcode, $origin_content ) {

		$feed_options = $this->get_feed_config();

		if ( ! empty( $feed_options['override-caption-shortcode-removal'] ) && PMC_Custom_Feed::get_instance()->is_feed() && 'caption' === $shortcode ) {

			$content = $origin_content;

		}

		return $content;
	}

	/**
	 * @param string $feed_name
	 * @param array $feed_options
	 * @param string $template_filename
	 */
	public function action_pmc_custom_feed_start( $feed_name = '', $feed_options = [], $template_filename = '' ) {

		$feed_options = $this->get_feed_config();

		if ( ! empty( $feed_options['override-caption-shortcode-removal'] ) && PMC_Custom_Feed::get_instance()->is_feed() ) {

			add_filter( 'img_caption_shortcode', [ $this, 'img_caption_shortcode_override' ], 99, 3 );

		}
	}


	/**
	 * Called on 'img_caption_shortcode' filter, this function displays images
	 * inserted into posts in custom feed with image credit instead of caption
	 *
	 * @param string $empty
	 * @param array $attr Attributes of the [caption] shortcode
	 * @param string $content Content from [caption] shortcode
	 *
	 * @return string HTML markup to display image with credit
	 */
	public function img_caption_shortcode_override( $markup, $attr, $content ) {

		if ( PMC_Custom_Feed::get_instance()->is_feed() ) {

			//parse attributes with defaults
			$attr = shortcode_atts(
				[
					'id'      => '',
					'align'   => 'alignnone',
					'width'   => '',
					'caption' => '',
				],
				$attr
			);

			if ( ! empty( $attr['id'] ) ) {
				$image_id = intval( str_replace( 'attachment_', '', $attr['id'] ) );
			}

			if ( ! empty( $image_id ) ) {

				$image_caption = empty( $attr['caption'] ) ? get_post_field( 'post_excerpt', $image_id ) : $attr['caption'];

				$image_credit = get_post_meta( $image_id, '_image_credit', true );
				$image_credit_for_alt = ! empty( $image_credit ) ? 'Credit: ' . $image_credit : '';

				// set alt attr data depending on value of image_caption and image_credit
				if ( ! empty( $image_caption ) ) {
					$alt_value = ! empty( $image_credit_for_alt ) ? $image_caption . ' - ' . $image_credit_for_alt : $image_caption;
				} else {
					$alt_value = ! empty( $image_credit_for_alt ) ? $image_credit_for_alt : '';
				}

				$image_html = do_shortcode( $content );
				$image_html = preg_replace( '/alt="([^"]*)"/i', 'alt="' . wp_strip_all_tags( $alt_value ) . '"', $image_html );

				$markup = '<p>' . wp_kses_post( $image_html ) . '<span class="credits">' . esc_html( $image_credit ) . '</span></p>';

			}

		}

		return $markup;

	}

	/**
	 * @codeCoverageIgnore Not sure how to test this as this is VIP GO Only
	 *
	 * ref: https://wordpressvip.zendesk.com/hc/en-us/requests/116458
	 * Flush edge cache for custom feeds if any post gets published
	 *
	 * @param string  $new
	 * @param string  $old
	 * @param WP_Post $post
	 */
	public function flush_edge_cache( string $new, string $old, \WP_Post $post ) {

		if ( ! defined( 'VIP_GO_APP_ENVIRONMENT' ) || false === VIP_GO_APP_ENVIRONMENT ) {
			return;
		}

		if ( ! in_array( 'publish', [ $new, $old ], true ) ) {
			return;
		}

		if ( ! function_exists( '\wpcom_vip_purge_edge_cache_for_url' ) ) {
			return;
		}

		$args = [
			'public'             => true,
			'show_ui'            => true,
			'publicly_queryable' => true,
		];

		$post_types = (array) get_post_types( $args );

		unset( $post_types['attachment'] );

		if ( in_array( $post->post_type, (array) $post_types, true ) ) {

			$count = 250;

			$custom_feed_post = get_posts( //phpcs:ignore
				[
					'post_type'        => self::post_type_name,
					'post_status'      => 'publish',
					'numberposts'      => $count, //phpcs:ignore 250 is ok`
					'suppress_filters' => false,//phpcs:ignore
				]
			);

			foreach ( $custom_feed_post as $feed_post ) {
				\wpcom_vip_purge_edge_cache_for_url( get_permalink( $feed_post ) );
			}


		}

		return;

	}

}

//EOF
