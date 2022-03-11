/* Variety's subscriber login status client side script
-------------------------------------------------------------- */

const loginStatus = {
	has_cookie: false,
	authorized: false,
	has_digital_access: false,

	secured_url_path: ( urlPath ) =>
		`https://${ window.location.host }${ urlPath }`,

	init() {
		const self = this;

		// use jQuery to do the lazy load after document is fully loaded.
		jQuery( () => {
			self.update();
		} );
	},

	update() {
		const self = this;

		if ( 'undefined' === typeof uls ) {
			return;
		}

		if ( uls.session.can_access( 'vy-digital' ) ) {
			this.set_html_authorized();
		} else {
			this.set_html_not_authorized();
		}

		jQuery( 'a[esp-promo-suffix]' ).each( function () {
			jQuery( this ).attr(
				'href',
				`https://www.pubservice.com/variety/?PC=VY&PK=${ self.current_promocode(
					'M',
					jQuery( this ).attr( 'esp-promo-suffix' )
				) }`
			);
		} );
	},

	// set the content for the html signin block when user logged in
	set_html_authorized() {
		const self = this;

		const cssClasses = [ 'authenticated', 'authenticated-pp' ];

		jQuery( 'body' ).addClass( cssClasses.join( ' ' ) );
		jQuery( '.vy-logout' ).attr(
			'href',
			this.secured_url_path( '/digital-subscriber-access/#action=logout' )
		);
		jQuery( '#digital-link-text' )
			.attr( 'title', 'View Print Edition' )
			.attr( 'href', this.secured_url_path( '/access-digital/' ) )
			.attr( 'target', '_blank' )
			.show();
		jQuery( '#subscribe-link-section #subscribe-link-text' )
			.text( 'Access Premier' )
			.attr( 'href', this.secured_url_path( '/print-plus/' ) )
			.show();

		if ( jQuery.cookie( 'uls3_username' ) ) {
			jQuery( '.vy-username' ).text( jQuery.cookie( 'uls3_username' ) );
		}

		// eslint-disable-next-line camelcase
		if ( 'undefined' !== typeof Variety_Authentication ) {
			jQuery( '.vy-logout' ).on( 'click', ( event ) => {
				self.set_overlay_processing( true );
				event.preventDefault();
				jQuery( '.vy-logout' ).unbind( 'click' );
				Variety_Authentication.logout( () => {
					self.set_html_not_authorized();
					window.location.reload();
				} );
				return false;
			} );
		}
		jQuery( '.variety-story-issue-login' ).show();
		jQuery( '.variety-story-issue-logout' ).hide();
	},

	// set the content for the html sigin block when user has not login
	set_html_not_authorized() {
		jQuery( '#sign-in-my-account' ).remove();
		jQuery( '#subscribe-link-text' )
			.html( 'Subscribe Today!' )
			.attr( 'href', '/subscribe-us/' )
			.attr( 'target', 'self' )
			.show();
		jQuery( '#digital-link-text' )
			.attr( 'title', 'Subscribe Today!' )
			.attr( 'href', '/subscribe-us/' )
			.removeAttr( 'target' )
			.show();
		jQuery( '.variety-story-issue-login' ).hide();
		jQuery( '.variety-story-issue-logout' ).show();
	},

	current_promocode( prefix, suffix ) {
		const date = new Date();
		const m = date.getMonth() + 1;
		const y = date.getFullYear() % 10;
		return prefix + y.toString() + m.toString( 16 ).toUpperCase() + suffix;
	},

	set_overlay_processing( active ) {
		let overlay = jQuery( '#overlay_ajax_loading' );

		if ( active ) {
			if ( ! overlay.length ) {
				overlay = jQuery( '<div></div>' ).attr(
					'id',
					'overlay_ajax_loading'
				);
				jQuery( 'body' ).append( overlay );
			}
			jQuery( overlay ).show();
		} else if ( overlay.length ) {
			jQuery( overlay ).hide();
		}
	},
};

if ( undefined !== window.pmc && undefined !== jQuery ) {
	pmc.hooks.add_filter( 'pmc-adm-set-targeting-keywords', ( keywords ) => {
		const originalKeywords = keywords;

		if ( 'undefined' === typeof originalKeywords.kw ) {
			originalKeywords.kw = [];
		}

		return originalKeywords;
	} );

	pmc.hooks.add_filter( 'pmc-adm-set-targeting-keywords-kw', ( keyword ) => {
		let originalKeyword = keyword;
		let kw = 'logged-out-subscriber';

		if ( 'undefined' === typeof jQuery.cookie ) {
			return originalKeyword;
		}

		if ( uls.session.is_valid() ) {
			kw = 'logged-in-subscriber';
		}

		if ( originalKeyword instanceof Array ) {
			originalKeyword.push( kw );
		} else {
			originalKeyword += ( originalKeyword ? ',' : '' ) + kw;
		}

		return originalKeyword;
	} );

	pmc.hooks.add_action( 'uls.ping.refresh', () => {
		loginStatus.update();
	} );
}

export default loginStatus;

/* eslint-enable */
