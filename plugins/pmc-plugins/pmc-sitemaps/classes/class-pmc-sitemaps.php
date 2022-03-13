<?php

use PMC\Global_Functions\Traits\Singleton;

/**
 *
 */
class PMC_Sitemaps {

	use Singleton;

	const POST_TYPE = 'pmc_sitemap';

	/**
	 * Content of the sitemap to output.
	 *
	 * @var string
	 */
	protected $_sitemap = '';

	/**
	 * Standardized string based on the value of $query->get( 'pmc_sitemap' )
	 *
	 * @var string
	 */
	protected $_sitemap_type = '';

	/**
	 * Value of $query->get( 'pmc_sitemap' )
	 *
	 * @var string
	 */
	protected $_sitemap_name = '';

	/**
	 * Value of $query->get( 'pmc_sitemap_n' )
	 *
	 * @var string
	 */
	protected $_sitemap_n = '';

	/**
	 * Flag whether the homepage has been added to a sitemap
	 *
	 * @var bool
	 */
	protected $_has_output_home = false;

	/**
	 * Store whether an archive index has been added
	 *
	 * @var array
	 */
	protected $_sitemap_archives_added = array();

	/**
	 * default values for sitemap caches
	 *
	 * @var array
	 */
	protected $a__post_defaults = array(
		'post_title' => '',
		'post_content' => '',
		'post_date' => '',
		'post_date_gmt' => '',
		'post_modified' => '',
		'post_modified_gmt' => '',
		'comment_status' => 'closed',
		'post_author' => 0,
		'ping_status' => 'closed',
		'post_parent' => 0,
		'post_status' => 'publish',
		'post_type' => 'pmc_sitemap',
		'guid' => '',
	);

	/**
	 * URI of sitemaps which need to be rebuilt
	 *
	 * @var array
	 */
	protected $a__rebuild_uri = array();

	/**
	 * options for internal use
	 *
	 * @var array
	 */
	protected $a__internal_opt = array(
		'first_build_timeout' => 1200,
		'rebuild_timeout' => 180,
	);

	/**
	 * image types so the sitemap can determine what attachments are valid images
	 *
	 * @var array
	 */
	public $a__img_types = array(
		'image/jpeg',
		'image/jpg',
		'image/png',
		'image/gif',
		'image/tiff',
	);

	/**
	 * taxonomy whitelist
	 *
	 * @var array
	 */
	public $a__valid_taxonomies = array(
		'category',
		'editorial',
	);

	/**
	 * post_type whitelist
	 *
	 * @var array
	 */
	public $a__valid_post_types = array(
		'attachment',
		'page',
		'post',
	);

	// hold pending post meta to save
	public $post_meta_to_save = [];

	/**
	 * Time in minutes before a index rebuild can be triggered again
	 */
	const rebuild_frequency       = 10; // 10 minutes
	const between_build_frequency = 5; // 5 minutes time in between build to prevent consecutive rebuild due to rebuild flag / cron job stacking

	protected function __construct() {

		add_action( 'init', array( $this, 'init' ) ,100 );

		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_filter( 'redirect_canonical', array( $this, 'prevent_trailing_slash' ) );
		add_action( 'pre_get_posts', array( $this, 'sitemap_request' ) );
		add_action( 'template_redirect', array( $this, 'render_sitemap' ), 99 );

		add_action( 'save_post', array( $this, 'update_post_last_modified' ) );
		add_action( 'edit_term', array( $this, 'update_term_last_modified' ), 10, 3 );
		add_action( 'create_term', array( $this, 'update_term_last_modified' ), 10, 3 );

		add_filter( 'cron_schedules', array( $this, 'add_cron_schedules' ) );
		//add action for cron event
		add_action( 'pmc_sitemap_update_caches', array( $this, 'update_sitemaps' ) );
		//schedule cron event
		if ( ! wp_next_scheduled('pmc_sitemap_update_caches') ) {
			wp_schedule_event( time(), 'pmc_five_minutes', 'pmc_sitemap_update_caches' );
		}

		// Add Meta Tags plugin 'SEO' meta box on posts/pages
		// Checkbox to 'Prevent search engines from indexing this page'
		add_filter( 'admin_footer', array( $this, 'generate_exclude_post_checkbox' ), 10, 0 );
		//The 'pmc_robots_txt' hook occurs in PMC SEO Tweaks
		add_filter( 'pmc_robots_txt', array( $this, 'exclude_post_from_robots_text' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_exclude_post_checkbox' ), 10, 3 );
		add_action( 'wp_head', array( $this, 'generate_noindex_follow_meta_tag' ), 10, 0 );

		add_filter( 'pmc_sitemaps_loc', array( $this, 'hotfix_pmc_sitemaps_loc' ) );
		add_filter( 'jetpack_sitemap_news_sitemap_item', array( $this, 'filter_jetpack_news_sitemap_item' ), 10, 2 );
		add_filter( 'jetpack_sitemap_news_skip_post', array( $this, 'filter_jetpack_sitemap_news_skip_post' ), 10, 2 );
		add_filter( 'pmc_sitemap_exclude_post', array( $this, 'sitemap_exclude_post' ), 10, 2 );
		add_filter( 'wpcom_sitemap_news_sitemap_item', array( $this, 'update_news_sitemap_titles' ), 10, 2 );
	}

	public function init() {
		// TODO: Move add_rewrite_rule stuff to a generate_rewrite_rules() method
		add_rewrite_rule( 'sitemap_index\.xml$', 'index.php?pmc_sitemap=index', 'top' );
		add_rewrite_rule( '([^/]+?)-sitemap([0-9]+)?\.xml$', 'index.php?pmc_sitemap=$matches[1]&pmc_sitemap_n=$matches[2]', 'top' );

		$args = array(
			'label'              => __( 'PMC Sitemaps', 'pmc-sitemaps' ),
			'public'             => false,
			'publicly_queryable' => true,
			'rewrite'            => false,
		);

		if ( is_admin() && is_user_logged_in() && current_user_can( 'export' ) ) {

			if ( function_exists( 'pmc_current_user_is_member_of' ) && pmc_current_user_is_member_of( 'pmc-dev' ) ) {

				$args['show_ui'] = true;

			}

		}

		register_post_type( self::POST_TYPE, $args );

	}

	public function add_query_vars( $public_query_vars ) {
		$public_query_vars[] = 'pmc_sitemap';
		$public_query_vars[] = 'pmc_sitemap_n';

		return $public_query_vars;
	}

	/**
	 * Cancel the canonical redirect (thereby preventing trailing slash)
	 */
	public function prevent_trailing_slash( $redirect_url ) {
		if ( ! empty( $this->_sitemap_name ) ) {
			return false;
		}

		return $redirect_url;
	}

	/**
	 * Check for sitemap requests
	 */
	public function sitemap_request( $query ) {
		if ( empty( $query->get( 'pmc_sitemap' ) ) ) {
			// Not a sitemap request, bail
			return;
		}

		$this->_sitemap_name = $query->get( 'pmc_sitemap' );

		//Private post type bail
		if ( 0 === stripos( $this->_sitemap_name, "_" ) ) {
			$query->set( 'is_404', true );

			return;
		}

		// Set & sanitize properties
		$this->_sitemap_name = sanitize_title_with_dashes( $this->_sitemap_name );
		$this->_sitemap_type = $this->_get_sitemap_type( $this->_sitemap_name );
		$this->_sitemap_n    = absint( $query->get( 'pmc_sitemap_n' ) );

		// Force 404 for post type indexes with date ranges that should not include date ranges.
		$ignore_date_range = (bool) ! apply_filters(
			'pmc_sitemaps_' . sanitize_key( $this->_sitemap_name ) . '_include_date_range',
			true
		);

		if (
			$ignore_date_range
			&& 'post_type' === $this->_sitemap_type
			&& ! empty( $this->_sitemap_name )
			&& ! empty( $this->_sitemap_n )
		) {
			$this->_sitemap_n    = null;
			$this->_sitemap_name = null;
			$this->_sitemap_type = null;
		}

		// Prevent titles like "Sitemap - Taxonomy - Post Tag 0"
		if ( 0 === $this->_sitemap_n ) {
			$this->_sitemap_n = null;
		}

		// Hack to normalize legacy rewrite rules
		// Previously sitemap index was "1", now it's "index"
		if ( 1 == $this->_sitemap_name ) {
			$this->_sitemap_name = 'index';
			$query->set( 'pmc_sitemap', '' );
		}
		$query->set( 'post_type', 'pmc_sitemap' );
		$query->set( 'pmc_sitemap', $this->get_sitemap_name( 'sanitize' ) );
		$query->set( 'name', '' );

		$query->set( 'posts_per_page', 1 );

		// Remove this filter to prevent weirdness when doing sub-queries to generate the sitemaps
		remove_action( 'pre_get_posts', array( $this, 'sitemap_request' ) );
	}

	/**
	 * Renders sitemap
	 *
	 * @since 2015-07-22 - Javier Martinez - PPT-5042 - Return empty node instead of a 404 error.
	 */
	public function render_sitemap() {

		$post_id = 0;

		if ( ! $this->_sitemap_name || ! get_query_var( 'pmc_sitemap' ) ) {
			// Not a sitemap request, bail
			return false;
		} elseif ( ! $this->_sitemap_type ) {
			// Not a defined sitemap type, return 404
			$GLOBALS['wp_query']->is_404 = true;
			return false;
		}

		// If $GLOBALS['post'] then we have a valid sitemap thanks to $this->sitemap_request(), otherwise we need to generate a new one and the $success depends on the outcome of that attempted generation.
		if ( !empty( $GLOBALS['post'] ) ) {

			if ( self::POST_TYPE !== $GLOBALS['post']->post_type ) {
				return false;
			}

			$post = $GLOBALS['post'];
			$pre_post_content = $post->post_content;

			// enabled on demand rebuild only if configured
			if ( defined( 'PMC_SITEMAP_REBUILD_ON_DEMAND' ) && PMC_SITEMAP_REBUILD_ON_DEMAND ) {
				$need_rebuild = intval( get_post_meta( $post->ID, 'pmc_sitemaps_rebuild' ) );
				if ( $need_rebuild || $this->is_debug() ) {
					$current_time = strtotime( current_time( 'mysql', 1 ) );
					$post_time = strtotime( $post->post_modified_gmt );
					$minute_old = ($current_time - $post_time) / 60;

					// @since 2015-07-22 - Javier Martinez - PPT-5133 - increase post age to 10 minutes
					// only rebuild if older than 10 minutes, contents might be update frequently and reset the rebuild flag

					if ( $minute_old > PMC_Sitemaps::rebuild_frequency || $this->is_debug() ) {
						$sitemap_content = $this->_generate_sitemap( 'update',$post->ID );

						$post_id = $post->ID;

						// since the site map is rebuild, we should trigger the index for rebuild as well
						if ( $this->_sitemap_type != 'index' ) {
							$this->_trigger_index_rebuild();
						}
					}
				}
			}

			if ( empty($sitemap_content) ) {
				// rebuild failed, return stalled contents
				$sitemap_content = $pre_post_content;
			}

		} else {
			$sitemap_content = $this->_generate_sitemap();
		}

		// If generate sitemap failed, it will set _invalid_sitemap
		// @since 2015-07-22 - Javier Martinez - PPT-5042 - Return empty node instead of a 404 error.
		if ( ! $sitemap_content ) {
			$sitemap_content = $this->_generate_sitemap_node( [] );
		}

		if ( true !== apply_filters( 'pmc_canonical_force_http', false ) ) {
			if ( 0 < strpos( $sitemap_content, '}}http://' ) ) {
				// We need to search and replace where need and avoid changing the xml namespace reference,
				// eg. <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ...
				$sitemap_content = str_replace( '}}http://', '}}https://', $sitemap_content );
				// Need to trigger rebuild
				update_post_meta( $post_id, 'pmc_sitemaps_rebuild', 0 );
			}
		}

		// Replace {{ and }} tokens with < >
		$sitemap_content = str_replace( '{{', '<', $sitemap_content );
		$sitemap_content = str_replace( '}}', '>', $sitemap_content );

		header( 'X-Robots-Tag: noindex, follow', true );
		header( 'Content-Type: text/xml' );
		echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>';

		echo $sitemap_content; // WPCS: XSS ok.

		if ( defined( 'IS_UNIT_TEST' ) && true === IS_UNIT_TEST ) {
			wp_die();
		}

		die();
	}

	/**
	 * Takes the rewrite match and makes sure it's a valid sitemap type, returns a standardised string representation
	 *
	 */
	protected function _get_sitemap_type( $sitemap_param_value ) {
		if ( empty( $sitemap_param_value ) ) {
			return false;
		}

		if ( 1 == $sitemap_param_value || 'index' === $sitemap_param_value ) {
			return 'index';
		} else if ( 'archive' === $sitemap_param_value ) {
			return 'archive';
		} else if ( post_type_exists( $sitemap_param_value ) ) {
			return 'post_type';
		} else if ( taxonomy_exists( $sitemap_param_value ) ) {
			// need check for valid value to allow site map generation override
			$valid_taxonomies = apply_filters( 'pmc_sitemaps_taxonomy_whitelist', $this->a__valid_taxonomies );
			if ( in_array( $sitemap_param_value, $valid_taxonomies ) ) {
				return 'taxonomy';
			}
		}

		// Check for valid custom sitemap type.
		$custom_sitemaps = $this->_get_registered_custom_sitemaps();

		if ( ! empty( $custom_sitemaps ) && array_key_exists( $sitemap_param_value, $custom_sitemaps ) ) {
			return 'custom_sitemap';
		}

		return false;
	}

	/**
	 * Expose this function to allow WP CLI call
	 * return sitemap uri, strip out any querystrings etc
	 */
	public function get_sitemap_name( $filter = 'ucwords' ) {
		// Piece together a human-readable title for the sitemap
		$sitemap_title = 'Sitemap - ' . $this->_sitemap_type . ' - ' . $this->_sitemap_name . ' ' . $this->_sitemap_n;
		$sitemap_title = trim( str_replace( '_', ' ', $sitemap_title ) );

		switch ( $filter ) {
			case 'sanitize':
				$sitemap_title = sanitize_title_with_dashes( $sitemap_title, '', 'save' );
				break;

			case 'ucwords':
			default:
				$sitemap_title = ucwords( $sitemap_title );
				break;
		}

		return $sitemap_title;
	}

	/**
	 * Much different from WordPress's _get_last_post_time() function
	 * This isn't meant to be a public API, just meant to encapsulate some specific logic for getting the last modified date & handle caching
	 * Date is stored as a timestamp so the return formatting can easily change
	 */
	protected function _get_last_modified_time( $type = 'post', $type_value = 'post', $args = array() ) {
		global $wpdb;

		$hash_key = $type_value;
		if ( 'post' == $type ) {
			if ( isset($args['range_start']) ) {
				$tmp_date = strtotime($args['range_start']);
			} else {
				$tmp_date = time();
			}
			$hash_key .= date('Y', $tmp_date) . date('m', $tmp_date);
		}
		$key = 'pmc_lastmod_' . md5( $hash_key );

		$date = wp_cache_get( $key, 'timeinfo' );
		if ( ! $date ) {
			if ( 'post' === $type ) {
				// 'attachment' post types don't need the whole "post_status='inherit'" bit, but this keeps us from having to write two different queries.	Small gain for the amount of duplicate code in this class.
				$post_status = ('attachment' === $type_value) ? 'inherit' : 'publish';
				if ( isset($args['range_start']) && isset($args['range_end']) ) {
					$date = $wpdb->get_var( $wpdb->prepare( "SELECT post_modified_gmt FROM {$wpdb->posts} WHERE post_status = %s AND post_type = %s AND post_date_gmt >= %s AND post_date_gmt < %s ORDER BY post_modified_gmt DESC LIMIT 1", $post_status, $type_value, $args['range_start'], $args['range_end'] ) );
				} else {
					$date = $wpdb->get_var( $wpdb->prepare( "SELECT post_modified_gmt FROM {$wpdb->posts} WHERE post_status = %s AND post_type = %s ORDER BY post_modified_gmt DESC LIMIT 1", $post_status, $type_value ) );
				}

			} elseif ( 'term' === $type ) {
				/*
				Calling get_posts() doesn't work, it's too resource intensive
				$latest_post = get_posts( array(
					'posts_per_page' => 1,
					'orderby' => 'modified',
					'order' => 'DESC',
					'tax_query' => array(
						array(
							'taxonomy' => $this->_sitemap_name,
							'terms' => $term->term_id,
							'field' => 'id',
							'include_children' => false,
						),
					),
				) );

				if ( isset($latest_post[0]->post_modified_gmt) ) {
					$node['lastmod'] = $latest_post[0]->post_modified_gmt;
				}
				*/
				$date = $wpdb->get_var( $wpdb->prepare( "SELECT p.post_modified_gmt FROM {$wpdb->term_relationships} AS tr JOIN {$wpdb->posts} AS p ON ( p.ID = tr.object_id ) WHERE tr.term_taxonomy_id = %d ORDER BY p.post_modified_gmt DESC LIMIT 1", intval( $type_value ) ) );

			}

			// Convert date to timestamp
			// Always return a date.  Defaults to current date ($date will be false if there's no cache value, passing false to date()'s second parameter returns the current time).
			$date = mysql2date( 'U', $date );
			wp_cache_set( $key, $date, 'timeinfo' );
		}

		return date( 'c', $date );
	}

	/**
	 * Split YYYYMM string into YYYY & MM
	 * Use: list($year, $month) = $this->parse_sitemap_n();
	 */
	protected function _parse_sitemap_n() {
		$yyyymm = array(
			intval( substr( $this->_sitemap_n, 0, 4 ) ), // yyyy
			intval( substr( $this->_sitemap_n, -2 ) ), // mm
		);
		return $yyyymm;
	}

	/**
	 * Add the sitemap cache
	 */
	protected function _save( $action = 'insert', $content = array(), $pmc_sitemaps_identity = array() ) {

		if ( ! is_array($content) || empty($content) ) {
			return false;
		}

		$old_content = $content;
		$post_data = wp_parse_args($content, $this->a__post_defaults);

		// Sanity check if the post ID isn't present on update
		if ( 'update' === $action && ! isset($post_data['ID']) ) {
			return false;
		}

		switch ( $action ) {
			case 'insert':

				//There should not be a case that title is empty.
				if ( empty( $post_data['post_title'] ) ) {
					return false;
				}

				$post_id = wp_insert_post( $post_data );
				// If insert was successful, save the sitemap's identity to a postmeta so when the builder has ot query the sitemap for update it can easily figure out what type of sitemap to rebuild
				if ( ! empty( $post_id ) ) {
					if ( ! empty( $pmc_sitemaps_identity ) ) {
						update_post_meta( $post_id, 'pmc_sitemaps_identity', $pmc_sitemaps_identity );
					}
				}
				break;

			case 'update':

				//If for some reason title is empty dont update it and leave it as it is
				if ( empty( $post_data['post_title'] ) ) {
					unset( $post_data['post_title'] );
				}
				$post_id = wp_update_post( $post_data );
				if ( $post_id ) {
					// we need to reset flag to prevent additional rebuilding
					update_post_meta( $post_id, 'pmc_sitemaps_rebuild', 0 );
				}
				break;
		}

		if ( $post_id < 1 ) {
			return false;
		}

		// save the post meta if any
		if ( !empty( $this->post_meta_to_save ) ) {
			foreach ( $this->post_meta_to_save as $key => $value ) {
				update_post_meta( $post_id, $key, $value );
			}
			$this->post_meta_to_save = [];
		}

		//Set debug data here to check in case sitemap has any issue
		unset( $post_data['post_content'] );
		unset( $old_content['post_content'] );
		$post_data['current_obj']      = $this;
		$post_data['old_post_content'] = $old_content;
		$post_data['action_type']      = $action;
		update_post_meta( $post_id, 'pmc_sitemaps_debug', $post_data );

		return $post_id;
	}

	protected function _generate_sitemap( $action = 'insert', &$post_id = 0 ) {
		// Since attempting to generate a sitemap if one doesn't exist, cache the fact it doesn't exist so we're not continuously trying to make one.
		// Implements a rudimentary locking mechanism to help prevent multiple sitemap generation requests from firing
		// This also prevents a sitemap from being generated more than once per minute
		$cache_key = 'pmc_' . $this->_sitemap_type . $this->_sitemap_name . $this->_sitemap_n;

		$pmc_cache = new PMC_Cache( $cache_key, 'sitemaps' );

		$myclock       = microtime( true );
		$lock_acquired = $pmc_cache->expires_in(
			self::between_build_frequency * MINUTE_IN_SECONDS
		)->updates_with(
			function() use ( $myclock ) {
				return $myclock;
			}
		)->get();

		if ( $lock_acquired !== $myclock && ! $this->is_debug() ) {
			return false;
		}

		$sitemap_xml = $this->get_sitemap_xml( $this->_sitemap_type, $post_id );

		if ( empty( $sitemap_xml ) ) {
			return false;
		}

		$sitemap_content = $this->generate_sitemap_post_data( $action, $sitemap_xml, $post_id );

		$pmc_sitemaps_identity = ( 'insert' === $action ) ? $this->_get_meta_data() : [];

		$post_id = $this->_save( $action, $sitemap_content, $pmc_sitemaps_identity );

		if ( ! $post_id ) {
			return false;
		}

		return $sitemap_xml;
	}

	/**
	 * generate the <url> node
	 */
	protected function _generate_sitemap_node( $data, $nodename = 'url' ) {
		$output = '{{' . $nodename . '}}';

		if( isset( $data['loc'] ) ) {
			$loc = apply_filters( 'pmc_sitemaps_loc',  $data['loc'] );
			$output .= '{{loc}}' . esc_url( $loc ) . '{{/loc}}';
		}

		if ( isset($data['lastmod']) ) {
			$date = mysql2date( 'Y-m-d\TH:i:s+00:00', $data['lastmod'] );

			if ( strtotime($date) < 0 ) {
				$date = '1970-01-01T00:00:00+00:00';
			}
			$output .= '{{lastmod}}' . esc_attr( $date ) . '{{/lastmod}}';
		}

		if ( isset( $data['changefreq'] ) ) {
			$output .= '{{changefreq}}' . esc_attr( $data['changefreq'] ) . '{{/changefreq}}';
		}

		if ( isset( $data['priority'] ) ) {
			$output .= '{{priority}}' . esc_attr( $data['priority'] ) . '{{/priority}}';
		}

		if ( isset($data['images']) && ( count($data['images']) > 0 ) ) {
			foreach ( $data['images'] as $image ) {
				$loc = apply_filters( 'pmc_sitemaps_loc',  $image['loc'] );
				$output .= '{{image:image}}';
				$output .= '{{image:loc}}' . esc_url( $loc ) . '{{/image:loc}}';
				if ( ! empty($image['title']) )
					$output .= '{{image:title}}' . PMC::esc_xml( PMC::strip_control_characters( strip_tags( force_balance_tags( $image['title'] ) ) ) ) . '{{/image:title}}';
				if ( ! empty($image['caption']) )
					$output .= '{{image:caption}}' . PMC::esc_xml( PMC::strip_control_characters( strip_tags( force_balance_tags( $image['caption'] ) ) ) ). '{{/image:caption}}';
				$output .= '{{/image:image}}';
			}
		}

		$output .= '{{/' . $nodename . '}}';

		return $output;
	}

	/**
	 *
	 * @todo This could be further abstracted to build an index of indexes
	 * @todo Get last modified date from subsitemaps if able, only fall back to querying last modified date directly if the subsitemap is unavailable for some reason (e.g., it's been deleted so that it will be regenerated).  This also means modifying the insert/update query for sitemaps to that the last modified date is the same as the most recent last modified date of the posts it contains.
	 */
	protected function _generate_index_urlset( $sitemap_id = 0 ) {

		$urlset = '';

		$urlset .= $this->_generate_post_type_index_urlset( $sitemap_id );

		$urlset .= $this->_generate_taxonomy_index_urlset();

		// reference post type specific sitemaps.
		if ( apply_filters( 'pmc_generate_archive_index', true ) ) {
			$urlset .= $this->_generate_archive_index_urlset();
		}

		$urlset .= $this->_generate_custom_sitemap_index_urlset();

		return ( ! empty( $urlset ) ) ? $urlset : false;

	}

	/**
	 * Helper function to get the registered custom sitemaps.
	 *
	 * @return array Returns the array containing registered custom sitemaps or empty array.
	 */
	protected function _get_registered_custom_sitemaps() : array {

		/**
		 * Allow custom sitemap index ( i.e. http://brand_name.com/my-custom-sitemap.xml ) to be appended to our main sitemap index ( https://brand_name.com/sitemap_index.xml ).
		 *
		 * Use a dynamic filter based on custom sitemap index key to provide urls to include in the custom sitemap index.
		 *
		 * Check pmc-rollingstone-2018/plugins/charts/classes/utils/class-sitemap.php for implementation example.
		 *
		 * @param array $custom_sitemaps An array containing required data for generating custom sitemap.
		 *
		 * Array should be in the following format.
		 * IMPORTANT:
		 * Array key will be used to create the sitemap index url, i.e if array key is 'custom_pages' then sitemap index will be '/custom_pages-sitemap.xml'.
		 * To provide the urls for this custom sitemap index use a dynamic filter named 'pmc_sitemaps_register_{$custom_sitemap_index_key}_urls', e.g. 'pmc_sitemaps_register_custom_pages_urls'
		 *
		 * [
		 *    'rollingstone_charts' => [
		 *         'lastmod' => 'date',
		 *    ],
		 *
		 *    'custom_pages' => [
		 *         'lastmod' => 'date',
		 *    ],
		 * ];
		 *
		 * @param PMC_Sitemaps $this     The current sitemap object.
		 */
		$custom_sitemaps = apply_filters( 'pmc_sitemaps_register_custom_sitemap', [], $this );

		if ( is_array( $custom_sitemaps ) ) {
			return $custom_sitemaps;
		}

		return [];
	}

	/**
	 * Helper function to get the url data for the custom sitemap index.
	 * Uses a dynamic filter based on custom sitemap-index key. ( key used when registering the custom sitemap index ).
	 *
	 * @param string $custom_sitemap_index  Key used when registering the custom sitemap, e.g. rollingstone-charts.
	 *
	 * @return array Returns the array containing custom sitemaps data or empty array.
	 */
	protected function _get_registered_custom_sitemap_urls( string $custom_sitemap_index ) : array {

		/**
		 * Dynamic filter to provide data for custom sitemap-index.
		 * If 'rollingstone_charts' was used as key when registering the custom sitemap-index then filter will be 'pmc_sitemaps_register_rollingstone_charts_urls'.
		 *
		 * Check pmc-rollingstone-2018/plugins/charts/classes/utils/class-sitemap.php for implementation example.
		 *
		 * @param array An array containing required url data for generating custom sitemap.
		 *
		 * Array should be in the following format.
		 *
		 * [
		 *    [
		 *       'loc'        => get_home_url() . '/link_to_some_custom_page,
		 *       'lastmod'    => 'date'
		 *       'changefreq' => 'hourly',
		 *       'priority'   => '0.9',
		 *    ],
		 *    [
		 *       'loc'        => get_home_url() . '/link_to_another_custom_page,
		 *       'lastmod'    => 'date'
		 *       'changefreq' => 'hourly',
		 *       'priority'   => '0.9',
		 *    ],
		 * ];
		 *
		 * @param PMC_Sitemaps $this            The current sitemap object.
		 */
		$custom_sitemap_urls = apply_filters( 'pmc_sitemaps_register_' . $custom_sitemap_index . '_urls', [], $this );

		if ( is_array( $custom_sitemap_urls ) ) {
			return $custom_sitemap_urls;
		}

		return [];
	}

	/**
	 * Helper function to append the custom sitemap nodes to our main sitemap index ( sitemap_index.xml ).
	 *
	 * @return string Returns custom urlset for appending in master sitemap index.
	 */
	protected function _generate_custom_sitemap_index_urlset() : string {

		$custom_sitemaps = $this->_get_registered_custom_sitemaps();

		// Sanity check for whether we have custom sitemaps registered.
		if ( empty( $custom_sitemaps ) ) {
			return '';
		}

		$output_urlset = '';

		foreach ( $custom_sitemaps as $sitemap_key => $custom_sitemap_index ) {

			$custom_sitemap_index['loc'] = $this->get_url( '/' . $sitemap_key . '-sitemap.xml' );

			$output_urlset .= $this->_generate_sitemap_node( $custom_sitemap_index, 'sitemap' );
		}

		return $output_urlset;
	}

	/**
	 * Generate urlset for custom sitemaps.
	 *
	 * @return string Returns urlset for custom sitemap.
	 */
	protected function _generate_custom_sitemap_urlset() : string {

		$custom_sitemap_urls = $this->_get_registered_custom_sitemap_urls( $this->_sitemap_name );

		// Sanity check if we have custom sitemap urls registered.
		if ( empty( $custom_sitemap_urls ) ) {
			return '';
		}

		$output_urlset = '';

		foreach ( $custom_sitemap_urls as $custom_sitemap_url_data ) {
			$output_urlset .= $this->_generate_sitemap_node( $custom_sitemap_url_data, 'url' );
		}

		return $output_urlset;

	}

	/**
	 * Archive index is simple, mostly-manual sitemap containing archive pages, such as archive pages for custom post types, home page, etc.
	 */
	protected function _generate_archive_index_urlset() {
		$urlset = '';

		$node = array(
			'loc'     => $this->get_full_url( '/archive-sitemap.xml' ),
			'lastmod' => date( 'c' ),
		);

		$urlset .= $this->_generate_sitemap_node( $node, 'sitemap' );

		return ( ! empty($urlset) ) ? $urlset : false;
	}

	protected function _generate_archive_urlset() {

		if ( ! apply_filters('pmc_generate_archive_index', true ) ) {
			return false;
		}

		$urlset = '';

		// Home page URL
		$urlset .= $this->_generate_sitemap_node( array(
			'loc'        => $this->get_full_url( '/' ),
			'priority' => 1,
			'changefreq' => ( get_option('page_on_front') ) ? 'monthly' : 'always',
		) );

		// Post type archive page
		// Get all public post types
		$post_types = get_post_types( array('public' => true) );

		// Sanity check
		if ( ! $post_types ) {
			return false;
		}

		$valid_post_types = $this->get_valid_post_types();

		//Store list of custom post types
		$custom_post_types = get_post_types( array(
		  'public'   => true,
		  '_builtin' => false
		), 'names', 'and' );

		$changefreq = 'weekly';

		foreach ( $valid_post_types as $post_type ) {
			$archive = get_post_type_archive_link( $post_type );

			if ( $archive ) {

				if ( is_array( $custom_post_types ) && in_array( $post_type, $custom_post_types ) ){
					$changefreq = 'hourly';
				}

				$urlset .= $this->_generate_sitemap_node( array(
					'loc'        => $this->get_full_url( $archive ),
					'priority' => 0.8,
					'changefreq' => $changefreq,
					'lastmod' => $this->_get_last_modified_time( 'post', $post_type )
				) );
			}
		}

		return ( ! empty($urlset) ) ? $urlset : false;
	}

	protected function _generate_post_type_urlset( $sitemap_id = 0 ) {
		$valid_post_types = $this->get_valid_post_types();

		if ( ! in_array( $this->_sitemap_name, $valid_post_types, true ) ) {
			return false;
		}

		global $wpdb;

		// To check if one of the posts is the homepage
		$front_id = get_option('page_on_front');

		list($year, $month) = $this->_parse_sitemap_n();

		$current_time_gmt = current_time( 'timestamp', 1 );

		$number = 500;
		$offset = 0;

		$has_posts = true;

		$urlsets = [];
		// default last modifed time set to very old to retreive everything
		$last_modified = 0;

		// If there is an existing sitemap, let's use last modified date to do delta query
		// to limit the data we have to requery. Everytime a new article publish, it trigger the affected month sitemap to rebuild.
		// by using the last build data & last post modified date stored, we can cut down the number of posts to retrieve
		// and avoid additional loop to retrieves the rest of the posts.  The urlsets stored in post meta contains all data we needed from last build.
		if ( $sitemap_id && ! $this->is_debug() ) {
			$urlsets = get_post_meta( $sitemap_id, '_post_type_urlsets', true );
			if ( empty( $urlsets ) || ! is_array( $urlsets ) ) {
				$urlsets = [];
			}
			$last_modified = intval( get_post_meta( $sitemap_id, '_post_type_last_modified', true ) );
		}

		// The time range filter need to match time field use in function _generate_post_type_index_urlset
		$start_time_str    = date( 'Y-m-d H:i:s', mktime(0, 0, 0, $month, 1, $year) );
		$end_time_str      = date( 'Y-m-d H:i:s', mktime(0, 0, 0, $month+1, 1, $year) );
		$last_modified_time_str = date( 'Y-m-d H:i:s', $last_modified );

		// Localize the range we're querying for, leveraging the `type_status_date` index Core provides.
		$start_time_str_local = $this->_adjust_utc_timestamp( $start_time_str );
		$end_time_str_local   = $this->_adjust_utc_timestamp( $end_time_str );

		// Encourage the query optimizer to use the `type_status_date` index.
		$post_stati = array_map( 'esc_sql', get_post_stati() );
		$post_stati = implode( "', '", $post_stati );
		$post_stati = "'{$post_stati}'";

		// Retrieve all excluded IDs by asking for more than a site has in practice.
		$excluded_ids = $this->get_excluded_post_ids( 250 );

		do {
			/*
			Using get_posts() uses way too much memory and resources, I wasn't able to generate a 100 post sitemap without hitting a 128MB RAM cap.
			If using this get_posts() code, need to set & increment $paged
			$posts = get_posts( array(
				'post_type' => $this->_sitemap_name,
				'post_status' => 'publish',
				'year' => $year,
				'month' => $month,
				'posts_per_page' => $number,
				'paged' => $paged,
			) );
			*/

			/**
			 * Query posts table in a manner that ensures we use the
			 * `type_status_date` index Core provides.
			 *
			 * Query's WHERE clause should only use `post_type`, `post_status`,
			 * and `post_date` to ensure index is leveraged.
			 *
			 * Unpublished content, as well as excluded items, are returned in
			 * this query in case they need to be removed from the $urlsets
			 * cache for this sitemap.
			 */
			$prepared_sql = $wpdb->prepare(
				"SELECT * FROM $wpdb->posts WHERE post_type = %s",
				$this->_sitemap_name
			);
			$prepared_sql .= " AND post_status IN ({$post_stati}) ";

			$date_range = apply_filters(
				'pmc_sitemaps_' . sanitize_key( $this->_sitemap_name ) . '_include_date_range',
				true
			);

			if (
				$date_range
				|| ( ! $date_range && ! empty( $month ) && ! empty( $year ) )
			) {
				$prepared_sql .= $wpdb->prepare(
					'AND post_date >= %s
					AND post_date < %s',
					$start_time_str_local,
					$end_time_str_local
				);
			}

			$prepared_sql .= $wpdb->prepare(
				' LIMIT %d, %d',
				$offset,
				$number
			);

			// Query for the posts to include in the sitemap.
			$posts = $wpdb->get_results( $prepared_sql );

			if ( ! $posts ) {
				$has_posts = false;
				break;
			}

			$offset = ($offset + $number);

			$posts_count = count( $posts );
			for ( $i = 0; $i < $posts_count; $i++ ) {
				// Condition previously part of SQL query, moved to PHP for performance.
				if ( $posts[ $i ]->post_modified_gmt < $last_modified_time_str ) {
					continue;
				}

				if ( apply_filters( 'pmc_sitemap_exclude_post', false, $posts[$i] ) ) {
					continue;
				}

				// Prevent listing the homepage post again
				if ( $posts[$i]->ID == $front_id ) {
					continue;
				}

				// excluded from seo
				if ( in_array( (int) $posts[ $i ]->ID, $excluded_ids, true ) ) {
					if ( isset( $urlsets[ $posts[$i]->ID ] ) ) {
						unset( $urlsets[ $posts[$i]->ID ] );
					}
					continue;
				}

				// Remove non-published content.
				if ( 'publish' !== $posts[ $i ]->post_status ) {
					if ( isset( $urlsets[ $posts[ $i ]->ID ] ) ) {
						unset( $urlsets[ $posts[ $i ]->ID ] );
					}
					continue;
				}

				// calculate the latest post last modified; since we do not want to use order by in mysql query to improve performance
				// the last modified is use to stored the last modified date value so we can do a detla query to get only those articles that have been updated since last time we process the list.
				$time = strtotime( $posts[$i]->post_modified_gmt );
				if ( $time > $last_modified ) {
					$last_modified = $time;
				}

				$node = array(
					'loc'        => $this->get_full_url( get_permalink( $posts[ $i ] ) ), // see note on sql query select statement
					'priority' => 0.7,
					'lastmod' => $posts[$i]->post_modified_gmt,
					'changefreq' => 'monthly',
				);

				// If the post was created < 30 days ago then changefreq should be hourly, otherwise use the default (monthly)
				$post_time_gmt = strtotime( $posts[$i]->post_date_gmt );
				if ( ( $current_time_gmt - $post_time_gmt ) < 2592000 ) {
					$node['changefreq'] = 'hourly';
					$node['priority'] = 0.9;
				}

				// Pages with no parent have a higher priority
				if ( 'page' === $posts[$i]->post_type ) {
					if ( 0 == $posts[$i]->post_parent ) {
						$node['priority'] = 0.5;
					} else {
						$node['priority'] = 0.1;
						$node['changefreq'] = 'yearly';
					}
				}

				// Add any attached images
				/*
				Querying the DB directly for speed and performance
				$attachments = get_posts( array(
					'post_type' => 'attachment',
					'numberposts' => -1,
					'post_status' => 'inherit',
					'post_parent' => $posts[$i]->ID,
				) );
				*/
				$attachments = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_mime_type, post_excerpt, post_content, post_title FROM {$wpdb->posts} WHERE post_parent=%d AND post_type='attachment'", $posts[$i]->ID ) );

				if ( $attachments ) {
					// Add an image array to the current node so we can populate it below.
					$node['images'] = array();

					$attachments_count = count( $attachments );
					for ( $x = 0; $x < $attachments_count; $x++ ) {
						// Skip anything that's not an image
						if ( ! in_array( $attachments[$x]->post_mime_type, $this->a__img_types ) ) {
							continue;
						}

						// We will populate the caption attribute with the caption text, and then fallback to the image description.
						$caption = html_entity_decode( $attachments[$x]->post_excerpt );
						if ( ! $caption ) {
							$caption = html_entity_decode( $attachments[$x]->post_content );
						}

						$image_data = wp_get_attachment_image_src( $attachments[$x]->ID, 'full' );

						$node['images'][] = array(
							'loc' => $image_data[0],
							'title' => strip_tags( force_balance_tags( html_entity_decode( $attachments[$x]->post_title ) ) ),
							'caption' => strip_tags( force_balance_tags( $caption ) ), // html_entity_decode() is run on this above
						);
					}

					// destroy variable
					unset( $attachments );
				}

				$urlsets[ $posts[$i]->ID ] = $this->_generate_sitemap_node( $node );
			}

			// destroy variable
			unset( $posts );

			$this->stop_the_insanity();

		} while ( true === $has_posts );

		// save the information so rebuild would be faster to retrieve only post since last change.
		$this->post_meta_to_save['_post_type_urlsets']       = $urlsets;
		$this->post_meta_to_save['_post_type_last_modified'] = $last_modified;
		return ( ! empty( $urlsets ) ) ? implode( '', $urlsets ) : false;
	}

	protected function _generate_taxonomy_urlset() {
		global $wpdb;

		$number       = 100;
		$offset       = 0;
		$more_terms   = true;
		$urlset       = '';
		$taxonomy     = $this->_sitemap_name;
		$last_term_id = false;

		do {
			/*
			TODO: $offset only works for non-hierarchical terms, if you use it for a hierarchical term WP will silently drop the offset.
			When used in a loop like this, that will cause an infinite loop.  So perhaps before calling get_terms I need to call get_taxonomy for the taxonomy and see if it's hierarchical.  Or perhaps just query against the database directly.
			Meanwhile, using get_terms() on a large resultset will cause out-of-memory issues, so we need to query the database directly.
			Ugh.

			$terms = get_terms( $this->_sitemap_name, array(
				'hide_empty' => true,
				'number' => $number,
				'offset' => $offset,
			) );
			*/

			$terms = apply_filters( 'pmc_sitemap_pre_taxonomy_terms', false, $this->_sitemap_name, $offset, $number );

			if ( false === $terms ) {
				$terms_sql = $wpdb->prepare( "SELECT tt.term_id, tt.term_taxonomy_id, tt.`count` FROM " . $wpdb->term_taxonomy . " AS tt WHERE tt.taxonomy = %s AND tt.`count` > 0 LIMIT %d, %d", $taxonomy, $offset, $number ); // phpcs:ignore
				$terms     = $wpdb->get_results( $terms_sql ); // phpcs:ignore
				$terms     = apply_filters( 'pmc_sitemap_taxonomy_terms', $terms, $taxonomy, $offset, $number );
			}

			if ( empty( $terms ) ) {
				$more_terms = false;
				break;
			}

			if ( $last_term_id === $terms[0]->term_id ) {
				// we need to break out to prevent endless loop
				$more_terms = false;
				break;
			}

			$last_term_id = $terms[0]->term_id;

			$offset = ($offset + $number);

			foreach( $terms as $term ) {
				// TODO: We could be intelligent about the changefreq and do things like if last modified < 7 days ago chf=daily, < 30 days ago changefreq=monthly, etc
				$node = array(
					'loc' => '',
					'priority' => 0.6,
					'changefreq' => 'daily',
				);

				$term_link = get_term_link( intval( $term->term_id ), $taxonomy );
				if ( ! $term_link || is_wp_error( $term_link ) ) {
					continue;
				}

				$node['loc'] = $this->get_full_url( $term_link );

				// Update priority based on count
				// TODO: These counts seem low, maybe make them modifiable or check to see if we should increase them.
				if ( $term->count > 10 ) {
					$node['priority'] = 0.6;
					$node['changefreq'] = 'hourly';
				} else if ( $term->count > 3 ) {
					$node['priority'] = 0.6;
				}else{
					if ( true !== apply_filters( 'pmc_sitemaps_taxonomy_force_generate', false, $taxonomy ) ) {
						continue; //skip anything that has 3 or less.
					}
				}

				// Get the date of the last modified post to use this taxonomy term
				$last_modified_gmt = $this->_get_last_modified_time( 'term', $term->term_taxonomy_id );

				if ( $last_modified_gmt ) {
					$node['lastmod'] = $last_modified_gmt;
				}

				$urlset .= $this->_generate_sitemap_node( $node );

				if ( function_exists('stop_the_insanity') ){
					stop_the_insanity();
				}
			}

		} while ( true === $more_terms );

		return ( ! empty($urlset) ) ? $urlset : false;
	}

	/**
	 * @see $this->_generate_index_urlset();
	 * @todo Get last modified date from subsitemaps if able, only fall back to querying last modified date directly if the subsitemap is unavailable for some reason (e.g., it's been deleted so that it will be regenerated).  This also means modifying the insert/update query for sitemaps to that the last modified date is the same as the most recent last modified date of the posts it contains.
	 */
	protected function _generate_taxonomy_index_urlset() {
		global $wpdb;

		// Get all public taxonomies
		$taxonomies = get_taxonomies( array(
			'public' => true,
			'show_ui' => true,
		) );

		// Sanity check
		if ( ! $taxonomies ) {
			return false;
		}

		// Filter default invalid post types so anything that adds a public post type can remove it from the sitemaps, too
		$valid_taxonomies = apply_filters( 'pmc_sitemaps_taxonomy_whitelist', $this->a__valid_taxonomies );

		$urlset = '';

		foreach ( $taxonomies as $taxonomy_name ) {
			if ( ! in_array( $taxonomy_name, $valid_taxonomies ) ) {
				continue;
			}

			// Bail if no term with posts are found
			$terms = get_terms( $taxonomy_name, array( 'hide_empty' => 1 ) );

			if ( empty( $terms ) || is_wp_error( $terms ) || ! is_array( $terms ) ) {
				continue;
			}

			// If none of term have more than 3 count
			// than don't add taxonomy in sitemap index.
			//
			// Because $this->_generate_taxonomy_urlset(),
			// will not allow any term in sitemap that has less than 3 count
			// than mean, if all term have less than 3 count than site map will be empty
			// So, we also need remove from sitemap index.
			$term_counts = wp_list_pluck( $terms, 'count' );
			if ( absint( max( $term_counts ) ) <= 3 ) {
				if ( true !== apply_filters( 'pmc_sitemaps_taxonomy_force_generate', false, $taxonomy_name ) ) {
					continue;
				}
			}

			// There's no good way to get the most recently modified post within a certain taxonomy (e.g., the latest post from ANY category or latest post from ANY tag).	Because every post will presumably have a category and tag, we'll just get the most recently modified post.  The only place this really hurts us is with custom taxonomies.	Too bad.
			$basename = $taxonomy_name . '-sitemap.xml';

			$node = array(
				'loc'     => $this->get_full_url( '/' . $basename ),
				'lastmod' => $this->get_last_post_modified_time(),
			);

			$urlset .= $this->_generate_sitemap_node( $node, 'sitemap' );
		}

		return ( ! empty($urlset) ) ? $urlset : false;
	}

	/**
	 * @see $this->_generate_index_urlset();
	 * @todo Get last modified date from subsitemaps if able, only fall back to querying last modified date directly if the subsitemap is unavailable for some reason (e.g., it's been deleted so that it will be regenerated).  This also means modifying the insert/update query for sitemaps to that the last modified date is the same as the most recent last modified date of the posts it contains.
	 */
	protected function _generate_post_type_index_urlset( $sitemap_id = 0 ) {
		global $wpdb;

		$valid_post_types = $this->get_valid_post_types();

		$urlsets = [];
		// default last modifed time set to very old to retreive everything
		$last_modified = 0;

		// If there is an existing sitemap, let's use last modified date to do delta query
		// to limit the data we have to requery. Everytime a new article publish, it trigger the affected month sitemap to rebuild.
		// by using the last build data & last post modified date stored, we can cut down the number of posts to retrieve
		// and avoid additional loop to retrieves the rest of the posts.  The urlsets stored in post meta contains all data we needed from last build.
		if ( $sitemap_id && ! $this->is_debug() ) {
			$urlsets = get_post_meta( $sitemap_id, '_post_type_index_urlsets', true );
			if ( empty( $urlsets ) || ! is_array( $urlsets ) ) {
				$urlsets = [];
			}
			$last_modified = intval( get_post_meta( $sitemap_id, '_post_type_index_last_modified', true ) );
		}

		$last_modified_time_str = date( 'Y-m-d H:i:s', $last_modified );

		/**
		 * Before we begin, lets grab the last modify date for all post types so
		 * we don't get a gap in case post publish while the index is generating
		 * so we can capture the delta change on the next run.
		 *
		 * Using `_get_lastpostmodified()` rather than `get_lastpostmodified()`
		 * to maintain behaviour of the direct query this replaced, which only
		 * checked the `post_modified_gmt` column. `get_lastpostmodified()` will
		 * use the highest `post_date_gmt` if that value exceeds that of the
		 * highest value found for `post_modified_gmt`.
		 *
		 * Using this helper will change the time slightly, as the query did not
		 * limit by post type, whereas the Core functions limit to post types
		 * that are public, even when "any" is passed. However, because the list
		 * of post types that we're iterating over matches the set used in
		 * `_get_last_post_time()`, there should be no practical impact.
		 */
		$last_modified = $this->get_last_post_modified_time();

		foreach ( $valid_post_types as $post_type ) {
			// fetch the years & months in which posts have been published
			// don't want to use a range here because there may be months or years with no posts, for example Movieline's Vault
			//
			// Note: the exclusion of seo is irrelevant here.  There is zero chance editors will flag all articles for the entire month to be exclude from seo.
			$prepared_sql = $wpdb->prepare( "
				SELECT YEAR(posts.post_date_gmt) as year,
					   MONTH(posts.post_date_gmt) as month
				FROM $wpdb->posts as posts
				WHERE posts.post_status = 'publish'
					AND posts.post_type = %s
					AND posts.post_modified_gmt >= %s
				GROUP BY year, month
			", $post_type, $last_modified_time_str );

			$dates_with_posts = $wpdb->get_results( $prepared_sql );

			$dates_with_posts = apply_filters( 'pmc_sitemap_' . $post_type . '_posts', $dates_with_posts );

			// Sanity check
			if ( empty( $dates_with_posts ) || ( ! is_array( $dates_with_posts ) ) ) {
				continue;
			}

			foreach ( $dates_with_posts as $date_with_posts ) {
				$start_time = mktime(0, 0, 0, $date_with_posts->month, 1, $date_with_posts->year);
				$end_time = mktime(0, 0, 0, $date_with_posts->month+1, 1, $date_with_posts->year);
				$last_modified_time_args = array(
					'range_start' => date('Y-m-d H:i:s', $start_time),
					'range_end' => date('Y-m-d H:i:s', $end_time),
				);

				$basename = $post_type . '-sitemap' . $date_with_posts->year . sprintf( '%02d', $date_with_posts->month );
				$basename = apply_filters(
					'pmc_sitemaps_' . sanitize_key( $post_type ) . '_basename',
					$basename
				);

				$basename .= '.xml';

				$node = array(
					'loc'     => $this->get_full_url( '/' . $basename ),
					'lastmod' => $this->_get_last_modified_time( 'post', $post_type, $last_modified_time_args ),
				);
				$urlsets[ $basename ] = $this->_generate_sitemap_node( $node, 'sitemap' );

			}
		}

		// Sort sitemaps in reverse chronological order.
		krsort( $urlsets );

		// Grab 'post' sitemaps.
		$post_sitemaps = preg_grep( '/post-sitemap/', $urlsets );

		// Keep 'post' sitemaps on top.
		if ( ! empty( $post_sitemaps ) ) {

			$other_post_type_sitemaps = array_diff( $urlsets, $post_sitemaps );

			$urlsets = array_merge( $post_sitemaps, $other_post_type_sitemaps );

		}

		// save the information so rebuild would be faster to retrieve only post since last change.
		$this->post_meta_to_save['_post_type_index_urlsets']       = $urlsets;
		$this->post_meta_to_save['_post_type_index_last_modified'] = strtotime( $last_modified );

		return ( ! empty( $urlsets ) ) ? implode( '', $urlsets ) : false;

	}

	/**
	 * We store UTC timestamps indicating when sitemap was last modified, but
	 * want to query using the `post_date` column, which is localized. In other
	 * words, we need `wp_date()` in reverse. Doing so allows queries to
	 * utilize the `type_status_date` index in the posts table.
	 *
	 * @param string $initial
	 * @return string
	 */
	protected function _adjust_utc_timestamp( string $initial ): string {
		$timezone = wp_timezone();

		if ( in_array( $timezone->getName(), [ '+00:00', 'UTC' ], true ) ) {
			return $initial;
		}

		$adjusted = date_create( $initial, new \DateTimeZone( 'UTC' ) );
		$adjusted->setTimezone( $timezone );
		$adjusted = $adjusted->format( 'Y-m-d H:i:s' );

		return $adjusted;
	}

	/**
	 * Add a 5-minute frequency to the cron schedule
	 * @version 2.0.0.1 Changed from 1 minute to 5 minutes per WP VIP review
	 */
	public function add_cron_schedules( $schedules ) {
		// add a 5 minute schedule to the existing set
		$schedules['pmc_five_minutes'] = array(
										'interval' => 300,
										'display' => __('PMC Five Minutes')
										);
		return $schedules;
	}

	/**
	 * Find any sitemaps flagged for rebuild and...rebuild them.
	 */
	public function update_sitemaps() {
		// Remove this filter to prevent weirdness when doing sub-queries to generate the sitemaps
		remove_action( 'pre_get_posts', array( $this, 'sitemap_request' ) );

		// Limit sitemap generation to 2 at a time
		// The concern here is that if there are too many sitemaps to generate the operation will timeout or take so long it enters a race condition with another wp-cron process.
		// use order by post_modified to rotate listing in a FIFO manner
		$sitemaps = get_posts( array(
			'posts_per_page' => 2,
			'post_type' => 'pmc_sitemap',
			'meta_key' => 'pmc_sitemaps_rebuild',
			'meta_value' => 1,
			'orderby' => 'post_modified',
			'order' => 'ASC',
		) );

		if ( ! $sitemaps ) {
			return;
		}

		foreach ( $sitemaps as $sitemap ) {
			$this->rebuild_content( $sitemap );
		}

		// Now regenerate the sitemap index
		$this->_sitemap_name = 'index';
		$this->_sitemap_type = 'index';
		$this->_sitemap_n = null;

		$sitemap = get_posts( array(
			'post_type'      => 'pmc_sitemap',
			'name'           => $this->get_sitemap_name( 'sanitize' ),
			'posts_per_page' => 1,
		) );

		if ( isset( $sitemap[0]->ID ) ) {
			// If this fails no need to flag it for regeneration, next time a detail sitemap gets generated it will try again
			$result = $this->_generate_sitemap( 'update', $sitemap[0]->ID );
		}
	}

	/**
	 * Expose this function so WP CLI Script can use to rebuild the site map content
	 * @param $sitemap
	 */
	function rebuild_content( $sitemap ) {
		$sitemap = get_post( $sitemap );

		$sitemap_identity = get_post_meta( $sitemap->ID, 'pmc_sitemaps_identity', true );
		// Sanity check.  We explicitly set the pmc_sitemaps_identity postmeta when creating the sitemap, so something would have to be pretty wrong for this to fail.
		if ( ! $sitemap_identity ) {
			return false;
		}

		// Set the class properties used when updating the sitemap
		$this->_sitemap_name = sanitize_title_with_dashes( $sitemap_identity['sitemap_name'] );
		$this->_sitemap_type = $this->_get_sitemap_type( $sitemap_identity['sitemap_name'] );
		$this->_sitemap_n    = absint( $sitemap_identity['sitemap_n'] );
		if ( 0 === $this->_sitemap_n ) {
			$this->_sitemap_n = null;
		}

		// Take control of the sitemap rebuild
		update_post_meta( $sitemap->ID, 'pmc_sitemaps_rebuild', 0 );

		// Rebuild
		$result = $this->_generate_sitemap( 'update', $sitemap->ID );

		// TODO: Need to detect legit error and non-support content to prevent unsuccessful rebuild on same id
		if ( ! $result ) {
			// Generation failed, let someone else try and rebuild
			update_post_meta( $sitemap->ID, 'pmc_sitemaps_rebuild', 1 );
			// update the modified date to rotate the processed item to end of queue for retry
			// TODO: detect and mark invalid entry and remove
			$sitemap_content = [
				'post_modified'     => current_time( 'mysql', 0 ),
				'post_modified_gmt' => current_time( 'mysql', 1 ),
				'ID'                => $sitemap->ID,
			];
			wp_update_post( $sitemap_content );
		}

		return $result;

	}

	/**
	 * Set/update the last modified time cache for a given post type
	 *
	 * @see $this->_get_last_modified_time()
	 */
	public function update_post_last_modified() {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset($GLOBALS['post']) ) {
			return;
		}

		if ( wp_is_post_revision( $GLOBALS['post']->ID ) || 'publish' !== $GLOBALS['post']->post_status ) {
			return;
		}

		// Remove this filter to prevent weirdness when doing sub-queries to generate the sitemaps
		remove_action( 'pre_get_posts', array( $this, 'sitemap_request' ) );

		$hash_key = $GLOBALS['post']->post_type;
		$tmp_date = strtotime($GLOBALS['post']->post_date_gmt);
		$year = date('Y', $tmp_date);
		$month = date('m', $tmp_date);
		$hash_key .= $year . $month;
		$key = 'pmc_lastmod_' . md5( $hash_key );

		wp_cache_set( $key, $tmp_date, 'timeinfo' );

		$this->_maybe_update_last_post_modified_time( $GLOBALS['post'] );
		$this->schedule_update( $GLOBALS['post']->post_type, $year, $month );
	}

	/**
	 * Maybe update the post option last post modified time if we have a public post.
	 *
	 * @param WP_Post $post
	 */
	private function _maybe_update_last_post_modified_time( \WP_Post $post ) : void {
		$valid_post_types = $this->get_valid_post_types();

		if ( ! in_array( $post->post_type, (array) $valid_post_types, true ) ) {
			return;
		}

		pmc_update_option( 'last_post_modified_time_gmt', $post->post_modified_gmt );
	}

	/**
	 * Get the last post modified date of a public post from pmc option.
	 *
	 * @return string
	 */
	public function get_last_post_modified_time() : string {
		$last_modified_date = pmc_get_option( 'last_post_modified_time_gmt' );

		if ( empty( $last_modified_date ) ) {
			$last_modified_date = _get_last_post_time( 'gmt', 'modified' );
		}

		return (string) $last_modified_date;
	}

	/**
	 * Returns an array of valid public post types for site maps.
	 *
	 * @return array
	 */
	public function get_valid_post_types() : array {
		$post_types = get_post_types( [ 'public' => true ] );

		// Filter default valid post types so anything that adds a public post type can add it to the sitemaps, too
		$valid_post_types = apply_filters( 'pmc_sitemaps_post_type_whitelist', $this->a__valid_post_types );

		if ( ! is_array( $valid_post_types ) ) {
			return [];
		}

		$valid_post_types = array_values( array_unique( (array) $valid_post_types ) );

		foreach ( $valid_post_types as $key => $valid_post_type ) {
			if ( ! in_array( $valid_post_type, (array) $post_types, true ) ) {
				unset( $valid_post_types[ $key ] );
			}
		}

		return array_values( $valid_post_types );
	}

	/**
	 * Set/update the last modified time cache for a given taxonomy
	 *
	 * @see $this->_get_last_modified_time()
	 */
	public function update_term_last_modified( $term_id, $tt_id, $taxonomy ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! post_type_exists( self::POST_TYPE ) ) {
			// There is no good way to do unit test on this code
			return; // @codeCoverageIgnore
		}

		// Remove this filter to prevent weirdness when doing sub-queries to generate the sitemaps
		remove_action( 'pre_get_posts', array( $this, 'sitemap_request' ) );

		$hash_key = $tt_id;
		$key = 'pmc_lastmod_' . md5( $hash_key );

		wp_cache_set( $key, time(), 'timeinfo' );

		$this->schedule_update( $taxonomy );
	}

	/**
	 * Helper function to rigger an existing site map to rebuild base on post type, year & month
	 * @param  string $type_value The site map type to rebuild
	 * @param  string $year       The optional year value
	 * @param  string $month      The optional month value
	 * @return mixed              Site map post object if exists, other false
	 */
	public function trigger_rebuild( $type_value, $year = '', $month = '' ) {

		if ( is_object( $type_value ) ) {
			$post       = $type_value;
			$type_value = $post->post_type;
			$tmp_date   = strtotime( $post->post_date_gmt );
			$year       = date( 'Y', $tmp_date );
			$month      = date( 'm', $tmp_date );
		}

		// Figure out which sitemap needs to be updated
		$this->_sitemap_name = $type_value;
		$this->_sitemap_type = $this->_get_sitemap_type( $type_value );
		$this->_sitemap_n = absint( $year . $month );
		if ( 0 === $this->_sitemap_n ) {
			$this->_sitemap_n = false;
		}

		// Get the sitemap post ID so we can update the meta value flagging it for update
		// If it doesn't exist we don't need to schedule an update, because it will be updated automatically the next time it's accessed.
		$sitemap = get_posts( array(
			'post_type'      => 'pmc_sitemap',
			'name'           => $this->get_sitemap_name( 'sanitize' ),
			'posts_per_page' => 1,
		) );

		if ( isset( $sitemap[0] ) ) {
			update_post_meta( $sitemap[0]->ID, 'pmc_sitemaps_rebuild', 1 );
			return $sitemap[0];
		}

		return false;

	}

	/**
	 * Flag a sitemap to be updated
	 */
	public function schedule_update( $type_value, $year = '', $month = '' ) {

		if ( ! $this->trigger_rebuild( $type_value, $year, $month ) ) {
			// Sitemap doesn't exist for that year/month yet, so create the skeleton and flag it for update, the cron will create the sitemap and then update the sitemap_index with the new sitemap
			// Populate the array for saving the sitemap post
			$current_date = current_time( 'mysql', 0 );
			$current_date_gmt = current_time( 'mysql', 1 );
			$sitemap_content = array(
				'post_title'        => $this->get_sitemap_name(),
				'post_modified'     => $current_date,
				'post_modified_gmt' => $current_date_gmt,
				'post_date'         => $current_date,
				'post_date_gmt'     => $current_date_gmt,
			);

			$sitemap_identity = $this->_get_meta_data();
			$this->_save( 'insert', $sitemap_content, $sitemap_identity );
		}

		// we also want to trigger the index rebuild for on demand support
		if ( defined('PMC_SITEMAP_REBUILD_ON_DEMAND') && PMC_SITEMAP_REBUILD_ON_DEMAND ) {
			$this->_trigger_index_rebuild();
		}
	}

	protected function _trigger_index_rebuild() {
		// prevent looping, we only want to trigger index rebuild if and only if current sitemap is not index
		// since this function may be trigger by on demand rebuild for sitmap index
		if ( $this->_sitemap_type == 'index' ) {
			return;
		}

		$this->_sitemap_name = 'index';
		$this->_sitemap_type = 'index';
		$this->_sitemap_n = null;

		$sitemap = get_posts( array(
			'post_type'      => 'pmc_sitemap',
			'name'           => $this->get_sitemap_name( 'sanitize' ),
			'posts_per_page' => 1,
		) );

		if ( isset( $sitemap[0]->ID ) ) {
			update_post_meta( $sitemap[0]->ID, 'pmc_sitemaps_rebuild', 1 );
		}
	}

	/**
	 * Add a checkbox to the bottom of the VIP Plugin: 'Add Meta Tags'
	 * 'SEO' meta box on the edit post/page screen
	 *
	 * The following was created in response to PPT-2243
	 * https://penskemediacorp.atlassian.net/browse/PPT-2243
	 *
	 * Adds a checkbox to the VIP plugin: Add Meta Tags' 'SEO' meta box,
	 * which removes the page from being indexed by search engines.
	 * This is accomplished by removing the selected page from the sitemaps
	 * generated by the PMC plugin 'PMC Sitemaps', and by adding a
	 * 'noindex, follow' meta tag on the page.
	 *
	 * The mt_seo_fields filter is applied within the mt_seo_meta_box() function
	 * in the VIP plugin Add Meta Tags. The filter allows us to insert additional
	 * <input> fields within the plugin's meta box. HOWEVER, further down the code,
	 * when the input fields are output, a check is performed--not allowing any inputs
	 * of any type other than 'text' and <textarea>. As well, another check is performed
	 * which only allows a hardcoded list of form field names. That array of hardcoded
	 * names does not utilize apply_filters() -- hence there is no 'by the book'
	 * method to inject our own markup/form fields..
	 *
	 * Unless you consider the dark side--html dom injection via Javascript..
	 *
	 * @param  null
	 * @return null
	 */
	public function generate_exclude_post_checkbox() {
		global $post ;

		if ( empty( $post ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->parent_base !== 'edit' ) {
			return;
		}

		$exclude_meta = get_post_meta( $post->ID, '_mt_pmc_exclude_from_seo', true );

		// Enqueue the JavaScript to inject our form field markup
		// Into the Add Meta Tags 'SEO' meta box on the edit post screen
		wp_enqueue_script(
			$handle    = 'generate_exclude_post_checkbox',
			$src       = plugins_url( 'pmc-sitemaps-seo-meta-box.js', PMC_SITEMAPS_BASE_FILE ),
			$deps      = array('jquery'),
			$ver       = 1.0,
			$in_footer = true
		);

		// Grab the stored option if there was one
		// And make it available to our Javascript above
		$default_meta = apply_filters( 'pmc_sitemaps_' . sanitize_key( $post->post_type ) . '_exclude_from_seo_initial', 'off' );

		wp_localize_script(
			$handle      = 'generate_exclude_post_checkbox',
			$object_name = 'mt_pmc_exclude_from_seo',
			$l10n        = ( ! empty( $exclude_meta ) ) ? $exclude_meta : $default_meta // Cannot pass empty string to localize script. Ternary sets a default value.
		);

		// Enqueue our stylesheet (to adjust the display of the checkbox)
		wp_enqueue_style(
			$handle = 'generate_exclude_post_checkbox',
			$src    = plugins_url( 'pmc-sitemaps-seo-meta-box.css', PMC_SITEMAPS_BASE_FILE ),
			$deps   = array(),
			$ver    = 1.0,
			$media  = 'all'
		);

		//Output the markup we'll append to the 'SEO' meta box ?>
		<script type="text/template" id="exclude_post_checkbox">
			<div class="form-field mt_seo_meta mt_pmc_exclude_from_seo">
				<h4>
					<input type="checkbox" tabindex="5005" name="mt_pmc_exclude_from_seo" id="mt_pmc_exclude_from_seo">
					<label for="mt_pmc_exclude_from_seo"><?php _e( "Prevent search engines from indexing this post/page" ) ?></label>
				</h4>
				<div class="mt-form-field-contents">
					<p class="description"><?php _e( "When enabled, this post/page will not be seen by search engines. It will be removed from the website sitemap, and a noindex,follow meta tag will be output to the website header while viewing the post/page." ) ?></p>
				</div>
			</div>
		</script>
		<?php
	}// generate_exclude_post_checkbox

	/**
	 * Save the checkbox which 'Prevent search engines from indexing this post/page'
	 *
	 * @param  int   $post_id The ID of the post being edited
	 * @param  array $post    The PHP Superglobal $_POST containing the saved form data
	 * @param  bool  $update  True if we're updating the post (as opposed to creating new)
	 * @return null
	 */
	public function save_exclude_post_checkbox ( $post_id, $post, $update ) {
		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 *
		 * mt_seo_nonce is set via the VIP plugin: Add Meta Tags
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['mt_seo_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['mt_seo_nonce'], 'mt-seo' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		/* OK, its safe for us to save the data now. */

		$checkbox_value = "off";
		if ( isset( $_POST['mt_pmc_exclude_from_seo'] ) ) {
			// Sanitize user input.
			$checkbox_value = sanitize_text_field( $_POST['mt_pmc_exclude_from_seo'] );
		}

		// And finally save the user's chosen option
		update_post_meta( $post_id, '_mt_pmc_exclude_from_seo', $checkbox_value );
	}// save_exclude_post_checkbox

	/**
	 * Output a robots noindex,follow meta tag to wp_head when the post/page
	 * have the postmeta '_mt_pmc_exclude_from_seo' and it equals 'on'
	 *
	 * pmc-seo-tweaks adds <meta name="robots" content="noindex,follow" /> on is_search() pages
	 * So there may be duplicates output to the page. However, that's not bad
	 * and better safe than sorry.
	 *
	 * @param  null
	 * @return null
	 */
	function generate_noindex_follow_meta_tag () {
		// Make $post available within the scope of this function
		global $post ;

		if ( empty( $post ) ) {
			return;
		}

		// Select the _mt_pmc_exclude_from_seo post meta if it exists
		$exclude_from_seo   = get_post_meta( $post->ID, '_mt_pmc_exclude_from_seo', true );
		$canonical_override = get_post_meta( $post->ID, '_pmc_canonical_override', true );

		// Only output the noindex,follow tag if the postmeta == 'on'
		if ( 'on' === $exclude_from_seo && empty( $canonical_override ) ) {
		?>
		<meta name="robots" content="noindex, follow" />
		<?php
		}// end if
	}// generate_noindex_follow_meta_tag

	/**
	 * Add a Disallow rule to robots.txt for recent posts/pages with the
	 * _mt_pmc_exclude_from_seo postmeta. Noindex meta tags, as well as absence
	 * from sitemaps, will handle the remainder.
	 *
	 * Google requires that robots.txt be less than 500KiB.
	 *
	 * @param  string $output The contents of the robots.txt file
	 * @param  string $public '1' or '0' - Is the website is set to allow search engines (Settings > Reading)
	 * @return string $output The modified contents of the robots.txt file
	 */
	public function exclude_post_from_robots_text( $output, $public ): string {
		if ( ! $public ) {
			return $output;
		}

		$posts_excluded_from_seo = $this->get_excluded_post_ids( 50 );

		if ( empty( $posts_excluded_from_seo ) ) {
			return $output;
		}

		foreach ( $posts_excluded_from_seo as $id ) {
			$post_permalink = get_permalink( $id );
			$post_path      = wp_make_link_relative( $post_permalink );

			$output .= "Disallow: $post_path \n";
		}

		return $output;

	}// exclude_post_from_robots_text

	/**
	 * Retrieve IDs of posts to exclude from sitemaps.
	 *
	 * @param int $posts_per_page Quantity to return.
	 * @return array
	 * @throws ErrorException Invalid use of PMC_Cache.
	 */
	public function get_excluded_post_ids( int $posts_per_page = 20 ): array {
		$key = sprintf(
			'ids-%1$d-%2$s',
			$posts_per_page,
			$this->get_last_post_modified_time()
		);

		$cache = new PMC_Cache( $key, static::POST_TYPE . '-excludes' );
		$cache
			->expires_in( 300 )
			->on_failure_expiry_in( 60 )
			->updates_with(
				[ $this, 'get_excluded_post_ids_for_cache' ],
				[
					$posts_per_page,
				]
			);

		$ids = $cache->get();

		return is_array( $ids ) ? $ids : [];
	}

	/**
	 * Retrieve IDs of posts to exclude from sitemaps.
	 *
	 * For use with PMC_Cache, not for direct use.
	 *
	 * @param int $posts_per_page Quantity to return.
	 * @return array
	 */
	public function get_excluded_post_ids_for_cache( int $posts_per_page = 20 ): array {
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
		$ids = get_posts(
			[
				'post_type'        => 'any',
				'post_status'      => 'publish',
				'posts_per_page'   => $posts_per_page,
				'fields'           => 'ids',
				// Used as cache callback.
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_key'         => '_mt_pmc_exclude_from_seo',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'meta_value'       => 'on',
				'no_found_rows'    => true,
				'suppress_filters' => false,
			]
		);

		return $ids;
	}

	/**
	 * @since 2015-12-13 Amit Sannad
	 *        PMCVIP-665 Sitemap broken. When updating sitemap the meta data saved during insert was wrong,
	 * so gonna pass that while inserting rather then relying on private vars of this class.
	 *
	 * @param $post_data
	 *
	 * @return mixed
	 */
	protected function _get_meta_data() {

		$pmc_sitemaps_identity = array(
			'sitemap_name' => $this->_sitemap_name,
			'sitemap_type' => $this->_sitemap_type,
			'sitemap_n'    => $this->_sitemap_n,
		);

		return $pmc_sitemaps_identity;
	}

	/**
	 * Clear all of the caches for memory management
	 * @see WPCOM_VIP_CLI_Command::stop_the_insanity
	 */
	function stop_the_insanity() {
		/**
		 * @var \WP_Object_Cache $wp_object_cache
		 * @var \wpdb $wpdb
		 */
		global $wpdb, $wp_object_cache;

		$wpdb->queries = array(); // or define( 'WP_IMPORTING', true );

		if ( is_object( $wp_object_cache ) ) {
			$wp_object_cache->group_ops = array();
			$wp_object_cache->stats = array();
			$wp_object_cache->memcache_debug = array();
			$wp_object_cache->cache = array();

			if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
				$wp_object_cache->__remoteset(); // important
			}
		}
	}

	/**
	 * Helper function to debug and force cache clear.
	 * @return boolean True if debug is enabled and allowed
	 */
	function is_debug() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			// we won't be able to do any code coverage with constant WP_CLI define
			return true; // @codeCoverageIgnore
		}

		if ( ! isset( $_GET['debug'] ) ) {
			return false;
		}

		// we need to protect this debug with some secret to avoid abuse
		if ( $_GET['debug'] !== md5( date('Y-m') ) ) {
			return false;
		}

		// only enable debug if user logged in to avoid potential batcaching
		return is_user_logged_in();
	}

	// helper function to return correct url for the site map
	// Working issue where home_url return site url not the home url
	// @see https://wordpressvip.zendesk.com/hc/en-us/requests/65221
	function get_url( $path ) {
		$url = home_url( $path );

		$host     = wp_parse_url( $url, PHP_URL_HOST );
		$mappings = $this->get_url_mappings();

		if ( isset( $mappings[ '//' . $host ] ) ) { // @codingStandardsIgnoreLine Use of non secure or protocol relative URL '//' is not allowed unless explicitly stated.
			$url = str_replace( array_keys( (array) $mappings ), array_values( (array) $mappings ), $url );
		}

		return $url;
	}

	/**
	 * Temporary fix to return post url with 'http' scheme.
	 *
	 * @version 2017-05-02 CDWE-339
	 * @version 2017-07-03 Hau - Force full URL fix to match with home url
	 */
	function get_full_url( $post_url ) {

		$path = wp_parse_url( $post_url, PHP_URL_PATH );

		$post_url = $this->get_url( $path );

		if ( true === apply_filters( 'pmc_canonical_force_http', false ) ) {
			$post_url = str_replace( 'https://', 'http://', $post_url );
		} else {
			$post_url = str_replace( 'http://', 'https://', $post_url );
		}

		return $post_url;

	}

	/**
	 * VIP Hotfix
	 * Zendesk #65221
	 */
	function hotfix_pmc_sitemaps_loc( $url )  {

		$mappings = $this->get_url_mappings();

		if ( true === apply_filters( 'pmc_canonical_force_http', false ) ) {
			$mappings['https://'] = 'http://';
		} else {
			$mappings['http://'] = 'https://';
		}

		if ( false === strpos( $url, '.files.wordpress' ) && false === strpos( $url, '/pmc.com' ) ) {
			$url = str_replace( array_keys( (array) $mappings ), array_values( (array) $mappings ), $url );
		}

		return $url;
	}

	/**
	 * Adding keywords and image elements to each item in the xml.
	 * Ex url: https://www.rollingstone.com/news-sitemap.xml.
	 *
	 * @param $item_array
	 * @param $post_id
	 *
	 * @return array
	 */
	public function filter_jetpack_news_sitemap_item( $item_array, $post_id ) {
		if ( empty( (int) $post_id ) ) {
			return $item_array;
		}

		$key            = $post_id . '-jetpack_news_sitemap_item_keywords';
		$pmc_term_cache = new PMC_Cache( $key );

		$terms = $pmc_term_cache
			->expires_in( 900 )
			->updates_with( 'get_the_terms', array( $post_id, 'post_tag' ) )
			->get();

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) && is_array( $terms ) ) {

			$keywords = [];

			foreach ( $terms as $term ) {
				if ( is_a( $term, 'WP_Term' ) ) {
					$keywords[] = sanitize_text_field( $term->name );
				}
			}

			if ( ! empty( $keywords ) ) {
				$keywords = implode( ', ', $keywords );
				$item_array['url']['news:news']['news:keywords'] = html_entity_decode( ent2ncr( $keywords ), ENT_HTML5 );
			}
		}

		if ( current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $post_id ) ) {

			$post_thumbnail_id  = get_post_thumbnail_id( $post_id );
			$post_thumbnail_src = wp_get_attachment_image_src( $post_thumbnail_id );

			if ( $post_thumbnail_src ) {
				$item_array['url']['image:image'] = array( 'image:loc' => esc_url( $post_thumbnail_src[0] ) );
			}
		}

		return $item_array;
	}

	/**
	 * Check if the post has _mt_pmc_exclude_from_seo or _pmc_canonical_override option enabled to skip from the sitemap
	 *
	 * @param $is_skipped
	 * @param $post
	 *
	 * @return boolean $is_skipped
	 */
	public function filter_jetpack_sitemap_news_skip_post( $is_skipped, $post ) {
		$exclude_from_seo   = get_post_meta( $post->ID, '_mt_pmc_exclude_from_seo', true );
		$canonical_override = get_post_meta( $post->ID, '_pmc_canonical_override', true );

		if ( 'on' === $exclude_from_seo || ! empty( $canonical_override ) ) {
			$is_skipped = true;
		}

		return $is_skipped;
	}

	/**
	 * Check if the post has _pmc_canonical_override option enabled to exclude from the sitemap
	 *
	 * @param $is_exlcluded
	 * @param $post
	 *
	 * @return boolean $is_exlcluded
	 */
	public function sitemap_exclude_post( $is_exlcluded, $post ) {
		$canonical_override = get_post_meta( $post->ID, '_pmc_canonical_override', true );

		if ( ! empty( $canonical_override ) ) {
			$is_exlcluded = true;
		}

		return $is_exlcluded;
	}

	/**
	 * strip html tags from the titles for news sitemaps.
	 * assigns SEO title tag to $item['news:news']['news:title'].
	 *
	 * @param $item array
	 *
	 * @param $post object
	 *
	 * @return $item array
	 */
	public function update_news_sitemap_titles( $item, $post ) {

		if ( ! empty( $post ) && ! empty( $post->ID ) && ! empty( $item['news:news'] ) ) {

			$item['news:news']['news:title'] = ( ! empty( $item['news:news']['news:title'] ) ) ? $item['news:news']['news:title'] : '';

			$post_seo_title = get_post_meta( $post->ID, 'mt_seo_title', true );

			$title = empty( $post_seo_title ) ? $item['news:news']['news:title'] : $post_seo_title;

			$item['news:news']['news:title'] = wp_strip_all_tags( $title );
		}

		return $item;
	}

	/**
	 * To get an array of url mapping for sitemaps.
	 *
	 * @return array $mappings
	 */
	public function get_url_mappings() {

		$mappings = array(
			'//boygeniusreport.wordpress.com'  => '//bgr.com',
			'//pmchollywoodlife.wordpress.com' => '//hollywoodlife.com',
			'//pmcvariety.wordpress.com'       => '//variety.com',
			'//pmcvarietylatino.wordpress.com' => '//varietylatino.com',
			'//pmctvline2.wordpress.com'       => '//tvline.com',
			'//pmcdeadline2.wordpress.com'     => '//deadline.com',
			'//pmcspy.wordpress.com'           => '//spy.com',
			'//pmcfootwearnews.wordpress.com'  => '//footwearnews.com',
			'//pmcwwd.wordpress.com'           => '//wwd.com',
			'//robbreportedit.wordpress.com'   => '//robbreport.com',
		);

		$mappings = apply_filters( 'pmc_sitemaps_url_mappings', $mappings );

		return $mappings;
	}

	/**
	 * Generate Sitemap XML markup.
	 *
	 * @param string $sitemap_type type of sitemap for which urlset to generate.
	 * @param int    $post_id      sitemap post id.
	 *
	 * @return string
	 */
	public function get_sitemap_xml( $sitemap_type, $post_id = 0 ) {

		$urlset_generator_method = '_generate_' . $sitemap_type . '_urlset';

		if ( ! method_exists( $this, $urlset_generator_method ) ) {
			return '';
		}

		$urlset = call_user_func_array( [ $this, $urlset_generator_method ], [ $post_id ] );

		if ( empty( $urlset ) ) {
			return '';
		}

		// Piece together the sitemap XML.
		switch ( $sitemap_type ) {
			case 'index':
				$sitemap_xml = '{{sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"}}';
				break;

			default:
				$sitemap_xml = '{{urlset
				xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
				xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
				xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
				xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"}}';
				break;
		}

		$sitemap_xml .= $urlset;

		switch ( $sitemap_type ) {
			case 'index':
				$sitemap_xml .= '{{/sitemapindex}}';
				break;

			default:
				$sitemap_xml .= '{{/urlset}}';
				break;
		}

		return $sitemap_xml;
	}

	/**
	 * Generate sitemap post object data to before insert or update.
	 *
	 * @param string $action      operation name e.g insert/update.
	 * @param string $sitemap_xml sitemap updated xml markup.
	 * @param int    $post_id     sitemap post id.
	 *
	 * @return array
	 */
	public function generate_sitemap_post_data( $action, $sitemap_xml, $post_id = 0 ) {

		$current_date     = current_time( 'mysql', 0 );
		$current_date_gmt = current_time( 'mysql', 1 );

		// Populate the array for saving the sitemap post.
		$sitemap_content = [
			'post_content'      => $sitemap_xml,
			'post_modified'     => $current_date,
			'post_modified_gmt' => $current_date_gmt,
		];

		// Additional info when inserting.
		if ( 'insert' === $action ) {
			$sitemap_content['post_title']    = $this->get_sitemap_name();
			$sitemap_content['post_date']     = $current_date;
			$sitemap_content['post_date_gmt'] = $current_date_gmt;
		} elseif ( 'update' === $action && 0 !== $post_id ) {
			$sitemap_content['ID'] = $post_id;
		}

		return $sitemap_content;
	}

}

//EOF
