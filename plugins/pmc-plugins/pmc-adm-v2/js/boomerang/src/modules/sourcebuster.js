/**
 * Sourcebuster js module to get referrer and pageview
 *
 */
/* global sbjs, blogherads  */

function sourcebuster() {
    this.init = function() {
        if ( 'object' === typeof sbjs ) {
            try {

                // Inform Sourcebuster that we're ready to capture the
                // user's referrer information.
                // @see https://github.com/alexfedoseev/sourcebuster-js
                sbjs.init({

                    // Kill Sourcebuster's cookies when the session stops
                    lifetime: 0,

                    // Update the direct traffic referrer value, from
                    // `(direct)` to `direct` just so it's easier to read.
                    typein_attributes: {
                        source: 'direct',
                        medium: 'none',
                    },

                    // Define referral alias'
                    // There are a number of these baked into SB
                    // @see https://github.com/alexfedoseev/sourcebuster-js#organics
                    referrals: [
                        {
                            host: 'l.facebook.com',
                            medium: 'social',
                            display: 'facebook'
                        },
                        {
                            host: 'www.facebook.com',
                            medium: 'social',
                            display: 'facebook'
                        }
                    ],

                    /**
                     * Tell ADM to use the referrer when sending targeting keywords.
                     *
                     * @param object sb The Sourcebuster object
                     */
                    callback: function () {
                        if ( window.sbjs.get.current.src ) {
                            blogherads.setTargeting( 'referrer', window.sbjs.get.current.src );
                        }
                        if ( window.sbjs.get.session.pgs ) {
                            blogherads.setTargeting( 'pageview', window.sbjs.get.session.pgs );
                        }
                    }
                });
            } catch (e) {
                // do nothing
            }
        }
    };
}

export default sourcebuster;
