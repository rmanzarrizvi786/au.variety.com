import { __ } from '@wordpress/i18n';

import MetaToggle from '../../components/meta-toggle';

const ExcludeFromRiver = () => {
	const helps = {
		true: __( 'Will not appear in river', 'pmc-gutenberg' ),
		false: __( 'Can appear in river', 'pmc-gutenberg' ),
	};

	return (
		<MetaToggle
			metaKey="_exclude_post_from_river"
			label={ __( 'River', 'pmc-gutenberg' ) }
			help={ helps }
		/>
	);
};

export default ExcludeFromRiver;
