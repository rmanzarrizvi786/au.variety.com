<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="featured-chart // <?php echo esc_attr( $featured_chart_classes ?? '' ); ?>">
	<iframe title="Featured Chart" aria-label="Chart" src="<?php echo esc_url( $featured_chart_iframe_url ?? '' ); ?>" scrolling="no" frameborder="0" width="100%" height="<?php echo esc_attr( $featured_chart_iframe_height_attr ?? '' ); ?>"></iframe>

	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-button.php', $c_button, true ); ?>
</section>
