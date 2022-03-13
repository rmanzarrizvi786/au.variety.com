<div class="pmc-stocks-graph"></div>
<script>
	// Document Ready.
	jQuery(function() {
		if ( 'undefined' !== typeof window.pmc && 'undefined' !== typeof window.pmc.stocks ) {
			pmc.stocks.build_graph( <?php echo wp_json_encode( $data ); ?>, <?php echo wp_json_encode( $summary ); ?>, <?php echo wp_json_encode( $loader ); ?> );
		}
	});
</script>