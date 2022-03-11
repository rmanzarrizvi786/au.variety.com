<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<header class="o-header <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_heading_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_heading ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $o_figure ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-figure.php', $o_figure, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $c_button ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-button.php', $c_button, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $o_sponsored_by ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-sponsored-by.php', $o_sponsored_by, true ); ?>
	<?php } ?>
</header>
