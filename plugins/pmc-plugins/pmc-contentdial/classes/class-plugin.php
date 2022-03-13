<?php
/**
 * Class for PMC Content Dial Shortcode
 *
 * @since 2019-03-07 - Vinod Tella - ROP-1789
 */

namespace PMC\ContentDial;

use \PMC\Global_Functions\Traits\Singleton;

class Plugin {

	use Singleton;

	/**
	 * constructor
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		add_shortcode( 'pmc-contentdial', [ $this, 'register_contentdial_shortcode' ] );
	}

	/**
	 * Register ContentDial shortcode.
	 *
	 * @param array $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function register_contentdial_shortcode( $atts = [], $content = '' ) {

		if ( empty( $atts['id'] ) ) {
			return $content;
		}

		$tag = sprintf(
			'<script src="%s" data-branded-content-id="%s"></script>',
			esc_url( sprintf( 'https://contentdial.com/contentdial-snowplow.js?bciid=%s', $atts['id'] ) ),
			esc_attr( $atts['id'] )
		);

		return $content . $tag;
	}

}
