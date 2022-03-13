<?php
/**
 * Class responsible for creating and rendering shortcode
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2020-08-11
 */

namespace PMC\Ceros_Embeds;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC;


class Shortcode {

	use Singleton;

	const TAG = 'pmc-ceros-embed';

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore This is class constructor. Method calls here have their own individual tests.
	 */
	protected function __construct() {
		$this->_register();
	}

	/**
	 * Method to register shortcode with WP
	 *
	 * @return void
	 */
	protected function _register() : void {

		add_shortcode( self::TAG, [ $this, 'parse_it' ] );

	}

	/**
	 * Method which parses our shortcode and returns the HTML for render
	 *
	 * @param array $atts
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	public function parse_it( $atts = [] ) : string {

		$atts = ( ! is_array( $atts ) ) ? [] : $atts;
		$html = '';

		$atts = shortcode_atts(
			[
				'container_id'                  => '',
				'container_style'               => '',
				'container_aspect_ratio'        => '',
				'container_mobile_aspect_ratio' => '',
				'iframe_src'                    => '',
				'iframe_style'                  => '',
				'iframe_css'                    => '',
				'iframe_title'                  => '',
			],
			$atts
		);

		$container_id = sprintf( '%s-%s', Admin::ID, time() );

		$html = PMC::render_template(
			sprintf( '%s/templates/frontend/shortcode.php', PMC_CEROS_EMBEDS_ROOT ),
			[
				'id'   => $container_id,
				'css'  => sprintf( '%s %s', Admin::ID, $container_id ),
				'atts' => $atts,
			]
		);

		return $html;

	}

}    // end class

//EOF
