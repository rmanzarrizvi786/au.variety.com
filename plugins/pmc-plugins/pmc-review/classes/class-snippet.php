<?php
/**
 * Snippet class.
 *
 * @package pmc-review
 * @since 2018-05-11
 */

namespace PMC\Review;

use PMC\Global_Functions\Traits\Singleton;

class Snippet {

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Adds hook callbacks.
	 */
	protected function _setup_hooks() {

		add_shortcode( 'pmc_film_review_snippet', [ $this, 'do_shortcode' ] );
		add_shortcode( 'pmc_review_snippet', array( $this, 'do_shortcode' ) );

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ) );

	}

	/**
	 * Enqueue snippet style.
	 */
	public function enqueue_style() {

		wp_enqueue_style( 'pmc-review-snippet', PMC_REVIEW_URL . 'assets/build/pmc-review-snippet.css' );

	}

	/**
	 * Adds MCE filters.
	 */
	public function admin_init() {
		global $pagenow;

		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		if ( false === get_user_option( 'rich_editing' ) ) {
			return;
		}

		add_filter( 'mce_external_plugins', array( $this, 'filter_mce_external_plugins' ) );
		add_filter( 'mce_buttons', array( $this, 'filter_mce_buttons' ), 10, 2 );
		add_filter( 'mce_css', array( $this, 'filter_mce_css' ) );
	}

	/**
	 * Returns content from the shortcode.
	 *
	 * @param array $atts Unused.
	 * @param string $content The shortcode content.
	 * @return string The shortcode output.
	 */
	public function do_shortcode( $atts, $content ) {

		return $content;
	}

	/**
	 * Add CSS to MCE editor.
	 *
	 * @param string $css;
	 * @return string The modified CSS;
	 */
	public function filter_mce_css( $css ) {

		$css .= ( ! empty( $css ) ? ',' : '' ) . PMC_REVIEW_URL . 'assets/build/pmc-review-snippet.css';

		return $css;

	}

	/**
	 * Load the snippet JS.
	 *
	 * @param array $plugins MCE plugins.
	 * @return array Filtered list of MCE plugins.
	 */
	public function filter_mce_external_plugins( $plugins ) {

		if ( apply_filters( 'pmc_review_block_editor_skip', false ) ) {
			return $plugins;
		}

		$plugins['pmc_review_snippet'] = PMC_REVIEW_URL . 'assets/build/pmc-review-snippet.js?ver=1.1';

		return $plugins;
	}

	/**
	 * Add the button to the content editor.
	 *
	 * @param array $buttons The existing buttons.
	 * @param boolean|string $editor_id The current editor ID.
	 * @return array Themodififed buttons array.
	 */
	public function filter_mce_buttons( $buttons, $editor_id = false ) {

		if ( 'content' === $editor_id ) {
			$buttons[] = 'pmc_review_snippet';
		}

		return $buttons;

	}
}
