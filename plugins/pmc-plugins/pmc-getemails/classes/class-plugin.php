<?php
/**
 * PMC GetEmails
 *
 * @author Reef Fanous <rfanous@pmc.com>
 *
 * @group pmc-getemails
 */
namespace PMC\GetEmails;

use PMC\Global_Functions\Traits\Singleton;

class Plugin {

	use Singleton;

	/**
	 * Construct
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() : void {
		add_action( 'wp_enqueue_scripts', [ $this, 'action_wp_enqueue_scripts' ] );
		add_filter( 'script_loader_tag', [ $this, 'filter_script_loader_tag' ], 10, 2 );
		add_filter( 'js_do_concat', [ $this, 'filter_do_concat' ], 10, 2 );
	}

	/**
	 * Enqueue PMC GetEmails js file
	 *
	 */
	public function action_wp_enqueue_scripts() : void {
		$id = apply_filters( 'pmc_getemails_id', '' );

		if (
			false === apply_filters( 'pmc_getemails_js', true )
			|| empty( $id )
		) {
			return;
		}

		wp_enqueue_script( 'pmc-getemails-js', plugins_url( 'js/getemails.js', __DIR__ ), [], PMC_GETEMAILS_VERSION );

		wp_localize_script(
			'pmc-getemails-js',
			'pmc_getemails',
			[ 'id' => $id ]
		);
	}

	/**
	 * Filter function to add GDPR privacy cookie blocking.
	 *
	 * @param string $tag Enqueued script tag.
	 * @param string $handle Enqueued script handle.
	 *
	 * @return string
	 */
	public function filter_script_loader_tag( $tag, $handle ) : string {
		$blocker_atts = [
			'type'  => 'text/javascript',
			'class' => '',
		];

		if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
			$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0004' );
		}

		if ( 'pmc-getemails-js' === $handle ) {
			if ( false === strpos( $tag, 'type' ) ) {
				return str_replace( '<script', sprintf( '<script type=\'%s\' class=\'%s\'', esc_attr( $blocker_atts['type'] ), esc_attr( $blocker_atts['class'] ) ), $tag );
			} else {
				return str_replace( '<script type=\'text/javascript\'', sprintf( '<script type=\'%s\' class=\'%s\'', esc_attr( $blocker_atts['type'] ), esc_attr( $blocker_atts['class'] ) ), $tag );
			}
		}

		return $tag;
	}

	/**
	 * Filter for whether or not to concat js files
	 * script tags are added to getemails js tag so need to exclude from concat
	 *
	 * @param bool $do_concat
	 * @param string $handle
	 *
	 * @return bool
	 */
	public function filter_do_concat( $do_concat, $handle ) : bool {
		$do_concat = ( 'pmc-getemails-js' === $handle ) ? false : $do_concat;

		return $do_concat;
	}

}
