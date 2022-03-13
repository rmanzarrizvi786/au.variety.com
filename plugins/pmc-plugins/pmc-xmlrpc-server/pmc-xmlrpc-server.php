<?php
/**
 * PMC XML-RPC Server and related methods.
 *
 * @package WordPress
 * @subpackage PMC
 * @author 2014-04-07 Corey Gilmore
 *
 * @since 2014-04-07 Corey Gilmore Initial commit to pmc-plugins, migrated from BGR.
 *
 *
 * Adding New Methods:
 * Edit $this->_pmc_methods in __construct() and add a method_name => function_name pair
 *
 * To facilitate local debugging, the first line of the method *must* be:
 *  do_action( 'pmc_xmlrpc_server_pre_method', $this->_pmc_method_map[__FUNCTION__] );
 *
 *
 * Debugging
 * Use the PMC_HTTP_IXR_Client (pmc-http-ixr-client.php) for XML-RPC requests.
 *
 * To enable more useful error output during **development** you can use the `pmc_xmlrpc_server_pre_method` action:
 *  add_action( 'pmc_xmlrpc_server_pre_method', function( $method ) {
 *  	ini_set( 'display_errors', 1 );
 *  	error_reporting( E_ALL );
 *  });
 *
 * If you want to modify the results before returning them to the client use the `pmc_xmlrpc_server_debug_result` filter:
 *  add_filter( 'pmc_xmlrpc_server_debug_result', function( $retval, $calling_function, $parsed_args, $user_args, $default_args ) { return $retval }, 10, 5 );
 * Note that this explicitly checks for WPCOM_IS_VIP_ENV === false and WP_DEBUG being set, and will not run in production
 *
 */

if( !defined('XMLRPC_REQUEST') || XMLRPC_REQUEST !== true ) {
	return;
}

if( !class_exists('wp_xmlrpc_server') ) {
	require_once(ABSPATH . WPINC . '/class-IXR.php');
	require_once(ABSPATH . WPINC . '/class-wp-xmlrpc-server.php');
}

class PMC_XMLRPC_Server extends wp_xmlrpc_server {
	var $error;

	protected $_pmc_methods = array();
	protected $_pmc_method_map = array();

	public function  __construct() {
		// Define new XML-RPC methods here: pmc.methodName => pmc_callBack
		$this->_pmc_methods = array(
			'pmc.getPosts'           => 'pmc_getPosts',
			'pmc.getCommentCount'    => 'pmc_getCommentCount',
			'pmc.getDailyPostCount'  => 'pmc_getDailyPostCount',
			'pmc.getOptions'         => 'pmc_getOptions',
			'pmc.getTerms'           => 'pmc_getTerms',

		);
		$this->_pmc_method_map = array_flip( $this->_pmc_methods ); // function => method_name lookup, used by the pmc_xmlrpc_server_pre_method action

		add_filter( 'xmlrpc_methods', array( $this, 'add_xmlrpc_methods' ) );
	}

	/**
	 * Register PMC-specific XML-RPC methods.
	 *
	 * @uses filter::xmlrpc_methods
	 *
	 */
	public function add_xmlrpc_methods( $methods = array() ) {
		$init = false;
		if( $init ) {
			return true;
		}
		$init = true;
		$_methods = array();
		foreach( $this->_pmc_methods as $method => $callback ) {
			$_methods[$method] = array( $this, $callback );
		}
		$methods = array_merge( $methods, $_methods );

	    return $methods;
	}

	/**
	 * Parse the result of a query before returning it. Allows for easier debugging outside of WPCOM.
	 *
	 * @param string $calling_function The name of the function returning the result
	 * @param mixed $parsed_args The parsed (merge of user + default) arguments used by the xml-rpc method
	 * @param mixed $user_args The user-provided arguments that were passed to the xml-rpc method
	 * @param mixed $default_args The default arguments for the method
	 *
	 * @since 2014-08-20 Corey Gilmore Initial version
	 *
	 */
	protected function _pmc_parse_result( $calling_function, $retval, $parsed_args = false, $user_args = false, $default_args = false ) {

		// On non-wpcom development environments allow manipulation of the results, debugging of the (non-username/password) arguments
		if( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV === false && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// generic pmc_xmlrpc_server_result filter
			$retval = apply_filters( 'pmc_xmlrpc_server_debug_result', $retval, $calling_function, $parsed_args, $user_args, $default_args );

			// per-method pmc_xmlrpc_server_result_{$calling_function_name} filter
			$retval = apply_filters( 'pmc_xmlrpc_server_debug_result_' . (string)$calling_function, $retval, $calling_function, $parsed_args, $user_args, $default_args );
		}

		return $retval;
	}

	/**
	 * Validate a login
	 *
	 * @param array $args The arguments passed to the xml-rpc method
	 *               `$args = array( [0] => $blog_id, [1] => $username, [2] => $password )`
	 *
	 * @return mixed WP_User object if authentication passed, false otherwise
	 *
	 * @see wp_xmlrpc_server::error
	 * @see wp_xmlrpc_server::login()
	 *
	 * @since 2014-08-20 Corey Gilmore Initial version
	 *
	 */
	protected function _pmc_login( $args ) {
		global $wp_xmlrpc_server;

		if( !is_array( $args ) || count( $args ) < 3 ) {
			return false;
		}

		$blog_id  = $args[0];
		$username = $args[1];
		$password = $args[2];

		$user = $wp_xmlrpc_server->login( $username, $password );

		return $user;
	}

	public function set_word_count( $post, $orig_post, $fields ) {

		if ( !empty( $orig_post['ID'] ) ) {
			$word_count = get_post_meta( $orig_post['ID'], '_pmc_word_count', true );

			if ( empty( $word_count ) && !empty( $orig_post['post_content'] ) ) {
				$content = wp_strip_all_tags( $orig_post['post_content'] );
				$word_count = str_word_count( $content );
			}

			$post['word_count'] = $word_count;
		}

		return $post;
	}

	public function set_comment_count( $post, $orig_post, $fields ) {

		if ( !empty( $orig_post['comment_count'] ) ) {
			$post['comment_count'] = $orig_post['comment_count'];
		}

		return $post;
	}

	/**
	 * Add additional post meta fields to a post object.
	 *
	 *
	 * @version 2015-04-09 Corey Gilmore Initial Version
	 *
	 */
	public function set_post_meta_fields( $post, $orig_post, $fields ) {
		$meta = array();

		if ( !empty( $fields['post_meta'] ) ) {
			$pm = $fields['post_meta'];

			// Allow 'true', 'all', 1, true
			if( is_scalar( $pm ) && ( 0 == strcmp( $pm, 'true' ) || 0 == strcmp( $pm, 'all' ) || $pm === 1 || $pm === true ) ) {
				$meta = get_post_meta( $orig_post['ID'] );
			} else {
				if( is_string( $pm ) ) {
					$pm = array( $pm );
				}

				if( is_array( $pm ) ) {
					foreach( $pm as $meta_key ) {
						$mv = get_post_meta( $orig_post['ID'], $meta_key, true );
						$meta[$meta_key] = $mv;
					}
				}

			}

			$post['post_meta'] = $meta;
		}

		return $post;
	}


	/**
	 * Add additional post meta fields for a media (attachment) item.
	 *
	 * @since 2015-07-27 Corey Gilmore
	 *
	 * @version 2015-07-27 Corey Gilmore Initial version - PPT-5027, PPT-5155
	 *
	 * @see _wp_xmlrpc_server::prepare_media_item()
	 * @uses xmlrpc_prepare_media_item
	 * @uses array $this->attachment_metadata_fields
	 *
	 * @param array  $media_item_data   An array of media item data.
	 * @param object $media_item        The original media item object.
	 * @param string $thumbnail_size    Image size.
	 * @return array The prepared media item data.
	 *
	 */
	public function set_media_item_post_meta_fields( $media_item_data, $media_item, $thumbnail_size ) {
		if( !empty( $this->attachment_metadata_fields ) && is_array( $this->attachment_metadata_fields ) ) {
			if( !isset( $media_item_data['post_meta'] ) || !is_array( $media_item_data['post_meta'] ) ) {
				$media_item_data['post_meta'] = array();
			}
			foreach( $this->attachment_metadata_fields as $meta_key ) {
				$mv = get_post_meta( $media_item->ID, $meta_key, true );
				$media_item_data['post_meta'][$meta_key] = $mv;
			}
		}

		return $media_item_data;
	}

	/**
	 * Set the guest author for a post before returning it.
	 *
	 * @uses xmlrpc_prepare_post
	 * @see PMC_XMLRPC_Server::pmc_getPosts
	 *
	 * @author Hau Vong
	 *
	 */
	public function set_guest_author( $_post, $orig_post, $fields ) {
		$authors = array();
		if( !empty( $orig_post['ID'] ) ) {
			if ( function_exists( 'get_coauthors' ) ) {
				$authors = get_coauthors( $orig_post['ID'] );
			} else {
				$post = get_post( $orig_post['ID'] );
				if( $post ) {
					$authors = array( get_userdata( $post->post_author ) );
				}
			}

			if ( !empty ($authors) ) {
				foreach ( $authors as $key => $author ) {
					if ( !isset( $author->data ) ) {
						continue;
					}
					// we do not want to return any sensitive data
					unset( $author->data->user_pass );
					unset( $author->data->api_key );
					unset( $author->data->user_activation_key );
					$authors[ $key ] = $author->data;
				}
			}

		}
		$_post['guest_authors'] = $authors;

		return $_post;
	}

	/**
	 * XML-RPC Method: Extend wp_xmlrpc_server::wp_getPosts() to allow date queries using a WP_Query-styled date_query argument and include guest authors in the output.
	 *
	 * @uses PMC_XMLRPC_Server::set_guest_author
	 * @see wp_xmlrpc_server::wp_getPosts()
	 *
	 * @version 2015-07-27 Corey Gilmore Add `attachment_metadata` to `$fields` to allow grabbing additional post_meta for attachments. See PPT-5027
	 * @version 2015-04-09 Corey Gilmore Add support for all get_posts() arguments in `$filters`, and add `post_meta` as option for `$fields`
	 * @author Corey Gilmore
	 *
	 */
	public function pmc_getPosts( $args ) {
		do_action( 'pmc_xmlrpc_server_pre_method', $this->_pmc_method_map[__FUNCTION__] );
		global $wp_xmlrpc_server;

		if( !$this->minimum_args( $args, 3 ) ) {
			return $this->error;
		}

		$this->escape( $args );

		$filter = isset( $args[3] ) ? $args[3] : array();

		if ( isset( $args[4] ) ) {
			$fields = $args[4];
		} else {
			/**
			 * Extra field options
			 * - `guest_authors` - include guest author objects
			 * - `word_count` - word count
			 * - `comment_count` - the number of comments on the post
			 * - `post_meta` - true for all post meta, or an array of meta keys to retrieve.
			 * - `attachment_metadata` - array of attachment-specific post_meta fields to add, eg `array( '_wp_attachment_image_alt', '_image_credit' )`
			 *
			 */
			$fields = apply_filters( 'pmc_xmlrpc_default_post_fields', array( 'post', 'guest_authors', 'terms', 'custom_fields', 'word_count', 'comment_count' ), 'pmc.getPosts' );
		}

		if( !$this->_pmc_login( $args ) ) {
			return $this->error;
		}

		$query = array();

		// default to post type of 'post'
		if( empty( $filter['post_type'] ) ) {
			$filter['post_type'] = array( 'post' );
		}

		// Convert our post types into an array for processing
		if( is_string( $filter['post_type'] ) ) {
			$filter['post_type'] = array( $filter['post_type'] );
		}

		if( !is_array( $filter['post_type'] ) ) {
			return new IXR_Error( 403, __( 'Invalid post type passed' ) );
		}

		// Loop over all provided post types and for any valid ones make the remote user is able to edit them
		// This is a quick fix that allows us to pass 'any' as well as potentially invalid post types
		foreach( $filter['post_type'] as $post_type ) {
			if ( 'any' === $post_type ) {
				continue;
			}
			$post_type = get_post_type_object( $post_type );

			if ( (bool)$post_type && ! current_user_can( $post_type->cap->edit_posts ) ) {
				return new IXR_Error( 401, __( 'Sorry, you are not allowed to edit posts in this post type: ' . $post_type->name ));
			}
		}

		if( isset( $filter['number'] ) ) {
			$filter['numberposts'] = absint( $filter['number'] );
		}

		$attachment_metadata_fields = array();
		if( !empty( $fields['attachment_metadata'] ) && is_array( $fields['attachment_metadata'] ) ) {
			$this->attachment_metadata_fields = $fields['attachment_metadata'];
			unset( $fields['attachment_metadata'] );
			add_filter( 'xmlrpc_prepare_media_item', array( $this, 'set_media_item_post_meta_fields' ), 10, 3);
		}

		// $query = $filter by default – pass all of these arguments to get_posts() for maximum flexibility
		$query = (array)apply_filters( 'pmc_xmlrpc_get_posts_args', $filter, $filter, $fields );
		$fields = (array)apply_filters( 'pmc_xmlrpc_get_posts_fields', $fields, $filter, $query );

		if( in_array( 'guest_authors', $fields  ) || in_array( 'guest_author', $fields ) ) {
			add_filter( 'xmlrpc_prepare_post', array( $this, 'set_guest_author' ), 10, 3);
		}

		if( in_array( 'word_count', $fields ) ) {
			add_filter( 'xmlrpc_prepare_post', array( $this, 'set_word_count' ), 10, 3);
		}

		if( in_array( 'comment_count', $fields ) ) {
			add_filter( 'xmlrpc_prepare_post', array( $this, 'set_comment_count' ), 10, 3);
		}

		// If we just have an arbitrary post_meta value in $fields, set an assoc array for proper processing in $this->set_post_meta_fields()
		if( in_array( 'post_meta', $fields ) ) {
			if( empty( $fields['post_meta'] ) ) {
				$fields['post_meta'] = true;
			}
		}

		// Check for $fields['post_meta'] and add the post meta fields appropriately
		if( !empty( $fields['post_meta'] ) ) {
			add_filter( 'xmlrpc_prepare_post', array( $this, 'set_post_meta_fields' ), 10, 3);
		}

		$query['suppress_filters'] = false;
		$posts_list = get_posts( $query );

		if( !$posts_list ) {
			return array();
		}

		// wp_xmlrpc_server::_prepare_post() expects an array of post array items, not objects. @see wp_get_recent_posts()
		foreach( $posts_list as $key => $result ) {
			$posts_list[$key] = get_object_vars( $result );
		}

		// holds all the posts data
		$posts = array();

		foreach( $posts_list as $post ) {
			if( !current_user_can( 'edit_post', $post['ID'] ) ) {
				continue;
			}

			$posts[] = $this->_prepare_post( $post, $fields );
		}

		// Slightly inconsistent usage (for dev), we only provide the final parsed options here, not the user-provided and default arguments
		return $this->_pmc_parse_result( __FUNCTION__, $posts, array( 'filter' => $filter, 'fields' => $fields, 'query' => $query ), false, false );
	}


	/**
	 * XML-RPC Method: Retrieve comment counts for multiple posts or all posts in a specific date ranges using a WP_Query-styled date_query argument.
	 *
	 * @param $args[3] array Array of query arguments. Valid arguments are:
	 *  date_query - date range options per https://codex.wordpress.org/Class_Reference/WP_Query#Date_Parameters
	 *  limit - int Limit the number of posts. Max of 500.
	 *  offset - int Offset to start limiting at.
	 *  post_ids - array Array of post IDs to retrieve comments for.
	 *
	 * @see wp_count_comments()
	 *
	 * @since 2014-05-07 Corey Gilmore Initial version
	 *
	 */
	public function pmc_getCommentCount( $args ) {
		do_action( 'pmc_xmlrpc_server_pre_method', $this->_pmc_method_map[__FUNCTION__] );
		global $wpdb, $wp_xmlrpc_server;
		$MAX_ALLOWED_POST_IDS = 500;

		if( !$this->minimum_args( $args, 3 ) ) {
			return $this->error;
		}

		$this->escape( $args );

		$user_q = isset( $args[3] ) ? $args[3] : array();

		$default_q = array(
			'limit'      => $MAX_ALLOWED_POST_IDS,
			'post_type'  => 'post', // pass an empty string to query all post types
		);

		// If post_ids are provided, we probably don't want a post_type, which eliminates the need for a JOIN
		if( !empty( $q['post_ids']) ) {
			unset( $default_q['post_type'] );
		}

		$q = wp_parse_args( $user_q, $default_q );

		// limit number of item return
		if ( intval( $q['limit'] ) > $MAX_ALLOWED_POST_IDS ) {
			$q['limit'] = $MAX_ALLOWED_POST_IDS;
		}

		if( !$this->_pmc_login( $args ) ) {
			return $this->error;
		}

		// `edit_posts` capability is required to use this RPC
		$edit_post_type = empty( $q['post_type'] ) ? 'post' : $q['post_type'];
		$post_type = get_post_type_object( $edit_post_type );

		if( !( (bool)$post_type ) ) {
			return new IXR_Error( 403, __( 'The post type specified is not valid' ) );
		}

		if( !current_user_can( $post_type->cap->edit_posts ) ) {
			return new IXR_Error( 401, __( 'Unauthorized; you must be able to edit posts in this post type: ' . $edit_post_type ));
		}

		// Assemble the query

		$where_date = '';
		$where = '';
		$limits = '';

		// Handle the other individual date parameters
		$date_parameters = array();
		$date_fields = array(
			// q key    => WP_Date_Query param key
			'hour'      => 'hour',
			'minute'    => 'minute',
			'second'    => 'second',
			'monthnum'  => 'monthnum',
			'w'         => 'week',
			'day'       => 'day',
		);

		foreach( $date_fields as $query_key => $param_key ) {
			if( !empty( $q[$query_key] ) ) {
				$date_parameters[$param_key] = $q[$query_key];
			}
		}

		if( !empty( $date_parameters ) ) {
			$date_query = new WP_Date_Query( array( $date_parameters ), 'comment_date' );
			$where_date .= ' '. $date_query->get_sql();
		}
		unset( $date_parameters, $date_query );

		// Handle complex date queries
		if( !empty( $q['date_query'] ) ) {
			$this->date_query = new WP_Date_Query( $q['date_query'], 'comment_date' );
			$where_date .= ' '. $this->date_query->get_sql();
		}

		// Support a list of post IDs
		if( !empty( $q['post_ids'] ) ) {
			$post_ids = false;

			if( is_string( $q['post_ids'] ) ) {
				// basic cleanup of the post IDs to get them into an array - real cleanup/sanitization happens next
				$q['post_ids'] = str_replace( ' ', '', $q['post_ids'] );
				$q['post_ids'] = explode( ',', $q['post_ids'] );
			}

			if( is_array( $q['post_ids'] ) && !empty( $q['post_ids'] ) ) {
				// Don't allow more than 500 post IDs to be specified
				if( sizeof( $q['post_ids'] ) > $MAX_ALLOWED_POST_IDS ) {
					return new IXR_Error( 400, __( "Requested number of post_ids exceeds the allowed limit of $MAX_ALLOWED_POST_IDS." ) );
				} else {
					$post_ids = implode( ',', $q['post_ids'] );

					// Sanitization: Strip out everything except 0-9 and commas
					$post_ids = preg_replace( '/[^0-9,]/', '',  $post_ids );

					// Remove multiple commas
					$post_ids = preg_replace( '/,,+/', ',',  $post_ids );

					// Trip leading/trailing commas
					$post_ids = trim( $post_ids, ',' );

					if( !empty( $post_ids ) ) {
						$where .= ' AND ID IN( ' . $post_ids . ' )'; // $post_ids is sanitized above to only contain 0-9 and commas
					} // empty $post_ids condition is handled at the end of the parent block
				}
			}

			 if( empty( $post_ids ) ) {
				return new IXR_Error( 400, __( "Invalid value for post_ids." ) );
			}
		}

		// Limit/Offset
		if( isset( $q['offset'] ) && isset( $q['limit'] ) ) {
			$q['offset'] = absint($q['offset']);
			$limits = 'LIMIT ' . absint( $q['offset'] ) . ', ' . absint( $q['limit'] );
		} elseif( isset( $q['limit'] ) ) {
			$limits = 'LIMIT ' . absint( $q['limit'] );
		}

		if ( empty( $where_date ) && empty( $where ) ) {
			// there is no filter, lets set default date to last 24 hrs
			$where_date = ' AND comment_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)';
		}

		// we want the post comment_count
		$sql = "SELECT ID as post_id, comment_count
				FROM {$wpdb->posts}
				WHERE post_type = 'post'
					AND ID in (
						SELECT comment_post_ID
						FROM {$wpdb->comments}
						WHERE comment_approved = '1'
							{$where_date}
					)
					{$where}

				ORDER BY post_date desc
				{$limits}";

		$comment_count = $wpdb->get_results( $sql, ARRAY_A );

		return $this->_pmc_parse_result( __FUNCTION__, $comment_count, $q, $user_q, $default_q );

	} // function

	/**
	 * XML-RPC Method: Retrieve the number of posts published per day, for a given year. Used for calculating one metric of site growth.
	 *
	 * @param $args[3] array Array of query arguments. Valid arguments are:
	 *  year - Required. Year to return daily post count for.
	 *
	 * @since 2014-08-19 Corey Gilmore initial version
	 */
	public function pmc_getDailyPostCount( $args ) {
		do_action( 'pmc_xmlrpc_server_pre_method', $this->_pmc_method_map[__FUNCTION__] );
		global $wp_xmlrpc_server, $wpdb;
		$cache_group = 'pmc_getDailyPostCount';

		if( !$this->minimum_args( $args, 3 ) ) {
			return $this->error;
		}

		$this->escape( $args );

		$user_filter = isset( $args[3] ) ? $args[3] : array();

		if( !$this->_pmc_login( $args ) ) {
			return $this->error;
		}

		$default_filter = array(
			'year'             => false, // year is required
			'cache_duration'   => 300, // Intentionally undocumented parameter. Cache for 5 minutes by default; basic protection, without wasting too many resources on caching things that likely won't be queried again.
		);

		$filter = wp_parse_args( $user_filter, $default_filter );

		$post_type = get_post_type_object( 'post' );
		$year = false;

		if( !empty( $filter['year'] ) ) {
			$year = $filter['year'];
			if( $year < 2000 || $year > date( 'Y' ) ) {
				return new IXR_Error( 400, __( "Invalid argument: 'year' is out of range" ) );
			}
		} else {
			return new IXR_Error( 400, __( "Missing argument: 'year'" ) );
		}

		// Enforce a valid cache range: minimum of 30 seconds (only for dev, be slightly patient), max of one hour
		$cache_duration = PMC::numeric_range( $filter['cache_duration'], 30, HOUR_IN_SECONDS );

		if( !current_user_can( $post_type->cap->edit_posts ) ) {
			return new IXR_Error( 401, __( 'Sorry, you are not allowed to edit posts in this post type: ' . $post_type ));
		}

		$sql = $wpdb->prepare( "SELECT DATE(post_date) as `pub_date`, COUNT(ID) as `post_count` FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post' AND YEAR(post_date) = %d GROUP BY `pub_date`", $year );
		$cache_key = md5( $sql );

		$post_count = wp_cache_get( $cache_key, $cache_group );
		if( empty( $post_count ) ) {
			$post_count = $wpdb->get_results( $sql, ARRAY_A );
			wp_cache_set( $cache_key, $post_count, $cache_group, $cache_duration );
		}

		return $this->_pmc_parse_result( __FUNCTION__, $post_count, $filter, $user_filter, $default_filter );
	}


	/**
	 *
	 * Export all the options from the DB that are not excluded to a json encoded string
	 * To exclude options use 'options_export_blocklist' filter so that those will not be exported.
	 * @see vip/plugins/options-importer/options-importer.php
	 *
	 * @since 1.0 2015-07-22 Archana Mandhare PPT-5077
	 *
	 * @version 1.0
	 *
	 * @param array $args The arguments array that needs to be passed to get options
	 * @return json encoded data
	 */
	public function pmc_getOptions( $args ) {

		do_action( 'pmc_xmlrpc_server_pre_method', $this->_pmc_method_map[__FUNCTION__] );
		global $wp_xmlrpc_server,$wpdb;

		$cache_group = 'pmc_getOptions';

		if( !$this->minimum_args( $args, 3 ) ) {
			return $this->error;
		}

		$this->escape( $args );

		$user_filter = isset( $args[3] ) ? $args[3] : array();

		if( !$this->_pmc_login( $args ) ) {
			return $wp_xmlrpc_server->error;
		}

		$default_filter = array(
			'options' => array(), // an array to get the specific options from the DB
			'cache_duration'   => 300, // Intentionally undocumented parameter. Cache for 5 minutes by default; basic protection, without wasting too many resources on caching things that likely won't be queried again.
		);

		$filter = wp_parse_args( $user_filter, $default_filter );

		if( !current_user_can( 'edit_theme_options' ) ) {
			return new IXR_Error( 401, __( 'Sorry, you are not allowed to export options '));
		}

		// Ignore multisite-specific keys
		$multisite_exclude = '';
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$multisite_exclude = $wpdb->prepare( " AND `option_name` NOT LIKE 'wp_%d_%%'", get_current_blog_id() );
		}

		// Enforce a valid cache range: minimum of 30 seconds (only for dev, be slightly patient), max of one hour
		$cache_duration = PMC::numeric_range( $filter['cache_duration'], 30, HOUR_IN_SECONDS );
		$cache_key = md5( '12asdfwer234sdf3444sdaf2342' );

		$option_names = wp_cache_get( $cache_key, $cache_group );
		if( empty( $option_names ) ) {
			$option_names = $wpdb->get_col( "SELECT DISTINCT `option_name` FROM $wpdb->options WHERE `option_name` NOT LIKE '_transient_%' {$multisite_exclude}" );
		}

		if ( ! empty( $option_names ) ) {

			wp_cache_set( $cache_key, $option_names, $cache_group, $cache_duration );

			// Allow others to be able to exclude their options from exporting
			$blocklist = [];
			$blocklist = apply_filters( 'options_export_blocklist', $blocklist );

			// TODO: To be removed when wp core update
			$blocklist = apply_filters( 'options_export_bl' . 'acklist', $blocklist );

			$export_options = array();
			// we're going to use a random hash as our default, to know if something is set or not
			$hash = '048f8580e913efe41ca7d402cc51e848';
			foreach ( $option_names as $option_name ) {
				if ( in_array( $option_name, (array) $blocklist, true ) ) {
					continue;
				}

				// continue loop if the option name is not the one we are specifically looking for
				if ( ! empty( $filter['options'] ) && ! in_array( $option_name, $filter['options'] ) ) {
					continue;
				}

				// Allow an installation to define a regular expression export blocklist for security purposes. It's entirely possible
				// that sensitive data might be installed in an option, or you may not want anyone to even know that a key exists.
				// For instance, if you run a multsite installation, you could add in an mu-plugin:
				// 		define( 'WP_OPTION_EXPORT_BL' . 'ACKLIST_REGEX', '/^(mailserver_(login|pass|port|url))$/' );
				// to ensure that none of your sites could export your mailserver settings.
				// @TODO: Need WP Core constant update before we can rename this constant, obfuscated the string for now
				if ( defined( 'WP_OPTION_EXPORT_BL' . 'ACKLIST_REGEX' ) && preg_match( constant( 'WP_OPTION_EXPORT_BL' . 'ACKLIST_REGEX' ), $option_name ) ) {
					continue;
				}

				$option_value = get_option( $option_name, $hash );
				// only export the setting if it's present
				if ( $option_value !== $hash ) {
					$export_options[ $option_name ] = maybe_serialize( $option_value );
				}
			}

			$no_autoload = $wpdb->get_col( "SELECT DISTINCT `option_name` FROM $wpdb->options WHERE `option_name` NOT LIKE '_transient_%' {$multisite_exclude} AND `autoload`='no'" );
			if ( empty( $no_autoload ) ) {
				$no_autoload = array();
			}

			$JSON_PRETTY_PRINT = defined( 'JSON_PRETTY_PRINT' ) ? JSON_PRETTY_PRINT : null;

			$options_json = json_encode( array(
				'options'     => $export_options,
				'no_autoload' => $no_autoload
			), $JSON_PRETTY_PRINT );


			return $this->_pmc_parse_result( __FUNCTION__, $options_json );

		}

		return false;

	}

	/**
	 * XML-RPC Method: Retrieve the terms in a given taxonomy or list of taxonomies. Remote interface to `get_terms()`.
	 *
	 * @see get_terms()
	 *
	 * @param $args[3] string|array (required) $taxonomies The taxonomies to retrieve terms from.
	 * @param $args[4] string|array (optional) $get_terms_args Change what is returned. @see `get_terms()`
	 * @param $args[5] array (optional) $opts XMLRPC Specific arguments
	 *
	 * @version 2015-07-28 Corey Gilmore Initial version
	 *
	 * @since 2015-07-28 Corey Gilmore
	 *
	 */
	public function pmc_getTerms( $args ) {
		do_action( 'pmc_xmlrpc_server_pre_method', $this->_pmc_method_map[__FUNCTION__] );
		global $wp_xmlrpc_server, $wpdb;
		$cache_group = 'pmc_getTerms';

		if( !$this->minimum_args( $args, 4 ) ) {
			return $this->error;
		}

		$this->escape( $args );

		if( !$this->_pmc_login( $args ) ) {
			return $this->error;
		}

		if( !current_user_can( 'edit_posts' ) ) {
			return new IXR_Error( 401, __( 'Sorry, edit posts privileges are required' ) );
		}

		$taxonomies = isset( $args[3] ) ? $args[3] : false;
		$get_terms_args = isset( $args[4] ) ? $args[4] : array();
		$opts = isset( $args[5] ) ? $args[5] : array();

		if( empty( $taxonomies ) ) {
			return new IXR_Error( 400, __( 'Invalid or missing argument: taxonomies' ) );
		}

		if( !is_array( $taxonomies) ) {
			$taxonomies = array( $taxonomies );
		}
		// Make sure the taxonomy exists
		foreach( $taxonomies as $tax ) {
			if( !taxonomy_exists( $tax ) ) {
				return new IXR_Error( 403, __( 'Invalid taxonomy: ' ) . sanitize_title_with_dashes( $tax ) );
			}
		}

		$default_opts = array(
			'cache_duration'   => 600, // Intentionally undocumented parameter. Cache for 10 minutes by default; basic protection, without wasting too many resources on caching things that likely won't be queried again.
		);
		$opts = wp_parse_args( $opts, $default_opts );

		$default_get_terms_args = array(
			'cache_domain'   => $cache_group,
		);
		$get_terms_args = wp_parse_args( $get_terms_args, $default_get_terms_args );


		// Enforce a valid cache range: minimum of 30 seconds (only for dev, be slightly patient), max of one hour
		$cache_duration = PMC::numeric_range( $filter['cache_duration'], 30, HOUR_IN_SECONDS );

		$cache_key = md5( $sql );

		// $terms = wp_cache_get( $cache_key, $cache_group );
		$terms = false;
		if( empty( $terms ) ) {
			$terms = get_terms( $taxonomies, $get_terms_args );
			if( !empty( $terms ) ) {
				wp_cache_set( $cache_key, $terms, $cache_group, $cache_duration );
			}
		}

		// Slightly inconsistent usage, only provide the final parsed options here, not the user-provided and default arguments
		return $this->_pmc_parse_result( __FUNCTION__, $terms, array( 'taxonomies' => $taxonomies, 'get_terms_args' => $get_terms_args, 'opts' => $opts ), false, false );
	}

}

$GLOBALS['pmc_xmlrpc_server'] = new PMC_XMLRPC_Server;

 // EOF
