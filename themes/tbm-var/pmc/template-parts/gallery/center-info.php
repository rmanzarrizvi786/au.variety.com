<?php if ( is_array( $gallery ) ) : ?>
	<?php foreach ( $gallery as $item ) : ?>
		<?php $credit = function_exists( 'pmc_get_photo_credit' ) ? pmc_get_photo_credit( $item['ID'] ) : false; ?>
		<div class="gallery__center-photo-info">
			<?php if ( ! empty( $item['title'] ) ) : ?>
				<h2><?php echo esc_html( $item['title'] ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $item['caption'] ) ) : ?>
				<p class="sub-text-1"><?php echo esc_html( strip_tags( $item['caption'] ) ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $credit ) ) : ?>
				<p class="gallery__center-caption sub-text-2"><?php echo esc_html( $credit ); ?></p>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
<?php endif; ?>
