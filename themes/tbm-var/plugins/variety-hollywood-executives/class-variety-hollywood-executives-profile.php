<?php

/**
 * variety_hollywood_executives class with helper functions & which registers variety_hollywood_executives profiles as custom post_type
 *
 * @since 2011-11-09 Amit Gupta
 * @version 2012-06-24 Amit Gupta
 * @version 2013-07-16 Adaeze Esiobu
 */
class Variety_Hollywood_Executives_Profile {

	const VY_500_YEAR_TAXANOMY = 'vy500_year';
	const VY_500_VARIETY_ID_TAXANOMY = 'vy500_variety_id';

	public static $a__options = array(); //array containing the various options or labels etc
	public static $a__metaname = array(); //array containing the names of meta keys
	protected static $_a__img = array(); //array containing the image sizes

	/**
	 * Constructor
	 */
	public function __construct() {
		self::$a__options = array(
			'is_hollywood_exec_type' => false,
			'post_type' => 'hollywood_exec',
			'taxonomy' => 'vy500_year',
			'post_slug' => 'exec',
			'option_name' => 'hollywood_exec_profile_rewrite_flush',
			'latest_stories_def_count' => 12,
			'ajax_token' => 'page',
		);
		self::$_a__img = array(
			'thumbnail' => array( 'w' => 200, 'h' => 200 ),
			'hollywood_exec_index_thumb' => array( 'w' => 105, 'h' => 150 ),
			'hollywood_exec_page_river_thumb' => array( 'w' => 230, 'h' => 130 ),
		);

		$this->_setup_hooks();

	}

	/**
	 * All the hooks.
	 */
	protected function _setup_hooks() : void {

		//add hollywood exec index page image size
		add_image_size( 'hollywood_exec_index_thumb', self::$_a__img['hollywood_exec_index_thumb']['w'], self::$_a__img['hollywood_exec_index_thumb']['h'], true );

		//add image size
		add_image_size( 'hollywood_exec_page_river_thumb', self::$_a__img['hollywood_exec_page_river_thumb']['w'], self::$_a__img['hollywood_exec_page_river_thumb']['h'], true );

		//register hollywood_exec profile as custom post type
		add_action( 'init', array( $this, 'register_type' ) );

		// register VY 500 taxonomy
		add_action( 'init', array( $this, 'register_taxonomy' ) );

		//register hollywood exec company taxonomy
		add_action( 'init', array( $this, 'register_hollywood_company_taxonomy' ) );

		add_action( 'parse_query', array( $this, 'alter_posts_per_page' ) );

		//print styles etc to the wp_head
		add_action( 'wp_enqueue_scripts', array( $this, 'print_frontend_head' ) );

		// register user roles and capabilities.
		add_action( 'init', array( $this, 'register_user_roles_and_capabilities' ) );

		// Redirect requests from tag page to exec page if it exists.
		add_action( 'template_redirect', [ $this, 'maybe_do_tag_redirect' ] );

		add_action( 'wp', [ $this, 'remove_default_title' ] );

		add_action( 'wp_head', [ PMC::class, 'render_title_tag' ], 0 ); // this must be at 0 priority to render title HTML tag up in the page

		add_filter( 'pmc_render_title_tag', [ $this, 'modify_seo_title' ] );

	}

	/**
	 * Alter the query on the hollywood exec archive page to show X posts instead of the default
	 *
	 * @param obj &$wp_query
	 */
	public function alter_posts_per_page( $wp_query ) {
		if ( is_post_type_archive( self::$a__options['post_type'] ) && ! is_admin() ) {
			$wp_query->query_vars['posts_per_page'] = 50;
		}
	}

	/**
	 * combine all elements of array using the separator
	 * @param $arr
	 * @param $separator
	 * @return string
	 */
	public function array_to_string( $arr, $separator ) {
		$arr = array_filter( (array) $arr, 'trim' );
		return implode( $separator, $arr );
	}

	/**
	 * Returns <option> tags with numbers starting $start till $end with $default as selected
	 * @param int $start
	 * @param int $end
	 * @param $default
	 * @param string $showempty
	 * @param string $zeropad
	 * @return string
	 */
	public function get_number_options( $start = 1, $end = 31, $default = -1, $showempty = 'hide', $zeropad = 'show' ) {
		$start = intval( $start );
		$end = intval( $end );
		$default = intval( $default );
		$default = ( $default <= 0 ) ? -1 : $default;
		if ( $end < $start || ( -1 !== $default && ( $default < $start || $default > $end ) ) ) {
			return;
		}
		$options = '';
		if ( 'show' === $showempty ) {
			$options = sprintf( '<option value="" %s>Select</option>', selected( $default, -1, false ) );
		}
		$pad_length = 0;
		if ( 'show' === $zeropad ) {
			$pad_length = 2;
		}
		for ( $i = $start; $i <= $end; $i++ ) {
			$options .= sprintf( '<option value="%1$s" %2$s>%3$s</option>', $i, selected( $default, $i, false ), str_pad( $i, $pad_length, 0, STR_PAD_LEFT ) );
		}
		return $options;
	}

	/**
	 * Returns <option> tags with months with $default as selected
	 * @param string $default
	 * @param string $showempty
	 * @param string $show
	 * @return string
	 */
	public function get_month_options( $default = '', $showempty = 'hide', $show = 'full' ) {

		// cal_info() isn't currently enabled on WordPress.com :(
		// otherwise we could use $calendar = cal_info(CAL_GREGORIAN);
		$calendar = array(
			'months' => array(
				'January',
				'February',
				'March',
				'April',
				'May',
				'June',
				'July',
				'August',
				'September',
				'October',
				'November',
				'December',
			),
			'abbrevmonths' => array(
				'Jan',
				'Feb',
				'Mar',
				'Apr',
				'May',
				'Jun',
				'Jul',
				'Aug',
				'Sep',
				'Oct',
				'Nov',
				'Dec',
			),
		);
		switch ( $show ) {
			case 'short':
				$months = $calendar['abbrevmonths'];
				break;
			case 'full':
			default:
				$months = $calendar['months'];
				break;
		}
		$options = '';
		if ( 'show' === $showempty ) {
			$options .= sprintf( '<option value="0" %1$s>%2$s</option>', selected( $default, '0', false ), esc_html__( 'Select', 'pmc-variety' ) );
		}
		foreach ( $months as $month ) {
			$options .= sprintf( '<option value="%1$s" %2$s>%1$s</option>', $month, selected( $default, $month, false ) );
		}
		return $options;
	}

	/**
	 * Returns <option> tags with Countries with $default as selected
	 * @param string $default
	 * @param string $showempty
	 * @return string
	 */
	public function get_country_options( $default = '', $showempty = 'hide' ) {
		$default = ( empty( $default ) && '0' !== $default ) ? 'United States' : $default;
		$arr_countries = array(
			'Afghanistan', 'Albania', 'Algeria', 'American Samoa', 'Andorra', 'Angola', 'Anguilla', 'Antarctica', 'Antigua And Barbuda', 'Argentina', 'Armenia', 'Aruba', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia', 'Bosnia Hercegovina', 'Botswana', 'Bouvet Island', 'Brazil', 'Brunei Darussalam', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Byelorussian SSR', 'Cambodia', 'Cameroon', 'Canada', 'Cape Verde', 'Cayman Islands', 'Central African Republic', 'Chad', 'Chile', 'China', 'Christmas Island', 'Cocos (Keeling) Islands', 'Colombia', 'Comoros', 'Congo', 'Cook Islands',
			'Costa Rica', 'Cote D\'Ivoire', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Czechoslovakia', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador', 'Egypt', 'El Salvador', 'England', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Falkland Islands', 'Faroe Islands', 'Fiji', 'Finland', 'France', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Great Britain', 'Greece', 'Greenland', 'Grenada', 'Guadeloupe', 'Guam', 'Guatamala', 'Guernsey', 'Guiana', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Heard Islands', 'Honduras', 'Hong Kong', 'Hungary', 'Iceland', 'India',
			'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Isle Of Man', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jersey', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Korea, South', 'Korea, North', 'Kuwait', 'Kyrgyzstan', 'Lao People\'s Dem. Rep.', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macau', 'Macedonia', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Mariana Islands', 'Marshall Islands', 'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Mexico', 'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar',
			'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'Netherlands Antilles', 'Neutral Zone', 'New Caledonia', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island', 'Northern Ireland', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Pitcairn', 'Poland', 'Polynesia', 'Portugal', 'Puerto Rico', 'Qatar', 'Reunion', 'Romania', 'Russian Federation', 'Rwanda', 'Saint Helena', 'Saint Kitts', 'Saint Lucia', 'Saint Pierre', 'Saint Vincent', 'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Scotland', 'Senegal', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands',
			'Somalia', 'South Africa', 'South Georgia', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Svalbard', 'Swaziland', 'Sweden', 'Switzerland', 'Syrian Arab Republic', 'Taiwan', 'Tajikista', 'Tanzania', 'Thailand', 'Togo', 'Tokelau', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Turks and Caicos Islands', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City State', 'Venezuela', 'Vietnam', 'Virgin Islands', 'Wales', 'Western Sahara', 'Yemen', 'Yugoslavia', 'Zaire', 'Zambia', 'Zimbabwe',
		);
		$options = '';
		if ( 'show' === $showempty ) {
			$options .= sprintf( '<option value="" %1$s>%2$s</option>\n',  selected( $default, '0', false ), esc_html__( 'Select', 'pmc-variety' ) );
		}
		foreach ( $arr_countries as $country ) {
			$options .= sprintf( '<option value="%1$s" %2$s>%1$s</option>\n',  $country, selected( $default, $country, false ) );
		}
		return $options;
	}

	/**
	 * @return mixed
	 * register the hollywood exec company taxonomy.
	 */
	public function register_hollywood_company_taxonomy() {
		if ( taxonomy_exists( 'hollywood_exec_company' ) ) {
			return;
		}
		register_taxonomy(
			'hollywood_exec_company',
			self::$a__options['post_type'],
			array(
				'labels' => array(
					'name' => 'Hollywood Exec Companies',
					'add_new_item' => 'Add New Hollywood Exec Company',
					'new_item_name' => 'New Hollywood Exec Company'
				),
				'show_ui'       => true,
				'public'        => false,
				'show_tagcloud' => false,
				'hierarchical'  => true,
			)
		);
	}

	/**
	 * Called on WordPress init, registers our celeb profile as custom post type
	 */
	public function register_type() {
		if ( post_type_exists( self::$a__options['post_type'] ) ) {
			//post_type already registered, bail
			return;
		}
		$labels = array(
			'name' => _x( 'Hollywood Exec Profiles', 'post type general name' ),
			'singular_name' => _x( 'Hollywood Exec Profile', 'post type singular name' ),
			'add_new' => _x( 'Add New', 'hollywood exec profile' ),
			'add_new_item' => __( 'Add New Hollywood Exec Profile' ),
			'edit_item' => __( 'Edit Hollywood Exec Profile' ),
			'new_item' => __( 'New Hollywood Exec Profile' ),
			'view_item' => __( 'View Hollywood Exec Profile' ),
			'search_items' => __( 'Search Hollywood Exec Profiles' ),
			'not_found' => __( 'Nothing found' ),
			'not_found_in_trash' => __( 'Nothing found in Trash' ),
			'parent_item_colon' => '',
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'has_archive' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'query_var' => true,
			'menu_icon' => '',
			'rewrite' => array( 'slug' => self::$a__options['post_slug'] ),
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => 100,
			'supports' => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'taxonomies' => array( 'post_tag', 'editorial' ),
		);

		register_post_type( self::$a__options['post_type'], $args );
	}

	/**
	 * Register taxonomies
	 */
	public function register_taxonomy() {
		$this->register_vy500_year_taxonomy();
		$this->register_vy500_variety_id_taxonomy();
	}

	/**
	 * Hidden taxonomy to attach variety ID to post
	 */
	public function register_vy500_variety_id_taxonomy() {
		$args = array(
			'label'        => __( 'Variety ID' ),
			'public'       => false,
			'rewrite'      => false,
		);

		register_taxonomy(
			self::VY_500_VARIETY_ID_TAXANOMY,
			self::$a__options['post_type'],
			$args
		);
	}

	/**
	 * Register VY 500 years taxonomy
	 */
	public function register_vy500_year_taxonomy() {
		$labels = array(
			'name'                       => __( 'VY 500 years', 'pmc-variety' ),
			'singular_name'              => __( 'VY 500 year', 'pmc-variety' ),
			'search_items'               => __( 'Search VY 500 year', 'pmc-variety' ),
			'popular_items'              => __( 'Popular VY 500 year', 'pmc-variety' ),
			'all_items'                  => __( 'All VY 500 years', 'pmc-variety' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit VY 500 year', 'pmc-variety' ),
			'update_item'                => __( 'Update VY 500 year', 'pmc-variety' ),
			'add_new_item'               => __( 'Add New VY 500 year', 'pmc-variety' ),
			'new_item_name'              => __( 'New VY 500 year Name', 'pmc-variety' ),
			'separate_items_with_commas' => __( 'Separate VY 500 years with commas', 'pmc-variety' ),
			'add_or_remove_items'        => __( 'Add or remove VY 500 years', 'pmc-variety' ),
			'choose_from_most_used'      => __( 'Choose from the most used VY 500 years', 'pmc-variety' ),
			'not_found'                  => __( 'No VY 500 years found.', 'pmc-variety' ),
			'menu_name'                  => __( 'VY 500 years', 'pmc-variety' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'public'                => false,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'vy500' ),
		);

		register_taxonomy(
			self::VY_500_YEAR_TAXANOMY,
			self::$a__options['post_type'],
			$args
		);
	}

	/**
	 * retrieves specified value from post object
	 * instead of writing call to global $post object each time, the code looks cleaner with just this function call
	 * @param $key
	 * @return bool|int
	 */
	protected function get_post_value( $key ) {
		global $post;
		switch ( $key ) {
			case 'ID':
				if ( isset( $post->ID ) ) {
					return $post->ID;
				}
				break;
		}
		return false;
	}

	/**
	 * returns image thumbnail for post
	 *
	 * TODO: Need to modify this function coz if image does not exist then it'll return empty & if get_the_image() is called
	 * to get image then it does not return image in the desired size. Perhaps will be better to extend code to use Lead/Teaser
	 * image from custom field & even if that not available then fallback to get_the_image()
	 * @param $post_id
	 * @param string $img_size
	 * @param array $override_attr
	 * @return array|bool|mixed|string|void
	 */
	public function get_image_thumbnail( $post_id, $img_size = 'thumbnail', $override_attr = array( ) ) {
		$post_id = intval( $post_id );
		if ( $post_id < 1 || empty( $img_size ) ) {
			return false;
		}
		$defaults = array(
			'meta_key' => array( 'Lead Image', 'Teaser Image' ),
			'post_id' => $post_id,
			'size' => $img_size,
			'link_to_post' => false,
			'echo' => false,
		);
		$img_html = "";
		if ( has_post_thumbnail( $post_id ) ) {
			$img_html = get_the_post_thumbnail( $post_id, $img_size );
		}
		if ( empty( $img_html ) ) {
			//fetch images for this post
			$arr_images = get_posts( array(
				'post_type'        => 'attachment',
				'post_mime_type'   => 'image',
				'post_parent'      => $post_id,
				'posts_per_page'   => 20,
				'suppress_filters' => false,
			) );
			if ( $arr_images ) {
				//get array keys which are attachment IDs
				$arr_keys = array_keys( $arr_images );
				//lets grab first image
				$img_html = wp_get_attachment_image( $arr_keys[0], $img_size );
			} else {
				$img_html = get_the_image( $defaults );
			}
		}
		if ( ! empty( $img_html ) ) {
			if ( ! empty( $override_attr ) && is_array( $override_attr ) ) {
				foreach ( $override_attr as $attr => $val ) {
					if ( empty( $attr ) || ! is_string( $attr ) ) {
						continue;
					}
					$search = '/(.*)' . $attr . '="(.*?)"(.*)/';
					$replace = '${1}' . $attr . '="' . $val . '"${3}';
					$img_html = preg_replace( $search, $replace, $img_html );
					unset( $search, $replace );
				}
			}
			return $img_html;
		}
		unset( $defaults, $img_html );
		return false;
	}

	/**
	 * this function takes in a profile slug & returns its permalink
	 * @param $slug
	 * @return bool|string
	 */
	protected function _get_profile_url( $slug ) {
		$slug = sanitize_title( $slug );
		if ( empty( $slug ) ) {
			return false;
		}
		$args = array(
			'name' => $slug,
			'numberposts' => 1,
			'post_type' => self::$a__options['post_type'],
			'post_status' => 'publish',
			'suppress_filters' => false,
		);
		$post = get_posts( $args );
		if ( ! empty( $post ) && isset( $post[0]->ID ) ) {
			return get_permalink( $post[0]->ID );
		}
		unset( $args, $post );
		return false;
	}

	/**
	 * returns the version number to be used with stylesheers/js etc
	 * @return string
	 */
	private function _get_version() {
		$version = VARIETY_HOLLYWOOD_EXECUTIVES_PROFILE_VERSION;
		$current_server = explode( '.', $_SERVER['HTTP_HOST'] );
		$dev_servers = array( 'local', 'alpha', 'gamma' );
		$bln_dev = false; //assume current server is not dev server
		foreach ( $dev_servers as $dev_server ) {
			if ( in_array( $dev_server, $current_server, true ) ) {
				$bln_dev = true;
			}
		}
		if ( true === $bln_dev ) {
			$version = md5( microtime() );
		}
		return $version;
	}

	/**
	 * print out stylesheet for front-end
	 */
	public function print_frontend_head() {
		//if current page is not our post type then don't print anything
		if ( get_post_type() !== self::$a__options['post_type'] ) {
			return;
		}
		self::$a__options['is_hollywood_exec_type'] = true; //set to true as the current page is celeb page
		$version = $this->_get_version();
		//register stylesheets
		wp_register_style( 'variety-hollywood-exec-profile-style', plugins_url( 'styles/style-variety-hollywood-executives.css', __FILE__ ), false, $version );
		wp_register_style( 'variety-hollywood-exec-style-desktop', plugins_url( 'styles/style-variety-hollywood-executives-desktop.css', __FILE__ ), array( 'variety-hollywood-exec-profile-style' ), $version );
		wp_register_style( 'variety-hollywood-exec-profile-style-mobile', plugins_url( 'styles/style-variety-hollywood-executives-mobile.css', __FILE__ ), array( 'variety-hollywood-exec-profile-style' ), $version );
		//register scripts
		wp_register_script( 'variety-hollywood-exec-profile-script-page', plugins_url( 'js/variety-hollywood-executives-page.js', __FILE__ ), array( 'jquery' ), $version );

		//load main stylesheet
		wp_enqueue_style( 'variety-hollywood-exec-profile-style' );

		if ( jetpack_is_mobile() ) {
			wp_enqueue_style( 'variety-hollywood-exec-profile-style-mobile' ); //load mobile stylesheet
		} else {
			wp_enqueue_style( 'variety-hollywood-exec-style-desktop' ); //load desktop stylesheet
		}

		//if not our archive page then need to print the JS
		if ( ! is_post_type_archive( self::$a__options['post_type'] ) ) {
			//load celeb-profile page script
			wp_enqueue_script( 'variety-hollywood-exec-profile-script-page' );
			$localized_array = array(
				'ajaxurl' => home_url( '/wp-admin/admin-ajax.php' ),
				'offset' => 1,
				'ajax_token' => self::$a__options['ajax_token'],
			);
			if ( isset( $GLOBALS['variety_hollywood_exec_profile_url'] ) && ! empty( $GLOBALS['variety_hollywood_exec_profile_url'] ) ) {
				$localized_array['current_url'] = $GLOBALS['variety_hollywood_exec_profile_url'];
			}
			wp_localize_script( 'variety-hollywood-exec-profile-script-page', 'HollywoodExecProfile', $localized_array );
			unset( $localized_array );
		}
	}

	/**
	 * Register user roles and capabilities for hollywood_exec custom post type.
	 *
	 * @since 2017-10-02 CDWE-681 Chandra Patel
	 */
	public function register_user_roles_and_capabilities() {

		if ( ! function_exists( 'wpcom_vip_add_role_caps' ) || ! function_exists( 'wpcom_vip_add_role' ) ) {
			return;
		}

		$capabilities = array(
			'manage_exec_profiles' => true,
		);

		wpcom_vip_add_role( 'vi-hollywood-exec-profile-author', 'VI Hollywood Exec Profile Author', $capabilities );

		// Add capabilities to administrator role as well.
		wpcom_vip_merge_role_caps( 'administrator', $capabilities );

	}

	/**
	 * Redirect from Tag page to Exec Page if it exists.
	 */
	public function maybe_do_tag_redirect() : void {

		// Get tag value.
		$tag_slug = get_query_var( 'tag' );

		// Validate where we are.
		if ( is_admin() || ! is_tag() || empty( $tag_slug ) ) {
			return;
		}

		$tag_redirect_url = $this->_get_profile_url( $tag_slug );

		if ( empty( $tag_redirect_url ) ) {
			return;
		}

		wp_safe_redirect( $tag_redirect_url, 301 );
		exit; // @codeCoverageIgnore

	}

	/**
	 * Remove default title if on single exec page
	 */
	public function remove_default_title() {
		global $post;

		if ( is_single() && get_post_type( $post ) === self::$a__options['post_type'] ) {
			remove_action( 'wp_head', '_wp_render_title_tag', 1 );
		}
	}

	/**
	 * Modify SEO title on single exec page to match expected format.
	 */
	public function modify_seo_title( $title ) {
		global $post;

		if ( is_single() && get_post_type( $post ) === self::$a__options['post_type'] ) {
			// translators: returns the executive name, accompanied by the Variety.com tag after a |
			return sprintf( __( '%s - Entertainment Executive | Variety.com', 'pmc-variety' ), get_the_title() );
		}

		return $title;
	}
}

//EOF
