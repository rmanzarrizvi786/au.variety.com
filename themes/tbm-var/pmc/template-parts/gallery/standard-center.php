<?php
$vertical          = pmc_get_the_primary_term( 'vertical' );
$meta_prefix       = 'gallery_intro_card_details_';
$gallery_meta_data = get_post_meta( get_the_ID() );

if ( array_key_exists( $meta_prefix . 'title', $gallery_meta_data ) ) {
	$gallery_intro_title = $gallery_meta_data[ $meta_prefix . 'title' ][0];
}
if ( array_key_exists( $meta_prefix . 'description', $gallery_meta_data ) ) {
	$gallery_intro_description = $gallery_meta_data[ $meta_prefix . 'description' ][0];
}

$up_next_post = pmc_get_upnext_gallery( 'category' );
$prev_post = pmc_get_upnext_gallery( 'category', false );

?>
<div class="gallery__center">

	<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/gallery-navigation.php', [], true ); ?>

	<div class="gallery__center-main-image">

		<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/waiting-animation.php', [], true ); ?>

		<?php // Placeholder for the main gallery image being shown ?>
		<img src="" style="display: none;"/>
		<?php
		if ( ! empty( $gallery_intro_title ) && ! empty( $gallery_intro_description ) && ! empty( $vertical ) ) {
			PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/intro-card.php', [
				'vertical'    => $vertical,
				'title'       => $gallery_intro_title,
				'description' => $gallery_intro_description
			], true );
		}
		?>
		<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/interstitial.php', [], true ); ?>

	</div>

	<?php if ( ! empty( $prev_post ) ) : ?>
		<div class="gallery__center-prev">
			<div class="prevInfo">
				<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>"> </a>
			</div>
		</div>
	<?php endif; ?>
	<?php if ( ! empty( $up_next_post ) ) : ?>
		<div class="gallery__center-upnext">
			<div class="nextInfo">
				<a href="<?php echo esc_url( get_permalink( $up_next_post->ID ) ); ?>">
					<div class="gallery__center-next-label">
						<span><?php esc_html_e( 'Next', 'pmc-core' ); ?></span>
					</div>
					<h2><?php echo esc_html( $up_next_post->post_title ); ?></h2>
				</a>
			</div>
		</div>

	<?php endif; ?>
</div>
