<?php
/**
 * Template for gallery thumbnails.
 *
 * @author Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since 2017-07-11 CDWE-449
 *
 * @package pmc-google-amp
 */

?>

<div class="gallery-thumbnail-container">
	<?php if ( ! empty( $gallery_data ) ) { ?>
	<ul class="gallery-thumbnail-list">
		<?php foreach ( $gallery_data as $index => $item ) { ?>
		<li class="gallery-thumbnail-item <?php echo ( $current_slide === $item['attachment_name'] ) ? 'active' : ''; printf( ' thumbnail-%d ', esc_attr( $index + 1 ) ); ?>">
			<a href="<?php echo esc_url( $item['amp_link'] ) ?>" title="<?php echo esc_attr( $item['image_title'] ) ?>">
				<img src="<?php echo esc_url( $item['image_url'] ) ?>" title="<?php echo esc_attr( $item['image_title'] ) ?>" alt="<?php echo esc_attr( $item['image_title'] ) ?>" class="thumbnail">
			</a>
		</li>
		<?php } // endforeach ?>
	</ul>
	<div class="clear"></div>
	<?php } // Endif ?>
</div>
