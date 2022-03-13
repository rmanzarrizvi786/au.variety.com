<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<header class="o-header <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_heading_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_heading ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-heading', $c_heading, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $o_figure ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-figure', $o_figure, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $c_button ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-button', $c_button, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $o_sponsored_by ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-sponsored-by', $o_sponsored_by, true ); ?>
	<?php } ?>
</header>
