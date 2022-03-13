<?php
/**
 * These values can be overridden for a site by a block of HTML, so the output of filter is not being HTML escaped.
 */
$site_logo = apply_filters( 'pmc-adm_ads-interruptus_interstitial-site-logo', esc_html( get_bloginfo( 'name' ) ) );
$site_name = apply_filters( 'pmc-adm_ads-interruptus_interstitial-site-name', esc_html( get_bloginfo( 'name' ) ) );

?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="pragma" content="no-cache" />
	<?php if ( function_exists( 'jetpack_is_mobile' ) && jetpack_is_mobile() ) { ?>
		<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1">
	<?php } ?>
	<meta name="robots" content="noindex, follow" />
	<link rel="shortcut icon" href="/favicon.ico" />
	<title>Advertisement | <?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
	<?php wp_head(); ?>
	<style>
	body {
		font-size: 1em; font-family:Verdana, Geneva, sans-serif;
	}

	a { color: #000; text-decoration: none; }

	#ad-interruptus-header {
		width:75%; margin: 1% auto; padding:0;
		font-weight: bold;
	}

	#ad-interruptus-header #site-logo, #ad-interruptus-header #continue-link {
		width: 40%; display: inline-block;
	}

	#ad-interruptus-header #site-logo {
		left 0; font-size: 2em;
	}

	#ad-interruptus-header #continue-link {
		float: right; text-align:right; font-size: 1em;
	}

	#timer {
		width:85%; margin: 2% auto; padding:0;
		text-align:center;
	}

	#pmc-adm-ad-interruptus {
		width:90%; margin: 20px auto; padding:0;
		text-align:center; display: block;
	}
	</style>
	<script type="text/javascript" language="javascript" class="script-mobile">
	var counter = ( typeof ad_display_duration !== 'undefined' ) ? ad_display_duration : 8;

	function redirect_timer() {
		if ( counter == 0 ) {
			// to stop the counter
			clearInterval( redirect_interval );
			pmc_ads_interruptus_back_to_site();
			return;
		}

		document.getElementById( "pmc_ads_interruptus_timer" ).innerHTML = counter;

		--counter;
	}

	var redirect_interval = setInterval( "redirect_timer()", 1000 );
	</script>
</head>

<body <?php body_class( 'interstitial' ); ?>>

	<div id="ad-interruptus-header">
		<div id="site-logo"><a href="javascript:pmc_ads_interruptus_back_to_site();"><?php echo wp_kses_post( $site_logo ); ?></a></div>
		<div id="continue-link">
			<a href="javascript:pmc_ads_interruptus_back_to_site();">Continue to <?php echo wp_kses_post( $site_name ); ?></a>
		</div>
	</div>

	<div id="timer">
		You will be redirected back to the article in <span id="pmc_ads_interruptus_timer"></span> seconds
		<script type="text/javascript" language="javascript">
			redirect_timer();
		</script>
	</div>

	<div id="pmc-adm-ad-interruptus"><?php pmc_adm_render_ads( 'Interstitial' ); ?></div>

	<?php wp_footer(); ?>
</body>
</html>
