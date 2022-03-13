<?php
/**
 * ULS Header script to be on page
 *
 * @package pmc-subscription
 *
 */

// @codeCoverageIgnoreStart
if ( pmc_paywall_enabled() ) :
	$fast_js_url = pmc_paywall_fast_js_url();
	$roadblock   = 'null';
	if ( is_single() ) {
		$roadblock = pmc_paywall_roadblock() ? 1 : 0;
	}
	$wwd_pmc_meta = PMC_Page_Meta::get_page_meta();
	$args         = array(
		'content_id'           => isset( $post->ID ) ? intval( $post->ID ) : 0,
		'content_type'         => sanitize_text_field( $wwd_pmc_meta['page-type'] ),
		'request_hostname'     => rawurlencode( home_url() ),
		'request_path'         => rawurlencode( $wp->request ),
		'required_entitlement' => sanitize_text_field( $wwd_pmc_meta['page_access_level'] ),
		'current_entitlement'  => sanitize_text_field( $wwd_pmc_meta['subscriber-type'] ),
		'roadblock_hit'        => $roadblock,
	);

	$fast_js_url = add_query_arg( $args, $fast_js_url );

	$js_cookie = 'detect_cookie=js; path=/; domain=.' . apply_filters( 'wwd_cookie_domain', 'wwd.com' );
	?>
	<script type="text/javascript">
		<?php
		$wwd_urlparts = wp_parse_url( WWD_URL );
		?>
		<?php
		/**
		 * We choose to use 'esc_js' instead of 'wp_json_encode',
		 * so that we get URLs without backslashes.
		 * (e.g. https://wwd.com instead of https:\/\/wwd.com)
		 *
		 * This way exproxy can replace URLs properly.
		 */
		?>
		window.uls_script_options = {
			'cookie_domain': '<?php echo esc_js( $wwd_urlparts['host'] ); ?>',
			'domain': '<?php echo esc_js( $wwd_urlparts['host'] ); ?>',
			'site_url': '<?php echo esc_js( site_url() ); ?>',
			'uls_url': '<?php echo esc_js( pmc_paywall_host() ); ?>',
		};
	</script>
	<script type="text/javascript">
		(function(){
			try {
				document.cookie = <?php echo wp_json_encode( $js_cookie ); ?>;
			}catch(ignore) {}

			var new_script = document.createElement('script');
			new_script.type = 'text/javascript';
			new_script.async = false;
			new_script.id = '__fast__js__';
			new_script.src = <?php echo wp_json_encode( $fast_js_url, JSON_UNESCAPED_SLASHES ); ?>;
			var script = document.getElementsByTagName('script')[0];
			script.parentNode.insertBefore( new_script, script );
		})();
	</script>

	<?php

	endif;

	// @codeCoverageIgnoreEnd
