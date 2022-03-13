<?php
namespace PMC\Buy_Now;

use PMC\Global_Functions\Traits\Singleton;

class Config {
	use Singleton;

	protected function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		// Use priority 9 to allow theme override the css at default priority 10
		add_action( 'amp_post_template_css', [ $this, 'action_amp_post_template_css' ], 9 );
	}

	/**
	 * Action callback to implement buy now template override for css
	 * @return void
	 * @throws \Exception
	 */
	public function action_amp_post_template_css() {
		$css_configs = [
			'buy_button_color'    => '#ffffff',
			'buy_button_bg_color' => 'red',
		];

		$mappings = [
			'artnews'         => [],
			'billboard'       => [],
			'blogher'         => [],
			'deadline'        => [],
			'dirt'            => [],
			'goldderby'       => [],
			'footwearnews'    => [],
			'indiewire'       => [],
			'rollingstone'    => [ 'buy_button_bg_color' => '#D92227' ],
			'robbreport'      => [ 'buy_button_bg_color' => '#000000' ],
			'sheknows'        => [],
			'soaps'           => [],
			'sourcingjournal' => [],
			'sportico'        => [],
			'spy'             => [],
			'stylecaster'     => [],
			'thr'             => [ 'buy_button_bg_color' => '#D92227' ],
			'tvline'          => [],
			'variety'         => [ 'buy_button_bg_color' => '#D71440' ],
			'vibe'            => [],
			'wwd'             => [ 'buy_button_bg_color' => '#D0011B' ],
			'bgr'             => [],
		];

		$lob = \PMC::lob();
		if ( ! empty( $lob ) && ! empty( $mappings[ $lob ] ) ) {
			$css_configs = wp_parse_args( $mappings[ $lob ], $css_configs );
		}

		$template_file = sprintf( '%s/templates/amp-css.php', untrailingslashit( PMC_BUY_NOW_PLUGIN_DIR ) );
		\PMC::render_template( $template_file, $css_configs, true );
	}

}
