<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<a href="<?php echo esc_url( $linked_gallery_url ?? '' ); ?>" title="<?php echo esc_html( $linked_gallery_title_text ?? '' ); ?>" class="linked-gallery // lrv-a-unstyle-link">

	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image_primary, true ); ?>

	<div class="lrv-a-grid u-align-items-stretch a-cols5@tablet a-cols4@mobile-max u-grid-gap-50 lrv-u-margin-t-050">

			<div class="lrv-u-flex u-align-items-flex-end u-background-color-pale-sky lrv-u-color-white lrv-u-padding-lr-050 lrv-u-padding-tb-025">
				<div class="lrv-u-font-family-secondary lrv-u-font-size-12@mobile-max lrv-u-font-size-14 lrv-u-font-weight-bold">View<br>Gallery</div>
			</div>

			<?php foreach ( $linked_gallery_items ?? [] as $item ) { ?>
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $item, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $linked_gallery_last_item ) ) { ?>
				<div class="a-hidden@mobile-max">
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $linked_gallery_last_item, true ); ?>
				</div>
			<?php } ?>

	</div>

</a>
