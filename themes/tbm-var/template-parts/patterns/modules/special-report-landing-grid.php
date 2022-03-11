<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="special-report-landing-content // <?php echo esc_attr( $special_report_landing_content_classes ?? '' ); ?>">
	<?php foreach ( $special_report_landing_items ?? [] as $item ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-grid-story.php', $item, true ); ?>
	<?php } ?>
</div>
