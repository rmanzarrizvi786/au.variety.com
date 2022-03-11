window.addEventListener( 'DOMContentLoaded', () => {
	if (
		undefined !== window.pmc &&
		undefined !== window.pmc.subscription_v2
	) {
		pmc.subscription_v2.on_subscriber_data_loaded( ( subscriber ) => {
			document.body.classList.add( 'authenticated', 'authenticated-vip' );

			const elemVipUsername = document.querySelector(
				'.vy-vip-username'
			);
			const elemVipLogout = document.querySelector( '.vy-vip-logout' );

			// Better check if our dom nodes exist lest we run into any errors
			if ( elemVipUsername && elemVipLogout ) {
				// We are on VIP page
				elemVipUsername.textContent = subscriber.user.acct.name;
				elemVipLogout.href =
					elemVipLogout.href +
					'&session-id=' +
					subscriber.session.session_id;
				elemVipLogout.classList.remove( 'lrv-u-display-none' );
			}
		} );
	}
} );
