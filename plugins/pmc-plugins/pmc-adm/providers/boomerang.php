<?php
/**
 * Boomerang Ad Wrapper
 *
 * @since   05-08-2018 Vinod Tella READS-1221
 *
 * @package pmc-adm
 */
class Boomerang_Provider extends PMC_Ad_Provider {

	// Exclude these display type from Optimize Cumulative Layout Shift
	const EXCLUDE_DISPLAY_TYPE_FROM_CLS = [
		'reskin',
		'inlineoop',
		'bottom',
		'nativemini',
		'nativecontent',
		'nativesidebar',
		'medrecnativemini',
		'medrecnativecontent',
		'mobileincontentnativecontent',
	];

	/**
	 * Ad provider ID.
	 *
	 * @var string Provider ID.
	 */
	protected $_id = 'boomerang';

	/**
	 * Ad provider name.
	 *
	 * @var string Provider Name.
	 */
	protected $_title = 'Boomerang';

	/**
	 * To store count for auto div ID.
	 *
	 * @var int
	 */
	protected $_auto_uid = 0;

	/**
	 * To store site specific script url to enqueue.
	 *
	 * @var string Script URL.
	 */
	protected $_script_url = false;

	/**
	 * List out the templates to show in the admin form. These will be rendered in the same order as flex column wrap.
	 * The admin templates are in /pmc-adm/templates/provider-admin/*.php
	 *
	 * @var array
	 */
	protected $_fields = [
		'ad-display-type' => [
			'title'    => 'Ad display type',
			'required' => true,
			'options'  => [
				'bottom'                       => 'Adhesion - Mobile',
				'frame1'                       => 'Adhesion - Desktop Top',
				'frame2'                       => 'Adhesion - Desktop Bottom',

				'banner'                       => 'Banner - 728x90',
				'flexbanner'                   => 'Flexbanner - Multisize: 728x90, 970x90, 970x250',
				'flexrec'                      => 'Flexrec - Multisize: 300x250, 300x600',

				'medrec'                       => 'Medrec - 300x250',
				'medrecnativemini'             => 'Medrec Native Mini - Multisize: 300x250, 2x2 ',
				'medrecnativecontent'          => 'Medrec Native Content - Multisize: 300x250, 3x3',

				'tinybanner'                   => 'Mobile banner - Multisize: 300x50, 320x50',
				'mobileincontent'              => 'Mobile In Content - Multisize: 300x250, 320x50, 300x50',
				'mobileincontentnativecontent' => 'Mobile In Content Native In Content - 300x250, 300x50, 320x50, 3x3',

				'nativemini'                   => 'Native Mini - 2x2',
				'nativecontent'                => 'Native Content - 3x3',
				'nativesidebar'                => 'Native Sidebar - 4x4',

				'reskin'                       => 'OOP - Skin (reskin)',
				'inlineoop'                    => 'OOP - Inline OOP',

				'sky'                          => 'Sky - 160x600',
				'widebanner'                   => 'Wide Banner - Multisize: 970x250, 970x90',
				'wideincontent'                => 'Wide In Content - Multisize: 300x250, 728x90',
				'widestincontent'              => 'Widest In Content - Multisize: 300x250, 728x90, 970x250, 970x90',

				'320x50'                       => '320x50',
				'300x50'                       => '300x50',
				'300x600'                      => '300x600',
				'970x250'                      => '970x250',
			],
		],
		'sitename'        => [
			'title'       => 'Sitename',
			'required'    => true,
			'placeholder' => 'pmc (require)',
		],
		'div-id'          => [
			'title'       => 'Div ID',
			'required'    => true,
			'placeholder' => 'div-gpt-12363453o8 (require)',
		],
		'slot-type'       => [
			'title'    => 'Slot Type',
			'required' => true,
			'options'  => [
				'normal' => 'Normal',
				'oop'    => 'Out of page',
			],
		],
		'ad-width'        => [
			'title'       => 'Ad width: Format [300, 50], [320, 50]',
			'required'    => true,
			'placeholder' => '[300, 250] (require)',
			'validator'   => 'gpt-ad-width',
		],
	];

	/**
	 * List out the templates to show in the admin form. These will be rendered in the same order as flex column wrap.
	 * The admin templates are in /pmc-adm/templates/provider-admin/*.php
	 *
	 * @var array
	 */
	protected $_admin_templates = [
		'basic',
		'when-to-render',
		'device',
		'status',
		'time-frame',
		'custom-boomerang',
		'conditionals',
		'contextual-player',
		'targetting',
	];

	/**
	 * Constructor Method.
	 *
	 * @param string $key Provider ID.
	 * @param array  $config
	 */
	public function __construct( $key, array $config = array() ) {

		if ( ! empty( $config['script_url'] ) ) {
			$this->_script_url = $config['script_url'];
			unset( $config['script_url'] );
		}

		parent::__construct( $key, $config );
	}

	/**
	 * Return all the templates for this provider required from /pmc-adm/templates/provider-admin/*.php
	 *
	 * @return array
	 */
	public function get_admin_templates() {
		return $this->_admin_templates;
	}

	/**
	 * Include any 3rd-party scripts.
	 */
	public function include_assets() {
		add_action( 'wp_head', [ $this, 'load_boomerang_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
	}

	/**
	 * To enqueue scripts for boomerang ads.
	 *
	 * @action wp_enqueue_scripts
	 *
	 * @return boolean
	 */
	public function wp_enqueue_scripts() {

		if ( empty( $this->_script_url ) ) {
			return false;
		}

		wp_enqueue_script( 'pmc-async-adm-boomerang-theme-script', $this->_script_url, [], null );
		wp_enqueue_script( 'pmc-async-adm-boomerang-script', 'https://ads.blogherads.com/static/blogherads.js', [ 'pmc-async-adm-boomerang-theme-script' ], null );

	}

	/**
	 * To get primary term for taxonomy.
	 *
	 * @codeCoverageIgnore Test case already written.
	 *
	 * @param WP_Post $post Post Object.
	 * @param string  $taxonomy taxonomy type.
	 *
	 * @return bool|mixed
	 */
	protected function _get_primary_term( $post, $taxonomy = 'category' ) {

		if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) ) {
			return false;
		}

		$term = false;

		if ( class_exists( 'PMC_Primary_Taxonomy' ) ) {

			$term = PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $post, $taxonomy );

		} else {
			$terms = get_the_terms( $post, $taxonomy );

			if ( ! empty( $terms ) ) {
				$term = reset( $terms );
			}

			unset( $terms );
		}

		if ( ! empty( $term ) && is_a( $term, 'WP_Term' ) && ! empty( $term->parent ) ) {
			$parent_term = get_term( $term->parent );
			if ( ! empty( $parent_term ) && is_a( $parent_term, 'WP_Term' ) ) {
				$term = $parent_term;
			}
		}

		return $term;
	}

	/**
	 * To prepare data for Global targeting setting.
	 *
	 * @return bool|array
	 */
	public function prepare_boomerang_global_settings() {

		if ( empty( $this->_script_url ) ) {
			return false;
		}

		$taxonomy = apply_filters( 'pmc_adm_boomerang_taxonomy_for_vertical', 'category' );
		$taxonomy = ( ! empty( $taxonomy ) && taxonomy_exists( $taxonomy ) ) ? $taxonomy : 'category';

		$data = [];

		$data['header_script_url'] = $this->_script_url;
		$data['taxonomy_type']     = $taxonomy;

		// Load global meta tags for ads.
		$targeting_data = [];

		if ( is_home() || is_front_page() ) {

			$data['vertical'] = 'home';

			$targeting_data = [
				'ci' => 'HOM',
				'cn' => 'homepage',
				'pt' => 'home',
			];

		} elseif ( is_single() ) {

			$post = get_post();
			$post = ( ! empty( $post ) && is_a( $post, 'WP_Post' ) ) ? $post : false;

			if ( $post ) {

				switch ( $post->post_type ) {
					case 'gallery':
					case 'pmc-gallery':
					case 'pmc-list-slideshow':
						$page_type = 'slideshow';
						break;
					case 'post':
						$page_type = 'article';
						break;
					default:
						$page_type = $post->post_type;
						break;
				}

				$targeting_data = [
					'pt' => $page_type,
					'ci' => sprintf( 'ART-%d', $post->ID ),
				];

				if ( empty( $data['vertical'] ) ) {
					$term             = $this->_get_primary_term( $post, $taxonomy );
					$data['vertical'] = $term->slug;
				}

				$tags = get_the_terms( $post, 'post_tag' );

				if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
					$targeting_data['tag'] = wp_list_pluck( $tags, 'slug' );
				}
			}
		} elseif ( is_archive() ) {

			$term = get_queried_object();
			$term = ( ! empty( $term ) && is_a( $term, 'WP_Term' ) ) ? $term : false;

			if ( ! empty( $term->parent ) ) {
				$parent_term = get_term( $term->parent );
				if ( ! empty( $parent_term ) && is_a( $parent_term, 'WP_Term' ) ) {
					$term = $parent_term;
				}
			}

			if ( $term ) {
				$targeting_data = [
					'ci' => sprintf( '%s-%d', $term->taxonomy, $term->term_id ),
				];

				if ( empty( $data['vertical'] ) ) {
					$data['vertical'] = $term->slug;
				}
			}

			$targeting_data['pt'] = 'landing';

		}

		if ( empty( $data['vertical'] ) ) {
			$data['vertical'] = 'ros';
		}

		$data['targeting_data'] = $targeting_data;

		return apply_filters( 'pmc_adm_prepare_boomerang_global_settings', $data );
	}

	/**
	 * Load Boomerang header tags
	 */
	public function load_boomerang_scripts() {

		$data = $this->prepare_boomerang_global_settings();

		if ( empty( $this->_script_url ) ) {
			return false;
		}

		$header_tag_template = PMC_ADM_DIR . '/templates/boomerang-tags.php';

		\PMC::render_template( $header_tag_template, $data, true );
	}

	/**
	 * To prepare ad data.
	 *
	 * @param  array $data Ad data.
	 *
	 * @return array Ad data.
	 */
	protected function _prepare_ad_data( $data ) {

		if ( empty( $data ) || ! is_array( $data ) ) {
			return $data;
		}

		$data['key'] = $this->get_key();

		// Make div-id unique to avoid any potential dup that might cause entire gpt ads not working.
		$data['div-id'] = sprintf( '%s-uid%d', $data['div-id'], $this->_auto_uid ++ );

		$data = $this->_apply_cls_optimization( (array) $data );

		/**
		 * Filter to modify ad unit data.
		 */
		$data = apply_filters( 'pmc_adm_prepare_boomerang_ad_data', $data );

		return $data;
	}

	/**
	 * Apply Optimize Cumulative Layout Shift
	 * @param array $data
	 * @return array
	 */
	protected function _apply_cls_optimization( array $data ) : array {

		$cls_option = \PMC_Cheezcap::get_instance()->get_option( 'pmc_optimize_cls' );

		$ad_width = ( ! empty( $data['ad-width'] ) ) ? json_decode( sprintf( '[%s]', $data['ad-width'] ) ) : '';

		if ( 'disable' !== $cls_option
			&& ! in_array( $data['ad-display-type'], (array) self::EXCLUDE_DISPLAY_TYPE_FROM_CLS, true )
			&& 'oop' !== $data['slot-type']
			&& ! preg_match( '/skin|sticky/', $data['location'] )
			&& is_array( $ad_width )
		) {

			$cls_width     = 0;
			$cls_height    = 0;
			$can_optimized = false;

			if ( in_array( $cls_option, [ 'enable', 'maxsize' ], true ) ) {

				foreach ( $ad_width as $size ) {
					if ( count( $size ) === 2 ) {
						if ( $cls_width < $size[0] ) {
							$cls_width = $size[0];
						}
						if ( $cls_height < $size[1] ) {
							$cls_height = $size[1];
						}
					}
				}

				// determine if we can turn on cls optimization if max size is at least 50 pixel
				$can_optimized = ( $cls_width >= 50 || $cls_height >= 50 );

			} elseif ( 'minsize' === $cls_option ) {

				foreach ( $ad_width as $size ) {
					if ( count( $size ) === 2 ) {
						if ( $cls_width > $size[0] || 0 === $cls_width ) {
							$cls_width = $size[0];
						}
						if ( $cls_height > $size[1] || 0 === $cls_height ) {
							$cls_height = $size[1];
						}
						if ( ! $can_optimized ) {
							// we can turn on cls optimization if there is at least one ad with minimal 50 pixels
							$can_optimized = ( $size[0] >= 50 || $size[1] >= 50 );
						}
					}
				}

			}

			if ( ! empty( $cls_width ) && ! empty( $cls_height ) && $can_optimized ) {
				if ( $cls_width < 50 ) {
					$cls_width = 50;
				}
				if ( $cls_height < 50 ) {
					$cls_height = 50;
				}

				// check if we have ad-text style applied, if we we need to increase the height to additional 25px
				if ( ! empty( $data['css-class'] ) && preg_match( '/\bad-text\b/', $data['css-class'] ) ) {
					$cls_height += 25;
				}
				if ( empty( $data['css-style'] ) ) {
					$data['css-style'] = '';
				}
				$data['css-style'] = sprintf( 'min-width:%dpx;min-height:%dpx;%s', (int) $cls_width, (int) $cls_height, $data['css-style'] );
			}

		}

		return $data;
	}

	/**
	 * To Render or return an ad markup.
	 *
	 * @param array $data Ads Data.
	 * @param bool $echo should echo or not.
	 *
	 * @return string Ad template
	 *
	 * @throws Exception
	 */
	public function render_ad( array $data, $echo = false ) {

		if ( empty( $this->_script_url ) ) {
			return false;
		}

		$data = $this->_prepare_ad_data( $data );

		$template_file = sprintf( '%s/templates/ads/%s.php', untrailingslashit( PMC_ADM_DIR ), $this->get_id() );

		return PMC::render_template( $template_file, [ 'ad' => $data ], $echo );
	}

}

//EOF
