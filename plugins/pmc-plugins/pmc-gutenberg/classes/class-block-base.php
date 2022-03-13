<?php

namespace PMC\Gutenberg;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Gutenberg\Interfaces\Block_Base as Interfaces;

/**
 * Abstract class for Block Registration
 *
 * Base Block for all of our blocks
 *
 * @codeCoverageIgnore This is abstract and is tested via other means
 */
abstract class Block_Base {
	use Singleton;

	/**
	 * @property array args passed to block.
	 */
	protected $_block_args = [];

	/**
	 * @property name of the block.
	 */
	protected $_block;

	/**
	 * Does block have a stylesheet to be enqueued along with its script?
	 *
	 * @var bool
	 */
	protected $_has_stylesheet = false;

	/**
	 * @property set to the name of the template file.
	 */
	public $template;

	/**
	 * Block styles to be registered for this block.
	 *
	 * @var array
	 */
	protected $_styles;

	/**
	 * Enqueue Assets and Register Block. Called on `init`.
	 */
	final public function init(): void {
		if ( ! $this->_class_implements_interface() ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf(
					/* translators: Full name of interfaces namespace. */
					esc_html__(
						'Block class must implement either the `%1$s` or `%2$s` interface from the `%3$s` namespace.',
						'pmc-gutenberg'
					),
					'With_Larva_Data',
					'With_Render_Callback',
					'PMC\Gutenberg\Interfaces\Block_Base'
				),
				1
			);

			return;
		}

		if ( ! $this->_dependencies_are_satisfied() ) {
			return;
		}

		$name = "pmc/{$this->_block}";

		$script_asset_path = PMC_GUTENBERG_PLUGIN_PATH . PMC_GUTENBERG_BUILD_DIR_SLUG . $this->_block . '.asset.php';
		// Path does not include user input.
		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		$script_asset = require $script_asset_path;

		$block_js = PMC_GUTENBERG_PLUGIN_URL . PMC_GUTENBERG_BUILD_DIR_SLUG . $this->_block . '.js';

		$handle = 'block-' . $this->_block;

		wp_register_script(
			$handle,
			$block_js,
			$script_asset['dependencies'],
			$script_asset['version']
		);

		$this->_block_args['editor_script'] = $handle;

		if ( $this->_has_stylesheet ) {
			$block_css  = substr( $block_js, 0, -2 );
			$block_css .= 'css';

			wp_register_style(
				$handle,
				$block_css,
				[ 'wp-edit-blocks' ],
				$script_asset['version']
			);

			$this->_block_args['editor_style'] = $handle;
		}

		if (
			! isset( $this->_block_args['render_callback'] )
			&& method_exists( $this, 'render_callback' )
		) {
			$this->_block_args['render_callback'] = [ $this, 'render_callback' ];
		}

		/**
		 * If defined in JS, these are ignored, otherwise these match the values
		 * enforced by the scaffold tool and `src/builtin/global-overrides.js`.
		 */
		if ( ! isset( $this->_block_args['supports'] ) ) {
			$this->_block_args['supports'] = [];
		}
		$this->_block_args['supports']['anchor']          = false;
		$this->_block_args['supports']['customClassName'] = false;
		$this->_block_args['supports']['html']            = false;

		register_block_type(
			$name,
			$this->_block_args
		);

		if ( ! empty( $this->_styles ) ) {
			foreach ( $this->_styles as $args ) {
				register_block_style(
					$name,
					$args
				);
			}
		}
	}

	/**
	 * Validate that block class implements one of its required interfaces.
	 *
	 * @return bool
	 */
	private function _class_implements_interface(): bool {
		$interfaces = class_implements( $this );

		if ( empty( $interfaces ) ) {
			return false;
		}

		if (
			array_key_exists(
				Interfaces\With_Larva_Data::class,
				$interfaces
			)
			|| array_key_exists(
				Interfaces\With_Render_Callback::class,
				$interfaces
			)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Confirm that block's dependencies are loaded before initializing block.
	 *
	 * @return bool
	 */
	private function _dependencies_are_satisfied(): bool {
		if ( ! method_exists( $this, 'get_dependent_classes' ) ) {
			return true;
		}

		foreach ( $this->get_dependent_classes() as $class ) {
			if ( ! class_exists( $class, false ) ) {
				return false;
			}
		}

		return true;
	}
}
