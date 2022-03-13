<?php
/**
 * Add metabox into gift guide post type to link content.
 *
 * @author Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @package pmc-gift-guide
 */

namespace PMC\Gift_Guide;

use PMC\Global_Functions\Traits\Singleton;
use PMC_LinkContent;

class Link_Content {

	use Singleton;

	/**
	 * Link_Content constructor.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		add_action( 'admin_init', [ $this, 'setup_hooks' ] );
	}

	/**
	 * To setup actions/filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		$is_enable = apply_filters( 'pmc_gift_guide_enable_link_content', false );

		if ( true !== $is_enable ) {
			return;
		}

		/**
		 * Actions
		 */
		add_action( 'add_meta_boxes', [ $this, 'meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_post' ] );

		// Set up PMC_LinkContent
		PMC_LinkContent::enqueue();
	}

	/**
	 * To add metabox in post edit screen.
	 *
	 * @return void
	 */
	public function meta_boxes() {

		$post_types = apply_filters( 'pmc_gift_guide_link_post_types', [ Common::POST_SLUG ] );

		add_meta_box(
			Common::POST_SLUG . '-link-box',
			__( 'Article Information', 'pmc-gift-guide' ),
			[ $this, 'render_link_box_content' ],
			$post_types,
			'normal',
			'core'
		);
	}

	/**
	 * To render content of meta box.
	 *
	 * @return void
	 */
	public function render_link_box_content() {
		global $post;

		if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) ) {
			return;
		}

		wp_nonce_field( 'save_gift_guide_link_content', Common::POST_SLUG . '-link-box' );

		$meta_key    = Common::POST_SLUG . '-linked-article';
		$linked_data = get_post_meta( $post->ID, $meta_key, true );

		if ( ! empty( $linked_data ) && is_array( $linked_data ) ) {

			$linked_data = wp_parse_args( $linked_data, [
				'url'   => '',
				'id'    => '',
				'title' => '',
			] );

			$linked_data = wp_json_encode( $linked_data );
		} else {
			$linked_data = false;
		}

		PMC_LinkContent::insert_field( $linked_data, 'Article', 'article' );
	}

	/**
	 * To save data of into metabox for link content.
	 *
	 * @param $post_id Post ID
	 *
	 * @return void
	 */
	public function save_post( $post_id ) {

		if ( empty( $post_id ) || 0 > intval( $post_id ) ) {
			return;
		}

		$url_path = \PMC::filter_input( INPUT_POST, 'pmclinkcontent-post-value-article' );
		$nonce    = \PMC::filter_input( INPUT_POST, 'pmc-gift-guide-link-box' );

		if (
			( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			|| ( ! isset( $_POST['pmclinkcontent-post-value-article'] ) ) // WPCS: Input var okay. CSRF okay.
			|| ( empty( $nonce ) )
			|| ( ! wp_verify_nonce( $nonce, 'save_gift_guide_link_content' ) )
			|| ( ! current_user_can( 'edit_posts', $post_id ) )
		) {
			return;
		}

		$meta_key = Common::POST_SLUG . '-linked-article';
		$url_path = trim( $url_path );
		// Strip slashes thanks to wp_magic_quotes(). stripcslashes() to preserve linebreaks.
		$url_path = stripcslashes( $url_path );
		// Replace new lines to prevent it from throwing null during json decode.
		$url_path = preg_replace( '/[\r\n]+/', ' ', $url_path );
		// Convert any ISO-8859-1 to UTF-8.
		$url_path = utf8_encode( $url_path );
		// json_decode() also serves as $url_path validation.
		$url_parts = json_decode( $url_path );

		if ( ! $url_parts ) {
			delete_post_meta( $post_id, $meta_key );
			return;
		}

		/**
		 * wp_json_encode() turns unicode characters into \uXXXX
		 * PHP 5.4 adds JSON_UNESCAPED_UNICODE, but for better compatibility we're using esc_html() to convert
		 * the unicode characters into entities.
		 * The wp_json_encode() here is to pre-emptively encode the entities, and substr() to remove the quotes
		 * added by wp_son_encode().
		 */
		$title = esc_html( $url_parts->title );
		$title = substr( wp_json_encode( $title ), 1, -1 );

		$new_meta_value = array(
			'url'   => esc_url_raw( $url_parts->url ),
			'id'    => intval( $url_parts->id ),
			'title' => $title,
		);

		update_post_meta( $post_id, $meta_key, $new_meta_value );

	}

}
