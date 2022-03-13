<?php
/**
 * Base class for ad providers
 */

abstract class PMC_Ad_Provider {

	protected $_id;
	protected $_key;
	protected $_title;
	protected $_config = array();
	protected $_fields = array();
	protected $zone;
	protected $hostname;
	protected $sitename;

	/**
	 * Store the key.
	 *
	 * @param string $key
	 * @param array $config
	 */
	public function __construct( $key, array $config = array() ) {
		$this->_key = $key;
		$this->_config = $config + $this->_config;

		if ( isset( $this->_config['default_fields'] ) ) {
			foreach ( $this->_config['default_fields'] as $key => $value ) {
				if ( !isset( $this->_fields[ $key ] ) ) {
					continue;
				}
				$this->_fields[ $key ]['default'] = $value;
			}
		}

		add_action( 'wp_head', [ $this, 'action_wp_enqueue_scripts' ] );

		$this->include_assets();
	}

	/**
	 * Enqueue Google Publisher Tag script.
	 */
	public function action_wp_enqueue_scripts() {
		// Ignoring coverage here. This code is needed temporarily until we move to V2 version.
		// @codeCoverageIgnoreStart
		if ( true === apply_filters( 'pmc_adm_load_google_gpt_script_js', true ) && false === PMC_Ads::get_instance()->get_provider( 'boomerang' ) ) {
			$blocker_atts = [
				'type'  => 'text/javascript',
				'class' => '',
			];

			if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {

				$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0004' );
			}
			?>
			<script class="<?php echo esc_attr($blocker_atts['class']);?>" type="<?php echo esc_attr($blocker_atts['type']);?>" src="https://securepubads.g.doubleclick.net/tag/js/gpt.js" async></script>
			<?php
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Return a list of extra fields.
	 *
	 * @return array
	 */
	public function get_fields() {
		return apply_filters( 'pmc_ad_provider_fields', $this->_fields, $this );
	}

	/**
	 * Return the provider key.
	 *
	 * @return string
	 */
	public function get_key() {
		return $this->_key;
	}

	/**
	 * Return the provider ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->_id;
	}

	/**
	 * Return the provider title.
	 *
	 * @return mixed
	 */
	public function get_title() {
		return $this->_title;
	}

	/**
	 * Return default params.
	 *
	 * @return array
	 */
	public function get_params() {
		return array();
	}

	/**
	 * Return a list of keywords from the request.
	 *
	 * @return array
	 */
	public function get_keywords( $keywords_limit = false ) {
		global $post;

		$keywords = array();
		$keywords_limit = apply_filters( 'pmc_adm_custom_keywords_limit', $keywords_limit );

		// all taxonomy pages
		if ( is_tax() || is_tag() || is_category() ) {
			if ( $term = get_queried_object() ) {
				$keywords[] = $term->slug;
			}
		}

		if ( ( is_single() || is_page() ) && !empty( $post->ID ) ) {

			$keywords_taxonomies = apply_filters( 'pmc_adm_custom_keywords_taxonomies', array( 'category', 'post_tag', 'editorial', 'vertical' ) );

			foreach ( $keywords_taxonomies as $taxonomy ) {
				$terms = get_the_terms( $post->ID, $taxonomy );

				if ( empty( $terms ) || is_wp_error( $terms ) ) {
					continue;
				}

				$keywords = array_merge( $keywords, wp_list_pluck( array_values( $terms ), 'slug' ) );
			}

		}

		if ( !empty( $keywords_limit ) && $keywords_limit > 0 ) {
			$keywords = array_slice( array_unique( $keywords ), 0, intval( $keywords_limit ) );
		}

		$keywords = apply_filters( 'pmc_adm_custom_keywords', $keywords );

		return array_filter( array_unique( $keywords ) );
	}

	/**
	 * Return a list of terms list for given post and taxonomies from the request.
	 *
	 * @param int $keywords_limit
	 *
	 * @return array
	 */
	public function get_topics( $keywords_limit = 0 ) {

		global $post;

		$keywords = array();

		$keywords_limit      = apply_filters( 'pmc_adm_topic_keywords_limit', $keywords_limit );
		$keywords_taxonomies = apply_filters( 'pmc_adm_topic_keywords_taxonomies', [ 'editorial' ] );
		$keywords_post_types = apply_filters( 'pmc_adm_topic_keywords_post_types', [ 'post', 'pmc_top_video', 'pmc-gallery', 'pmc_list' ] );

		if ( ! empty( $post->ID ) && is_singular( $keywords_post_types ) ) {

			foreach ( $keywords_taxonomies as $taxonomy ) {
				$terms = get_the_terms( $post->ID, $taxonomy );

				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$keywords = array_merge( $keywords, wp_list_pluck( array_values( $terms ), 'slug' ) );
				}
			}
		}

		if ( ! empty( $keywords_limit ) && $keywords_limit > 0 ) {
			$keywords = array_slice( array_unique( (array) $keywords ), 0, intval( $keywords_limit ) );
		}

		$keywords = apply_filters( 'pmc_adm_topic_keywords', $keywords, $keywords_taxonomies, $keywords_post_types );

		return array_filter( array_unique( (array) $keywords ) );
	}

	public function get_zone() {

		if ( !empty( $this->zone ) ) {
			return $this->zone;
		}

		$ad_zone = 'ros'; // default ad zone

		global $wp_query;

		if ( is_home() ) {
			$ad_zone = 'homepage';
		} elseif ( is_category() ) {
			$category = get_category( get_query_var( 'cat' ) );
			$ad_zone  = $category->slug;
		} elseif ( is_tag() ) {
			$tag = get_tag( get_query_var( 'tag' ) );

			if ( empty( $tag ) ) {
				$tag = $wp_query->get_queried_object();
			}

			$ad_zone = $tag->slug;
		} elseif ( is_single() || is_page() ) {
			// Hack to determine if this is a gallery page
			if ( strpos( $_SERVER['REQUEST_URI'], '/gallery/' ) !== false ) {
				$ad_zone = 'pics';
			}
		}

		$this->zone = $ad_zone;

		return $ad_zone;
	}

	/**
	 * Include any 3rd-party scripts.
	 */
	abstract public function include_assets();

	/**
	 * Render an ad.
	 */
	abstract public function render_ad( array $data, $echo = false );

	/**
	 * Return the Admin UI templates from /pmc-adm/templates/provider-admin/*.php
	 */
	abstract public function get_admin_templates();

}

//EOF
