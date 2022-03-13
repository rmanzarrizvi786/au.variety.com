<?php
$container = implode( ',', apply_filters( 'pmc_adm_dfp_skin_main_content', [ 'main-wrapper' ] ) );
?>
<!-- Placeholder for Responsive Skin Ad -->
<div id="skin-ad-section" data-content-container="<?php echo esc_attr( $container ); ?>">

	<div id="skin-ad-left-rail-container"></div>

	<div id="skin-ad-right-rail-container"></div>
	<div id="skin-ad-inject-container">
		<?php pmc_adm_render_ads( 'responsive-skin-ad' ) ?>
	</div>
</div>

<!-- End Placeholder for Responsive Skin Ad -->
