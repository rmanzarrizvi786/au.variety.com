<?php
/**
 * Class contains functions that deals with the Google Indexing API functions
 *
 */

namespace PMC\Google_Breaking_News;

use PMC;
use \PMC_Cheezcap;
use \PMC\Global_Functions\Traits\Singleton;


class Plugin {

	use Singleton;

	const PRIVATE_KEY     = 'pmc_api_private_key';
	const CLIENT_EMAIL    = 'pmc_client_email';
	const INDEXING_NONCE  = 'pmc_gbreaking_news';
	const STATUS_META_KEY = '_pmc_gnews_indexing';


	/**
	 * Registering all hooks in the _init function
	 */
	protected function __construct() {

		add_action( 'admin_menu', array( $this, 'admin_page' ) );
		add_action( 'save_post', array( $this, 'init_google_content_indexing' ), 11, 2 );
		add_action( 'post_submitbox_start', array( $this, 'action_post_submitbox_start' ), 10, 0 );
		add_action( 'quick_edit_custom_box', array( $this, 'action_post_submitbox_start' ), 10, 2 );

		/**
		 * Filters.
		 */
		add_filter( 'pmc_cheezcap_groups', array( $this, 'set_cheezcap_group' ) );

		// Make it earliest possible, so we can remove other callbacks from filter.
		add_filter( 'the_content', array( $this, 'maybe_clean_the_content' ), 0 );

	}

	/**
	 * Adding WP Nonce to post edit page
	 *
	 */
	public function action_post_submitbox_start() {
		$nonce = Plugin::INDEXING_NONCE;
		wp_nonce_field( $nonce, $nonce.'_nonce' );
	}

	/**
	 * Setting Cheezcap options required
	 * @param array $cheezcap_groups
	 * @return array
	 */
	public function set_cheezcap_group( $cheezcap_groups = array() ) {
		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = array();
		}
		// Needed for compatibility with BGR_CheezCap
		if ( class_exists( 'BGR_CheezCapGroup' ) ) {
			$cheezcap_group_class = 'BGR_CheezCapGroup';
		} else {
			$cheezcap_group_class = 'CheezCapGroup';
		}

		$cheezcap_options = array(
			new \CheezCapTextOption(
				'Private Key',
				'Copy and paste the Private key that is obtained from the google console',
				self::PRIVATE_KEY,
				'',
				true
			),
			new \CheezCapTextOption(
				'Client Email',
				'Enter the Client Email(Ex variety-xxxxx@variety-xxxx.iam.gserviceaccount.com)',
				self::CLIENT_EMAIL,
				'' // default value
			),
		);

		$cheezcap_groups[] = new $cheezcap_group_class( "Google Real Time Indexing ", "pmc_gbreaking_news", $cheezcap_options );
		return $cheezcap_groups;
	}

	/**
	 * Initiating indexing request
	 * @param $post_id
	 * @param $post
	 */
	public function init_google_content_indexing( $post_id, $post ) {
		if ( ! apply_filters( 'pmc_gbreaking_news_index_post', true, $post_id, $post ) ) {
			return;
		}

		if ( class_exists( 'WPCOM_Liveblog' ) && is_callable( 'WPCOM_Liveblog::is_liveblog_post' ) ) {
			if ( \WPCOM_Liveblog::is_liveblog_post( $post_id ) === true ) {
				return;
			}
		}

		if( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
			return;
		}

		$nonce = Plugin::INDEXING_NONCE;
		if ( !isset( $_POST['pmc_gbreaking_news_nonce'] ) || ! wp_verify_nonce( $_POST[$nonce.'_nonce'], $nonce ) ) {
			return;
		}

		$allowed_post_types = apply_filters( 'pmc_google_indexing_allowed_post_types', array( 'post' ) );
		$post_type          = $post->post_type;

		if ( $post->post_status !== 'publish' || ! in_array( $post_type, $allowed_post_types ) ) {

			return;
		}
		$content = $this->get_content_to_index( $post_id );

		if ( $content !== false ) {
			$content = $this->format_content( $post_id, $post, $content );

			if ( empty( $content ) ) {
				return;
			}
			$request_status = $this->make_request( $content );
			update_post_meta( $post_id, Plugin::STATUS_META_KEY, $request_status );
		}

	}

	/**
	 * Get amp content by post_id
	 * @param $post_id
	 * @return mixed bool|string
	 */
	public function get_content_to_index( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}

		if ( ! function_exists( 'amp_load_classes') ) {
			return false;
		}
		amp_load_classes();//This will load AMP_Post_Template class

		if ( ! class_exists( 'AMP_Post_Template' ) ) {
			return false;
		}
		ob_start();
		do_action( 'pre_amp_render_post', $post_id );
		amp_add_post_template_actions();
		$template = new \AMP_Post_Template( $post_id );
		$template->load();
		$amp_content = ob_get_clean();
		return $amp_content;

	}

	/**
	 * Get amp url for a given post
	 * @param $post_id
	 * @return string
	 */
	public function get_amp_url( $post_id ) {
		$amp_url = function_exists( 'amp_get_permalink' ) ? amp_get_permalink( $post_id ) : false;
		return $amp_url;
	}

	/**
	 * Helper function to pass all required variables for template to fill
	 * and returns executed content as string
	 * @param $post_id
	 * @param $post
	 * @param $content
	 * @return string
	 */
	public function format_content( $post_id, $post, $content ) {
		if ( empty( $post_id ) || empty( $post ) || empty( $content ) ) {
			return false;
		}
		$template = PMC_GOOGLE_BREAKING_NEWS_ROOT . '/templates/breaking-news-request-template.php';
		$amp_url = $this->get_amp_url( $post_id );
		if ( empty( $amp_url ) ) {
			return false;
		}
		$args = array(
			'content_safe'  => $content,
			'post_id'  => $post_id,
			'post'     => $post,
			'amp_url'  => $amp_url
		);

		$content = PMC::render_template( $template, $args );

		return $content;
	}


	/**
	 * Calling API class to initiate request
	 * @param $content
	 * @return bool
	 */
	public function make_request( $content ) {
		if ( empty( $content ) ) {
			return false;
		}

		$google_index_api = Indexing_API::get_instance();
		$response = $google_index_api->init_request( $content );

		return $response;

	}

	/**
	 * Adding menu in the admin
	 */
	public function admin_page() {
		add_options_page(
			'Google Realtime Indexing',
			'Google Indexing Status',
			'manage_options',
			'google_realtime_indexing',
			array ( $this, 'show_indexing_status_page' )
		);
	}

	/**
	 * Display the last indexing status
	 */
	public function show_indexing_status_page() {
		$last_status = wp_cache_get( Indexing_API::RESPONSE_CACHE_KEY );
		$last_request = wp_cache_get( Indexing_API::REQUEST_CACHE_KEY );
		echo PMC::render_template( PMC_GOOGLE_BREAKING_NEWS_ROOT . '/templates/admin-indexing-status.php', array(
			'last_status' => $last_status,
			'last_request' => $last_request,
		) );
	}

	/**
	 * To check if current request is from 'Googlebot-News' or not.
	 *
	 * @since 2017-10-25 - Dhaval Parekh - CDWE-621
	 *
	 * @return bool
	 */
	protected function _is_google_news_bot() {

		// @codingStandardsIgnoreStart
		if ( function_exists( 'vary_cache_on_function' ) ) {
			vary_cache_on_function(
				'return (bool) preg_match( "/Googlebot-News/i", $_SERVER["HTTP_USER_AGENT"] );'
			);
		}
		// @codingStandardsIgnoreEnd

		return (bool) preg_match( '/Googlebot-News/i', PMC::filter_input( INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING ) );
	}

	/**
	 * Remove extra filter to make content clean.
	 *
	 * @global array  $wp_filter
	 *
	 * @param  string $content Post Content.
	 *
	 * @return string Post Content.
	 */
	public function maybe_clean_the_content( $content ) {

		if ( empty( $content ) ) {
			return $content;
		}

		if ( ! defined( 'PMC_ENABLE_LEAN_CONTENT_FOR_GOOGLEBOT' ) || true !== PMC_ENABLE_LEAN_CONTENT_FOR_GOOGLEBOT ) {
			return $content;
		}

		if ( ! $this->_is_google_news_bot() ) {
			return $content;
		}

		global $wp_filter;
		$callbacks = $wp_filter['the_content']->callbacks;

		// Remove all short codes.
		$content = strip_shortcodes( $content );

		/**
		 * While key is Object type, and value is function name.
		 * which need to remove.
		 * For
		 */
		$default_callbacks_to_remove = array(
			// Normal function.
			'convert_smilies',
			'do_shortcode',
			'shortcode_unautop', // Since we remove all shortcodes, we don't need this.

			// Static function of class.
			'PMC_Nofollow_White_List::filter_text',

			// Class name as key value as function name.
			'PMC\Google_Amp\Single_Post' => array(
				'maybe_remove_oembed_embeddables',
			),
			'PMC_Inject_Content'         => array(
				'filter_the_content_7',
				'filter_the_content_11',
			),
			'WP_Embed'                   => array(
				'run_shortcode',
				'autoembed',
			),
			'LazyLoad_Images'            => array(
				'add_image_placeholders',
			),
		);

		/**
		 * Filter to add callback list that need to remove from the_content filter
		 * to make leaner version of content for Googlebot-News user agent.
		 *
		 * @since 2017-10-27 - Dhaval Parekh - CDWE-621
		 */
		$callbacks_to_remove = apply_filters( 'pmc_google_breaking_news_the_content_remove_callbacks', $default_callbacks_to_remove );

		if ( empty( $callbacks_to_remove ) || ! is_array( $callbacks_to_remove ) ) {
			$callbacks_to_remove = $default_callbacks_to_remove;
		}

		$class_list = array_keys( $callbacks_to_remove );

		foreach ( $callbacks as $priority => $callback_list ) {

			if ( empty( $callback_list ) || ! is_array( $callback_list ) ) {
				continue;
			}

			foreach ( $callback_list as $index => $callback ) {

				if ( empty( $callback ) || ! is_array( $callback ) || empty( $callback['function'] ) ) {
					continue;
				}

				switch ( gettype( $callback['function'] ) ) {
					case 'string':
						/**
						 * For static and normal function index of function is
						 * same as function name, while for Class::function() have
						 * different type of index.
						 * So, By checkin index name also
						 * This way we can prevent removing class's function
						 * which have same name as normal functon in list.
						 */
						if ( $index === $callback['function'] && in_array( $callback['function'], $callbacks_to_remove, true ) ) {
							remove_filter( 'the_content', $callback['function'], $priority );
						}
						break;

					case 'array':
						$object = $callback['function'][0];

						$class_name = false;

						if ( 'object' === gettype( $object ) ) {
							$class_name = get_class( $object );
						} elseif ( 'string' === gettype( $object ) ) {
							$class_name = $object;
						}

						$function = $callback['function'][1];

						if ( ! empty( $object ) &&
							( in_array( $object, $class_list, true ) || in_array( $class_name, $class_list, true ) ) &&
							( ! empty( $callbacks_to_remove[ $class_name ] ) && in_array( $function, $callbacks_to_remove[ $class_name ], true ) ) ) {

							remove_filter( 'the_content', array( $object, $function ), $priority );
						}

						break;
					case 'object':
						// Remove all Inline functions.
						remove_filter( 'the_content', $callback['function'], $priority );
						break;
				}
			}
		}

		return $content;
	}

}

// EOF