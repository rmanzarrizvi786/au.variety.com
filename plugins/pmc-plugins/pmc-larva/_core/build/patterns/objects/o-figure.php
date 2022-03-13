<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $o_figure_link_url ) ) { ?>
	<a href="<?php echo esc_url( $o_figure_link_url ?? '' ); ?>" class="o-figure__link <?php echo esc_attr( $o_figure_link_classes ?? '' ); ?>"
		<?php if ( ! empty( $o_figure_link_target_attr ) ) { ?>
			target="<?php echo esc_attr( $o_figure_link_target_attr ?? '' ); ?>"
		<?php } ?>
		<?php if ( ! empty( $o_figure_link_tabindex_attr ) ) { ?>
			tabindex="<?php echo esc_attr( $o_figure_link_tabindex_attr ?? '' ); ?>"
		<?php } ?>
	>
<?php } ?>

<figure class="o-figure <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_figure_classes ?? '' ); ?> lrv-u-max-width-100p" style="<?php echo esc_attr( $o_figure_width_attr ?? '' ); ?>">

	<?php if ( ! empty( $c_lazy_image ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-lazy-image', $c_lazy_image, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $c_figcaption ) ) { ?>
		<?php if ( ! empty( $o_figure_figcaption_outer ) ) { ?>
			<div class="o-figure__figcaption-outer <?php echo esc_attr( $o_figure_figcaption_outer_classes ?? '' ); ?>">
		<?php } ?>

			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-figcaption', $c_figcaption, true ); ?>

		<?php if ( ! empty( $o_figure_figcaption_outer ) ) { ?>
			</div>
		<?php } ?>
	<?php } ?>

</figure>

<?php if ( ! empty( $o_figure_link_url ) ) { ?>
	</a>
<?php } ?>
