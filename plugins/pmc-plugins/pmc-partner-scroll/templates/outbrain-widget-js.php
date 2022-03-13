<?php
/**
 * This script needs to be in a php file because it's rendered in footer and only when an outbrain
 * javascript is enqueued.
 *
 * phpcs:ignorefile -- Ignoring the file because it's plain javascript
 */
?>
<script>
(function(){
    function pmcLoadOutbrain() {

        var outbrainContainer = document.querySelector("footer");
        var outbrainWidget    = document.createElement('script');

        outbrainWidget.setAttribute('type', 'text/javascript');
        outbrainWidget.setAttribute('src', 'https://widgets.outbrain.com/outbrain.js');
        outbrainWidget.setAttribute('async','async');

        outbrainContainer.appendChild( outbrainWidget );
    }

    var scrollSubscriber = document.cookie.indexOf("scroll0=") > -1;

    if ( ! scrollSubscriber ) {
        pmcLoadOutbrain();
    }
})();
</script>
