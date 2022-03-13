<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="carousel-grid //">
	<div class="lrv-a-wrapper">
		<div class="lrv-a-carousel-grid <?php echo esc_attr( $carousel_grid_overlay_layout_classes ?? '' ); ?>">

			<div class="lrv-u-padding-a-1">
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-card', $o_card_large, true ); ?>
			</div>

			<div class="lrv-u-padding-a-1 <?php echo esc_attr( $carousel_grid_overlay_secondary_classes ?? '' ); ?>">
				<?php foreach ( $o_card_items ?? [] as $item ) { ?>
					<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-card', $item, true ); ?>
				<?php } ?>
			</div>

		</div>
	</div>
</div>
