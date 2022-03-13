import { __ } from '@wordpress/i18n';

import TaxonomyToggle from '../../components/taxonomy-toggle';

const ExcludeFromGoogleNews = () => {
	const helps = {
		true: __( 'Will not appear in Google News', 'pmc-gutenberg' ),
		false: __( 'Can appear in Google News', 'pmc-gutenberg' ),
	};

	return (
		<TaxonomyToggle
			taxonomySlug="_post-options"
			termSlug="exclude-from-google-news"
			label={ __( 'Google News', 'pmc-gutenberg' ) }
			help={ helps }
		/>
	);
};

export default ExcludeFromGoogleNews;
