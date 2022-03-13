<?php
/**
 * Custom Google Site Search box template
 *
 * @author Amit Gupta <agupta@pmc.com>
 */
?>
<div class="pmc-cgss-container">
	<!-- Google CSE Search Box Begins  -->
	<form action="<?php echo esc_url( $search_results_url ); ?>">
		<input type="hidden" name="engine" value="<?php echo esc_attr( $engine_name ); ?>">
		<input type="text" name="q" size="10" placeholder="<?php echo esc_attr( $search_box_placeholder ); ?>" class="pmc-cgss-box">
		<input type="image" src="https://www.google.com/uds/css/v2/search_box_icon.png" class="gsc-search-button gsc-search-button-v2 pmc-cgss-btn" title="search">
	</form>
	<!-- Google CSE Search Box Ends -->
</div>
