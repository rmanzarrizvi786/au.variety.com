<?php
/**
 * Class Injection
 *
 * Handlers for the Injection functionality.
 *
 * @package pmc-core-v2
 * @since   2017-08-29
 */

namespace PMC\Core\Inc;

/**
 * Class Injection
 *
 */
class Injection {

	use \PMC\Global_Functions\Traits\Singleton;

	/**
	 * Initialize the class
	 */
	protected function __construct() {
		add_filter( 'pmc_inject_content_paragraphs', array( $this, 'inject' ) );
	}

	/**
	 * Inject
	 *
	 * Inserts content into post content based on
	 * character count.
	 *
	 * This is based upon code from the pmc-ad-placeholders plugin.
	 *
	 * @since  2017.1.0
	 * @filter pmc_inject_content_paragraphs
	 * @see    PMC\Ad_Placeholders\Injection
	 *
	 * @param array $paragraphs An array of paragraphs.
	 *
	 * @return string Updated array of paragraphs.
	 */
	public function inject( $paragraphs = [] ) {

		// If this is an amp template, bail.
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return $paragraphs;
		}

		// Only run on single post types. Further conditions are set in the callbacks.
		if ( ! is_singular() ) {
			return $paragraphs;
		}

		global $post;
		$clean_content  = $this->clean_up_content( $post->post_content );
		$content_length = strlen( $clean_content ); // This is an approximation.
		$content_array  = explode( '</p>', $clean_content );
		$char_count     = 0;

		/*
		 * Definitions:
		 * 'pos'      = number of characters before displaying the injection
		 * 'min'      = minimum content length of the article, in characters
		 * 'inserted' = if this injection has already been used
		 * 'callback' = a non-static callback function located within this class
		 *
		 * Note that 1 word ≈ 6 characters.
		 *
		 * first ad.
		 */
		$inj = array(
			'related' => array(
				'pos'      => 100,
				'min'      => 300,
				'inserted' => false,
				'callback' => array( $this, 'inject_related_card' ),
			),
		);

		$inj = apply_filters( 'pmc_core_injection_args', $inj, $post );

		foreach ( $content_array as $index => $content ) {
			if ( ! empty( $content ) ) {
				$char_count = $char_count + ( strlen( $content ) - 3 );
				foreach ( $inj as $label => $atts ) {
					$atts['min'] = ( $atts['min'] < $atts['pos'] ) ? $atts['pos'] : $atts['min'];
					if ( $char_count > $atts['pos'] && $content_length > $atts['min'] && true !== $atts['inserted'] ) {
						$inj[ $label ]['inserted'] = true;

						if ( ! empty( $atts['callback'] ) && is_callable( $atts['callback'] ) ) {
							$paragraphs[ $index + 1 ][] = call_user_func( $atts['callback'] );
						}
					}
				}
			}
		}

		return $paragraphs;

	}

	/**
	 * Clean up Content
	 *
	 * strips shortcodes and tags from content,
	 * leaving only paragraphs.
	 *
	 * @param string $content Some content.
	 *
	 * @return string Cleaned content.
	 */
	public function clean_up_content( $content ) {
		$content = wpautop( $content );
		$content = strip_shortcodes( $content );
		$content = strip_tags( $content, '<p>' );

		return $content;
	}

	/**
	 * Inject Related Card
	 *
	 * Inserts the Related Card into the content.
	 *
	 * @since 2017.1.0
	 *
	 * @return string The card markup.
	 */
	public function inject_related_card() {

		$items = apply_filters( 'pmc_core_filter_related_articles', Related::get_instance()->get_related_items() );

		$path = locate_template( 'template-parts/article/related.php' );

		return \PMC::render_template( $path, compact( 'items' ) );
	}
}
