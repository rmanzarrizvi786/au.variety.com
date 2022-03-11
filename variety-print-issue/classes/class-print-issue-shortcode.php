<?php
/**
 * Print Issue Shortcode
 *
 * @package pmc-variety-2020
 *
 * @since 2020-4-15
 */

namespace Variety\Plugins\Variety_Print_Issue;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Print_Issue_Shortcode
 *
 * @package pmc-variety-2020
 */
class Print_Issue_Shortcode {

	use Singleton;

	const SHORTCODE = 'print_issue';

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_action( 'init', [ $this, 'register_shortcode' ], 11 );
	}

	public function register_shortcode() {
		add_shortcode( self::SHORTCODE, [ $this, 'shortcode_print_issue' ] );
	}

	public function get_print_issue_data( $post_id ) {

		$terms = get_the_terms( $post_id, 'print-issues' );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return false;
		}

		$image_id = get_term_meta( $terms[0]->term_id, 'print-issue-image-id', true );

		return [
			'image_id' => $image_id,
		];
	}

	public function shortcode_print_issue() {

		return \PMC::render_template(
			CHILD_THEME_PATH . '/plugins/variety-print-issue/templates/print-issue-shortcode.php',
			[]
		);
	}


}

//EOF
