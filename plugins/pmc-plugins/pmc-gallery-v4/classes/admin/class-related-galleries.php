<?php
/**
 * Add related galleries.
 *
 * @since 2019-02-25 PMCP-1178 Sayed Taqui.
 *
 * @package pmc-gallery-v4
 */

namespace PMC\Gallery\Admin;

use PMC\Gallery\Defaults;
use \PMC\Global_Functions\Traits\Singleton;

class Related_Galleries {

	use Singleton;

	/**
	 * Related gallery fields.
	 *
	 * @var array
	 */
	public $related_gallery_fields = array();

	/**
	 * Automatic select meta key.
	 *
	 * @var string
	 */
	public $automatic_select_key = 'pmc-gallery-automatic-next-gallery';

	/**
	 * Nonce name.
	 */
	const NONCE_NAME = 'pmc-gallery-related-galleries-security';

	/**
	 * Nonce action.
	 */
	const NONCE_ACTION = 'pmc-gallery-related-galleries-action';

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	protected function _init() {

		// @codeCoverageIgnoreStart
		if ( ! class_exists( 'PMC_LinkContent' ) ) {
			if ( function_exists( 'pmc_load_plugin' ) ) {
				pmc_load_plugin( 'pmc-linkcontent', 'pmc-plugins' );
			} else {
				return;
			}
		}
		// @codeCoverageIgnoreEnd

		$this->related_gallery_fields = array(
			array(
				'key'   => sprintf( '%s-next-gallery', Defaults::NAME ),
				'title' => esc_html__( 'Next Gallery', 'pmc-gallery-v4' ),
			),
		);

		// Set up PMC_LinkContent
		\PMC_LinkContent::enqueue();

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );

		foreach ( $this->related_gallery_fields as $field ) {
			add_filter( 'pmclinkcontent_post_types_' . $field['key'], array( $this, 'pmc_link_content_post_types' ) );
		}
	}

	/**
	 * Post types for search field.
	 *
	 * @return array
	 */
	public function pmc_link_content_post_types() {
		return array( Defaults::NAME );
	}

	/**
	 * Add meta boxes.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'pmc_gallery_related_galleries',
			esc_html__( 'Next Gallery', 'pmc-gallery-v4' ),
			array( $this, 'render_meta_boxes' ),
			array( Defaults::NAME ),
			'normal',
			'core'
		);
	}

	/**
	 * Render Meta Box content
	 *
	 * @return void
	 */
	public function render_meta_boxes() {
		global $post;

		if ( empty( $this->related_gallery_fields ) ) {
			return;
		}

		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$automatic_select_value = get_post_meta( $post->ID, $this->automatic_select_key, true );
		$automatic_select_value = ( '' === $automatic_select_value ) ? 1 : intval( $automatic_select_value );

		printf(
			'<p><input type="checkbox" name="%1$s" value="1" id="%2$s" %3$s> <label for="%4$s">%5$s</label></p>',
			esc_attr( $this->automatic_select_key ),
			esc_attr( $this->automatic_select_key ),
			esc_attr( checked( $automatic_select_value, 1, false ) ),
			esc_attr( $this->automatic_select_key ),
			esc_html__( 'Automatically select next gallery', 'pmc-gallery-v4' )
		);

		foreach ( $this->related_gallery_fields as $field ) {

			$field_data = get_post_meta( $post->ID, $field['key'], true );

			if ( is_array( $field_data ) && ! empty( $field_data ) ) {
				$field_data = wp_json_encode( $field_data );
			}

			\PMC_LinkContent::insert_field( $field_data, $field['title'], $field['key'] );

		}

	}

	/**
	 * Save related galleries.
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function save_post( $post_id ) {

		$key_prefix = 'pmclinkcontent-post-value-';
		$nonce      = \PMC::filter_input( INPUT_POST, self::NONCE_NAME, FILTER_SANITIZE_STRING );

		if (
			( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| ( wp_doing_ajax() )
			|| ( ! $nonce )
			|| ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) )
			|| ( ! current_user_can( 'edit_posts', $post_id ) )
			|| empty( $this->related_gallery_fields )
		) {
			return;
		}

		$automatic_select = \PMC::filter_input( INPUT_POST, $this->automatic_select_key, FILTER_SANITIZE_STRING );

		update_post_meta( $post_id, $this->automatic_select_key, intval( $automatic_select ) );

		foreach ( $this->related_gallery_fields as $field ) {
			$value    = json_decode( trim( \PMC::filter_input( INPUT_POST, $key_prefix . $field['key'] ) ) ); // Values will be sanitized individually.
			$meta_key = $field['key'];

			if ( null !== $value ) {
				update_post_meta(
					$post_id,
					$meta_key,
					array(
						'id'    => absint( $value->id ),
						'url'   => esc_url_raw( $value->url ),
						'title' => sanitize_text_field( $value->title ),
					)
				);
			} else {
				update_post_meta( $post_id, $meta_key, '' );
			}
		}

	}

}

// EOF
