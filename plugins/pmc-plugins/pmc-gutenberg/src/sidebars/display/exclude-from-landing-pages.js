import { __ } from '@wordpress/i18n';

import TaxonomyToggle from '../../components/taxonomy-toggle';

const ExcludeFromLandingPages = () => {
	const helps = {
		true: __( 'Will not appear on landing pages', 'pmc-gutenberg' ),
		false: __( 'Can appear on landing pages', 'pmc-gutenberg' ),
	};

	return (
		<TaxonomyToggle
			taxonomySlug="_post-options"
			termSlug="exclude-from-landing-pages"
			label={ __( 'Landing Pages', 'pmc-gutenberg' ) }
			help={ helps }
		/>
	);
};

export default ExcludeFromLandingPages;
