<?php

namespace PMC\Gutenberg\Blocks;

use PMC\Gutenberg\Block_Base;
use PMC\Gutenberg\Interfaces\Block_Base\With_Render_Callback;
use WP_Block;

class One_Off extends Block_Base implements With_Render_Callback {

	/**
	 * Holds configuration from the theme.
	 */
	protected $_theme_one_offs = [];

	/**
	 * Path to one-off templates from the theme.
	 */
	protected $_theme_one_offs_template_path = '';

	/**
	 * Default path for templates.
	 */
	protected $_default_path;

	function __construct() {
		$this->_block = 'one-off';

		$this->_default_path = sprintf(
			'%s/%s',
			get_stylesheet_directory(),
			'template-parts/blocks/one-off/'
		);

		$this->set_theme_one_offs_template_path( $this->_default_path );

		$this->_setup_hooks();
	}

	function _setup_hooks() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'localize_data' ] );
	}

	/**
	 * @codeCoverageIgnore Tested as part of hooks
	 **/
	function localize_data() : void {

		wp_localize_script(
			'block-' . $this->_block,
			'pmc_theme_one_offs',
			$this->_theme_one_offs
		);

	}

	function set_theme_one_offs( array $theme_one_offs ) : void {
		$this->_theme_one_offs = $theme_one_offs;
	}

	function set_theme_one_offs_template_path( string $path ) : void {
		$this->_theme_one_offs_template_path = $path;
	}

	function get_theme_one_offs_template_path() : string {
		return $this->_theme_one_offs_template_path;
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

		$template_file = sprintf(
			'%s/%s.php',
			untrailingslashit(
				$this->get_theme_one_offs_template_path()
			),
			$attrs['oneOffTemplate']
		);

		try {
			$output = \PMC::render_template( $template_file, [] );
		} catch ( \Exception $e ) {

			// Show a very obvious error message.
			$output = sprintf(
				'<p style="font-weight:bold;color:red;">Error retrieving template from %s</p>%s',
				$template_file,
				$e
			);
		}

		return $output;

	}

}
