<?php
/**
 * Renders Taboola shortcode.
 *
 * @var string $canonical_url Cacnonical URL of article to be sent to Taboola.
 *
 * @package pmc
 */

if ( empty( $canonical_url ) ) {
	return;
}

?>

<div id="taboola-below-article-thumbnails"></div>

<script type="text/javascript">
	if ( 'undefined' !== window.taboolaDivCount && -1 === document.cookie.indexOf('scroll0=') ) {
		document.getElementById("taboola-below-article-thumbnails").setAttribute("id", "taboola-below-article-thumbnails-" + window.taboolaDivCount);

		if (0 === window.taboolaDivCount) {
			window._taboola = window._taboola || [];

			_taboola.push({
				mode: 'thumbnails-a',
				container: "taboola-below-article-thumbnails-" + window.taboolaDivCount,
				placement: 'Below Article Thumbnails',
				target_type: 'mix'
		 	});

			_taboola.push({
				article: 'auto',
				url: '<?php echo esc_js( $canonical_url ); ?>'
			});

			window.taboolaDivsLoaded[window.taboolaDivCount] = true;
		} else {
			window.taboolaDivsLoaded[window.taboolaDivCount] = false;
		}

		window.taboolaDivCount++;
	}
</script>
