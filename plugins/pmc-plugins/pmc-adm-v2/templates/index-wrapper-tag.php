<?php
// Template for IX wrapper script
?>
	<script type="text/javascript">
		// Adding IndexExchange wrapper tag to the page
		function pmc_add_index_wrapper_script() {
			var ref = document.getElementsByTagName('script')[0];
			var script = document.createElement('script');
			script.src = '<?php echo esc_url( $url ); ?>';
			ref.parentNode.insertBefore(script, ref);
		}

		if( 'object' === typeof pmc_meta &&
			'undefined' !== typeof pmc_meta.is_eu &&
			true === pmc_meta.is_eu
		) {
			pmc.hooks.add_action( 'pmc_adm_consent_data_ready', function( consent_data ) {
				if ( 'object' === typeof consent_data && 'undefined' !== typeof consent_data.returnValue ) {
					pmc_add_index_wrapper_script();
				}
			});
		} else {
			pmc_add_index_wrapper_script()
		}
	</script>
<?php
