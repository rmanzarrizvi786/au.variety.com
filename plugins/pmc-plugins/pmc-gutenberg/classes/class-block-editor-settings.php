<?php
/**
 * Conform Gutenberg to Larva and PMC plugins.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Larva;
use WP_Block_Editor_Context;

/**
 * Class Block_Editor_Settings.
 */
class Block_Editor_Settings {
	use Singleton;

	/**
	 * Block_Editor_Settings constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_filter( 'should_load_remote_block_patterns', '__return_false' );

		add_action( 'after_setup_theme', [ $this, 'override_theme_supports' ] );
		add_action( 'after_setup_theme', [ $this, 'add_colors_from_larva_tokens' ] );
		add_filter( 'block_editor_settings_all', [ $this, 'override_block_editor_settings' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'hide_color_picker' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'localize_pmc_gutenberg_blocks_config' ] );
	}

	/**
	 * Disable settings that interfere with, or contradict what, Larva allows.
	 */
	public function override_theme_supports(): void {
		add_theme_support( 'disable-custom-font-sizes' );
		add_theme_support( 'editor-font-sizes', [] );

		add_theme_support( 'disable-custom-gradients' );
		add_theme_support( 'editor-gradient-presets', [] );

		remove_theme_support( 'custom-line-height' );
		remove_theme_support( 'custom-spacing' );
		remove_theme_support( 'custom-units' );

		remove_theme_support( 'core-block-patterns' );
	}

	/**
	 * Support only colors provided by theme's Larva tokens.
	 */
	public function add_colors_from_larva_tokens(): void {

		$json = file_get_contents(
			sprintf(
				'%1$s/%2$stokens/%3$s%4$s.json',
				Larva\Config::get_instance()->get( 'core_directory' ),
				PMC_GUTENBERG_BUILD_DIR_SLUG,
				Larva\Config::get_instance()->get( 'tokens' ),
				Larva\Config::get_instance()->get( 'compat_css' ) ? '-compat' : ''
			)
		);

		$design_tokens = json_decode( $json, true );
		unset( $json );

		$editor_color_palette = [
			[
				'name'  => __( 'Brand Primary', 'pmc-gutenberg' ),
				'slug'  => 'brand-primary',
				'color' => $design_tokens['COLOR_BRAND_PRIMARY'] ?? null,
			],
			[
				'name'  => __( 'Brand Secondary', 'pmc-gutenberg' ),
				'slug'  => 'brand-secondary',
				'color' => $design_tokens['COLOR_BRAND_SECONDARY'] ?? null,
			],
			[
				'name'  => __( 'Brand Accent', 'pmc-gutenberg' ),
				'slug'  => 'brand-accent',
				'color' => $design_tokens['COLOR_BRAND_ACCENT'] ?? null,
			],
			[
				'name'  => _x( 'Black', 'Larva color: black', 'pmc-gutenberg' ),
				'slug'  => 'black',
				'color' => $design_tokens['COLOR_BLACK'] ?? null,
			],
			[
				'name'  => _x( 'White', 'Larva color: white', 'pmc-gutenberg' ),
				'slug'  => 'white',
				'color' => $design_tokens['COLOR_WHITE'] ?? null,
			],
			[
				'name'  => _x( 'Grey', 'Larva color: grey', 'pmc-gutenberg' ),
				'slug'  => 'grey',
				'color' => $design_tokens['COLOR_GREY'] ?? null,
			],
			[
				'name'  => _x(
					'Light Grey',
					'Larva color: light grey',
					'pmc-gutenberg'
				),
				'slug'  => 'grey-light',
				'color' => $design_tokens['COLOR_GREY_LIGHT'] ?? null,
			],
		];

		$editor_color_palette = array_values(
			array_filter(
				$editor_color_palette,
				static function( array $item ): bool {
					return ! empty( $item['color'] );
				}
			)
		);

		// Add brand colors to the block editor palette.
		add_theme_support( 'editor-color-palette', $editor_color_palette );
	}

	/**
	 * Disable features incompatible with Larva or our plugins.
	 *
	 * @param array                   $settings Block editor settings.
	 * @param WP_Block_Editor_Context $context  Context where editor appears.
	 * @return array
	 */
	public function override_block_editor_settings(
		array $settings,
		WP_Block_Editor_Context $context
	): array {
		if ( null === $context->post ) {
			return $settings;
		}

		// Disable duotone feature for all blocks.
		_wp_array_set(
			$settings,
			[
				'__experimentalFeatures',
				'color',
				'customDuotone',
			],
			false
		);

		// Remove all duotone options.
		_wp_array_set(
			$settings,
			[
				'__experimentalFeatures',
				'color',
				'duotone',
			],
			[]
		);

		// Remove all gradient options.
		_wp_array_set(
			$settings,
			[
				'__experimentalFeatures',
				'color',
				'gradients',
			],
			[
				'core'  => [],
				'theme' => [],
			]
		);

		// Remove all color palettes registered by WordPress.
		_wp_array_set(
			$settings,
			[
				'__experimentalFeatures',
				'color',
				'palette',
				'core',
			],
			[]
		);

		// Disable drop-cap option for all blocks.
		_wp_array_set(
			$settings,
			[
				'__experimentalFeatures',
				'typography',
				'dropCap',
			],
			false
		);

		// Remove all font-size options.
		_wp_array_set(
			$settings,
			[
				'__experimentalFeatures',
				'typography',
				'fontSizes',
			],
			[
				'core'  => [],
				'theme' => [],
			]
		);

		return $settings;
	}

	/**
	 * Prevent selection of colors not sourced from Larva tokens (those set in
	 * the `add_colors_from_larva_tokens()` method of this class).
	 *
	 * The `disable-custom-colors` theme-supports flag disables all color
	 * overrides, including the color palette we register.
	 *
	 * @link https://github.com/WordPress/gutenberg/issues/29568
	 */
	public function hide_color_picker(): void {
		$screen = get_current_screen();

		if ( null === $screen || ! $screen->is_block_editor() ) {
			return;
		}

		wp_add_inline_style(
			'wp-components',
			<<<CSS
			/* Prevent selection of colors not sourced from Larva tokens. */ 
			button.components-color-palette__custom-color[aria-label="Custom color picker"] { 
				display: none; 
			}
			CSS
		);

	}

	/**
	 * Localize all blocks configuration under one object
	 *
	 * EX: pmc_gutenberg_blocks_config = [
	 *    ['pmc_carousel_block_config'] => [
	 *        'video' => [ 'post_type' = 'pmc_top_videos' ],
	 *        ]
	 * ];
	 *
	 */
	public function localize_pmc_gutenberg_blocks_config() : void {
		$pmc_blocks_config = apply_filters( 'pmc_gutenberg_blocks_config', [] );

		wp_localize_script(
			'wp-edit-post',
			'pmc_gutenberg_blocks_config',
			$pmc_blocks_config
		);
	}
}
