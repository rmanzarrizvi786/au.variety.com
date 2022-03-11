<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<header class="hub-header // <?php echo esc_attr( $hub_header_classes ?? '' ); ?>">
	<div class="lrv-u-flex lrv-u-flex-direction-column lrv-u-align-items-center">
		<?php if ( ! empty( $c_heading ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
		<?php } ?>
		<?php if ( ! empty( $c_tagline ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true ); ?>
		<?php } ?>
	</div>
	<div class="lrv-u-flex lrv-u-width-100p lrv-u-justify-content-center lrv-u-align-items-center lrv-u-flex-direction-column@mobile-max lrv-u-border-t-1 u-border-color-brand-secondary-70 lrv-u-padding-t-075">
		<?php if ( ! empty( $o_sponsored_by ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-sponsored-by.php', $o_sponsored_by, true ); ?>
		<?php } ?>
		<?php if ( ! empty( $c_logo ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/components/c-logo.php', $c_logo, true ); ?>
		<?php } ?>
	</div>
</header>
