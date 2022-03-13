<?php
/**
 * Protected Embed Shortcode main class.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since 2018-09-07 READS-1421
 */

namespace PMC\Protected_Embeds;

use PMC\Global_Functions\Traits\Singleton;

class Shortcode {

	use Singleton;

	const OPTION_NAME = 'pmc-protected-embeds-domain-whitelist';

	/**
	 * Shortcode instance render count.
	 *
	 * @var int
	 */
	protected $_render_counter = 0;

	/**
	 * Shortcode constructor.
	 */
	protected function __construct() {
		add_filter( 'pmc_global_cheezcap_options', [ $this, 'add_cheezcap_options' ] );
		add_shortcode( 'pmc-protected-embed', [ $this, 'render' ] );
	}

	/**
	 * Add cheezcap textarea to fetch whitelisted domain names for protected embeds.
	 *
	 * @param array $cheezcap_options list of cheezcap options.
	 *
	 * @return array
	 */
	public function add_cheezcap_options( array $cheezcap_options = [] ) : array {

		$cheezcap_options[] = new \CheezCapTextOption(
			__( 'Protected embeds domain whitelist', 'pmc-protected-embeds' ),
			__( 'This whitelist specifies the domains from which embeds can be added in posts etc. using Protected Embeds feature. This list accepts comma separated values [e.g music.amazon.com,art19.com].', 'pmc-protected-embeds' ),
			self::OPTION_NAME,
			'',
			true,
			false
		);

		return $cheezcap_options;
	}

	/**
	 * Protected embed shortcode handler.
	 *
	 * @param array  $atts    shortcode attributes.
	 * @param string $content current post content.
	 *
	 * @return string
	 */
	public function render( array $atts = [], string $content = '' ) : string {

		$default_atts = [
			'src'             => '',
			'width'           => '300',
			'height'          => '250',
			'allowfullscreen' => 'true',
		];

		$atts            = shortcode_atts( $default_atts, $atts );
		$width           = $atts['width'];
		$height          = $atts['height'];
		$src             = $atts['src'];
		$allowfullscreen = ( 'true' === strtolower( $atts['allowfullscreen'] ) ) ? 'true' : 'false';

		if ( ! empty( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( ! $this->is_whitelisted_domain( $src ) ) {
			return $content;
		}

		$this->_render_counter++;

		$embed = sprintf(
			'<iframe id="%s" class="pmc-protected-embed" width="%s" height="%s" src="%s" frameborder="0" scrolling="no" allowfullscreen="%s"></iframe>',
			esc_attr( 'pmc-protected-embed-' . $this->_render_counter ),
			esc_attr( $width ),
			esc_attr( $height ),
			esc_url( $src ),
			esc_attr( $allowfullscreen )
		);

		return $embed . $content;

	}

	/**
	 * To check embed domain is whitelisted.
	 *
	 * @param string $url protected embed source url.
	 *
	 * @return bool
	 */
	public function is_whitelisted_domain( string $url ) : bool {

		if ( empty( $url ) ) {
			return false;
		}

		$whitelisted_domains_list = \PMC_Cheezcap::get_instance()->get_option( self::OPTION_NAME );
		$whitelisted_domains_list = trim( $whitelisted_domains_list );
		$whitelisted_domains_list = str_replace( ' ', '', $whitelisted_domains_list );

		if ( empty( $whitelisted_domains_list ) ) {
			return false;
		}

		$whitelisted_domains = explode( ',', $whitelisted_domains_list );
		$parsed_url          = wp_parse_url( $url );

		if ( is_array( $parsed_url ) && ! empty( $parsed_url['host'] ) &&
			( empty( $parsed_url['scheme'] ) || 'https' === $parsed_url['scheme'] ) &&
			is_array( $whitelisted_domains ) && in_array( $parsed_url['host'], (array) $whitelisted_domains, true )
		) {
			return true;
		}

		return false;
	}
}
