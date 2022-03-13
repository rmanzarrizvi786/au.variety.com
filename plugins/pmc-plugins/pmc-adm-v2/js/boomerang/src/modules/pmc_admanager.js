import { get_interrupt_ad_id } from './utils.js';
/* global blogherads, pmc */
function pmcAdManager() {
    this.settings = {
        interrupts_hide_container: '#container',
        redirect_interval: 0,
        interrupt_counter: window.pmc_intertitial_ad_timer || 0
    };

    this.show_interrupt_ads = function() {
        // If user agent is googlebot then don't display ad.
        if ( typeof navigator.userAgent !== 'undefined' )  {
            var bot_pattern = /googlebot|googlebot-news/i;
            if ( bot_pattern.test( navigator.userAgent ) )  {
                return;
            }
        }

        //Do not want to show interrupt ads for pages with referrer `flipboard.com`.
        let referrer_url = document.referrer,
            referrer = '',
            cookie_check = null,
            self = this;

        if ( 'undefined' !== typeof referrer_url && '' !== referrer_url ) {
            referrer = referrer_url.match(/:\/\/(.[^/]+)/)[1];
            if( 'flipboard.com' === referrer ) {
                return;
            }
        }

        /**
         * User has an ad blocker, so let's not bother with any of this.
         */
        if ( window.pmc_is_adblocked ) {
            return;
        }

        if ( this.settings.redirect_interval ) {
            clearInterval( this.settings.redirect_interval );
        }

        const interrupt_ad = blogherads.getSlotById( get_interrupt_ad_id() );

        cookie_check = pmc.cookie.get( pmc.pmc_adm_interstitial_ck );

        if (
            ( cookie_check === null || typeof cookie_check === 'undefined' || cookie_check === '' ) &&
            'object' === typeof interrupt_ad
        ) {
            pmc.cookie.set(pmc.pmc_adm_interstitial_ck, 1, pmc.pmc_adm_interstitial_interval, '/');
            self.settings.redirect_interval = setInterval( function() {self.interrupt_timer() }, 1000 );
            document.body.classList.add('interrupt-ads');
            document.getElementById('pmc-adm-interrupts-container').style.display = 'block';

            try {
                window.postMessage( 'pmc_show_interrupt_ads', '*' );
                if ( pmc.hooks ) {
                    pmc.hooks.do_action('show_interrupt_ads');
                }
            }
            catch ( e ) {
                // do nothing
            }

            interrupt_ad.display();
            self.interrupt_timer();
        } else {
            this.hide_interrupt_ads();
        }

    }

    this.hide_interrupt_ads = function() {
        clearInterval( this.settings.redirect_interval );
        this.settings.redirect_interval = 0;
        document.body.classList.remove( 'interrupt-ads' );
        window.dispatchEvent( new Event('resize') );
        document.getElementById('pmc-adm-interrupts-container').style.display = 'none';

        const interrupt_ad_id =  get_interrupt_ad_id();
        let event = new CustomEvent( 'pmc-hide-interrupt-ads', { 'detail': 'Fires when the interrupt ads are done.'});
        document.dispatchEvent( event );

        blogherads.getSlots().forEach( function ( slot ) {
            if ( interrupt_ad_id !== slot.domId ) {
                slot.display();
            } else {
                blogherads.destroySlots( [ slot.domId ] ); //remove interstitial slot from page.
            }
        } );

        try {
            window.postMessage( 'pmc_hide_interrupt_ads', '*' );
            if ( pmc.hooks ) {
                pmc.hooks.do_action('hide_interrupt_ads');
            }
        }
        catch ( e ) {
            // do nothing
        }

    }

    this.hide_interrupt = function() {
        this.hide_interrupt_ads();
    }

    this.interrupt_timer  = function(){
        if( this.settings.interrupt_counter === 0 ) {
            this.hide_interrupt_ads();
        }else{
            if ( this.settings.redirect_interval ) {
                this.settings.interrupt_counter --;
            }
            if ( document.getElementById( 'pmc_ads_interrupts_timer' ) ) {
                document.getElementById( 'pmc_ads_interrupts_timer' ).innerHTML = this.settings.interrupt_counter;
            }
        }
    }

}

export default pmcAdManager;
