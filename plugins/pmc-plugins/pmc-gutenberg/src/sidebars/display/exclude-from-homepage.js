import { __ } from '@wordpress/i18n';

import TaxonomyToggle from '../../components/taxonomy-toggle';

const ExcludeFromHomepage = () => {
	const helps = {
		true: __( 'Will not appear on homepage', 'pmc-gutenberg' ),
		false: __( 'Can appear on homepage', 'pmc-gutenberg' ),
	};

	return (
		<TaxonomyToggle
			taxonomySlug="_post-options"
			termSlug="exclude-from-homepage"
			label={ __( 'Homepage', 'pmc-gutenberg' ) }
			help={ helps }
		/>
	);
};

export default ExcludeFromHomepage;
