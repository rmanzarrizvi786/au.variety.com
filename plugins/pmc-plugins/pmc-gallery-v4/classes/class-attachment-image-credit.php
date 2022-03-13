<?php
/**
 * This class Allows Images to be saved with credit
 */

namespace PMC\Gallery;

use PMC\Global_Functions\Traits\Singleton;

class Attachment_Image_Credit {

	use Singleton;

	protected function __construct() {
		add_filter( 'attachment_fields_to_save', array( $this, 'save_fields' ), 10, 2 );
		add_filter( 'attachment_fields_to_edit', array( $this, 'add_fields' ), 10, 2 );

		// Enhanced captioning for posts
		add_action( 'admin_footer-post-new.php', [ $this, 'replace_tmpl_attachment' ] );
		add_action( 'admin_footer-post.php', [ $this, 'replace_tmpl_attachment' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		add_action( 'wp_ajax_pmc-save-attachment-credit', [ $this, 'save_attachment_credit_field' ] );
		add_filter( 'wp_prepare_attachment_for_js', [ $this, 'wp_prepare_attachment_for_js' ], 10, 3 );

	}

	public function add_fields( $form_fields, $attachment ) {
		$help_text = 'Ex. Jane Smith/Shutterstock <a href="#" class="dashicons dashicons-editor-help imgedit-help-toggle" title="' . esc_attr__( 'Give credit where credit is due. Best practices state that images must be from a reputable source and can be credited. Image credits must include, when possible the photographer’s name and organization, separated by a slash without spaces.', 'pmc-gallery-v4' ) . '"></a>';

		$form_fields['image_credit'] = array(
			'label' => esc_html__( 'Image Credit', 'pmc-gallery-v4' ),
			'value' => get_post_meta( $attachment->ID, '_image_credit', true ),
			// this value should not be escape here, wp core apply esc_attr when it build the <input> tag
			'helps' => wp_kses(
				$help_text,
				array(
					'a' => array(
						'href'  => array(),
						'title' => array(),
						'class' => array(),
					),
				)
			),
		);

		$image_source_url_value = get_post_meta( $attachment->ID, 'image_source_url', true );

		if ( empty( $image_source_url_value ) ) {
			$image_source_url_value = get_post_meta( $attachment->ID, 'sk_image_source_url', true );
		}

		$form_fields['image_source_url'] = [
			'label' => esc_html__( 'Image Source URL', 'pmc-gallery-v4' ),
			'value' => $image_source_url_value,
		];

		return $form_fields;
	}

	public function save_fields( $post, $attachment ) {
		if ( isset( $attachment['image_credit'] ) ) {
			$image_credit = wp_strip_all_tags( $attachment['image_credit'] );
			update_post_meta( $post['ID'], '_image_credit', $image_credit );
			$gallery_attachment            = Attachment_Detail::get_instance();
			$keywords                      = $gallery_attachment->get_unique_word( $attachment );
			$keywords                      = array_merge( $keywords, explode( ' ', $post['post_content_filtered'] ) );
			$keywords                      = array_unique( (array) $keywords );
			$post['post_content_filtered'] = sanitize_text_field( implode( ' ', $keywords ) );
			$post['meta_input']            = array(
				'search_keyword' => $post['post_content_filtered'],
			);
		}

		if ( isset( $attachment['image_source_url'] ) ) {
			$img_source_url = esc_url_raw( $attachment['image_source_url'] );
			update_post_meta( $post['ID'], 'image_source_url', $img_source_url );
		}

		return $post;
	}

	/**
	 * Enhanced captioning for Galleries
	 *
	 * This customizes the caption field in the pop-up media manager on posts so
	 * that the caption box expands when the image gains focus.
	 */
	public function replace_tmpl_attachment() {
		?>
		<script type="text/javascript">
					jQuery( document ).ready( function ( $ ) {
						var tmplImageDetailsFirst = $( "script#tmpl-image-details:first" );
						tmplImageDetailsFirst.remove();
					} );
		</script>
		<?php

		include_once PMC_GALLERY_PLUGIN_DIR . '/template-parts/js-templates/image-details.php'; //phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

	}

	/**
	 * Enqueue script to manage Image credit field from Caption shortcode screen.
	 */
	public function admin_enqueue_scripts( $hook ) {

		if ( ! empty( $hook ) && ( 'post.php' === $hook || 'post-new.php' === $hook ) ) {

			// Enqueue Script.
			wp_enqueue_script(
				'admin_caption_shortcode_frame',
				sprintf( '%s/assets/build/js/admin-caption-shortcode-frame.js', PMC_GALLERY_PLUGIN_URL ),
				[ 'jquery' ],
				1.0,
				true
			);

		}

	}

	/**
	 * function to add Image Credit metadata to attachment data prepared for JavaScript
	 *
	 * @param array $response Array of prepared attachment data.
	 * @param \WP_Post $attachment Attachment object.
	 * @param array|false $meta Array of attachment meta data, or false if there is none.
	 *
	 * @return array
	 */
	public function wp_prepare_attachment_for_js( $response, $attachment, $meta ): array {

		if ( is_array( $response ) && is_a( $attachment, 'WP_Post' ) ) {
			$response['image_credit'] = get_post_meta( $attachment->ID, '_image_credit', true );
		}

		return $response;

	}

	/**
	 * Ajax callback to save image_credit value to attachment from Edit caption shortcode UI frame.
	 */
	public function save_attachment_credit_field() {

		$id = \PMC::filter_input( INPUT_POST, 'attachment_id', FILTER_SANITIZE_NUMBER_INT );

		if ( ! isset( $id ) || ! $id ) {
			wp_send_json_error();
		}

		$id = absint( $id );

		check_ajax_referer( 'update-post_' . $id, 'nonce' );

		$post = get_post( $id, ARRAY_A );

		if ( 'attachment' !== $post['post_type'] ) {
			wp_send_json_error();
		}

		$image_credit = \PMC::filter_input( INPUT_POST, 'image_credit', FILTER_SANITIZE_STRING );

		$update = update_post_meta( $id, '_image_credit', $image_credit );

		if ( ! $update ) {
			wp_send_json_error();
		}

		wp_send_json_success( $update );

		// codecov ignore because this final bracket keeps getting missed because of the wp_send_json_success.
	} // @codeCoverageIgnore
}

// EOF
