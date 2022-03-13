/**
 * Preroll Player.
 *
 */

/* global pmc, jQuery, pmcadm_floating_preroll_data, jwplayer, pmc_admanager, pmcFloatingVideoOptions */

function prerollPlayer() {

    // player elements and utility vars
    this.player_instance = {},
    this.pmc_floating_ad_main_div = '.floating-preroll-ad',
    this.is_floating_ad_showed = false,
    this.time_gap = 0,
    this.cookie = '',
    this.interval_id = '',
    this.direct_slold = false,

    /**
     * Init function.
     */
    this.init = function() {

        var _self = this;

        // Skip floating pre-roll ads if Localized data not available
        if (
            'undefined' === typeof pmcadm_floating_preroll_data ||
            ! pmcadm_floating_preroll_data ||
            'undefined' === typeof pmcadm_floating_preroll_data.time_gap ||
            'undefined' === typeof pmc
        ) {
            return false;
        }

        // Fetch cookie name for floating pre-roll ad, Only proceed if name available
        _self.cookie = pmcadm_floating_preroll_data.cookie_name;

        if ( '' === _self.cookie || ! _self.cookie ) {
            return false;
        }

        // Get the cookie value
        var floating_preroll_cookie = pmc.cookie.get( _self.cookie );

        // If cookie not set then init the floating player OR ELSE remove the container
        if ( floating_preroll_cookie == null || typeof floating_preroll_cookie === 'undefined' || '' === floating_preroll_cookie || 0 === parseInt( pmcadm_floating_preroll_data.time_gap ) ) {

            if ( document.body.classList.contains( 'interrupt-ads' ) ) {

                _self.interval_id = setInterval( function () {

                    if ( 0 === pmc_admanager.settings.redirect_interval ) {
                        _self.show_floating_preroll_ad();
                    }
                }, 1000 );
            } else {

                _self.show_floating_preroll_ad();
            }
        } else {

            _self.remove_floating_player();
            return false;
        }

        pmc.hooks.add_action( 'pmc_adm_dfp_direct_sold', function () {
            _self.direct_slold = true;
            _self.remove_floating_player();
        } );
    },

    /**
     * Loads preroll ad and bind it's events.
     */
    this.show_floating_preroll_ad = function() {

        var jwplayers_divs = jQuery( '[id ^=jwplayer_][id $=_div]' ),
            _self = this,
            playlist_url = false,
            media_id = pmcadm_floating_preroll_data.media_id,
            playlist_id = pmcadm_floating_preroll_data.playlist_id,
            time_gap = pmcadm_floating_preroll_data.time_gap,
            related_videos = jQuery('.l-pvm-video [id ^=jwplayer_][id $=_div]'),
            player_width = 400,
            player_height = 225;

        if ( playlist_id ) {
            playlist_url = 'https://cdn.jwplayer.com/v2/playlists/' + playlist_id;
        }
        else if ( media_id ) {
            playlist_url = 'https://cdn.jwplayer.com/v2/media/' + media_id;
        }
        // Serve this ads only for desktop.
        // Proceed if Media ID is set.
        if ( ! playlist_url ) {
            return;
        }

        // Use default time (1 day) if no value passed for it.
        if ( '' !== time_gap ) {
            _self.timegap = time_gap;
        }

        clearInterval( _self.interval_id );

        if ( 1200 > jQuery( window ).width() ) {
            player_height = 190;
            player_width = 300;
            jQuery( '.floating-preroll-ad-container' ).css( 'width', '314px' );
            jQuery( '.floating-preroll-ad-container' ).css( 'height', '204px' );
        }

        if ( 0 === ( jwplayers_divs.length - related_videos.length ) && 'function' === typeof jwplayer ) {

            var jwConfig = {
                playlist: playlist_url,
                autostart: true,
                mute: true,
                floating: true,
                'height': player_height,
                'width': player_width
            };

            // player setup

            if ( 'function' === typeof window.pmc_jwplayer ) {
                _self.player_instance = window.pmc_jwplayer( 'jwplayer_floating_preroll_ad' ).setup( jwConfig ).instance();
            } else {
                _self.player_instance = window.jwplayer( 'jwplayer_floating_preroll_ad' ).setup( jwConfig );
            }

            _self.player_instance.on( 'beforePlay', function () {
                if (
                    'object' === typeof pmcFloatingVideoOptions &&
                    1 === parseInt( pmcFloatingVideoOptions.jwplayer_style_v2 )
                ) {
                    let videoTitle = _self.player_instance.getPlaylistItem().title;
                    if ( 'string' === typeof videoTitle ) {
                        jQuery( '.floating-preroll-ad-title' ).text( videoTitle );
                    }
                }
            } );

            _self.player_instance.on( 'firstFrame', function () {
                _self.show_floating_player();
            } );

            _self.player_instance.on( 'adImpression', function () {

                pmc.cookie.set( _self.cookie, 1, _self.time_gap, '/' );
                _self.is_floating_ad_showed = true;
                _self.show_floating_player();

            } );

            _self.player_instance.on( 'adError', function () {
                if ( true === _self.is_floating_ad_showed ) {
                    return;
                }

                if (
                    'object' === typeof pmcFloatingVideoOptions &&
                    1 === parseInt( pmcFloatingVideoOptions.preroll_not_required )
                ) {
                    _self.show_floating_player();
                } else {
                    _self.remove_floating_player();
                }
            } );

            jQuery( document ).on( 'click', '.floating-preroll-ad-close', function() {
                _self.remove_floating_player();
            } );
        } else {
            jQuery( this.pmc_floating_ad_main_div ).remove();
        }

    },

    /**
     * To show Floating player pre-roll ads DOM. (show close button after 5 seconds)
     */
    this.show_floating_player = function() {
        setTimeout( function () {
            jQuery( '.floating-preroll-ad-close' ).show();
        }, 5000 );
        jQuery( this.pmc_floating_ad_main_div ).show();
    },

    /**
     * To Remove the floating player DOM and jwplayer.
     */
    this.remove_floating_player = function() {
        if ( 'function' === typeof this.player_instance.remove ) {
            this.player_instance.remove();
        }
        jQuery( this.pmc_floating_ad_main_div ).remove();
    }

}

export default prerollPlayer;
