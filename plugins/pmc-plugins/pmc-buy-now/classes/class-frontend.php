<?php

namespace PMC\Buy_Now;

use PMC\Global_Functions\Traits\Singleton;
use PMC\EComm\Tracking;

class Frontend {

	use Singleton;

	/**
	 * Identifier for the shortcode
	 *
	 * @var string
	 */
	const SHORTCODE_TAG = 'buy-now';

	/**
	 * __construct function of class.
	 *
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks
	 */
	protected function _setup_hooks() : void {
		add_shortcode( self::SHORTCODE_TAG, [ $this, 'shortcode_output' ] );
	}

	/**
	 * Calculate percentage off by providing sale and original prices.
	 *
	 * @param $sale_price
	 * @param $orig_price
	 *
	 * @return int
	 */
	public function calculate_percentage( $sale_price, $orig_price ) : int {
		$sale_price = ltrim( (string) $sale_price, '$' );
		$orig_price = ltrim( (string) $orig_price, '$' );

		if ( ! is_numeric( $sale_price ) || ! is_numeric( $orig_price ) ) {
			return 0;
		}

		if ( (float) $sale_price >= (float) $orig_price ) {
			return 0;
		}

		return (int) round( ( $orig_price - (float) $sale_price ) / ( (float) $orig_price ) * 100 );
	}

	/**
	 * Outputs shortcode template.
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function shortcode_output( array $atts, string $content = '' ) : string {

		$defaults = [
			'link'       => '',
			'target'     => '',
			'text'       => '',
			'price'      => '',
			'orig_price' => '',
			'percentage' => '',
		];
		$options  = (array) apply_filters( 'pmc_buy_now_options', [] );

		foreach ( $options as $option ) {
			$defaults[ $option['name'] ] = $option['default'] ?? '';
		}

		$defaults['button_type'] = 'default';

		$atts = shortcode_atts(
			$defaults,
			$atts
		);

		$atts['link']       = Tracking::get_instance()->track( $atts['link'] );
		$atts['percentage'] = $this->calculate_percentage( $atts['price'], $atts['orig_price'] );

		if ( ! empty( $atts['price'] ) && 0 !== strpos( $atts['price'], '$' ) && is_numeric( $atts['price'] ) ) {
			$atts['price'] = '$' . $atts['price'];
		}

		$buy_now_data = [
			'data'         => array_merge( $atts, [ 'content' => $content ] ),
			'amp_template' => sprintf( '%s/templates/shortcode-amp.php', untrailingslashit( PMC_BUY_NOW_PLUGIN_DIR ) ),
			'template'     => sprintf( '%s/templates/shortcode.php', untrailingslashit( PMC_BUY_NOW_PLUGIN_DIR ) ),
		];

		$buy_now_data  = apply_filters( 'pmc_buy_now_data', $buy_now_data );
		$template_html = '';

		if ( isset( $buy_now_data['template_html'] ) ) {
			$template_html = $buy_now_data['template_html'];
		} else {
			if ( \PMC::is_amp() ) {
				$template_file = $buy_now_data['amp_template'];
			} else {
				$template_file = $buy_now_data['template'];
			}
			if ( ! empty( $buy_now_data['data']['link'] ) && ! empty( $template_file ) ) {
				$template_html = \PMC::render_template(
					$template_file,
					$buy_now_data['data'],
					false
				);
			}
		}

		return $template_html;
	}

}

// EOF
