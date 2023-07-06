<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="c-lazy-image <?php echo esc_attr($modifier_class ?? ''); ?> <?php echo esc_attr($c_lazy_image_classes ?? ''); ?>">
	<?php if (!empty($c_lazy_image_link_url)) { ?>
		<a href="<?php echo esc_url($c_lazy_image_link_url ?? ''); ?>" class="c-lazy-image__link <?php echo esc_attr($c_lazy_image_link_classes ?? ''); ?>" <?php if (!empty($c_lazy_image_link_attr)) { ?> target="<?php echo esc_attr($c_lazy_image_link_attr ?? ''); ?>" <?php } ?>>
		<?php } ?>
		
		<?php if (!empty($c_lazy_image_crop_class)) { ?>
			<div class="<?php echo esc_attr($c_lazy_image_crop_class ?? ''); ?>" style="<?php echo esc_attr($c_lazy_image_crop_style_attr ?? ''); ?>">
			<?php } ?>

			<?php if (!empty($c_lazy_image_markup1)) { ?>
				<?php //echo wp_kses_post($c_lazy_image_markup ?? ''); ?>
			<?php } else { ?>
				<?php if($c_lazy_image['post_id'] == 9536) { ?>
					<!-- "https://images.thebrag.com/var/uploads/2023/07/gary-vee.jpg" -->
					<?php $c_lazy_image_src_url = "https://images.thebrag.com/var/uploads/2023/07/gary-vee.jpg"; ?>
				<?php } ?>
				
				<img class="c-lazy-image__img <?php echo esc_attr($c_lazy_image_img_classes ?? ''); ?>" src="<?php echo esc_url($c_lazy_image_src_url ?? ''); ?>" alt="<?php echo esc_attr($c_lazy_image_alt_attr ?? ''); ?>" data-lazy-srcset="<?php echo esc_attr($c_lazy_image_srcset_attr ?? ''); ?>" data-lazy-sizes="<?php echo esc_attr($c_lazy_image_sizes_attr ?? ''); ?>" height="<?php echo esc_attr($c_lazy_image_height_attr ?? ''); ?>" width="<?php echo esc_attr($c_lazy_image_width_attr ?? ''); ?>">
			<?php } ?>

			<?php if (!empty($c_lazy_image_crop_class)) { ?>
			</div>
		<?php } ?>

		<?php if (!empty($c_lazy_image_link_url)) { ?>
		</a>
	<?php } ?>
</div>