import reflowForSideSkinAd from './SideSkinAd';

export default function initSideSkinAd( e ) {
	const parametersMessagePattern = 'pmcadm:dfp:skinad:parameters';
	let serializedParameters = '';

	if (
		'string' === typeof e.data &&
		'object' === typeof window.pmc.skinAds
	) {
		// eslint-disable-line camelcase
		if (
			parametersMessagePattern ===
			e.data.substring( 0, parametersMessagePattern.length )
		) {
			serializedParameters = e.data.substring(
				parametersMessagePattern.length
			);

			if ( serializedParameters ) {
				reflowForSideSkinAd();

				window.pmc.skinAds.refresh_skin_rails(); // eslint-disable-line camelcase
			}
		}
	}
}
