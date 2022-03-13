<?php
/**
 * Original code reference: pmc-variety-2014/plugins/variety-vertical/variety-vertical.php
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Vertical {

	use Singleton;

	const CACHE_GROUP      = 'pmc-vertical';
	const CACHE_DURATION   = 300;
	const CACHE_KEY_PREFIX = 'primary_vertical_';

	function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
	}

	public function get_post_types() {
		$post_types = apply_filters( 'pmc-vertical-post-types', array( 'post' ) );
		if ( !is_array( $post_types ) ) {
			$post_types = explode(',', $post_types );
		}
		return array_filter( array_unique( $post_types ) );
	}

	/**
	 * @ref: pmc-variety-2014/plugins/variety-vertical/variety-vertical.php, lines 24-83
	 */
	public function action_init() {
		global $wp_rewrite;

		register_taxonomy('vertical', $this->get_post_types(), array(
				'label'         => 'Vertical',
				'labels' => array(
					'name'               => __('Verticals','pmc-plugins'),
					'singular_name'      => __('Vertical','pmc-plugins'),
					'add_new_item'       => __('Add New Vertical','pmc-plugins'),
					'edit_item'          => __('Edit Vertical','pmc-plugins'),
					'new_item'           => __('New Vertical','pmc-plugins'),
					'view_item'          => __('View Vertical','pmc-plugins'),
					'search_items'       => __('Search Verticals','pmc-plugins'),
					'not_found'          => __('No Verticals found.','pmc-plugins'),
					'not_found_in_trash' => __('No Verticals found in Trash.','pmc-plugins'),
					'all_items'          => __('Verticals','pmc-plugins')
				),
				'public'        => true,
				'show_ui'       => true,
			'show_in_rest'     => true,
				'hierarchical'  => true,
				// rewrite as /v/term
				'rewrite'       => array(
					'slug'          => 'v',
					'with_front'    => false,
					'hierarchical'  => true,
				),
				// admins only
				'capabilities'  => array(
					'manage_terms'  => 'manage_options', // admin+
					'edit_terms'    => 'manage_options', // admin+
					'delete_terms'  => 'manage_options', // admin+
					'assign_terms'  => 'edit_posts', // contributor+
				),
			));


		/**
		 * Enable vertical feed URLs
		 * The feed URLs must be handled before any other as the regular vertical
		 * URL re-write rule is a catch-all.
		 *
		 * @ticket PPT-3971
		 * @since 2015-02-23 Amit Gupta
		 */
		add_rewrite_rule( '^v/([^/]+)/feed/?', 'index.php?feed=rss2&taxonomy=vertical&term=$matches[1]', 'top' );
		add_rewrite_rule( '^v/([^/]+)/([^/]+)/feed/?', 'index.php?feed=rss2&taxonomy=vertical&term=$matches[1]&category_name=$matches[2]', 'top' );

		/**
		 * Enable /v/:term/:category URLs
		 */
		add_rewrite_rule( '^v/([^/]+)/([^/]+)/?$', 'index.php?vertical=$matches[1]&category_name=$matches[2]', 'top' );
		add_rewrite_rule( '^v/([^/]+)/([^/]+)/page/([0-9]+)?$', 'index.php?vertical=$matches[1]&category_name=$matches[2]&paged=$matches[3]', 'top' );
		add_rewrite_rule( '([0-9]{4})/([^/]+)/([^/]+)/([^/]+)-([0-9]+)/?$', 'index.php?year=$matches[1]&vertical=$matches[2]&category_name=$matches[3]&name=$matches[4]&p=$matches[5]', 'top' );
		$wp_rewrite->add_rewrite_tag('%vertical%', '([^/]+)', "vertical=");

		//Ading Menu placeholders for Verticals
		//added 50 arbitarily for a finite number VIP policy
		$verticals = get_terms(
			'vertical',
			array(
				'number' => 50
			)
		);

		foreach ( $verticals as $vertical ) {
			register_nav_menu(
				$vertical->slug . '-vertical-menu',
				$vertical->name . ' Vertical Menu'
			);
		} // foreach

		$this->setup_hooks();
	} // function action init

	public function setup_hooks() {

		// allow theme customize various feature to support
		$supports = apply_filters( 'pmc-vertical-supports',
				array(
					'category-rss',
					'sailthru-recurring-post',
					'permalink',
					)
				);

		if ( in_array( 'category-rss', $supports ) ) {
			add_filter( 'pmc_custom_feed_the_category_rss', array( $this, 'filter_pmc_custom_feed_the_category_rss' ) );
		}

		if ( in_array( 'sailthru-recurring-post', $supports ) ) {
			add_filter( 'sailthru_process_recurring_post', array( $this, 'filter_sailthru_process_recurring_post' ), 10, 2 );
		}

		if ( !defined( 'WPCOM_VIP_CUSTOM_PERMALINKS' ) ) {
			if (function_exists('wpcom_vip_load_permastruct')) {
				wpcom_vip_load_permastruct( $this->permalink_structure() );
			}

			if (function_exists('wpcom_vip_load_category_base')) {
				wpcom_vip_load_category_base('c');
			}

			if (function_exists('wpcom_vip_load_tag_base')) {
				wpcom_vip_load_tag_base('t');
			}
		}

		if ( in_array( 'permalink', $supports ) ) {
			add_filter( 'post_link', array( $this, 'filter_permalink_tags' ), 10, 3 );
			add_filter( 'post_type_link', array( $this, 'filter_permalink_tags' ), 10, 3 );
			add_filter( 'pmc_canonical_url', array( $this, 'filter_pmc_canonical_url' ) );
			add_action( 'generate_rewrite_rules', array( $this, 'action_generate_rewrite_rules' ) );
			add_action( 'wp', array( $this, 'action_wp' ) );
		}

		add_action( 'wp_head', array( $this, 'action_wp_head_robots' ) );
		add_filter( 'pmc_primary_taxonomy_settings', array( $this, 'filter_pmc_primary_taxonomy_settings' ) );
		add_action( 'template_redirect', array( $this, 'redirect_canonical' ) );

	} // function setup hooks

	/**
	 * Redirects incoming links to the proper URL based on the site url for
	 * `vertical` terms.
	 *
	 * Took reference from WordPress core `redirect_canonical()`.
	 *
	 * Note: not using `redirect_canonical` hook because
	 * `redirect_canonical()` only check for default taxomony `category`
	 * not for custom taxomony. and before it apply filter function will return.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 * @global WP_Query $wp_query
	 * @global WP $wp
	 */
	public function redirect_canonical() {
		global $wp_rewrite, $wp_query, $wp;

		// Check if request is for single page, If yes then procced.
		if ( is_single() && strpos( $wp_rewrite->permalink_structure, '%vertical%' ) !== false && get_query_var( 'vertical' ) ) {

			// Get current request url.
			$request_url = user_trailingslashit( home_url( $wp->request ) );

			// Get current requested `vertical` term.
			$vertical_query_var = get_query_var( 'vertical' );
			$vertical = get_term_by( 'name', $vertical_query_var, 'vertical' );

			if ( empty( $vertical ) || is_wp_error( $vertical ) ) {
				$vertical = get_term_by( 'slug', $vertical_query_var, 'vertical' );
			}

			/**
			 * Check if `vertical` term exists and it is assigned to requested post or not.
			 * If not then procced.
			 */
			if ( ( ! $vertical || is_wp_error( $vertical ) ) || ! has_term( $vertical->term_id, 'vertical', $wp_query->get_queried_object_id() ) ) {

				$redirect_url = get_permalink( $wp_query->get_queried_object_id() );

				/**
				 * If redirect url is same as request url then
				 * don't do any thing, this will protect against chained redirects.
				 */
				if ( $redirect_url !== $request_url ) {
					wp_safe_redirect( $redirect_url, 301 );
					exit();
				}
			}
		}
	}

	// Filter hook to enable primary vertical support
	public function filter_pmc_primary_taxonomy_settings( $args ) {

		if ( !isset( $args['taxonomy'] ) ) {
			$args['taxonomy'] = array();
		}

		if ( !isset( $args['post_type'] ) ) {
			$args['post_type'] = array();
		}

		$args['taxonomy']['vertical']  = 'Vertical';
		if ( !in_array( 'post', $args['post_type'] ) ) {
			$args['post_type'][] = 'post';
		}

		return $args;
	}

	/**
	 * @ref: pmc-variety-2014/plugins/variety-vertical/variety-vertical.php, lines 507-523
	 */
	function action_generate_rewrite_rules( $wp_rewrite ) {
		$wp_rewrite->permalink_structure = $this->permalink_structure();
	}

	public function permalink_structure() {
		$prefix              = apply_filters( 'pmc_permalink_prefix', '%year%' );
		$permalink_structure = '/' . $prefix . '/%vertical%/%category%/%postname%-%post_id%/';
		return apply_filters( 'pmc_vertical_permalink_structure', $permalink_structure );
	}

	/**
	 * Parse taxonomy tags for permalink URLs
	 *
	 * Filter on the %vertical% custom rewrite tag. If we have it stored in options,
	 * then jobs sending email newsletters don't know how to handle it. This way we can
	 * include the vertical in the permalink on the site, and have it removed in the email
	 * notification. The email notification permalinks will resolve via canonical redirect.
	 *
	 * @ref: pmc-variety-2014/plugins/variety-vertical/variety-vertical.php, lines 131-151
	 */
	function filter_permalink_tags( $permalink, $post = 0, $leavename = false, $canonical = false ) {

		/**
		 * Whether to override permalink or not.
		 *
		 * @param bool     $disable   Current value of whether to disable permalink overriding or not, defaults to false.
		 * @param string   $permalink Permalink being overridden.
		 * @param \WP_Post $post      Post object for which the permalink is being overridden.
		 *
		 */
		$permalink_override = apply_filters( 'pre_pmc_vertical_permalink_tag', '', $permalink, $post, $leavename, $canonical );
		if ( ! empty( $permalink_override ) ) {
			return $permalink_override;
		}

		$post = get_post($post);

		if ( !$post || ! in_array( get_post_type($post), $this->get_post_types() ) ) {
			return $permalink;
		}

		// we don't want to do anything to the permalink if it's a draft and has no slug replacement
		if ( 'auto-draft' === get_post_status( $post ) && false === strpos( $permalink,'%') ) {
			return $permalink;
		}

		// we do not want to make any change if permalink have the form: http://domain/?p=x
		if ( preg_match("/\\/\\?p=\\d+\$/", $permalink ) ) {
			return $permalink;
		}

		$time = strtotime( $post->post_date );
		$parts = parse_url( $permalink );

		if ( !empty( $parts['host'] ) && 'post' == get_post_type( $post ) ) {
			// override the permalink and use our custom permalink
			$permalink = $parts['scheme'] . '://'. $parts['host'] . $this->permalink_structure();
		}

		if ( ! $canonical ) {
			// the queried vertical
			if ( $vertical = get_query_var( 'vertical' ) ) {
				$vertical = get_term_by( 'slug', $vertical, 'vertical' );
			}
			// the queried category
			if ( $category = get_query_var( 'category_name' ) ) {
				$category = get_term_by( 'slug', $category, 'category' );
			}
		}

		if ( empty( $vertical ) || is_wp_error( $vertical ) || ! has_term( $vertical->term_id, 'vertical', $post ) ) {
			$vertical = $this->primary_vertical( $post );
		}

		if ( !empty( $vertical ) ) {
			$vertical = $vertical->slug;
		} else {
			$vertical = 'more';
		}

		if ( empty( $category ) || is_wp_error( $category ) || ! has_term( $category->term_id, 'category', $post ) ) {
			$category = $this->primary_category( $post );
		}

		if ( !empty( $category ) ) {
			$category = $category->slug;
		} else {
			$category = 'uncategorized';
		}

		if ( false == strpos( $permalink, '%vertical%' ) ) {
			$year = date('Y', $time );
			return str_replace( '/' . $year . '/', '/' . $year . '/' . $vertical . '/', $permalink);
		} else {

			$codes = array(
					'%year%',
					'%vertical%',
					'%category%',
					'%post_id%',
					'%postname%',
				);
			$replaces = array(
					date('Y', $time ),
					$vertical,
					$category,
					$post->ID,
					$leavename ? '%postname%' : $post->post_name,
				);
			$permalink = str_replace( $codes, $replaces, $permalink );

		}

		return $permalink;
	} // function filter permalink

	/**
	 * @ref: pmc-variety-2014/plugins/variety-vertical/variety-vertical.php, lines 507-523
	 */
	function filter_pmc_custom_feed_the_category_rss( $tax_list ) {

		$terms = get_the_terms( get_the_ID(), 'vertical' );

		if ( empty( $terms ) ) {
			return $tax_list;
		}

		foreach ( (array) $terms as $term ) {
			$term_name            = sanitize_term_field( 'name', $term->name, $term->term_id, 'post_tag', 'rss' );
			$tax_list[$term_name] = $term->taxonomy;
		}

		return $tax_list;

	} // function filter_pmc_custom_feed_the_category_rss

	/**
	 * @ref: pmc-variety-2014/plugins/variety-vertical/variety-vertical.php, lines 468-500
	 */
	function filter_sailthru_process_recurring_post( $feed_post, $original_post ) {

		if ( !is_feed() ) {
			return;
		}
		$primary_vertical = array();
		$vertical         = array();

		if ( taxonomy_exists( 'vertical' ) ) {
			$p_vertical = $this->primary_vertical( $original_post );
			if ( !empty( $p_vertical ) ) {
				$primary_vertical = array(
					'name' => isset( $p_vertical->name ) ? $p_vertical->name : "",
					'link' => isset( $p_vertical->link ) ? $p_vertical->link : ""
				);
			}

			$all_verticals = $this->get_post_terms( $original_post );

			if ( !empty( $all_verticals ) )
				foreach ( $all_verticals as $vert ) {
					$vertical[] = array(
						'name' => isset( $vert->name ) ? $vert->name : "",
						'link' => isset( $vert->link ) ? $vert->link : ""
					);
				}

		}
		$feed_post['primary_vertical'] = $primary_vertical;
		$feed_post['verticals']        = $vertical;

		return $feed_post;
	} // funcion filter_sailthru_process_recurring_post

	/**
	 * Return all vertical terms for a post.
	 */
	public function get_post_terms($post) {
		$terms = get_the_terms( $post, 'vertical' );

		if ( is_wp_error( $terms ) ) {
			return false;
		}

		if ( is_array($terms) ) {
			return array_values( $terms );
		}

		return $terms;
	} // function get post terms

	/**
	 * Return all vertical terms.
	 */
	function get_terms() {
		$terms = get_terms('vertical');

		if ( is_wp_error( $terms ) ) {
			return false;
		}

		return $terms;
	} // function get terms

	/**
	 * @see PMC_Primary_Taxonomy::get_primary_taxonomy
	 */
	public function primary_category( $post ) {
		return PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $post, 'category' );
	}

	/**
	 * @see PMC_Primary_Taxonomy::get_primary_taxonomy
	 */
	function primary_vertical( $post ) {
		return PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $post, 'vertical' );
	} // function primary vertical

	/**
	 * Modify canonical url for Vertical + Category intersection.
	 *
	 * @param string $canonical_url
	 * @return string
	 *
	 * @ref: pmc-variety-2014/plugins/variety-vertical/variety-vertical.php, lines 367-386
	 */
	function filter_pmc_canonical_url( $canonical_url ) {
		// only override canonical with new permalink if it's an article
		if ( is_single() && ( 'post' === get_post_type() || 'pmc-best-of' === get_post_type() ) ) {
			return $this->filter_permalink_tags( $canonical_url, get_post(), false, true );
		}

		$vertical = get_query_var( 'vertical' );
		$cat = get_query_var( 'category_name' );
		$page = get_query_var( 'paged' );

		if ( !empty( $vertical ) && !empty( $cat ) ) {

			$canonical_url = home_url( '/' ) . 'v/' . $vertical . '/' . $cat . '/';

			if ( !empty( $page ) ) {
				$canonical_url .= 'page/' . $page . '/';
			}
		}

		return $canonical_url;
	} // function filter canonical url

	/**
	 * Render robots meta tag for Vertical + Category intersection
	 *
	 * @ref: pmc-variety-2014/plugins/variety-vertical/variety-vertical.php, lines 393-416
	 */
	function action_wp_head_robots() {
		if ( !is_archive() ) {
			return;
		}

		if ( ! PMC::is_production() ) {
			return;
		}

		$page = get_query_var( 'paged' );

		if ( !empty( $page ) ) {
			return;
		}

		$vertical = get_query_var( 'vertical' );
		$cat = get_query_var( 'category_name' );

		if ( !empty( $vertical ) && !empty( $cat ) ) {
			echo "\n" . '<meta name="robots" content="index,follow" />' . "\n";
		} elseif ( !empty( $cat ) ) {
			echo "\n" . '<meta name="robots" content="noindex,follow" />' . "\n";
		}
	} // function action wp head robots

	/*
	 * Need to do canonical url redirect, since redirect_canonical isn't working for:
	 * /%year%/%category%/%postname%-%post_id%/ => /%year%/%vertical%/%category%/%postname%-%post_id%/
	 * This is the earliest hook we can attach to get the permalink info via get_permalink()
	 *
	 * @ref: pmc-variety-2014/plugins/variety-vertical/variety-vertical.php, lines 433-457
	 */
	function action_wp () {
		if ( !is_single() )
			return;

		$original_path = $_SERVER['REQUEST_URI'];

		// only process pattern matching /%year%/%category%/%postname%-%post_id%/
		if ( ! preg_match( '@/[0-9]{4}/[^/]+/[^/]+-[0-9]+/?$@', $original_path ) ) {
			if ( ! preg_match( '@/[0-9]{4}/%vertical%/[^/]+/[^/]+-[0-9]+/?$@', urldecode( $original_path ) ) ) {
				return;
			}
		}

		if ( $permalink = get_permalink() ) {
			if ( in_array( get_post_type(), $this->get_post_types() ) ) {
				$permalink_path = parse_url( $permalink, PHP_URL_PATH );
				if ( $permalink_path && $original_path != $permalink_path ) {
					wp_safe_redirect( $permalink, 301 );
					exit;
				}
			}
		}
	} // function action wp

}

PMC_Vertical::get_instance();

// EOF
