<?php
/**
 * Parse blocks in post content into a flat list, un-nesting all inner blocks.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

/**
 * Class Block_Flattener.
 */
class Block_Flattener {
	/**
	 * Content to parse blocks from.
	 *
	 * @var string
	 */
	protected string $_content = '';

	/**
	 * Blocks to be parsed.
	 *
	 * @var array
	 */
	protected array $_blocks = [];

	/**
	 * Block_Flattener constructor.
	 *
	 * @param string|null  $content Content to parse blocks from, null to use
	 *                              post object.
	 */
	public function __construct( ?string $content = null ) {
		if ( is_string( $content ) ) {
			$this->_content = $content;
		}

		$this->_do();
	}

	/**
	 * Retrieve post's flattened blocks.
	 *
	 * @return array|null
	 */
	public function get(): ?array {
		return ! empty( $this->_blocks ) ? $this->_blocks : null;
	}

	/**
	 * Perform parsing.
	 */
	protected function _do(): void {
		if ( empty( $this->_content ) ) {
			return;
		}

		$this->_blocks = parse_blocks( $this->_content );
		$this->_flatten_blocks();
	}

	/**
	 * Flatten nested blocks, such as `core/columns`, for simpler parsing.
	 */
	protected function _flatten_blocks(): void {
		$blocks = [];

		foreach ( $this->_blocks as $block ) {
			$blocks[] = $this->_flatten_block( $block );
		}

		$this->_blocks = array_merge( ...$blocks );
	}

	/**
	 * Flatten single block.
	 *
	 * @param array $block Block details.
	 * @return array
	 */
	protected function _flatten_block( array $block ): array {
		$blocks = [];

		switch ( $block['blockName'] ?? null ) {
			case 'core/columns':
			case 'core/column':
			case 'core/group':
			case 'pmc/digital-daily-polar-ad':
				foreach ( $block['innerBlocks'] as $inner_block ) {
					$blocks[] = $this->_flatten_block( $inner_block );
				}

				$blocks = array_merge( ...$blocks );
				break;

			default:
				$blocks[] = $block;
				break;
		}

		return $blocks;
	}
}
