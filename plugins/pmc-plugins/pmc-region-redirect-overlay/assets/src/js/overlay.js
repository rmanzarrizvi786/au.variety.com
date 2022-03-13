/**
 * JS to show overlay on frontend
 *
 * @author Amit Gupta
 */

/* global pmc, pmc_fastly_geo_data, pmc_region_redirect_overlay */

class PMCRedirectOverlay {

	/**
	 * Class constructor
	 */
	constructor() {

		this.$                      = jQuery;
		this.long_term_cookie_name  = 'pmc_reg_rd_overlay_banner';
		this.short_term_cookie_name = 'pmc_reg_rd_overlay_banner_sesn';

		this.setup_hooks();
		this.maybe_display_banner();

	}

	/**
	 * Method to set up listeners to different hooks
	 *
	 * @return {void}
	 */
	setup_hooks = () => {

		// hide banner when close button is clicked
		this.$( '.pmc-reg-rd-overlay-banner .btn-close' ).on( 'click', this.hide_banner );

		// expire current session when any link is clicked in banner
		this.$( '.pmc-reg-rd-overlay-banner' ).on( 'click', 'a', this.expire_session );

	}

	/**
	 * Method to hide banner whose close button is clicked
	 *
	 * @param {object} e
	 *
	 * @return {void}
	 */
	hide_banner = ( e ) => {
		e.preventDefault();
		this.$( e.target ).parent().hide();
	}

	/**
	 * Method to expire current user session
	 *
	 * @return {void}
	 */
	expire_session = () => {
		pmc.cookie.expire( this.short_term_cookie_name );
	}

	/**
	 * Method to determine if banner should be displayed or not
	 *
	 * @return {boolean}
	 */
	should_display_banner = () => {

		// If long term cookie is not set then we want to show banner
		let long_term_cookie_value = pmc.cookie.get( this.long_term_cookie_name );
		let display_banner         = ( pmc.is_empty( long_term_cookie_value ) );

		// If short term cookie is set then display banner
		// even if long term cookie is set
		let short_term_cookie_value = pmc.cookie.get( this.short_term_cookie_name );
		display_banner              = ( ! pmc.is_empty( short_term_cookie_value ) ) ? true : display_banner;

		return display_banner;

	}

	/**
	 * Method to set cookie for banner
	 *
	 * @return {void}
	 */
	set_cookies = () => {

		let cookie_value = pmc.cookie.get( this.long_term_cookie_name );

		// Set cookies only if long term cookie is not set
		if ( ! pmc.is_empty( cookie_value ) ) {
			return;
		}

		let cookie_life = 30;  // default to 30 days

		if (
			'undefined' !== typeof pmc_region_redirect_overlay
			&& 'undefined' !== typeof pmc_region_redirect_overlay.dnd_duration
			&& 0 < parseInt( pmc_region_redirect_overlay.dnd_duration )
		) {
			cookie_life = parseInt( pmc_region_redirect_overlay.dnd_duration );
		}

		cookie_life = ( cookie_life * 86400 );		// duration for which cookie will live (in seconds)

		pmc.cookie.set( this.long_term_cookie_name, 'hide', cookie_life, '/' );
		pmc.cookie.set( this.short_term_cookie_name, 'show' );

	}

	/**
	 * Method to get the country of current user
	 *
	 * @return {string}
	 */
	get_current_country = () => {
		let country = ( 'undefined' !== typeof pmc_fastly_geo_data && 'undefined' !== typeof pmc_fastly_geo_data.country ) ? pmc_fastly_geo_data.country : '';
		return country.toLowerCase();
	};

	/**
	 * Method to check if we have local site set for a country or not
	 *
	 * @param {string} country
	 * @return {boolean}
	 */
	has_local_site = ( country = '' ) => {

		if ( 'undefined' === typeof country || pmc.is_empty( country ) ) {
			return false;
		}

		return pmc_region_redirect_overlay.countries.includes( country );

	};

	/**
	 * Method to display overlay banner if we have local website for current user's country
	 *
	 * @return {void}
	 */
	maybe_display_banner = () => {

		const current_country = this.get_current_country();

		if ( ! this.should_display_banner() || ! this.has_local_site( current_country ) ) {
			return;
		}

		const overlay_html = pmc_region_redirect_overlay.overlay_html[ current_country ];
		const $banner      = this.$( '#pmc-reg-rd-overlay-banner' );

		$banner.find( '.message' ).html( overlay_html );
		$banner.show();

		this.set_cookies();

	};

}

new PMCRedirectOverlay();

//EOF
