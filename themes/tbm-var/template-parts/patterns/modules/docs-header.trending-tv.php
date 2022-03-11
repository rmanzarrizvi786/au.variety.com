<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<header class="docs-header // <?php echo esc_attr( $docs_header_classes ?? '' ); ?>">
	<div class="lrv-u-flex lrv-u-flex-direction-column lrv-u-align-items-center">
		<?php if ( ! empty( $c_heading ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
		<?php } ?>
	</div>
	<div class="inner-docs-header // <?php echo esc_attr( $inner_docs_header_classes ?? '' ); ?>">
		<?php if ( ! empty( $o_sponsored_by ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-sponsored-by.php', $o_sponsored_by, true ); ?>
		<?php } ?>
		<?php if ( ! empty( $c_logo ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/components/c-logo.php', $c_logo, true ); ?>
		<?php } ?>
	</div>
	<div class="data_credit // <?php echo esc_attr( $o_data_by_inner_docs_header_classes ?? '' ); ?>">
		<?php if ( ! empty( $o_data_by ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-sponsored-by.php', $o_data_by, true ); ?>
		<?php } ?>
		<?php if ( ! empty( $o_data_by_logo ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/components/c-logo.php', $o_data_by_logo, true ); ?>
		<?php } ?>
	</div>
</header>
