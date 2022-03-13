<?php if ( 'top' === $position ) : ?>
	<noscript>
		<img src="https://sb.scorecardresearch.com/p?c1=2&c2=<?php echo esc_attr( $option['values']['id'] ); ?>&c3=&c4=&c5=&c6=&c15=&cv=2.0&cj=1" />
	</noscript>
<?php endif; ?>

<?php if ( 'bottom' === $position && isset( $option['values']['id'] ) ) : 
	$blocker_atts = [
		'type'  => 'text/javascript',
		'class' => '',
	];
	
	if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
		$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0002' );
	}
	
	?>
	<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">
		var comscore_vals = { c1: "2", c2: "<?php echo esc_js( $option['values']['id'] ); ?>", c3: "", c4: "", c5: "", c6: "", c15: "" };
		var _comscore = _comscore || []; _comscore.push(comscore_vals); (function() { var s = document.createElement("script"), el = document.getElementsByTagName("script")[0]; s.async = true; s.src = (document.location.protocol == "https:" ? "https://sb" : "http://b") + ".scorecardresearch.com/beacon.js"; el.parentNode.insertBefore(s, el); })();

		// Added to ensure that Comscore tags are triggered to fire on each slide of galleries
		(function(history){
			var pushState = history.pushState;
			history.pushState = function(state) {
			if (typeof history.onpushstate == "function") {
				history.onpushstate({state: state});
			}

			return pushState.apply(history, arguments);
			}
		})(window.history);

		// Trigger comscore beacon on change location.href
		window.onpopstate = history.onpushstate = function() {
			if ( COMSCORE !== undefined ) {
				setTimeout( function() {
					COMSCORE.beacon(comscore_vals);
				}, 1);
			}
		};
	</script>
<?php endif; ?>
