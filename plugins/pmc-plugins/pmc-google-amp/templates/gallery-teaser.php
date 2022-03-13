<?php
/**
 * Display linked gallery for runway post type.
 *
 * @author Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since 2017-07-05 CDWE-446
 *
 * @package pmc-google-amp
 */

$items = array_values( $gallery->items );
$gallery_permalink = get_permalink( $gallery->id );
?>
<div class="gallery-image-section">

	<div class="gallery-thumbnails">

		<?php
		foreach ( $items as $key => $attachment_id ) {
			// Skip first image since it appears above as featured.
			if ( 0 === $key ) {
				continue;
			}

			$attachment = get_post( $attachment_id );
			$image_url = wp_get_attachment_image_url( $attachment_id, $image_size );

			if ( empty( $image_url ) ) {
				continue;
			}

			$amp_var = 'amp';
			if ( defined( 'AMP_QUERY_VAR' ) ) {
				$amp_var = AMP_QUERY_VAR;
			}

			$slide_url = sprintf( '%s#!%d/%s', $gallery->url, ( $key + 1 ), $attachment->post_name );

			/**
			 * If AMP pages are enable for gallery than link
			 * should be for AMP slide.
			 */
			if ( defined( 'ENABLE_AMP_GALLERY' ) && ENABLE_AMP_GALLERY ) {
				$slide_url = sprintf( '%s/%s/%s/', untrailingslashit( $gallery_permalink ), $attachment->post_name, $amp_var );
			}

			?>
			<a href="<?php echo esc_url( $slide_url ); ?>" class="gallery-thumbnail <?php echo 'thumbnail-' . esc_html( $attachment_id ); ?>" title="<?php echo esc_attr( $attachment->post_title ); ?>" >
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $attachment->post_title ); ?>" />
			</a>
			<?php
			if ( $num_items_to_display < ( $key + 1 ) ) {
				break;
			}
		}
		?>
	</div>
</div>
