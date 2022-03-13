<div class="pmc-amp-gallery">
	<div class="pmc-amp-gallery-image-counter">
		<?php
		/* translators: %1$s - Current image number, %2$s - Total images */
		printf( esc_html__( '%1$s of %2$s', 'pmc-google-amp' ), esc_html( $current_image_number ), esc_html( $total_images ) );
		?>
	</div>
	<div class="pmc-amp-gallery-nav">
		<div class="pmc-amp-gallery-image-prev-button" role="button">
			<a href="<?php echo esc_url( $prev_gallery_url ); ?>"><?php echo wp_kses_post( apply_filters( 'pmc_amp_gallery_prev_html', __( 'Prev', 'pmc-google-amp' ) ) ); ?></a>
		</div>
		<div class="pmc-amp-gallery-image-next-button" role="button">
			<a href="<?php echo esc_url( $next_gallery_url ); ?>"><?php echo wp_kses_post( apply_filters( 'pmc_amp_gallery_next_html', __( 'Next', 'pmc-google-amp' ) ) ); ?></a>
		</div>
	</div>
	<div class="pmc-amp-gallery-image"><?php echo $gallery_image_html; ?></div>
	<h1 class="pmc-amp-gallery-post-title"><?php echo esc_html( $post_title ); ?></h1>
	<div class="pmc-amp-gallery-image-title"><?php echo esc_html( $gallery_image_title ); ?></div>
	<div class="pmc-amp-gallery-image-caption"><?php echo wp_kses_post( $gallery_image_caption ); ?></div>
	<div class="pmc-amp-gallery-image-meta">
		<span class="pmc-amp-gallery-image-credit"><?php echo esc_html( $gallery_image_credit ); ?></span>
	</div>
</div>
