<?php
if (empty($id) || empty($widget_id)) {
	return;
}
?>
<section class="cxense-widget // <?php echo esc_attr($classes ?? ''); ?>">
	<div class="cxense-widget-div" id="<?php echo esc_attr($id ?? ''); ?>" data-widget_id="<?php echo esc_attr($widget_id ?? ''); ?>">
	</div>
</section>