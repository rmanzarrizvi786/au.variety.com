<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="<?php echo esc_attr( $region_selector_classes ?? '' ); ?>" data-collapsible="collapsed">
	<a class="js-edition_toggle <?php echo esc_attr( $toggle_classes ?? '' ); ?>" href="#" data-collapsible-toggle="always-show">
		<span class="lrv-a-screen-reader-only">Switch edition between</span>
		<span class="region-us-active">U.S. Edition</span>
		<span class="region-asia-active">Asia Edition</span>
		<span class="region-global-active">Global Edition</span>
	</a>
	<ul class="js-edition_panel edition_panel <?php echo esc_attr( $dropdown_classes ?? '' ); ?>" data-collapsible-panel>
		<li class="region-us-inactive"><a class="<?php echo esc_attr( $dropdown_item_classes ?? '' ); ?>" href="<?php echo esc_url( $us_url ?? '' ); ?>">U.S.</a></li>
		<li class="region-asia-inactive"><a class="<?php echo esc_attr( $dropdown_item_classes ?? '' ); ?>" href="<?php echo esc_url( $asia_url ?? '' ); ?>">Asia</a></li>
		<li class="region-global-inactive"><a class="<?php echo esc_attr( $dropdown_item_classes ?? '' ); ?>" href="<?php echo esc_url( $global_url ?? '' ); ?>">Global</a></li>
	</ul>
</div>
