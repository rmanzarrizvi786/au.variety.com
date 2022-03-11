<?php
/**
 * Class to add Ecomm Disclaimer post option
 *
 * @author Ebonie Butler <ebonie@yikesinc.com>
 *
 * @since  2021-11-15
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class Ecomm_Disclaimer {

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() {

		// Set taxonomy terms to automatically enable "Add Ecomm Disclaimer" post option for.
		add_filter( 'pmc_ecomm_default_tax_terms', [ $this, 'set_ecomm_tax_terms' ] );
		add_filter( 'pmc_ecomm_disclaimer_template', [ $this, 'set_ecomm_disclaimer_template' ] );
		add_filter( 'pmc_ecomm_display_disclaimer_by_default', [ $this, 'display_disclaimer_for_legacy_posts' ] );
	}

	/**
	 * Add 'shop' category to array of taxonomy
	 * terms to enable "Add Ecomm Disclaimer" post
	 * option for.
	 * 
	 * @param array $default_tax_terms Taxonomy terms.
	 *
	 * @return array Taxonomy terms.
	 */
	public function set_ecomm_tax_terms( $default_tax_terms ) {
		$default_tax_terms['vertical'][] = 'shopping';
		return $default_tax_terms;
	}

	/**
	 * Override disclaimer template.
	 * 
	 * @param string $template
	 *
	 * @return string $template
	 */
	public function set_ecomm_disclaimer_template( $template ) {
		$new_template = sprintf( '%s/template-parts/ecomm-disclaimer.php', untrailingslashit( CHILD_THEME_PATH ) );
		return file_exists( $new_template ) ? $new_template : $template;
	}

	/**
	 * Display disclaimer by default for legacy posts with 'shopping' vertical.
	 *
	 * @param boolean $show_disclaimer_by_default
	 * 
	 * @return boolean
	 */
	public function display_disclaimer_for_legacy_posts( $show_disclaimer_by_default ) {
		$post_id  = get_queried_object_id();
		$vertical = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $post_id, 'vertical' );
		if ( ! empty( $vertical ) && 'shopping' === $vertical->slug ) {
			return true;
		}
		return $show_disclaimer_by_default;
	}
}
