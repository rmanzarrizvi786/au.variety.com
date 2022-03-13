<!--cXense PMC tracking script -->
<amp-analytics type="cxense">
	<script type="application/json">
	{
		"vars": {
			"siteId":<?php echo wp_json_encode( $site_id ); ?>
		},
		"extraUrlParams":<?php echo wp_json_encode( $custom_parameters ); ?>
	}
	</script>
</amp-analytics>