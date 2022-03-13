var blogherads = blogherads || {};
blogherads.adq = blogherads.adq || [];

(function( blogherads ){

    (function(a,c,d,e){if(!a[c]){var b=a[c]={};b[d]=[];b[e]=function(a){b[d].push(a)}}})(window,'Scroll','_q','do');

    Scroll.config = {
        detected: document.cookie.indexOf("scroll0=") > -1
    };

    var scrollSubscriber = ( document.cookie.indexOf("scroll0=") > -1 );

    if ( scrollSubscriber ) {
        //Disable display and video ads
        blogherads.adq.push(function () {
            blogherads.disableAds();
        });

        /**
         * Disable SPOT.IM ads for scroll users.
         * @see https://github.com/SpotIM/spotim-integration-docs/blob/master/api/js-events/README.md
         */
        window.__SPOTIM_ADS_DISABLED__ = true;

        // Add css class for styling scroll user layouts.
        document.getElementsByTagName('html')[0].className += ' scrolluser';

        // Register virtual pageview whenever the URL changes.
        var pushState = history.pushState;

        history.pushState = function( state ) {
            if (
                'object' === typeof window.Scroll
                && 'function' === typeof window.Scroll.do
                && 'function' === typeof window.Scroll.virtualPage
            ) {

                Scroll.do(function() {
                    Scroll.virtualPage();
                });
            }

            return pushState.apply(history, arguments);
        };
    }
})( blogherads );
