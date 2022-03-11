/**
 * JS for adding action on Subscription User login state.
 * - This JS can be used to maintain all the subscription related function.
 */

export default class Subscription {
	constructor() {
		this.subscriptionBox = document.querySelector(
			'.a-subscription-banner'
		);
		this.toggleSubscriptionBox = this.toggleSubscriptionBox.bind( this );

		/**
		 * Check if cxense is loaded
		 * - Event listeners are added to cxense component.
		 * - Add only if cxense element exist.
		 */
		if ( cxpmc.initialized === true ) {
			this.subsHeaderBtn = document.querySelectorAll( '.cx-hdr-link' );

			this.subsHeaderBtn.forEach( ( el ) => {
				el.addEventListener(
					'mouseenter',
					this.toggleSubscriptionBox,
					false
				);
			} );

			this.subscriptionBox.addEventListener(
				'mouseleave',
				this.toggleSubscriptionBox,
				false
			);
		}

		// Send data to one signal service.
		if (
			undefined !== window.pmc &&
			undefined !== window.pmc.subscription_v2 &&
			undefined !== typeof OneSignal
		) {
			pmc.subscription_v2.send_onesignal_tags();
		}
	}

	// Toggle subscription flyout div.
	toggleSubscriptionBox() {
		this.subscriptionBox.classList.toggle( 'lrv-a-hidden' );
	}
}
