<?php
// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript

/**
 *
 * These scripts cannot be loaded asynchronously due to other OneTrust script dependencies
 */
?>
<script type="text/javascript" src="https://iabusprivacy.pmc.com/geo-info.js"></script>
<script defer='defer' src="https://cdn.cookielaw.org/scripttemplates/otSDKStub.js"  type="text/javascript" charset="UTF-8" data-ignore-ga="true" data-domain-script="<?php echo esc_attr( $site_id ); ?>"></script>
<script defer='defer' src="https://cdn.cookielaw.org/opt-out/otCCPAiab.js" type="text/javascript" charset="UTF-8" ccpa-opt-out-ids="C0002,C0004,SPD_BG" ccpa-opt-out-geo="ca" ccpa-opt-out-lspa="true"></script>
<?php if ( class_exists( '\PMC\Geo_Uniques\Plugin' ) ) { ?>
	<?php $region = \PMC\Geo_Uniques\Plugin::get_instance()->pmc_geo_get_region_code(); ?>
	<?php  if ( 'eu' === $region ) { ?>
		<script src="https://cdn.cookielaw.org/consent/tcf.stub.js" type="text/javascript" charset="UTF-8"></script>
	<?php } ?>
<?php } ?>

<script type="text/javascript">
	// Override the OneTrust geo location data with our own
	if ( 'object' === typeof pmc_fastly_geo_data && 'string' === typeof pmc_fastly_geo_data.region && 'string' === typeof pmc_fastly_geo_data.country) {
		var OneTrust = {
			geolocationResponse: {
				stateCode: pmc_fastly_geo_data.region,
				countryCode: pmc_fastly_geo_data.country,
			}
		}
	}

	function OptanonWrapper() {
		//Temp fix to solve cache issue
		if (
			'' === location.search &&
			'function' === typeof window.loadGA &&
			pmc_fastly_geo_data &&
			pmc_fastly_geo_data.continent &&
			'EU' !== pmc_fastly_geo_data.continent
		) {
			window.loadGA( true );
			var consent_data = {
				'returnValue': '',
				'success': true
			};
			pmc.hooks.do_action( 'pmc_adm_consent_data_ready', consent_data );
		}

		if ( 'object' === typeof pmc_onetrust ) {
			pmc_onetrust.init();
		}
	}
</script>
