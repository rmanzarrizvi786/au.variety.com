<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<ul class="dirt-details // <?php echo esc_attr( $dirt_details_classes ?? '' ); ?>">

	<?php if ( ! empty( $dirt_details_view_gallery_value_text ) ) { ?>
		<li>
			<a href="<?php echo esc_url( $dirt_details_view_gallery_url ?? '' ); ?>" class="lrv-a-unstyle-link">
				<span class="<?php echo esc_attr( $dirt_details_labels_classes ?? '' ); ?>">
					<?php echo esc_html( $dirt_details_view_gallery_label_text ?? '' ); ?>
				</span>
				<div class="u-width-25 lrv-a-glue-parent lrv-u-display-inline-block u-vertical-align-middle">
					<?php \PMC::render_template( CHILD_THEME_PATH . '/assets/build/svg/' . ( $dirt_details_gallery_count_svg ?? '' ) . '.svg', [], true ); ?>
					<div class="lrv-a-glue lrv-a-glue--b-0 lrv-u-width-100p lrv-u-font-size-12 u-font-family-basic lrv-u-flex lrv-u-justify-content-center lrv-u-align-items-center lrv-u-height-100p">
						<?php echo esc_html( $dirt_details_view_gallery_value_text ?? '' ); ?>
					</div>
				</div>
			</a>
		</li>
	<?php } ?>

	<?php if ( ! empty( $dirt_details_seller_value_text ) ) { ?>
		<li>
			<span class="<?php echo esc_attr( $dirt_details_labels_classes ?? '' ); ?>">
				<?php echo esc_html( $dirt_details_seller_label_text ?? '' ); ?>:
			</span>
			<?php echo esc_html( $dirt_details_seller_value_text ?? '' ); ?>
		</li>
	<?php } ?>

	<?php if ( ! empty( $dirt_details_location_value_text ) ) { ?>
		<li>
			<span class="<?php echo esc_attr( $dirt_details_labels_classes ?? '' ); ?>">
				<?php echo esc_html( $dirt_details_location_label_text ?? '' ); ?>:
			</span>
			<?php echo esc_html( $dirt_details_location_value_text ?? '' ); ?>
		</li>
	<?php } ?>

	<?php if ( ! empty( $dirt_details_price_value_text ) ) { ?>
		<li>
			<span class="<?php echo esc_attr( $dirt_details_labels_classes ?? '' ); ?>">
				<?php echo esc_html( $dirt_details_price_label_text ?? '' ); ?>:
			</span>
			<?php echo esc_html( $dirt_details_price_value_text ?? '' ); ?>
		</li>
	<?php } ?>

	<?php if ( ! empty( $dirt_details_size_value_text ) ) { ?>
		<li>
			<span class="<?php echo esc_attr( $dirt_details_labels_classes ?? '' ); ?>">
				<?php echo esc_html( $dirt_details_size_label_text ?? '' ); ?>:
			</span>
			<?php echo esc_html( $dirt_details_size_value_text ?? '' ); ?>
		</li>
	<?php } ?>

</ul>
