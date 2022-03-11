<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="homepage-voices__wrapper // u-margin-t-125 <?php echo esc_attr( $homepage_voices_wrapper_classes ?? '' ); ?>">

	<div class="homepage-voices // <?php echo esc_attr( $homepage_voices_classes ?? '' ); ?>">
		<div class="homepage-voices__header // <?php echo esc_attr( $homepage_voices_header_classes ?? '' ); ?>">
			<?php if ( ! empty( $c_span_title ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_title, true ); ?>
			<?php } ?>

			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true ); ?>

			<?php if ( ! empty( $c_span_subtitle ) ) { ?>
				<div class="lrv-u-padding-tb-050">
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_subtitle, true ); ?>
				</div>
			<?php } ?>

			<?php if ( ! empty( $cxense_magazine_subscribe_widget ) ) { ?>
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/cxense-widget.php', $cxense_magazine_subscribe_widget, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_link ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link, true ); ?>
			<?php } ?>
		</div>

		<?php if ( ! empty( $c_lazy_image_mobile ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image_mobile, true ); ?>
		<?php } ?>

		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-tease-list.php', $o_tease_list, true ); ?>

		<div class="homepage-voices__footer // <?php echo esc_attr( $header_voices_footer_classes ?? '' ); ?>">
			<?php if ( ! empty( $c_footer_tagline ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_footer_tagline, true ); ?>
			<?php } ?>

			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
		</div>
	</div>
</section>
