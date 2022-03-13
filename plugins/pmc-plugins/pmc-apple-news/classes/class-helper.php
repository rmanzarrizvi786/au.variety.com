<?php
/**
 * Class for helper methods.
 *
 * @since 2020-03-31
 *
 * @package pmc-apple-news
 */

namespace PMC\Apple_News;

use \PMC\Global_Functions\Traits\Singleton;

class Helper {

	use Singleton;

	const ENCODED_MARKER = 'pmc-apple-news-base64-decode:';

	/**
	 * @var bool Flag to determine if Apply News is rendering.
	 */
	private $is_rendering_json = false;

	/**
	 * Construct Mehod.
	 *
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * To setup actions/filters.
	 *
	 * @return void
	 */
	private function _setup_hooks() {
		add_action( 'apple_news_do_fetch_exporter', [ $this, 'action_apple_news_do_fetch_exporter' ], 1, 1 ); // Need to call as early as possilbe.
		add_filter( 'apple_news_exporter_content', [ $this, 'filter_apple_news_exporter_content' ], PHP_INT_MAX, 2 ); // Need to call as late as possible.
		add_filter( 'pmc_ecommerce_source', [ $this, 'filter_pmc_ecommerce_source' ] );
	}

	public function filter_pmc_ecommerce_source( $source ) {
		if ( $this->is_rendering_content() ) {
			$source = 'apple-news';
		}
		return $source;
	}

	/**
	 * Helper method to determine if Apple News is rendering JSON for a post-content.
	 *
	 * @return bool
	 */
	public function is_rendering_content() {
		return $this->is_rendering_json;
	}

	/**
	 * This filter is called when Apple News is processing the post-content,
	 * hence we set the flag to true here.
	 */
	public function action_apple_news_do_fetch_exporter() : void {
		$this->is_rendering_json = true;
	}

	/**
	 * This filter is called after Apple News has finished processing the post-content,
	 * hence we set the flag to false here.
	 */
	public function filter_apple_news_exporter_content( $content ) {
		$this->is_rendering_json = false;
		return $content;
	}

	/**
	 * Helper function to do base64 encode allow embed json data injecting into template before apple new generate json data
	 * @param array $data
	 * @param false $echo
	 * @return string
	 */
	public function wrap_json_data( array $data, $echo = false ) : string {
		$data = '<p>' . self::ENCODED_MARKER . base64_encode( wp_json_encode( $data ) ) . '</p>';
		if ( $echo ) {
			echo wp_kses_post( $data );
		}
		return $data;
	}

	/**
	 * Helper function do decode data from :wrap_json_data
	 * @param string $data
	 * @return mixed|string
	 */
	public function unwrap_json_data( string $data ) {
		if ( false !== strpos( $data, self::ENCODED_MARKER ) ) {
			$data = wp_strip_all_tags( $data );
			$data = substr( $data, strlen( self::ENCODED_MARKER ) );
			$data = json_decode( base64_decode( $data ), true );
		}
		return $data;
	}

	/**
	 * Helper function to decode APN json data that get injected by template via ::wrap_json_data
	 * @param array $components
	 * @return array
	 */
	public function unwrap_json_components( array $components ) : array {
		foreach ( $components as $key => $component ) {
			if ( ! empty( $component['components'] ) ) { // Post with featured image.
				$component['components'] = $this->unwrap_json_components( $component['components'] );
			} elseif ( ! empty( $component['text'] ) ) {
				$json = Helper::get_instance()->unwrap_json_data( $component['text'] );
				if ( is_array( $json ) ) {
					$component = $json;
				}
			}
			$components[ $key ] = $component;
		}
		return $components;
	}

}
