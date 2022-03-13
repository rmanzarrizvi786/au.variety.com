<?php
/**
 * Route built-in blocks' output to Larva templates.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg;

/**
 * Modify core WordPress blocks
 *
 * Setup the content and template for core WordPress blocks
 * that we want to use larva based templates for.
 *
 * @codeCoverageIgnore This is tested through integration tests
 */
class Builtin {

	/**
	 * Set to the name of the template file.
	 *
	 * @var string|null
	 */
	public ?string $template = null;

	/**
	 * Data passed to template.
	 *
	 * @var array|null
	 */
	public ?array $template_data = null;

	/**
	 * Block data from WordPress.
	 *
	 * @var array
	 */
	protected array $_block;

	/**
	 * Block markup.
	 *
	 * @property string
	 */
	protected string $_block_content;

	/**
	 * Block name without namespace.
	 *
	 * @var string
	 */
	protected string $_simplified_block_name;

	/**
	 * Block CSS class prefix.
	 *
	 * @var string
	 */
	protected string $_css_class_prefix = 'wp';

	/**
	 * Builtin constructor.
	 *
	 * @param string $block_content Block markup.
	 * @param array  $block         Block data, including attributes.
	 */
	public function __construct( string $block_content, array $block ) {
		$this->_block_content = $block_content;
		$this->_block         = $block;

		$block_name                   = explode(
			'/',
			$this->_block['blockName']
		);
		$this->_simplified_block_name = array_pop( $block_name );

		$pmc_css_class_prefix_blocks = [ 'columns' ];
		if ( in_array( $this->_simplified_block_name, (array) $pmc_css_class_prefix_blocks, true ) ) {
			$this->_css_class_prefix = 'pmc';
		}
	}

	/**
	 * Retrieve path to block template.
	 *
	 * @return string|null
	 */
	public function get_template(): ?string {
		if ( ! isset( $this->template ) ) {
			$this->_setup_block_instance();
		}
		return $this->template;
	}

	/**
	 * Retrieve template data to render block.
	 *
	 * @return array|null
	 */
	public function get_template_data(): ?array {
		if ( ! isset( $this->template_data ) ) {
			$this->_setup_block_instance();
		}
		return $this->template_data;
	}

	/**
	 * Parse block into template and template data.
	 */
	private function _setup_block_instance(): void {
		switch ( $this->_simplified_block_name ) {
			case 'button':
				$this->_core_button();
				break;
			case 'column':
			case 'columns':
				$this->_core_columns();
				break;
			case 'group':
				$this->_core_group();
				break;
			case 'heading':
				$this->_core_heading();
				break;
			case 'list':
				$this->_core_list();
				break;
			case 'paragraph':
				$this->_core_paragraph();
				break;
			case 'separator':
				$this->_core_separator();
				break;

			default:
				// Do nothing.
				break;
		}
	}

	/**
	 * Extract data from button markup.
	 */
	private function _core_button(): void {

		// https://regex101.com/r/lCeWQi/5
		preg_match( '/<a[^>]*href="([^"]*)"[^>]*>/', $this->_block_content, $matches );
		$href = $matches[1];

		// https://regex101.com/r/lCeWQi/4
		preg_match( '/<a[^>]*>((?>.|\s)+?)<\/a>/', $this->_block_content, $matches );
		$inner_html = $matches[1];

		if ( empty( $href ) || empty( $inner_html ) ) {
			$this->template_data = null;
			return;
		}

		$this->template = dirname( __DIR__ ) . '/template-parts/core-button.php';

		$this->template_data = [
			'data' => [
				'url'        => $href,
				'inner_html' => $inner_html,
				'styles'     => $this->_get_css_classes_for_attributes(),
			],
		];
	}

	/**
	 * Apply styles to column and columns blocks.
	 */
	private function _core_columns(): void {
		$styles = $this->_get_css_classes_for_attributes();

		if ( isset( $this->_block['attrs']['className'] ) ) {
			$styles['larvaColumns'] = $this->_block['attrs']['className'];
		} elseif ( 'column' === $this->_simplified_block_name ) {
			$styles['larvaColumns'] = 'lrv-a-grid-item';
		}

		$styles = array_filter( $styles );

		if ( empty( $styles ) ) {
			return;
		}

		$styles = implode( ' ', $styles );

		$pattern = '#class="(wp-block-'
			. preg_quote(
				$this->_simplified_block_name,
				'#'
			)
			. '[^"]*)"#i';

		$replacement = sprintf(
			'class="%1$s-block-%2$s %3$s"',
			$this->_css_class_prefix,
			$this->_simplified_block_name,
			$styles,
		);

		$this->_block_content = preg_replace(
			$pattern,
			$replacement,
			$this->_block_content,
			1
		);

		$this->template      = dirname( __DIR__ ) . '/template-parts/modified-block-content.php';
		$this->template_data = [
			'block_content' => $this->_block_content,
		];
	}

	/**
	 * Apply styles to group block.
	 */
	private function _core_group(): void {
		$styles = $this->_get_css_classes_for_attributes();

		if (
			isset(
				$this->_block['attrs']['fullBleedBackgroundColor'],
				$styles['background_color']
			)
			&& true === $this->_block['attrs']['fullBleedBackgroundColor']
		) {
			$styles['background_color'] .=
				' pmc-gutenberg-full-bleed-background-color';
		}

		if ( empty( $styles ) ) {
			return;
		}

		$styles = implode( ' ', $styles );

		$this->_block_content = preg_replace(
			'#class="(wp-block-group[^"]+)"#i',
			'class="wp-block-group ' . $styles . '"',
			$this->_block_content,
			1
		);

		$this->template      = dirname( __DIR__ ) . '/template-parts/modified-block-content.php';
		$this->template_data = [
			'block_content' => $this->_block_content,
		];
	}

	/**
	 * Extract data from heading markup.
	 */
	private function _core_heading(): void {
		// https://regex101.com/r/2UDdEn/1
		preg_match( '/<\/h([1-6])[^>]*>$/', $this->_block_content, $matches );
		$heading_level = $matches[1];

		// https://regex101.com/r/84kaKs/2/
		preg_match( '/<h[1-6][^>]*>((?>.|\s)+?)<\/h[1-6]>/', $this->_block_content, $matches );
		$inner_html = $matches[1];

		if ( empty( $heading_level ) || empty( $inner_html ) ) {
			$this->template_data = null;
			return;
		}

		$this->template = dirname( __DIR__ ) . '/template-parts/core-heading.php';

		// These heading levels have variants - this is carryover from IW influencers setup,
		// and might not be needed in the future.
		$heading_level_variants = [
			'1',
			'2',
			'3',
			'4',
			'5',
			'6',
		];

		if ( in_array( $heading_level, (array) $heading_level_variants, true ) ) {
			$variant = 'h' . $heading_level;
		} else {
			$variant = 'prototype';
		}

		$this->template_data = [
			'data'    => [
				'heading_level' => $heading_level,
				'inner_html'    => $inner_html,
				'styles'        => $this->_get_css_classes_for_attributes(),
			],
			'variant' => $variant,
		];
	}

	/**
	 * Parse list block.
	 */
	private function _core_list(): void {
		$this->template = dirname( __DIR__ ) . '/template-parts/core-list.php';

		$this->template_data = [
			'data' => [
				'inner_html' => $this->_block_content,
				'styles'     => $this->_get_css_classes_for_attributes(),
			],
		];
	}

	/**
	 * Extract paragraph block markup.
	 */
	private function _core_paragraph(): void {
		// https://regex101.com/r/5h76FA/1
		preg_match( '/<p[^>]*>((?>.|\s)+?)<\/p>/', $this->_block_content, $matches );
		$inner_html = $matches[1] ?? '';

		if ( empty( $inner_html ) ) {
			$this->template_data = null;
			return;
		}

		$this->template = dirname( __DIR__ ) . '/template-parts/core-paragraph.php';

		$this->template_data = [
			'data' => [
				'styles'     => $this->_get_css_classes_for_attributes(),
				'inner_html' => $inner_html,
			],
		];
	}

	/**
	 * Parse separator block.
	 */
	private function _core_separator(): void {
		$has_style = preg_match(
			'#is-style-(wide-thick|wide|thick)#i',
			$this->_block['attrs']['className'] ?? '',
			$style
		);

		if ( $has_style ) {
			$variant = $style[1];
		} else {
			$variant = 'prototype';
		}

		$this->template = dirname( __DIR__ ) . '/template-parts/core-separator.php';

		$this->template_data = [
			'data'    => [
				'styles' => $this->_get_css_classes_for_attributes(),
			],
			'variant' => $variant,
		];
	}

	/**
	 * Map values from style-related attributes to keys in
	 * Larva patterns.
	 *
	 * Note: The keys must match the name of the value in the
	 * larva template.
	 *
	 * @return array Styles to be added to the data for the template.
	 */
	private function _get_css_classes_for_attributes(): array {

		$styles = [];

		// Use the color attribute for border_color in core/separator block.
		if ( isset( $this->_block['attrs']['color'] ) && 'core/separator' === $this->_block['blockName'] ) {
			$border_color = $this->_block['attrs']['color'];

			$styles['border_color'] = 'lrv-u-border-color-' . $border_color;
		}

		if ( isset( $this->_block['attrs']['backgroundColor'] ) ) {
			$bg_color = $this->_block['attrs']['backgroundColor'];

			$styles['background_color'] = 'lrv-u-background-color-' . $bg_color;
		}

		if ( isset( $this->_block['attrs']['textColor'] ) ) {
			$color = $this->_block['attrs']['textColor'];

			$styles['color'] = 'lrv-u-color-' . $color;
		}

		if ( isset( $this->_block['attrs']['textAlign'] ) ) {
			$text_align = $this->_block['attrs']['textAlign'];

			$styles['text_align'] = 'lrv-u-text-align-' . $text_align;
		}

		// Both 'align' and 'textAlign' appear to be used for attribute names.
		if ( isset( $this->_block['attrs']['align'] ) ) {
			$align = $this->_block['attrs']['align'];

			$styles['text_align'] = 'lrv-u-text-align-' . $align;
		}

		if ( isset( $this->_block['attrs']['typographyFontSize'] ) ) {
			$size = $this->_block['attrs']['typographyFontSize'];

			$styles['typography'] = 'lrv-a-font-' . $size;
		}

		if ( isset( $this->_block['attrs']['width'] ) ) {
			$styles['width'] = sprintf(
				'lrv-u-width-%1$sp',
				$this->_block['attrs']['width']
			);
		}

		return $styles;
	}
}
