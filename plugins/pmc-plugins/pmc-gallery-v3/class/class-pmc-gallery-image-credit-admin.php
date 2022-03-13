<?php

/**
 * This class Allows Images to be saved with credit
 */
namespace PMC\Gallery\Attachment;

use PMC\Global_Functions\Traits\Singleton;

class Image_Credit {

	use Singleton;

	protected function __construct() {
		add_filter( 'attachment_fields_to_save', array( $this, 'save_fields' ), 10, 2 );
		add_filter( 'attachment_fields_to_edit', array( $this, 'add_fields' ), 10, 2 );
	}

	public function add_fields( $form_fields, $attachment ) {
		$help_text = 'Ex. Jane Smith/Shutterstock <a href="#" class="dashicons dashicons-editor-help imgedit-help-toggle" title="Give credit where credit is due. Best practices state that images must be from a reputable source and can be credited. Image credits must include, when possible the photographer’s name and organization, separated by a slash without spaces."></a>';
		$form_fields['image_credit'] = array(
			'label' => 'Image Credit',
			'value' => get_post_meta( $attachment->ID, '_image_credit', true ), // this value should not be escape here, wp core apply esc_attr when it build the <input> tag
			'helps' => wp_kses( $help_text, array( 'a' => array( 'href' => array(), 'title' => array(), 'class' => array() ) ) ), // this value need to be escape, on render there is no escape apply by wp core
		);

		return $form_fields;
	}

	public function save_fields( $post, $attachment ) {
		if ( isset( $attachment['image_credit'] ) ) {
			$image_credit = trim( strip_tags( $attachment['image_credit'] ) );
			update_post_meta( $post['ID'], '_image_credit', $image_credit );
			$gallery_attachment = \PMC\Gallery\Attachment\Detail::get_instance();
			$keywords = $gallery_attachment->get_unique_word( $attachment );
			$keywords = array_merge( $keywords, explode( ' ', $post['post_content_filtered'] ) );
			$keywords = array_unique( $keywords );
			$post['post_content_filtered'] = sanitize_text_field( implode( ' ', $keywords ) );
			$post['meta_input'] = array(
				'search_keyword' => $post['post_content_filtered'],
			);
		}

		return $post;
	}

}

Image_Credit::get_instance();

//EOF
