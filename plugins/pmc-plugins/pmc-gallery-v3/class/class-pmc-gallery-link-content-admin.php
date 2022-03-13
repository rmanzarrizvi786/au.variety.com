<?php
/* Add/Edit Gallery
 *
 *
 * @package PMC Gallery Plugin
 * @since 1/1/2013 Vicky Biswas
 *
 * Holds code needed to create backend for adding and editing galleries
 *
 * Technically this might ought to be PMC_Gallery_LinkContent because it adds functionality from PMC_LinkContent,
 * but I'm choosing Link_Content over LinkContent for standards consistency.
 */
namespace PMC\Gallery\Admin;

use PMC\Global_Functions\Traits\Singleton;

class Link_Content {

	use Singleton;

	/**
	 * Manages the Settings for PMC Gallery
	 */
	protected function __construct() {
		// We need PMC_LinkContent, without it this class does nothing.
		if ( ! class_exists( 'PMC_LinkContent' ) ) {
			if ( function_exists( 'pmc_load_plugin' ) ) {
				pmc_load_plugin( 'pmc-linkcontent', 'pmc-plugins' );
			} else {
				return;
			}
		}

		// Set up PMC_LinkContent
		\PMC_LinkContent::enqueue();

		add_action( 'add_meta_boxes', array( $this, 'meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

		add_filter( 'pmclinkcontent_post_types_gallery', array( $this, 'pmclinkcontent_post_types' ) );
	}

	/**
	 * Only search for pmc-gallery posts
	 *
	 * @param array $post_types
	 * @return array
	 */
	public function pmclinkcontent_post_types( $post_types ) {
		return array( \PMC_Gallery_Defaults::name );
	}

	/**
	 * Adds the meta box container
	 */
	public function meta_boxes() {
		$post_types = apply_filters( 'pmc_gallery_link_post_types', array( 'post' ) );
		add_meta_box(
			\PMC_Gallery_Defaults::name . '-link-box',
			'Add Link to a Gallery',
			array( $this, 'render_link_box_content' ),
			$post_types, 'normal', 'core'
		);
	}


	/**
	 * Render Meta Box content
	 */
	public function render_link_box_content() {
		global $post;
		wp_nonce_field( basename( __FILE__ ), \PMC_Gallery_Defaults::name . '-link-box' );

		$linked_data = get_post_meta( $post->ID, \PMC_Gallery_Defaults::name . '-linked-gallery', true );
		if ( is_array( $linked_data ) ) {
			$linked_data = array(
				'url' => $linked_data[0],
				'id' => $linked_data[1],
				'title' => $linked_data[2],
			);
			$linked_data = json_encode( $linked_data );
		}

		\PMC_LinkContent::insert_field( $linked_data, 'Gallery', 'gallery' );

	}

	/**
	 * Saves Linked Gallery ID
	 *
	 * @param int $post_id
	 * @return void
	 */
	function save_post( $post_id ) {

		if (
			( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			|| ( ! isset( $_POST['pmclinkcontent-post-value-gallery'] ) )
			|| ( ! isset($_POST['pmc-linkcontent-nonce']) )
			|| ( ! wp_verify_nonce( $_POST['pmc-linkcontent-nonce'], 'pmc-linkcontent' ) )
			|| ( ! current_user_can( 'edit_posts', $post_id ) )
		) {
			return;
		}

		$url_path = trim( $_POST['pmclinkcontent-post-value-gallery'] );
		// Strip slashes thanks to wp_magic_quotes().	 stripcslashes() to preserve linebreaks
		$url_path = stripcslashes( $url_path );
		// json_decode() also serves as $url_path validation
		$url_parts = json_decode( $url_path );

		if ( ! $url_parts ) {
			delete_post_meta( $post_id, \PMC_Gallery_Defaults::name . '-linked-gallery' );
			return;
		}

		/*
		 * json_encode() turns unicode characters into \uXXXX
		 * PHP 5.4 adds JSON_UNESCAPED_UNICODE, but for better compatibility we're using esc_html() to convert
		 * the unicode characters into entities.
		 * The json_encode() here is to pre-emptively encode the entities, and substr() to remove the quotes
		 * added by json_encode().
		 */
		/**
		 * @todo There's a better place to put the zoom code...
		 */
		$title = esc_html( $url_parts->title );
		$title = substr( json_encode( $title ), 1, -1 );
		$new_meta_value = array(
			'url' => esc_url_raw( $url_parts->url ),
			'id' => intval( $url_parts->id ),
			'title' => $title,
		);

		$current_meta_value = get_post_meta( $post_id, \PMC_Gallery_Defaults::name . '-linked-gallery', true );
		if ( $new_meta_value != $current_meta_value ) {

			// we need to remove old gallery -> post if exist
			if ( empty( $current_meta_value ) && $old_linked_data = json_decode( $current_meta_value, true ) ) {
				$old_gallery_id = $old_linked_data['id'];
				delete_post_meta( $old_gallery_id, \PMC_Gallery_Defaults::name . '-linked-post_id' );
			}

			// Link direction: post -> gallery
			update_post_meta( $post_id, \PMC_Gallery_Defaults::name . '-linked-gallery', json_encode( $new_meta_value ) );

			// Link direction: gallery -> post
			update_post_meta( $new_meta_value['id'], \PMC_Gallery_Defaults::name . '-linked-post_id', $post_id );
		}
	}

}

Link_Content::get_instance();

//EOF
