<?php
/**
 * Configure PWA plugin.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;

class PMC_PWA {
	use Singleton;

	/**
	 * PMC_PWA constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_filter( 'web_app_manifest', [ $this, 'add_icons_to_manifest' ] );
	}

	/**
	 * Add theme icons to manifest.
	 *
	 * @param array $manifest Manifest elements.
	 * @return array
	 */
	public function add_icons_to_manifest( array $manifest ): array {
		$sizes = [
			72,
			100,
			128,
			144,
			152,
			196,
			256,
			512,
		];

		$manifest['icons'] = [];

		foreach ( $sizes as $size ) {
			$manifest['icons'][] = [
				'src'   => esc_url_raw(
					sprintf(
						'%1$s/assets/app/icons/icon-%2$sx%2$d.png',
						CHILD_THEME_URL,
						$size
					)
				),
				'sizes' => "${size}x${size}",
				'type'  => 'image/png',
			];
		}

		return $manifest;
	}
}
