<?php
namespace PMC\EComm;

use PMC\Global_Functions\Traits\Singleton;

class Embed {
	use Singleton;

	protected $_hosts = [
		'amazon.com',
	];

	/**
	 * Constructor
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to initialize and setup wp hooks
	 */
	protected function _setup_hooks() : void {
		add_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10, 2 );
	}

	/**
	 * Filter to modify the iframe url to append tracking info
	 * @param string $html The resulting html content
	 * @param string $url  The url being process
	 */
	public function filter_embed_oembed_html( $html, $url ) {
		if ( preg_match( '/' . implode( '|', $this->_hosts ) . '/', $url ) ) {
			$html = preg_replace_callback(
				'/(<iframe[^>]+src=")([^"]+)(".*<.iframe>)/s',
				function ( $matches ) {
					return sprintf( '%s%s%s', $matches[1], esc_url( Tracking::get_instance()->track( $matches[2] ) ), $matches[3] );
				},
				$html
			);
		}
		return $html;
	}

}
