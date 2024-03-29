/*
  To Generate a Minified Version:
  npm install uglify-js -g
  cd .../themes/vip/pmc-plugins/pmc-breaking-news/js/
  uglifyjs breaking-news.js > breaking-news.min.js
 */

( function( $ ) {

	$( document ).ready( function () {

		$( '.pmc-breaking-news .dismiss-news-banner, .pmc-breaking-news i' ).click( function () {

			$( '.pmc-breaking-news').hide();
			$( '#breaking-news-label' ).hide();
			$( '.pmc-breaking-news-white-spacer' ).hide();

			if ( 'undefined' !== typeof( pmc_breaking_news_hash.hash ) && ! pmc.is_empty( pmc_breaking_news_hash.hash ) ) {
				pmc.cookie.set( 'pmc-brk-nws', pmc_breaking_news_hash.hash, 86400 );
			}

		} );

		if ( 'undefined' !== typeof( pmc_breaking_news_hash.hash ) && ! pmc.is_empty( pmc_breaking_news_hash.hash ) ) {

			var $brk_news = pmc.cookie.get( 'pmc-brk-nws' );

			if ( ! pmc.is_empty( $brk_news ) && pmc_breaking_news_hash.hash == $brk_news) {

				$( '.pmc-breaking-news' ).hide();
				$( '#breaking-news-label' ).hide();
				$( '.pmc-breaking-news-white-spacer' ).hide();

			} else {

				$( '#breaking-news-label' ).show();
				$( '.pmc-breaking-news' ).show();
				$( '.pmc-breaking-news-white-spacer' ).show();

			}

		} else {

			$( '#breaking-news-label' ).show();
			$( '.pmc-breaking-news' ).show();
			$( '.pmc-breaking-news-white-spacer' ).show();

		}

	});

})( jQuery );
