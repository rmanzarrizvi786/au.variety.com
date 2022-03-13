<?php
/**
 * This class Allows Images to be restrict for use
 */

namespace PMC\Geo_Restricted_Content;

use \PMC\Global_Functions\Traits\Singleton;

class Restrict_Image_Uses {

	use Singleton;

	const META_IMAGE_RESTRICTED_TYPE       = '_pmc_restricted_image_type';
	const META_IMAGE_ALLOWED_IN_FEED       = '_pmc_restricted_image_in_feeds';
	const META_IMAGE_ALLOWED_IN_NEWSLETTER = '_pmc_restricted_image_in_newsletters';
	const META_IMAGE_SINGLE_USED           = '_restricted_single_use_image_used';
	const META_IMAGE_SINGLE_USED_POST      = '_restricted_single_use_image_used_post';

	protected function __construct() {

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'save_post', [ $this, 'update_single_use_image_meta' ], 10, 3 );

		add_filter( 'attachment_fields_to_save', [ $this, 'save_fields' ], 10, 2 );
		add_filter( 'attachment_fields_to_edit', [ $this, 'add_fields' ], 10, 2 );
		add_filter( 'pmc_restricted_image_check_enabled', '__return_true' );

		// hook late so it doesn't override by other hook
		add_filter( 'wp_get_attachment_image_src', [ $this, 'maybe_block_image_from_feed' ], 99, 4 );
		// Hook early so image URL get replace before do shortcode
		add_filter( 'the_content', [ $this, 'maybe_block_image_from_feed_post_content' ], 1 );
		// hook late so it doesn't override by other hook
		add_filter( 'pmc_fetch_gallery', [ $this, 'filter_maybe_exclude_image_from_feed' ], 99, 2 );
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'mark_associated_press_images' ], 20, 2 ); // Needs to be higher than other filters to capture XMP info
		add_filter( 'update_post_metadata', [ $this, 'filter_check_single_image' ], 10, 4 );

	}

	/** Dont use single use image in multiple posts
	 * @param null|bool $retval
	 * @param int $object_id
	 * @param string $meta_key
	 * @param string $meta_value
	 *
	 * @return null|bool
	 */
	public function filter_check_single_image( $retval, $object_id, $meta_key, $meta_value ) {

		if ( '_thumbnail_id' === $meta_key && ! empty( $object_id ) && ! empty( $meta_value ) ) {

			$existing_post_id = get_post_meta( $meta_value, self::META_IMAGE_SINGLE_USED, true );

			if ( ! empty( $existing_post_id ) && $existing_post_id !== $object_id ) {

				$retval = false;
			} else {

				if ( 'single_use' === get_post_meta( $meta_value, self::META_IMAGE_RESTRICTED_TYPE, true ) ) {

					update_post_meta( $meta_value, self::META_IMAGE_SINGLE_USED, $object_id );
				}
			}
		}

		return $retval;
	}

	/**
	 * Enqueue any necessary scripts.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook ) {
		$file_extension       = ( \PMC::is_production() ) ? '.min' : '';
		$current_screen       = get_current_screen();
		$allowed_pages        = [ 'post.php', 'post-new.php', 'upload.php' ];
		$is_allowed_page      = ( ! empty( $hook ) && in_array( $hook, (array) $allowed_pages, true ) );
		$is_feat_img_backdoor = ( isset( $current_screen->base ) && 'posts_page_PMC_Featured_Image_Backdoor' === $current_screen->base );

		if ( empty( $is_allowed_page ) && empty( $is_feat_img_backdoor ) ) {
			return;
		}

		// Enqueue Style. Styling is also used for pmc-gallery-v4, so enqueue it.
		wp_enqueue_style(
			'restrict_image_uses_admin',
			sprintf( '%s/assets/css/admin.css', PMC_GEO_RESTRICTED_CONTENT_URL ),
			[],
			PMC_GEO_RESTRICTED_CONTENT_VERSION,
			false
		);

		// pmc-gallery-v4 plugin has its own JS to handle image restriction, so do not enqueue script.
		if ( isset( $current_screen->post_type ) && 'pmc-gallery' === $current_screen->post_type ) {
			return;
		}

		// Enqueue Script.
		wp_enqueue_script(
			'admin_media_library_image_restriction',
			sprintf( '%s/assets/js/admin-media-lib-image-restriction%s.js', PMC_GEO_RESTRICTED_CONTENT_URL, $file_extension ),
			[ 'jquery', 'media-views', 'media-models' ],
			PMC_GEO_RESTRICTED_CONTENT_VERSION,
			true
		);
	}

	/**
	 * Fires on save_post_post to update post meta for 'single use'(restricted) images.
	 *
	 * @param int      $post_id Post ID of post being saved.
	 * @param \WP_Post $post    Post Object.
	 * @param bool     $update  Whether this is an existing post being updated or not.
	 */
	public function update_single_use_image_meta( $post_id, $post, $update ) {

		$regex = '/wp-image-([\d]*)["|\' \']/m';

		// If post_id is empty, bail out.
		// If this is just a revision, bail out.
		// If $post is not object, bail out
		if ( ! empty( $post_id ) && ! wp_is_post_revision( $post_id ) && is_object( $post ) && 'pmc-gallery' !== $post->post_type ) {

			// Store previous "single use" images used in current post
			$existing_images = get_post_meta( $post_id, self::META_IMAGE_SINGLE_USED_POST, true );
			// To store current "single use" images used in current post
			$current_images             = [];
			$images_not_allowed_in_feed = [];
			$thumbnail_id               = get_post_thumbnail_id( $post_id );

			// Check if thumbnail is set or not
			if ( ! empty( $thumbnail_id ) ) {

				// If thumbnail is "single use" image then update meta-data for image else remove the meta-data entry
				if ( 'single_use' === get_post_meta( $thumbnail_id, self::META_IMAGE_RESTRICTED_TYPE, true ) ) {
					update_post_meta( $thumbnail_id, self::META_IMAGE_SINGLE_USED, $post_id );
					// Add image-id to $current_images list
					$current_images[] = $thumbnail_id;
				} else {
					delete_post_meta( $thumbnail_id, self::META_IMAGE_SINGLE_USED );
				}

				// If thumbnail is "allowed in feed" image then update meta-data for image else remove the meta-data entry
				if ( 'no' === get_post_meta( $thumbnail_id, self::META_IMAGE_ALLOWED_IN_FEED, true ) ) {
					$images_not_allowed_in_feed[] = $thumbnail_id;
				}

			}

			// Get the images used in post-content using regex
			if ( preg_match_all( $regex, $post->post_content, $matches, PREG_SET_ORDER ) ) {

				foreach ( $matches as $match ) {

					// Go through each images to check if it's "single use" image or not
					// If image is "single use" then update meta-data for image else remove the meta-data entry
					if ( 'single_use' === get_post_meta( $match[1], self::META_IMAGE_RESTRICTED_TYPE, true ) ) {

						update_post_meta( $match[1], self::META_IMAGE_SINGLE_USED, $post_id );
						// Add image-id to $current_images list
						$current_images[] = (int) $match[1];

					} else {
						delete_post_meta( $match[1], self::META_IMAGE_SINGLE_USED );
					}

					// Go through each images to check if it's "allowed in feed" image or not
					if ( 'no' === get_post_meta( $match[1], self::META_IMAGE_ALLOWED_IN_FEED, true ) ) {
						$images_not_allowed_in_feed[] = (int) $match[1];
					}

				}

			}

			$current_images = array_unique( (array) $current_images );

			// Add meta entry of List of "single use images" to current-post for cross references
			if ( ! empty( $current_images ) ) {
				update_post_meta( $post_id, self::META_IMAGE_SINGLE_USED_POST, $current_images );
			} else {
				// If there is no single use images for current post then delete meta entry
				delete_post_meta( $post_id, self::META_IMAGE_SINGLE_USED_POST );
			}

			$images_not_allowed_in_feed = array_unique( (array) $images_not_allowed_in_feed );

			// Add meta entry of List of "Allowed in feeds images" to current-post for cross references
			if ( ! empty( $images_not_allowed_in_feed ) ) {
				update_post_meta( $post_id, '_pmc_restricted_image_not_allowed_in_feeds', $images_not_allowed_in_feed );
			} else {
				// If there is no single use images for current post then delete meta entry
				delete_post_meta( $post_id, '_pmc_restricted_image_not_allowed_in_feeds' );
			}

			/**
			 * Remove meta entries from (single-use) images those are removed from current post
			 */
			if ( is_array( $existing_images ) ) {

				$existing_images = array_diff( array_unique( (array) $existing_images ), $current_images );

				foreach ( $existing_images as $image_id ) {

					delete_post_meta( $image_id, self::META_IMAGE_SINGLE_USED );

				}
			}
		}

	}

	/**
	 * Adds custom fields to the attachment fields list.
	 *
	 * @param array   $form_fields An array of attachment form fields.
	 * @param \WP_Post $attachment  The WP_Post attachment object.
	 *
	 * @return array An updated array of attachment form fields.
	 */
	public function add_fields( $form_fields, $attachment ):array {

		$form_fields['restricted_image_type'] = [
			'label' => esc_html__( 'Restricted Type', 'pmc-geo-restricted-content' ),
			'input' => 'html',
			'html'  => $this->image_restricted_type_input_fields( $attachment, get_post_meta( $attachment->ID, self::META_IMAGE_RESTRICTED_TYPE, true ) ),
		];

		$form_fields['allowed_in_feed'] = [
			'label' => esc_html__( 'Allowed in Feeds', 'pmc-geo-restricted-content' ),
			'input' => 'html',
			'html'  => $this->image_allowed_in_feed_input_fields( $attachment, get_post_meta( $attachment->ID, self::META_IMAGE_ALLOWED_IN_FEED, true ) ),
		];

		$form_fields['allowed_in_newsletter'] = [
			'label' => esc_html__( 'Allowed in Newsletter', 'pmc-geo-restricted-content' ),
			'input' => 'html',
			'html'  => $this->image_allowed_in_newsletter_input_fields( $attachment, get_post_meta( $attachment->ID, self::META_IMAGE_ALLOWED_IN_NEWSLETTER, true ) ),
		];

		return $form_fields;
	}

	/**
	 * save custom fields data to meta data.
	 *
	 * @param array $post       An array of post data.
	 * @param array $attachment An array of attachment metadata.
	 *
	 * @return array
	 */
	public function save_fields( $post, $attachment ): array {

		if ( isset( $post['ID'] ) ) {
			if ( isset( $attachment['restricted_image_type'] ) ) {
				update_post_meta( $post['ID'], self::META_IMAGE_RESTRICTED_TYPE, $attachment['restricted_image_type'] );
			}

			if ( isset( $attachment['image_allowed_in_feed'] ) ) {
				update_post_meta( $post['ID'], self::META_IMAGE_ALLOWED_IN_FEED, $attachment['image_allowed_in_feed'] );
			}

			if ( isset( $attachment['image_allowed_in_newsletter'] ) ) {
				update_post_meta( $post['ID'], self::META_IMAGE_ALLOWED_IN_NEWSLETTER, $attachment['image_allowed_in_newsletter'] );
			}
		}

		return $post;
	}

	/**
	 * Retrieve HTML for the image restricted radio buttons with the specified one checked.
	 *
	 * @param \WP_Post $post    The WP_Post attachment object.
	 * @param string   $checked Checked option
	 * @return string returns HTML content for image-restricted-type fields
	 */
	public function image_restricted_type_input_fields( $post, $checked = '' ) {

		$types = [
			'none'            => __( 'None', 'pmc-geo-restricted-content' ),
			'site_restricted' => __( 'Site Restricted', 'pmc-geo-restricted-content' ),
			'single_use'      => __( 'Single Use', 'pmc-geo-restricted-content' ),
		];

		if ( ! array_key_exists( (string) $checked, $types ) ) {
			$checked = 'none';
		}

		$out = [];

		foreach ( $types as $name => $label ) {

			$is_single_use = ( 'single_use' === $name ) ? get_post_meta( $post->ID, self::META_IMAGE_SINGLE_USED, true ) : '';

			$name  = esc_attr( $name );
			$out[] = "<input type='radio' name='attachments[{$post->ID}][restricted_image_type]' id='image-restricted-type-{$name}-{$post->ID}' value='$name' data-setting='image_restriction'" .
					( $checked === $name ? " checked='checked'" : '' ) .
					( 'single_use' === $name ? " data-singleUsePost='{$is_single_use}'" : '' ) .
					" /><label for='image-restricted-type-{$name}-{$post->ID}' class='align image-restricted-type-{$name}-label'>$label</label>";
		}

		return join( "\n", $out );
	}

	/**
	 * Retrieve HTML for the image restricted radio buttons with the specified one checked.
	 *
	 * @param \WP_Post $post    The WP_Post attachment object.
	 * @param string   $checked Checked option
	 * @return string returns HTML content for allowed-in-feed fields
	 */
	public function image_allowed_in_feed_input_fields( $post, $checked = '' ): string {

		$types = [
			'yes' => __( 'Yes', 'pmc-geo-restricted-content' ),
			'no'  => __( 'No', 'pmc-geo-restricted-content' ),
		];

		if ( ! array_key_exists( (string) $checked, $types ) ) {
			$checked = 'yes';
		}

		$out = [];

		foreach ( $types as $name => $label ) {
			$name  = esc_attr( $name );
			$out[] = "<input type='radio' name='attachments[{$post->ID}][image_allowed_in_feed]' data-setting='image_allowed_in_feed' id='image-allowed-in-feed-{$name}-{$post->ID}' value='$name'" .
					( $checked === $name ? " checked='checked'" : '' ) .
					" /><label for='image-allowed-in-feed-{$name}-{$post->ID}' class='align image-allowed-in-feed-{$name}-label'>$label</label>";
		}

		return join( "\n", $out );
	}

	/**
	 * Retrieve HTML for the image restricted radio buttons with the specified one checked.
	 *
	 * @param \WP_Post $post    The WP_Post attachment object.
	 * @param string   $checked Checked option
	 * @return string returns HTML content for allowed-in-feed fields
	 */
	public function image_allowed_in_newsletter_input_fields( $post, $checked = '' ): string {

		$types = [
			'yes' => __( 'Yes', 'pmc-geo-restricted-content' ),
			'no'  => __( 'No', 'pmc-geo-restricted-content' ),
		];

		if ( ! array_key_exists( (string) $checked, $types ) ) {
			$checked = 'yes';
		}

		$out = [];

		foreach ( $types as $name => $label ) {
			$name  = esc_attr( $name );
			$out[] = "<input type='radio' name='attachments[{$post->ID}][image_allowed_in_newsletter]' data-setting='image_allowed_in_newsletter' id='image-allowed-in-newsletter-{$name}-{$post->ID}' value='$name'" .
				( $checked === $name ? " checked='checked'" : '' ) .
				" /><label for='image-allowed-in-newsletter-{$name}-{$post->ID}' class='align image-allowed-in-newsletter-{$name}-label'>$label</label>";
		}

		return join( "\n", $out );
	}

	/**
	 * Filter to block images from feed if not allowed.
	 *
	 * @param array|false  $image         Either array with src, width & height, icon src, or false.
	 * @param int          $attachment_id Image attachment ID.
	 * @param string|array $size          Size of image. Image size or array of width and height values
	 *                                    (in that order). Default 'thumbnail'.
	 * @param bool         $icon          Whether the image should be treated as an icon. Default false.
	 *
	 * @return array|false Array of image properties if there is image src else return false.
	 */
	public function maybe_block_image_from_feed( $image, $attachment_id, $size, $icon ) {

		if ( is_feed() && is_array( $image ) && ! empty( $attachment_id ) ) {

			$replacement = self::get_image_replacement();

			if ( empty( $replacement ) ) {
				return $image;
			}

			if (
				( ! $this->is_newsletter_feed() && 'no' === get_post_meta( $attachment_id, self::META_IMAGE_ALLOWED_IN_FEED, true ) ) ||
				( $this->is_newsletter_feed() && $this->is_blocked_in_newsletter( $attachment_id ) )
			) {
				$image[0] = $replacement;
			}

		}

		return $image;

	}

	public function is_newsletter_feed() {
		return ( 'sailthru' === \PMC::filter_input( INPUT_GET, 'feed' ) );
	}

	public function is_blocked_in_newsletter( $attachment_id ) {
		return ( 'no' === get_post_meta( $attachment_id, self::META_IMAGE_ALLOWED_IN_NEWSLETTER, true ) );
	}

	/**
	 * Function to replace image URL from feed post-content if images is not allowed in feed.
	 *
	 * @param $content string post content
	 *
	 * @return string
	 */
	public function maybe_block_image_from_feed_post_content( $content = '' ): string {

		global $post;
		$regex = '/<img.+?wp-image-([\d]*)["|\' \'].*?>/i';

		if ( is_feed() && ! empty( $content ) && is_string( $content ) && is_a( get_post( $post ), 'WP_Post' ) ) {

			$not_allowed_in_feed = get_post_meta( $post->ID, '_pmc_restricted_image_not_allowed_in_feeds', true );
			$replacement         = self::get_image_replacement();

			if ( empty( $replacement ) ) {
				return $content;
			}

			if ( ! empty( $not_allowed_in_feed ) && preg_match_all( $regex, $content, $matches, PREG_SET_ORDER ) ) {

				foreach ( $matches as $match ) {

					if (
						( ! $this->is_newsletter_feed() && 'no' === get_post_meta( $match[1], self::META_IMAGE_ALLOWED_IN_FEED, true ) ) ||
						( $this->is_newsletter_feed() && $this->is_blocked_in_newsletter( $match[1] ) )
					) {

						if ( preg_match_all( '/src=[\'"]([^\'"]+)[\'"]/i', $match[0], $src_matches ) ) {
							$content = str_replace( $src_matches[1][0], $replacement, $content );
						}

					}
				}
			}
		}

		return (string)$content;

	}

	/**
	 * It gives replacement images URL.
	 *
	 * @return string default image URL for replacement
	 */
	public static function get_image_replacement(): string {
		return apply_filters( 'pmc_replace_image_in_feed', '' );
	}

	/**
	 * Function to replaces image URL for feeds from pmc-gallery posts if image is not allowed in feed.
	 *
	 * @param array $data   List of gallery slides
	 * @param  int $post_id current gallery post ID
	 *
	 * @return array List of gallery images
	 */
	public function filter_maybe_exclude_image_from_feed( $data, $post_id ) {

		if ( is_feed() && is_array( $data ) ) {

			$replacement = self::get_image_replacement();

			if ( ! empty( $replacement ) ) {

				foreach ( $data as $key => $gallery_item ) {

					if ( 'no' === get_post_meta( $gallery_item['ID'], self::META_IMAGE_ALLOWED_IN_FEED, true ) ) {
						$data[ $key ]['image'] = $replacement;
					}

				}
			}
		}

		return $data;

	}

	/**
	 * Mark all Associated Press images as single use
	 * @param array $metadata
	 * @param int $attachment_id
	 *
	 * @return array
	 */
	public function mark_associated_press_images( $metadata = [], $attachment_id = 0 ): array {
		if ( ! empty( $metadata['image_meta'] ) ) {

			$search_meta = [];
			foreach ( $metadata['image_meta'] as $key => $value ) {
				if ( is_string( $value ) ) {
					$search_meta[ $key ] = $value;
				}
			}
			$search_meta['_image_credit'] = get_post_meta( $attachment_id, '_image_credit', true );

			$list = [
				'Associated Press',
				'AP',
				'WRT AP',
				'\/AP',
				'via AP',
				'AP Images',
			];

			foreach ( $list as $item ) {
				$matches = preg_grep( '/\b' . $item . '\b/i', $search_meta );
				if ( ! empty( $matches ) ) {
					update_post_meta( $attachment_id, self::META_IMAGE_RESTRICTED_TYPE, 'single_use' );
					break;
				}
			}
		}

		return $metadata;
	}

}

// EOF
