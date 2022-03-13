<div class="pmc-stocks-table"></div>
<?php $flags_path = plugins_url( '../images/flags/',  __FILE__ ); ?>
<script>
	// Document Ready.
	jQuery(function() {
		if ( 'undefined' !== typeof window.pmc && 'undefined' !== typeof window.pmc.stocks ) {
			pmc.stocks.build_table( <?php echo wp_json_encode( $data ); ?>, <?php echo wp_json_encode( $regions ); ?>, <?php echo wp_json_encode( $categories ); ?>, <?php echo wp_json_encode( $flags_path ); ?> );
		}
	});
</script>
