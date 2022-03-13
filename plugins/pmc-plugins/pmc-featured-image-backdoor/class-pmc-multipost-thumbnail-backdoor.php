<?php

use PMC\Global_Functions\Traits\Singleton;

class PMC_Multipost_Thumbnail_Backdoor {

	use Singleton;

	private $_multi_thumbnail_sizes_to_support = array();

	protected function __construct() {

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		$this->_multi_thumbnail_sizes_to_support = apply_filters( 'pmc_multipost_thumbnail_backdoor', $this->_multi_thumbnail_sizes_to_support );

		if ( empty( $this->_multi_thumbnail_sizes_to_support ) ) {
			return;
		}

		add_filter( 'pmc_featured_image_backdoor_post_row_actions', array(
			$this,
			'multipost_thumbnail_post_actions'
		) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_ajax_pmc-multipost_thumbnail_backdoor_image', array(
			$this,
			'set_multipost_thumbnail_image'
		) );


		add_action( 'pmc_featured_image_backdoor_list_posts_row_html', array(
			$this,
			'row_html'
		) );

	}


	public function multipost_thumbnail_post_actions( $actions ) {

		if ( empty( $this->_multi_thumbnail_sizes_to_support ) ) {
			return $actions;
		}

		foreach ( $this->_multi_thumbnail_sizes_to_support as $slug => $text ) {
			$actions['inline hide-if-no-js'] .= ' | <a href="#" class="editinline-multi-thumb" data-text="' . esc_attr( $text ) . '" data-slug="' . esc_attr( $slug ) . '" title="' . esc_attr( 'Set ' . $text . ' Image' ) . '">' . esc_html( 'Set ' . $text . ' Image' ) . '</a>';
		}

		return $actions;

	}

	public function enqueue_scripts() {

		if ( 'posts_page_PMC_Featured_Image_Backdoor' !== get_current_screen()->id ) {
			return;
		}

		wp_enqueue_script( 'pmc-multipost-thumbnail-inline-edit', plugins_url( 'js/pmc-multipost-thumbnail-inline-edit.js', __FILE__ ), array( 'jquery' ) );

	}

	public function set_multipost_thumbnail_image() {
		check_ajax_referer( '_pmc_featured_image_inline_edit', 'backdoor_nonce' );

		header( 'Content-Type: application/json' );

		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( json_encode( array(
				'error'   => true,
				'message' => 'Are your sure you want to do that?',
			) ) );
		}

		if ( empty( $_POST['attachment_id'] ) || empty( $_POST['post_id'] ) ) {
			wp_die( json_encode( array(
				'error'   => true,
				'message' => 'Valid IDs were not sent.',
			) ) );
		}

		$attachment_id  = (int) $_POST['attachment_id'];
		$post_id        = (int) $_POST['post_id'];
		$post_type      = get_post_type( $post_id );
		$thumbnail_id   = sanitize_text_field( $_POST['thumbnail_id'] );
		$thumbnail_text = sanitize_text_field( $_POST['thumbnail_text'] );

		if ( 'post' !== $post_type || 'attachment' !== get_post_type( $attachment_id ) ) {
			wp_die( json_encode( array(
				'error'   => true,
				'message' => 'The correct post types were not sent.',
			) ) );
		}


		if ( empty( $_POST['thumbnail_id'] ) ) {
			wp_die( json_encode( array(
				'error'   => true,
				'message' => 'The correct thumbnail name was not sent.',
			) ) );
		}

		$result = MultiPostThumbnails::set_meta( $post_id, $post_type, $thumbnail_id, $attachment_id );

		if ( $result ) {
			$new_thumb_attr = array( 'style' => 'width:35px;height:35px;float:left;padding: 0 10px 5px 0;' );
			$new_thumb_html = wp_get_attachment_image( $attachment_id, 'thumbnail', false, $new_thumb_attr );
			$message        = sanitize_text_field( 'The ' . $thumbnail_text . ' image was updated successfully.' );

			wp_die( json_encode( array(
				'error'   => false,
				'message' => $message,
				'id'      => $post_id,
				'markup'  => $new_thumb_html,
			) ) );
		} else {
			wp_die( json_encode( array(
				'error'   => true,
				'message' => 'The ' . $thumbnail_text . ' image was unable to be set at this time.',
			) ) );

		}
	}

	public function row_html( $post = "" ) {
		if ( empty( $post ) ) {
			return;
		}

		if ( empty( $this->_multi_thumbnail_sizes_to_support ) ) {
			return;
		}

		foreach ( $this->_multi_thumbnail_sizes_to_support as $slug => $text ) {
			$thumbnail_id = MultiPostThumbnails::get_post_thumbnail_id( $post->post_type, $slug, $post->ID );
			?>
			<a href="#" class="editinline-multi-thumb" id="<?php echo esc_attr( $post->ID . "-{$slug}-image" ); ?>"
			   style="display:block;" data-text="<?php echo esc_attr( $text ); ?>"
			   title="<?php echo esc_attr( $text ); ?>"
			   data-slug="<?php echo esc_attr( $slug ); ?>"
			   data-image-id="<?php echo esc_attr( $thumbnail_id ); ?>">
				<?php
				if ( MultiPostThumbnails::has_post_thumbnail( $post->post_type, $slug, $post->ID ) ) {
					MultiPostThumbnails::the_post_thumbnail( $post->post_type, $slug, $post->ID, 'thumbnail', array( 'style' => 'width:35px;height:35px;float:left;padding: 0 10px 5px 0;' ) );
				} else {
					echo '<div style="width:34px;height:34px;float:left;margin: 0 10px 5px 0;background-color:#CCC;border:1px solid #999;"></div>';
				}
				?>
			</a>
			<?php
		}
	}

}

PMC_Multipost_Thumbnail_Backdoor::get_instance();
