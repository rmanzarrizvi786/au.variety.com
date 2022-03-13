<?php
namespace PMC\FAQ;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Utility
 *
 * Includes pmc-faq shortcode, property to verify if
 * the shortcode is within post content, and static assets.
 */
class Utility {

	use Singleton;

	const SHORTCODE = 'pmc-faq';

	protected $_faq_shortcode_in_content = false;

	/**
	 * Utility constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() : void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_larvaless' ] );

		// Set priority 1 to check shortcode before it renders.
		add_filter( 'the_content', [ $this, 'content_has_faq_shortcode' ], 1 );
		add_filter( 'pmc-google-amp-styles', [ $this, 'get_amp_styles' ] );

		add_shortcode( self::SHORTCODE, [ $this, 'faq_shortcode' ] );
	}

	/**
	 * Enqueue Limited larva CSS if Larva is not enabled on site.
	 */
	public function enqueue_larvaless() : void {
		$is_larva_active = apply_filters(
			'pmc_faq_larva_active',
			(bool) ( defined( 'PMC_LARVA_ACTIVE' ) && true === PMC_LARVA_ACTIVE )
		);

		if (
			empty( $is_larva_active )
			&& is_single()
		) {
			\PMC\Global_Functions\Styles::get_instance()->inline(
				'pmc-faq-larva',
				rtrim( PMC_FAQ_PATH, '/' ) . '/assets/css/'
			);
		}
	}

	/**
	 * Set _faq_shortcode_in_content property if the pmc-faq shortcode is present in post content.
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function content_has_faq_shortcode( $content ) : string {
		$this->_faq_shortcode_in_content = ( has_shortcode( $content, self::SHORTCODE ) );

		return $content;
	}

	/**
	 * Getter for the value of _faq_shortcode_in_content.
	 *
	 * @return bool
	 */
	public function is_faq_shortcode_in_content() : bool {
		return $this->_faq_shortcode_in_content;
	}

	/**
	 * FAQ shortcode.
	 *
	 * @return string
	 */
	public function faq_shortcode() : string {
		$post     = get_post();
		$template = '';

		if ( ! is_a( $post, '\WP_Post' ) ) {
			return $template;
		}

		$questions   = get_post_meta( $post->ID, 'pmc_faq_questions', true );
		$title       = get_post_meta( $post->ID, 'pmc_faq_title', true );
		$description = get_post_meta( $post->ID, 'pmc_faq_description', true );

		if ( empty( $title ) || empty( $questions ) ) {
			return $template;
		}

		$template = (string) \PMC::render_template(
			apply_filters( 'pmc_faq_template', sprintf( '%s/templates/faq.php', PMC_FAQ_PATH ) ),
			[
				'title'       => $title,
				'description' => $description,
				'questions'   => $questions,
			]
		);

		return $template;
	}

	/**
	 * Include styles for AMP pages.
	 *
	 * @param $styles
	 *
	 * @return string
	 */
	public function get_amp_styles( $styles ) : string {
		return $styles . \PMC::render_template( sprintf( '%s/assets/css/pmc-faq-larva.css', PMC_FAQ_PATH ) );
	}

}
