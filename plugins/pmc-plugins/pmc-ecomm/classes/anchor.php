<?php
namespace PMC\EComm;

use PMC\Global_Functions\Traits\Singleton;
use PMC;
use Exception;

/**
 * Process all <a> anchor link to allow additional changes to the url link via filter
 */
class Anchor {
	use Singleton;

	const FILTER_URL = 'pmc_ecomm_anchor_url';

	/*
	 * Constructor
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup all wp web hooks
	 */
	protected function _setup_hooks() {
		add_filter( 'the_content', [ $this, 'process_content' ] );
		add_filter( 'the_content_rss', [ $this, 'process_content' ] );
		add_filter( 'the_content_feed', [ $this, 'process_content' ] );
	}

	/**
	 * Process all <a> anchor link and add tracking info
	 * @see PMC\Affiliate_Links\Tagger::process_single_content
	 * @param string $content
	 * @return array|mixed|string|string[]|null
	 */
	public function process_content( $content ) {

		$can_process = is_feed() || is_single();
		// Take into account BGR landing pages
		if ( function_exists( 'bgr_is_single' ) ) {
			$can_process = is_feed() || bgr_is_single();
		}

		if ( ! $can_process ) {
			return $content;
		}

		$display_content = wpautop( $content );

		try {
			$doc = \PMC_DOM::load_dom_content( $display_content );

			if ( false !== $doc ) {

				$a_nodes = \PMC_DOM::get_dom_links( $doc );

				if ( false !== $a_nodes ) {

					$modified = false;

					// Loop through the <a> nodes and inject the tracking info
					for ( $i = 0; $i < $a_nodes->length; ++$i ) {

						$a_node  = $a_nodes->item( $i );
						$old_url = $a_node->getAttribute( 'href' );
						$url     = Tracking::get_instance()->track( $a_node->getAttribute( 'href' ) );
						$url     = apply_filters( self::FILTER_URL, $url, $a_node );
						if ( $url !== $old_url ) {
							$modified = true;
							$a_node->setAttribute( 'href', $url );
						}

					}

					if ( $modified ) {
						// Extract the HTML from the BODY tag
						$body    = $doc->getElementsByTagName( 'body' )->item( 0 );
						$content = \PMC_DOM::domnode_get_innerhtml( $body );
					}

				}

			}

		} catch ( Exception $e ) {
			$content = PMC::maybe_throw_exception( $e->getMessage(), 'Exception', $content );
		}

		return $content;
	}

}
