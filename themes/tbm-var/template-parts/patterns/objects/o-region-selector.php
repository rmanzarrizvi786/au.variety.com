<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="<?php echo esc_attr( $region_selector_classes ?? '' ); ?>" data-collapsible="collapsed">
	<a class="js-edition_toggle <?php echo esc_attr( $toggle_classes ?? '' ); ?>" href="#" data-collapsible-toggle="always-show">
		<span class="lrv-a-screen-reader-only">Switch edition between</span>
		<span class="region-us-active">ANZ Edition</span>
		<span class="region-asia-active">U.S. Edition</span>
	</a>
	<ul class="js-edition_panel edition_panel <?php echo esc_attr( $dropdown_classes ?? '' ); ?>" data-collapsible-panel>
		<li class="region-anz-inactive"><a class="<?php echo esc_attr( $dropdown_item_classes ?? '' ); ?>" href="<?php echo esc_url( $us_url ?? '' ); ?>" <?php echo isset($us_url_target) ? 'target="' . $us_url_target . '"' : ''; ?>>U.S. Edition</a></li>
	</ul>
</div>
