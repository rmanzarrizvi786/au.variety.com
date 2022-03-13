/**
 * - To create minified version of onetrust.min.js:
 * - npm install uglify -g (to install uglify)
 * - uglify -s onetrust.js -o onetrust.min.js
 *
 * PMC Onetrust listener
 * @type object
 */
/* global __tcfapi, loadGA, pmc_fastly_geo_data, ga, OnetrustActiveGroups */

var pmc_onetrust = {
    initialized: false,

    /**
     * To Initialize PMC Onetrust listener
     */
    init: function () {
        if (
            'object' === typeof pmc_fastly_geo_data &&
            'string' === typeof pmc_fastly_geo_data.continent &&
            'EU' !== pmc_fastly_geo_data.continent
        ) {
            this.substitutePlainTextScriptTags();
        }

        if ( 'function' === typeof __tcfapi ) {
            __tcfapi( 'getTCData', 2, function ( data, success ) {
                if (
                    'object' === typeof data &&
                    ( 'useractioncomplete' === data.eventStatus || 'tcloaded' === data.eventStatus ) &&
                    'object' === typeof data.purpose.consents &&
                    pmc_fastly_geo_data &&
                    pmc_fastly_geo_data.continent &&
                    'EU' === pmc_fastly_geo_data.continent
                ) {
                    if ( false === pmc_onetrust.initialized ) {
                        pmc_onetrust.initialized = true;
                        if ( data.purpose.consents[7] && data.purpose.consents[8] && data.purpose.consents[9] ) {
                            window.loadGA && window.loadGA(data.purpose.consents[1]); // GA Call.
                        } else {
                            pmc_onetrust.clear_ga_on_page();
                        }
                        var consent_data = {
                            'returnValue': data,
                            'success': success
                        };
                        pmc.hooks.do_action( 'pmc_adm_consent_data_ready', consent_data ); // Ad call.
                    }
                }
            });
        }
        if ( 'function' === typeof __cmp ) {
            this.handle_tcfv1_api();
        }
    },

    clear_ga_on_page: function () {
        // try to stop collecting
        window.ga( function () {
            var trackers = window.ga.getAll();
            trackers.forEach( function ( tracker ) {
                var uid = tracker.b.data.values[':trackingId'];
                if ( uid ) {
                    window[ 'ga-disable-' + uid ] = true;
                }
            });
        });
    },

    handle_tcfv1_api: function() {
        __cmp( 'getVendorConsents', 1, function( result, success ) {
            if (
                'object' === typeof result &&
                'object' === typeof result.purposeConsents &&
                'undefined' !== typeof result.purposeConsents[5]
            ) {
                if ( false === pmc_onetrust.initialized ) {
                    pmc_onetrust.initialized = true;
                    if (true === result.purposeConsents[5]) {
                        // Now make GA call
                        window.loadGA && window.loadGA(result.purposeConsents[1]);
                    } else {
                        pmc_onetrust.clear_ga_on_page();
                    }
                    var consent_data = {
                        'returnValue': result,
                        'success': success
                    };
                    pmc.hooks.do_action('pmc_adm_consent_data_ready', consent_data); // Ad call.
                }
            }

        });
    },

    /**
     * Reload scripts with type attribute text/plain
     */
    substitutePlainTextScriptTags: function() {
        var self = this,
            tags = [].slice.call( document.querySelectorAll( 'script[class*="optanon-category"]' ) );

        tags.forEach( function( tag ) {
            if ( tag.hasAttribute( 'type' ) && 'text/plain' === tag.getAttribute( 'type' ) ) {
                self.reactivateTag( tag );
            }
        });
    },

    /**
     * Check if the tag category is eligible to reactivate
     */
    reactivateTag: function( tag ) {
        var self = this,
            classes = tag.className
                .match( /optanon-category(-[a-zA-Z0-9]+)+($|\s)/ )[0]
                .split( /optanon-category-/i )[1]
                .split( '-' ),
            activate = true;

        if ( classes && 0 < classes.length ) {
            for ( var i = 0; i < classes.length; i++ ) {
                if ( ! self.canInsertForGroup( classes[i].trim() ) ) {
                    activate = false;
                    break;
                }
            }
            if ( activate ) {
                self.reactivateScriptTag( tag );
            }
        }
    },

    /**
     * Reactivate script tag
     */
    reactivateScriptTag: function( tag ) {
        var parent = tag.parentNode,
            newtag = document.createElement( tag.tagName ),
            attrs = tag.attributes;

        newtag.innerHTML = tag.innerHTML;

        if ( 0 < attrs.length ) {

            for ( var i = 0; i < attrs.length; i++ ) {

                if ( 'type' !== attrs[ i ].name ) {
                    newtag.setAttribute( attrs[ i ].name, attrs[ i ].value );
                } else {
                    newtag.setAttribute( 'type', 'text/javascript' );
                }
            }
        }

        parent.appendChild( newtag );
        parent.removeChild( tag );
    },

    /**
     * Check if user given consent to load tags.
     */
    canInsertForGroup: function( group ) {
        if ( pmc_fastly_geo_data && pmc_fastly_geo_data.continent && 'EU' === pmc_fastly_geo_data.continent ) {
            return ( 'string' === typeof OnetrustActiveGroups ) ? OnetrustActiveGroups.includes( group ) : false;
        } else {
            return true; //Load all tags for non EU region
        }
    }

};
