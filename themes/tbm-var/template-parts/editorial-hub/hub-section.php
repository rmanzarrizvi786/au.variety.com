<?php
/**
 * Hub Section Template.
 *
 * @package pmc-variety
 */

if ( empty( $slider ) ) {
	return;
}

?>

<section class="__hub-section lrv-a-wrapper">
	<div class="lrv-u-border-t-3 lrv-u-padding-t-125 u-margin-b-2@tablet">
	<?php
	\PMC::render_template(
		sprintf( '%s/template-parts/patterns/modules/stories-slider.php', untrailingslashit( CHILD_THEME_PATH ) ),
		$slider,
		true
	);
	?>
	</div>
</section>
