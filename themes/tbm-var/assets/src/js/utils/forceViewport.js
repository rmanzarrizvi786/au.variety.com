/**
 * Set the viewport size to 0.76 at tablet.
 *
 * Copied from pmc-footwearnews-2018. This is a hacky solution to
 * account for ads being served at larger than the viewport on tablet.
 * We should revisit this post launch and:
 *     1. Update the tablet breakpoint to match desktop
 *     2. Use a-scale-leaderboard-ad on the ads to ensure they fit in the viewport.
 */

export default function forceViewport() {
	const theView = document.querySelector( 'meta[name="viewport"]' );
	const viewPort = window.innerWidth;

	if ( 1000 > viewPort && 767 < viewPort ) {
		theView.setAttribute(
			'content',
			'width=device-width, initial-scale=0.76, maximum-scale=1.0, user-scalable=0'
		);
	} else {
		theView.setAttribute(
			'content',
			'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'
		);
	}
}
