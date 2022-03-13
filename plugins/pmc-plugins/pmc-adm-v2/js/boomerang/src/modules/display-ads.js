/**
 * Boomerang ad functions.
 *
 */
import { has_adunit_path, get_interrupt_ad_id } from './utils.js';

/* global pmc, blogherads, pmc_meta, pmc_admanager, jQuery */
function pmcDisplayAds() {
    /**
     * Init function.
     */
    this.init = function() {
        this.bind_events();
    };

    /**
     * To bind all events.
     */
    this.bind_events = function() {
        if (
            'undefined' !== typeof pmc &&
            'undefined' !== typeof pmc.hooks &&
            'undefined' !== typeof pmc.hooks.add_action
        ) {
            pmc.hooks.add_action( 'pmc_gallery_rotate_ads', this.rotate_ads_for_gallery );
            pmc.hooks.add_action( 'pmc_rotate_ads', this.rotate_ads.bind( this ) );
        }

        if ( 'loading' !== document.readyState ) {
            this.display();
        } else {
            let self = this;
            document.addEventListener('DOMContentLoaded', () => {
                self.display();
            });
        }
        window.addEventListener( 'message', this.direct_sold_ad_event_listener.bind( this ), false);

    };

    this.rotate_ads = function( ad_type ) {
        if ( ! ad_type || 'undefined' === typeof blogherads || 'function' !== typeof blogherads.reloadAds || this.is_direct_sold ) {
            return;
        }

        const slots = [];

        jQuery( 'div.slot-rotate-' + ad_type ).each( function() {
            let slotId = '';

            if ( 1 === parseInt( jQuery( this ).data( 'is-adhesion-ad' ) ) ) {
                slotId = 'skm-ad-bottom';
            }
            else {
                slotId = jQuery( this ).attr( 'id' );
            }

            const slot =  blogherads.getSlotById( slotId );

            if ( slot ) {
                slots.push( slot );
            }
        } );

        if ( slots && slots.length ) {
            blogherads.reloadAds( slots );
        }

    };

    /**
     * Callback function when gallery slide change
     * To refresh ads on page.
     */
    this.rotate_ads_for_gallery = function() {
        if ( 'undefined' === typeof blogherads || 'function' !== typeof blogherads.reloadAds ) {
            return;
        }

        if ( 'object' === typeof pmc_meta && 'string' === typeof pmc_meta.env && 'mobile' === pmc_meta.env ) {
            blogherads.reloadAds( blogherads.getSlots() );
        } else {
            blogherads.reloadAds();
        }
    };

    this.display = function() {
        //check if prestitial/interstitial needs to render first

        if ( '' !== get_interrupt_ad_id() ) {
            pmc_admanager.show_interrupt_ads();
        } else {
            const ad_slots = blogherads.getSlots();
            ad_slots.forEach( function ( slot ) {
                slot.removeBlock( 'gallery' );
                slot.display();
            } );
        }
    };

    /**
     * Check if the ad type is in the list of ads
     * @param ad_type {string}
     *
     * @return {boolean}
     */
    this.has_ads = function( ad_type ) {
        const ad_slots = blogherads.getSlots();
        let has_type = false;

        ad_slots.forEach( function ( slot ) {
            if ( has_adunit_path( slot, ad_type ) ) {
                has_type = true;
            }
        } );
        return has_type;
    };

    /**
     * Bind on window message event to determine direct sold ad flag
     * @param event {object}
     *
     */
    this.direct_sold_ad_event_listener = function( event ) {

        if ( 'undefined' !== typeof pmc && 'undefined' !== typeof pmc.hooks ) {

            if ( 'string' === typeof event.data ) {
                var message_pattern = 'pmcadm:dfp:isdirect=true';

                if ( event.data.substring( 0, message_pattern.length ) === message_pattern ) {
                    this.is_direct_sold = true;
                    blogherads.getSlots().forEach( slot => slot.setAutoRefreshTime( 0 ) );
                    pmc.hooks.do_action( 'pmc_adm_dfp_direct_sold', event );
                }
            }
        }
    };

}

export default pmcDisplayAds;
