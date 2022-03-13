<?php
/**
 * Boomerang Header tags to be on page
 *
 * @since   05-08-2018 Vinod Tella READS-1221
 *
 * @package pmc-adm
 */

?>

<script type="application/javascript">
	var googletag = googletag || {};
	googletag.cmd = googletag.cmd || [];
	googletag.cmd.push(function () {
		googletag.pubads().collapseEmptyDivs();
	});
	var blogherads = blogherads || {};
	blogherads.adq = blogherads.adq || [];
	blogherads.adq.push(function() {
		var config = {
			c3: '3940384',
			options: {}
		};
	});

	(function ( blogherads ) {
		blogherads.adq.push( function () {
			<?php if ( ! empty( $vertical ) ) { ?>
				blogherads.setConf( 'vertical', '<?php echo esc_js( $vertical ); ?>' );
			<?php } ?>

			<?php if ( ! empty( $is_sponsored ) ) : ?>
				blogherads.setSponsored();
			<?php endif; ?>

			<?php

			// Lazyload for Desktop.
			if ( ! empty( $lazyload_multiplier ) && is_numeric( $lazyload_multiplier ) ) {
				printf( "blogherads.setConf('lazyload_multiplier', %d);\n", absint( $lazyload_multiplier ) );
			}

			// Lazyload for Mobile.
			if ( ! empty( $lazyload_multiplier_mobile ) && is_numeric( $lazyload_multiplier_mobile ) ) {
				printf( "blogherads.setConf('lazyload_multi_mob', %d);\n", absint( $lazyload_multiplier_mobile ) );
			}

			?>

			<?php
			if ( ! empty( $targeting_data ) && is_array( $targeting_data ) ) {
				foreach ( $targeting_data as $key => $value ) {

					if ( is_array( $value ) ) {
						printf( "blogherads.setTargeting( '%s', %s );\n", esc_js( $key ), wp_json_encode( $value ) );
					} else {
						printf( "blogherads.setTargeting( '%s', '%s' );\n", esc_js( $key ), esc_js( $value ) );
					}
				}
			}
			?>

		} );
	})( blogherads );
</script>
