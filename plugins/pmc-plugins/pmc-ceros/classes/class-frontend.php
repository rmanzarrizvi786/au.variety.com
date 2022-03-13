<?php

namespace PMC\Ceros;

use \PMC\Global_Functions\Traits\Singleton;

class Frontend {

	use Singleton;

	/**
	 * __construct function of class.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		add_shortcode( 'pmc-ceros', [ $this, 'shortcode_output' ] );
	}

	/**
	 * Outputs shortcode template.
	 *
	 * @param $atts
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function shortcode_output( $atts ) : string {
		$atts = shortcode_atts(
			[
				'div_style'           => '',
				'id'                  => '',
				'aspect_ratio'        => '',
				'mobile_aspect_ratio' => '',
				'src'                 => '',
				'iframe_style'        => '',
			],
			$atts
		);

		$default_template = \PMC::render_template(
			sprintf( '%s/templates/shortcode.php', untrailingslashit( PMC_CEROS_PLUGIN_DIR ) ),
			$atts,
			false
		);

		return apply_filters( 'pmc_ceros_template', $default_template, $atts );
	}

}

// EOF
