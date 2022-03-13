<div class="pmc-listicle-gallery-v2">

	<a href="<?php echo esc_url( $data[ 'first_gallery_item_url' ] ); ?>">
		<div class="view-slideshow"><?php esc_html_e( 'view slideshow' ); ?></div>
		<?php the_post_thumbnail( 'large' ); ?>
	</a>

	<!-- body -->

	<div class="gallery-body">
		<div class="ad">
			<?php pmc_adm_render_ads( apply_filters( 'listicle_gallery_override_body_ad', 'right-rail-2' ) ); ?>
		</div>
		<?php echo wp_kses_post( $data[ 'content' ] ); ?>
	</div>

</div>