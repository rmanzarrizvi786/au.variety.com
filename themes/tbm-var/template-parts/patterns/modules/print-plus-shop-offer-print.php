<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="print-plus-shop-offer // <?php echo esc_attr( $print_plus_shop_offer_classes ?? '' ); ?>" >
	<div class="print-plus-shop-offer-title // <?php echo esc_attr( $print_plus_shop_offer_title_classes ?? '' ); ?>" >
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image_banner, true ); ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_name, true ); ?>
	</div>
	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image_offer, true ); ?>
	<div class="print-plus-shop-offer-body // <?php echo esc_attr( $print_plus_shop_offer_body_classes ?? '' ); ?>" >
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_first_item, true ); ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-checks-list.php', $o_checks_list_first_item_details, true ); ?>
		<?php if ( ! empty( $c_span_additional_offer_items ) ) { ?>
			<?php foreach ( $c_span_additional_offer_items ?? [] as $item ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $item, true ); ?>
			<?php } ?>
		<?php } ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_additional_item, true ); ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_offer_cost, true ); ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-button.php', $c_button_offer, true ); ?>
	</div>
</div>
