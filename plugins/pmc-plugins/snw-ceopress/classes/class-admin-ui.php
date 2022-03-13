<?php
/**
 * Adds admin UI elements
 *
 */

namespace SNW\CEO_Press;

use \PMC\Global_Functions\Traits\Singleton;

class Admin_UI {

	use Singleton;

	/**
	 * Admin_UI constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Adds hook callbacks.
	 */
	protected function _setup_hooks() {

		add_action( 'add_meta_boxes', [ $this, 'add_history_box' ] );

	}

	/**
	 * Register meta box for plugin
	 *
	 * @uses add_meta_box
	 * @return void
	 */
	public function add_history_box() {
		add_meta_box(
			'ceo_content_history',
			esc_html__( 'CEO Content History', 'snw-ceopress' ),
			[ $this, 'render_history_box' ],
			'post',
			'normal',
			'low'
		);
	}

	/**
	 * Callback function to render the metabox
	 *
	 * @param $post \WP_Post
	 */
	public function render_history_box( $post ) {

		$meta = get_post_meta( $post->ID, 'uuid', true );

		if ( empty( $meta ) ) {
			return;
		}

		$ceo_content = snw_get_remote( 'content/' . $meta, 'GET' );

		if (
			empty( $ceo_content ) ||
			! is_array( $ceo_content ) ||
			key_exists( 'error', $ceo_content ) ||
			! key_exists( '0', $ceo_content )
		) {
			return;
		}

		$ceo_content = $ceo_content[0];

		\PMC::render_template(
			sprintf( '%s/templates/template-history-box.php', SNW_CEO_DIR ),
			[
				'ceo_content' => $ceo_content,
			],
			true
		);

	}

}


//EOF
