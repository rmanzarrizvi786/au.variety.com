<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="must-read-widget // u-border-t-6 lrv-u-background-color-white lrv-u-padding-b-1 lrv-u-padding-lr-1 <?php echo esc_attr($must_read_widget_classes ?? ''); ?>">

	<div class="must-read-widget__header // <?php echo esc_attr($must_read_widget_header_classes ?? ''); ?>">
		<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true); ?>
	</div>

	<div class="must-read-widget__inner // <?php echo esc_attr($must_read_widget_inner_classes ?? ''); ?>">
		<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-list.php', $o_tease_list_primary, true); ?>

		<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-list.php', $o_tease_list_secondary, true); ?>
	</div>
</section>