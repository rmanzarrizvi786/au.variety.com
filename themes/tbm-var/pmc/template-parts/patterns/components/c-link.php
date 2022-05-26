<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ('#undefined' != $c_link_url) : ?>
	<a class="c-link <?php echo esc_attr($modifier_class ?? ''); ?> <?php echo esc_attr($c_link_classes ?? ''); ?>" href="<?php echo esc_url($c_link_url ?? ''); ?>" <?php if (!empty($c_link_target_attr)) { ?> target="<?php echo esc_attr($c_link_target_attr ?? ''); ?>" <?php } ?> <?php if (!empty($c_link_rel_attr)) { ?> rel="<?php echo esc_attr($c_link_rel_attr ?? ''); ?>" <?php } ?> <?php if (!empty($c_link_aria_label_attr)) { ?> aria-label="<?php echo esc_attr($c_link_aria_label_attr ?? ''); ?>" <?php } ?>>
		<?php echo esc_html($c_link_text ?? ''); ?>
	</a>
<?php else : ?>
	<span class="c-link <?php echo esc_attr($modifier_class ?? ''); ?> <?php echo esc_attr($c_link_classes ?? ''); ?>" <?php if (!empty($c_link_rel_attr)) { ?> rel="<?php echo esc_attr($c_link_rel_attr ?? ''); ?>" <?php } ?> <?php if (!empty($c_link_aria_label_attr)) { ?> aria-label="<?php echo esc_attr($c_link_aria_label_attr ?? ''); ?>" <?php } ?>>
		<?php echo esc_html($c_link_text ?? ''); ?>
	</span>
<?php endif; ?>