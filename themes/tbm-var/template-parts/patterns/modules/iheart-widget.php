<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="iheart-widget // <?php echo esc_attr( $iheart_widget_classes ?? '' ); ?>">
	<div class="lrv-u-padding-lr-1">
		<div class="iheart-widget__header-outer // <?php echo esc_attr( $iheart_widget_header_outer_classes ?? '' ); ?>">
			<h3 class="iheart-widget__header-title // <?php echo esc_attr( $iheart_widget_header_title_classes ?? '' ); ?>">
				<?php echo esc_html( $iheart_widget_header_title_text ?? '' ); ?>
			</h3>
			<div class="u-width-25">
				<?php \PMC::render_template( CHILD_THEME_PATH . '/assets/build/svg/' . ( $iheart_widget_svg ?? '' ) . '.svg', [], true ); ?>
			</div>
		</div>

		<div class="iheart-widget__header-subtitle // <?php echo esc_attr( $iheart_widget_header_subtitle_classes ?? '' ); ?>">
			<?php echo esc_html( $iheart_widget_header_subtitle_text ?? '' ); ?>
		</div>

		<iframe
			gesture="media"
			width="<?php echo esc_attr( $iheart_widget_iframe_width_attr ?? '' ); ?>"
			height="<?php echo esc_attr( $iheart_widget_iframe_height_attr ?? '' ); ?>"
			src="javascript:void(0);"
			data-lazy-src="<?php echo esc_url( $iheart_widget_iframe_url ?? '' ); ?>"
			frameborder="0"
		></iframe>

		<p class="iheart-widget__footer-subtitle // <?php echo esc_attr( $iheart_widget_footer_classes ?? '' ); ?>">
			<?php echo esc_html( $iheart_widget_footer_text ?? '' ); ?>
		</p>
	</div>
</section>
