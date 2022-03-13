<script>
	( function() {
		var newAmazonAffiliateId  = false,
			amazonUtmReplacements = <?php echo wp_json_encode( $amazon_utm_replacements ); ?>,
			// Regex: https://regex101.com/r/9vLK4m/1
			utm_campaign          = window.location.href.match( /utm_campaign=[^&|#]+/i );

		if ( utm_campaign && 'object' === typeof utm_campaign ) {
			utm_campaign = utm_campaign[0].toLowerCase();

			// Regex: https://regex101.com/r/vkRe3Z/1
			var variant = utm_campaign.match( /-(\d+)$/ );

			if ( variant && 'object' === typeof variant ) {
				variant = variant[1];
			} else {
				variant = '';
			}

			for ( var i = amazonUtmReplacements.length - 1; i >= 0; i-- ) {
				var utm = amazonUtmReplacements[i].utm;
					id  = amazonUtmReplacements[i].id;

				// The variant feature appends a number to the Tracking ID if it exists in the UTM campaign.
				if ( variant ) {
					// append variant to UTM if it exists, eg, change utm_campaign=spy-fb-unpaid to utm_campaign=spy-fb-unpaid-1 if variant is 1
					utm += '-' + variant;

					// add variant to ID if it exists, eg, change spyfb-20 to spyfb1-20 if variant is 1
					id = id.replace( /(-\d+)$/, variant + '$1' );
				}

				if ( utm_campaign === utm ) {
					newAmazonAffiliateId = id;
					break;
				}
			}

		}

		if ( newAmazonAffiliateId ) {
			var links = document.getElementsByTagName('a');

			for ( var i = links.length - 1; i >= 0; i-- ) {
				var link = links[i];

				if ( ! link.getAttribute( 'data-pmc-sp-product' ) ) {
					continue;
				}
				if ( -1 === link.host.indexOf( 'amazon.com' ) ) {
					continue;
				}

				var href = link.getAttribute( 'href' );

				href = href.replace( /tag=[A-Za-z0-9\-]+/, 'tag=' + newAmazonAffiliateId );
				link.setAttribute( 'href', href );
			}
		}
	}() );
</script>
