<?php
/**
 * Display image credit even when captions are not filled out
 * Filter to convert inline <img> tag to [caption] shortcode to display credit field on frontend
 *
 * @since 2020-03-20 ROP-2064
 *
 * Ref: pmc-robbreport-2017-v2/src/master/inc/classes/class-media.php
 */

namespace PMC\Global_Functions;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

class Image_Credit {

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup actions and filters.
	 */
	protected function _setup_hooks() {

		/**
		 * Filters.
		 */
		add_filter( 'the_content', [ $this, 'maybe_add_caption_shortcode' ], 8 ); // Prioritize before do_shortcode

	}


	/**
	 * If an image is missing a caption shortcode, generate one on the fly if we can.
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function maybe_add_caption_shortcode( $content ): string {

		if ( ! empty( $content ) && true === apply_filters( 'pmc_image_credit_show_without_caption', false ) ) {

			// Regex https://regex101.com/r/HHHnm4/2
			$pattern = '/.*(?!\]).(<img([^>]*)>)/im';
			$content = preg_replace_callback(
				$pattern,
				[ $this, '_inject_missing_caption_shortcode' ],
				wpautop( $content )
			);

		}

		return (string) $content;

	}

	/**
	 * Checks that a matching capture group has all the information needed
	 * to generate a caption shortcode.
	 *
	 * @param $matches
	 *
	 * @return string
	 */
	protected function _inject_missing_caption_shortcode( array $matches ) : string {

		$tag = (string) $matches[0];

		if ( 3 === count( $matches ) && ! has_shortcode( $matches[0], 'caption' ) ) {

			$atts      = shortcode_parse_atts( $matches[2] );
			$width     = $atts['width'] ?? '';
			$alignment = 'alignnone';
			$id        = 0;
			$p         = '';

			// If we don't have a width, bail.
			if ( empty( $width ) ) {
				return $tag;
			}

			if ( preg_match( '/wp-image-(\d+)/i', $atts['class'], $match ) ) {

				if ( 2 === count( $match ) ) {
					$id = intval( $match[1] );
				}

			}

			// If we don't have a valid ID, bail.
			if ( 1 > intval( $id ) ) {
				return $tag;
			}

			if ( preg_match( '/(align[^\s]+)/i', $atts['class'], $match ) ) {

				if ( 2 === count( $match ) ) {
					$alignment = $match[1];
				}

			}

			if ( preg_match( '/^<p>.+/i', $tag, $match ) ) {
				if ( 1 === count( $match ) ) {
					$p = '<p>';
				}
			}

			$tag = sprintf(
				'%1$s[caption id="attachment_%2$d" align="%3$s" width="%4$d"]%5$s[/caption]',
				$p,
				$id,
				$alignment,
				$width,
				$matches[1] // just the matched img tag
			);

		}

		return $tag;

	}

} //end of class

Image_Credit::get_instance();

//EOF
