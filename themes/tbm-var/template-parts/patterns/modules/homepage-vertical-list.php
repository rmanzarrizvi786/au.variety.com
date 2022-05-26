<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.

$vertical_list_classes = str_replace(' lrv-u-height-100p', '', $vertical_list_classes);
?>
<section class="vertical-list // lrv-u-flex@tablet lrv-u-flex-direction-column -lrv-u-height-100p <?php echo esc_attr($vertical_list_classes ?? ''); ?>">

	<div class="vertical-list__header // <?php echo esc_attr($vertical_list_header_classes ?? ''); ?>">
		<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true); ?>

		<b class="a-border-fancy--top"></b>
		<b class="a-border-fancy--bottom"></b>

	</div>

	<div class="vertical-list__inner //  lrv-u-flex-grow-1 <?php echo esc_attr($vertical_list_inner_classes ?? ''); ?>">
		<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-list.php', $o_tease_list_primary, true); ?>

		<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-list.php', $o_tease_list_secondary, true); ?>
	</div>

	<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true); ?>
</section>