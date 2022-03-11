<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="print-plus-shop-offer-footer // <?php echo esc_attr( $print_plus_shop_offer_footer_classes ?? '' ); ?>" >
	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_footer_information, true ); ?>
	<ul class="print-plus-shop-offer-footer__list // <?php echo esc_attr( $print_plus_shop_offer_footer_list_classes ?? '' ); ?>">
		<?php foreach ( $print_plus_shop_offer_footer_items ?? [] as $item ) { ?>
			<li class="print-plus-shop-offer-footer__list_item // <?php echo esc_attr( $print_plus_shop_offer_footer_list_item_classes ?? '' ); ?>">
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $item, true ); ?>
			</li>
		<?php } ?>
	</ul>
</div>
