import { __ } from '@wordpress/i18n';

import TaxonomyToggle from '../../components/taxonomy-toggle';

const ExcludeFromSectionFronts = () => {
	const helps = {
		true: __( 'Will not appear on section fronts', 'pmc-gutenberg' ),
		false: __( 'Can appear on section fronts', 'pmc-gutenberg' ),
	};

	return (
		<TaxonomyToggle
			taxonomySlug="_post-options"
			termSlug="exclude-from-section-fronts"
			label={ __( 'Section Fronts', 'pmc-gutenberg' ) }
			help={ helps }
		/>
	);
};

export default ExcludeFromSectionFronts;
