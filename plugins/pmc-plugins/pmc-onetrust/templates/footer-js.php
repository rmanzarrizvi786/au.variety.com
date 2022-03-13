<script>
	var ccpa = document.querySelector( <?php echo wp_json_encode( $ccpa_css_selector ); ?> );
	var gdpr = document.querySelector( <?php echo wp_json_encode( $gdpr_css_selector ); ?> );
	var ca_privacy_link = document.querySelector( <?php echo wp_json_encode( $ca_privacy_css_selector ); ?> );

	ccpa = ( ccpa.parentNode.nodeName.toLowerCase() === 'li' ) ? ccpa.parentElement : ccpa;
	gdpr = ( gdpr.parentNode.nodeName.toLowerCase() === 'li' ) ? gdpr.parentElement : gdpr;
	ca_privacy_link = ( ca_privacy_link.parentNode.nodeName.toLowerCase() === 'li' ) ? ca_privacy_link.parentElement : ca_privacy_link;

	if ( ccpa && gdpr && ca_privacy_link ) {
		if ( pmc_fastly_geo_data.region !== 'CA' && pmc_fastly_geo_data.continent !== 'EU' ) {
			ccpa.remove();
			gdpr.remove();
			ca_privacy_link.remove();
		} else if ( pmc_fastly_geo_data.region === 'CA' ) {
			gdpr.remove();

			ccpa.addEventListener("click", function( e ) {
				e.preventDefault();
				OneTrust.ToggleInfoDisplay();
			} );
		} else if (  pmc_fastly_geo_data.continent === 'EU' ) {
			ccpa.remove();
			ca_privacy_link.remove();

			gdpr.addEventListener("click", function( e ) {
				e.preventDefault();
				OneTrust.ToggleInfoDisplay();
			} );
		}
	}
</script>
