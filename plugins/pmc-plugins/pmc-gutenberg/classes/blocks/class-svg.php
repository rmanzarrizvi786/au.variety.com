<?php
/**
 * Add SVGs to post content.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\Blocks;

use PMC;
use PMC\Gutenberg\Block_Base;
use PMC\Gutenberg\Interfaces\Block_Base\With_Render_Callback;
use WP_Block;

/**
 * Class SVG.
 */
class SVG extends Block_Base implements With_Render_Callback {
	/**
	 * Supported SVGs, configured using `static::register()`.
	 *
	 * @var array
	 */
	protected array $_svgs = [];

	/**
	 * Register an SVG for use with this block.
	 *
	 * @param string      $name       Display name of SVG.
	 * @param string      $url        Source URL.
	 * @param array       $post_types Post types where SVG can appear.
	 * @param array|null  $extra_args {
	 *     Additional arguments, such as mobile variant.
	 *
	 *     @type bool $has_mobile_variant Whether this SVG has a mobile version.
	 * }
	 */
	public static function register(
		string $name,
		string $url,
		array $post_types,
		?array $extra_args = null
	): void {
		// Prevent class instantiation before `Gutenberg::init_blocks()` runs.
		if ( ! did_action( 'init' ) ) {
			// Action fires before test starts.
			return; // @codeCoverageIgnore
		}

		$slug = pathinfo( $url, PATHINFO_FILENAME );

		$extra_args = wp_parse_args(
			$extra_args ?? [],
			[
				'has_mobile_variant' => false,
			]
		);

		static::get_instance()
			->_svgs[ $slug ] = compact(
				'name',
				'url',
				'post_types',
				'extra_args'
			);
	}

	/**
	 * SVG constructor.
	 */
	public function __construct() {
		$this->_block = 'svg';

		// Required to support server-side rendering.
		$this->_block_args['attributes'] = [
			'slug' => [
				'type'    => 'string',
				'default' => '',
			],
		];

		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action(
			'enqueue_block_editor_assets',
			[
				$this,
				'localize_script_with_options',
			]
		);
	}

	/**
	 * Convert registered SVGs to array for use with `SelectControl` component.
	 */
	public function localize_script_with_options(): void {
		global $typenow;

		$options = wp_list_filter(
			$this->_svgs,
			[
				'post_types' => [
					$typenow,
				],
			]
		);

		$formatted_options = [];

		foreach ( $options as $slug => $details ) {
			$formatted_options[] = [
				'label' => $details['name'],
				'value' => $slug,
			];
		}

		usort( $formatted_options, [ $this, 'sort_by_label' ] );

		array_unshift(
			$formatted_options,
			[
				'label'   => __( 'Select SVG', 'pmc-gutenberg' ),
				'value'   => null,
				'default' => true,
			]
		);

		wp_localize_script(
			'block-' . $this->_block,
			'pmcGutenbergSvgOptions',
			$formatted_options
		);
	}

	/**
	 * Sort registered SVGs by their labels.
	 *
	 * @param array $first  First SVG to compare.
	 * @param array $second Second SVG to comapre.
	 * @return int
	 */
	public function sort_by_label( array $first, array $second ): int {
		return $first['label'] <=> $second['label'];
	}

	/**
	 * Retrieve details for a registered SVG.
	 *
	 * @param string $slug SVG slug (filename without extension).
	 * @return array|null
	 */
	public function get_details( string $slug ): ?array {
		if ( ! isset( $this->_svgs[ $slug ] ) ) {
			return null;
		}

		$details = $this->_svgs[ $slug ];

		if (
			false === $details['extra_args']['has_mobile_variant']
			|| ! PMC::is_mobile()
		) {
			return $details;
		}

		$details['url']  = substr(
			$details['url'],
			0,
			-4
		);
		$details['url'] .= '-mobile.svg';

		return $details;
	}

	/**
	 * Render block.
	 *
	 * Declaration must be compatible with interface.
	 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	 *
	 * @param array    $attrs   Block attributes.
	 * @param string   $content Block content.
	 * @param WP_Block $block   Block object.
	 * @return string
	 */
	public function render_callback(
		array $attrs,
		string $content,
		WP_Block $block
	): string {
		// phpcs:enable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		if ( empty( $attrs ) || empty( $attrs['slug'] ) ) {
			return '';
		}

		$details = $this->get_details( $attrs['slug'] );

		if ( null === $details ) {
			return '';
		}

		return sprintf(
			'<img src="%1$s" alt="%2$s" />',
			$details['url'],
			$details['name']
		);
	}
}
