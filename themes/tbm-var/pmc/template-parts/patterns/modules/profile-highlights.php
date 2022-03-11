<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section id="<?php echo esc_attr( $profile_highlights_id_attr ?? '' ); ?>" class="profile-highlights // <?php echo esc_attr( $profile_highlights_classes ?? '' ); ?>">

	<?php if ( ! empty( $c_heading ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
	<?php } ?>

	<div class="profile-highlights__slider // lrv-a-wrapper <?php echo esc_attr( $profile_highlights_slider_outer_classes ?? '' ); ?>">
		<div class="js-Flickity js-Flickity--100p js-Flickity--isContained js-Flickity--bordered-buttons <?php echo esc_attr( $profile_highlights_slider_classes ?? '' ); ?>">
			<?php foreach ( $slider_items ?? [] as $item ) { ?>
				<div class="js-Flickity-cell lrv-u-margin-lr-050 u-margin-lr-1@tablet">
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/modules/profile-blurb.php', $item, true ); ?>
				</div>
			<?php } ?>
		</div>
	</div>

</section>
