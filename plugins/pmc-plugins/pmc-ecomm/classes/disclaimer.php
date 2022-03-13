<?php
/**
 * Class to add Ecomm Disclaimer post option.
 *
 * @author  Ebonie Butler <ebonie@yikesinc.com>
 *
 * @since   2021-10-13
 * @package PMC\EComm
 */

namespace PMC\EComm;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Post_Options\API;

/**
 * Add Post Option
 */
class Disclaimer {

	use Singleton;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {
		add_action( 'init', [ $this, 'register_post_option' ] );

		// Make sure taxonomy terms is saved before checking if post has it.
		add_action( 'save_post', [ $this, 'set_post_option_on_save' ], 11, 2 );

		// Delay filter to make sure disclaimer shows up before anything else.
		add_filter( 'the_content', [ $this, 'maybe_prepend_html' ], 11 );
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_disclaimer_styles' ], 10 );
		add_filter( 'apple_news_exporter_content_pre', [ $this, 'maybe_prepend_disclaimer' ], 10, 2 );
		add_filter( 'apple_news_component_text_styles', [ $this, 'register_apple_news_custom_component_text_styles' ] );
	}

	/**
	 * Register post option term to filter sponsored posts.
	 *
	 * @return void
	 */
	public function register_post_option() : void {
		$term = $this->get_post_option();

		API::get_instance()->register_global_options(
			[
				sanitize_key( $term['slug'] ) => [
					'label' => sanitize_text_field( $term['name'] ),
				],
			]
		);
	}

	/**
	 * Get the slug of post option.
	 *
	 * @return array
	 */
	public function get_post_option() : array {
		$default = [
			'name' => __( 'Add Ecomm Disclaimer', 'pmc-ecomm' ),
			'slug' => 'add-ecomm-disclaimer',
		];

		$post_option = (array) apply_filters( 'pmc_ecomm_post_option', $default );

		if ( empty( $post_option['slug'] ) || empty( $post_option['name'] ) ) {
			return $default;
		}

		return $post_option;
	}

	/**
	 * Add Ecomm Disclaimer to Global Curation menu.
	 *
	 * @param int      $post_ID Post ID.
	 * @param \WP_Post $post    Post object.
	 *
	 * @return bool
	 */
	public function set_post_option_on_save( int $post_ID, \WP_Post $post ) : bool {

		// If post is being autosaved, an autodraft or a revision, bye.
		if ( wp_is_post_autosave( $post_ID ) || 'auto-draft' === $post->post_status || 'inherit' === $post->post_status ) {
			return false;
		}

		// If post option has already been enabled once, ignore.
		$enabled_once = get_post_meta( $post_ID, 'pmc_ecomm_enabled_once', true );
		if ( $enabled_once ) {
			return false;
		}

		$default_tax_terms = [
			'category' => [],
			'post_tag' => [],
		];

		/**
		 * Filters the taxonomy terms in which to apply ecomm disclaimer. Term slug must be added
		 * to relative taxonomy slug. (i.e. 'category' => [uncategorized])
		 *
		 * @param array     $default_tax_terms Associative array of taxonomy slugs and their array of term slugs.
		 */
		$default_tax_terms = apply_filters( 'pmc_ecomm_default_tax_terms', $default_tax_terms );

		$has_the_term = false;

		foreach ( $default_tax_terms as $taxonomy_slug => $term_slugs ) {
			foreach ( $term_slugs as $term_slug ) {
				if ( has_term( $term_slug, $taxonomy_slug, $post ) ) {
					$has_the_term = true;
					break 2;
				}
			}
		}

		if ( ! $has_the_term ) {
			return false;
		}

		// Get "Disclaimer" post option.
		$term = $this->get_post_option();

		// Hierarchical taxonomies must use term id versus slugs with wp_set_post_terms function.
		$post_option_obj = get_term_by( 'slug', $term['slug'], '_post-options' );

		// Add disclaimer post option to post.
		wp_set_post_terms( $post_ID, $post_option_obj->term_id, '_post-options', true );

		// Record that post option has been automatically enabled.
		update_post_meta( $post_ID, 'pmc_ecomm_enabled_once', true );
		return true;
	}

	/**
	 * Method to prepend disclaimer HTML if checks pass.
	 *
	 * @param string $content
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	public function maybe_prepend_html( string $content ) : string {

		global $post;
		global $wp_current_filter;

		// If content is NOT being called from a single page or feed, don't add disclaimer.
		// If content is being called for an excerpt, don't add disclaimer.
		if ( ( ! is_singular() && ! is_feed() )
			|| in_array( 'get_the_excerpt', (array) $wp_current_filter, true )
		) {
			return $content;
		}

		$enabled_once = get_post_meta( $post->ID, 'pmc_ecomm_enabled_once', true );

		// If post option isn't enabled
		// and has been manually disabled, don't add disclaimer.
		if ( ! API::get_instance()->post( $post->ID )->has_option( $this->get_post_option()['slug'] )
		&& ! empty( $enabled_once )
		) {
			return $content;
		}

		$show_disclaimer_by_default = apply_filters( 'pmc_ecomm_display_disclaimer_by_default', false );

		// If post option isn't enabled
		// and has not been manually disabled
		// and the disclaimers are not set to show by default don't add disclaimer.
		if ( ! API::get_instance()->post( $post->ID )->has_option( $this->get_post_option()['slug'] )
		&& empty( $enabled_once )
		&& ! $show_disclaimer_by_default
		) {
			return $content;
		}

		$template_dir = apply_filters( 'pmc_ecomm_disclaimer_template', sprintf( '%s/templates/ecomm-disclaimer.php', PMC_ECOMM_DIR ) );

		if ( file_exists( $template_dir ) ) {
			$template = \PMC::render_template(
				$template_dir
			);
		} else {
			$template = \PMC::render_template(
				sprintf( '%s/templates/ecomm-disclaimer.php', PMC_ECOMM_DIR )
			);
		}


		$content = $template . $content;

		return $content;
	}

	/**
	 * Enqueue styles.
	 *
	 * @return void
	 */
	public function wp_enqueue_disclaimer_styles() : void {
		wp_enqueue_style( 'pmc-ecomm-styles', sprintf( '%s/css/style.css', PMC_ECOMM_URL ), [], PMC_ECOMM_VERSION );
	}

	/**
	 * Function to add custom style for disclaimer.
	 *
	 * @param array $style Array of registered component text styles
	 *
	 * @return array
	 */
	public function register_apple_news_custom_component_text_styles( $style = [] ) : array {

		// Define new text style to include in disclaimer component.
		$style['disclaimer-text-style'] = apply_filters(
			'pmc_ecomm_apple_news_disclaimer_text_style',
			[
				'textColor' => 'grey',
				'fontSize'  => 14,
			]
		);

		return $style;
	}

	/**
	 * Filter apple news content to include disclaimer.
	 *
	 * @param array   $content Post content.
	 * @param integer $post_id Post id.
	 *
	 * @return string post content with disclaimer text
	 */
	public function maybe_prepend_disclaimer( $content = '', $post_id = 0 ) : string {
		// Ignore if post does not have "Add Ecomm Disclaimer" option enabled.
		if ( ! API::get_instance()->post( $post_id )->has_option( $this->get_post_option()['slug'] ) ) {
			return $content;
		}

		$plugin_template = sprintf( '%s/templates/ecomm-disclaimer.php', PMC_ECOMM_DIR );
		// Get disclaimer text.
		$template = apply_filters( 'pmc_ecomm_disclaimer_template', $plugin_template );

		if ( ! file_exists( $template ) ) {
			$template = $plugin_template;
		}

		$disclaimer = \PMC::render_template(
			$template
		);

		return $disclaimer . $content;
	}
}
