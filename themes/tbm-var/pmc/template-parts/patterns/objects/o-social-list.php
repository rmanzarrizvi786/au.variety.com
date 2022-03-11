<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<ul class="o-social-list <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_social_list_classes ?? '' ); ?>"
	<?php if ( ! empty( $o_social_list_labelledby_attr ) ) { ?>
		aria-labelledby="<?php echo esc_attr( $o_social_list_labelledby_attr ?? '' ); ?>"
	<?php } ?>
>
	<?php foreach ( $o_social_list_icons ?? [] as $item ) { ?>
		<li class="o-social-list__item <?php echo esc_attr( $o_social_list_item_classes ?? '' ); ?>">
			<?php if ( ! empty( $o_social_list_is_icon_button ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-icon-button.php', $item, true ); ?>
			<?php } else { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $item, true ); ?>
			<?php } ?>
		</li>
	<?php } ?>
</ul>
