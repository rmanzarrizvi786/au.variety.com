var blogherads = blogherads || {};
blogherads.adq = blogherads.adq || [];
var domainList = [ /sourcingjournal/ ]
var domain = window.location.href;
var isMatch = domainList.some( rx => rx.test( domain ) );

(function ( blogherads ) {
    // this code provided by GetEmails 3rd party. Should be executed on 2nd page view.
    !function(){var e=window.geq=window.geq||[];if(!e.initialize)if(e.invoked)window.console&&console.error&&console.error("GetEmails snippet included twice.");else{e.invoked=!0,e.methods=["page","suppress","trackOrder","identify","addToCart"],e.factory=function(t){return function(){var r=Array.prototype.slice.call(arguments);return r.unshift(t),e.push(r),e}};for(var t=0;t<e.methods.length;t++){var r=e.methods[t];e[r]=e.factory(r)}e.load=function(e){var t=document.createElement("script");t.type="text/javascript",t.async=!0,t.src="https://s3-us-west-2.amazonaws.com/storejs/a/"+e+"/ge.js";var r=document.getElementsByTagName("script")[0];r.parentNode.insertBefore(t,r)},e.SNIPPET_VERSION="1.5.0",e.load(pmc_getemails.id)}}();

    // add cookie to determine if page has been viewed at least once before for better getemails leads
    if ( typeof pmc === 'object' ) {
        var cookie_check = pmc.cookie.get( 'pmc-getemails' );

        if ( cookie_check === null || typeof cookie_check === 'undefined' ) {
            // set cookie for 30 day expiration
            pmc.cookie.set( 'pmc-getemails', 1, 2592000, '/' );
        } else if ( isMatch ){
            geq.page();
        } else {
            blogherads.adq.push(function () {
                geq.identify({user_id: blogherads.getTreasureDataObject().getTrackValues().td_client_id});
                geq.page();
            });
        }
    }
})( blogherads );
