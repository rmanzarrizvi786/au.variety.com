<?php
/**
 * Manage image sizes used with Digital Daily designs.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Images.
 */
class Images {
	use Singleton;

	/**
	 * Maximum image width used by Digital Daily designs.
	 */
	protected const WIDTH = 1260;

	/**
	 * Image details.
	 */
	public const SIZES = [
		'digital-daily-1-1'         => [
			'width'  => self::WIDTH,
			'height' => self::WIDTH,
			'ratio'  => '1:1',
		],
		'digital-daily-2-3'         => [
			'width'  => self::WIDTH,
			'height' => 1890,
			'ratio'  => '2:3',
		],
		'digital-daily-3-4'         => [
			'width'  => self::WIDTH,
			'height' => 1680,
			'ratio'  => '3:4',
		],
		'digital-daily-3-4-no-crop' => [
			'width'  => self::WIDTH,
			'height' => 1680,
			'ratio'  => '3:4',
			'crop'   => false,
		],
		'digital-daily-4-3'         => [
			'width'  => self::WIDTH,
			'height' => 945,
			'ratio'  => '4:3',
		],
		'digital-daily-5-2'         => [
			'width'  => self::WIDTH,
			'height' => 504,
			'ratio'  => '5:2',
		],
		'digital-daily-16-9'        => [
			'width'  => self::WIDTH,
			'height' => 709,
			'ratio'  => '16:9',
		],
	];

	/**
	 * Images constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		// Run after theme registers its image sises.
		add_action(
			'after_setup_theme',
			[
				$this,
				'add_image_sizes',
			],
			20
		);

		// Run after theme sets its image-ratio map.
		add_filter(
			'wpcom_thumbnail_editor_args',
			[
				$this,
				'set_aspect_ratios',
			],
			20
		);
	}

	/**
	 * Register configured image sizes.
	 */
	public function add_image_sizes(): void {
		foreach ( static::SIZES as $name => $args ) {
			add_image_size(
				$name,
				$args['width'],
				$args['height'],
				$args['crop'] ?? true
			);
		}
	}

	/**
	 * Configure thumbnail editor to support configured image sizes' ratios.
	 *
	 * @param array $thumbnail_editor_args Aspect-ratio map.
	 * @return array
	 */
	public function set_aspect_ratios( array $thumbnail_editor_args ): array {
		if ( ! is_array( $thumbnail_editor_args['image_ratio_map'] ) ) {
			$thumbnail_editor_args['image_ratio_map'] = [];
		}

		foreach ( static::SIZES as $name => $image_args ) {
			$ratio = $image_args['ratio'];

			if ( ! isset( $thumbnail_editor_args['image_ratio_map'][ $ratio ] ) ) {
				$thumbnail_editor_args['image_ratio_map'][ $ratio ] = [];
			}

			$thumbnail_editor_args['image_ratio_map'][ $ratio ][] = $name;
		}

		ksort( $thumbnail_editor_args['image_ratio_map'], SORT_NATURAL );

		return $thumbnail_editor_args;
	}
}
