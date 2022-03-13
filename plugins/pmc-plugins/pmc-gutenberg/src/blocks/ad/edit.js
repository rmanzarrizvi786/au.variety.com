import { BlockControls } from '@wordpress/block-editor';
import { Placeholder, Spinner, ToolbarButton } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withDispatch, withSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import AdSelect from './ad-select';
import {
	PMC_ADM_REST_BASE,
	PMC_ADM_DEFAULT_VERSION,
	PMC_ADM_ENTITY_KIND,
	PMC_ADM_ENTITY_NAME,
} from './constants';
import './edit.scss';

const Render = ( {
	attributes: { location, provider },
	hasSingleProvider,
	isResolving,
	locationTitle,
	providerTitle,
	setAttributes,
} ) => {
	const label = __( 'Advertisement', 'pmc-gutenberg' );

	if ( provider && location ) {
		let instructions;

		if ( isResolving ) {
			instructions = <Spinner />;
		} else if ( hasSingleProvider ) {
			instructions = locationTitle;
		} else {
			instructions = `${ locationTitle } from ${ providerTitle }`;
		}

		return (
			<>
				<Placeholder label={ label } instructions={ instructions } />

				<BlockControls group="other">
					<ToolbarButton
						onClick={ () =>
							setAttributes( { location: '', provider: '' } )
						}
					>
						{ __( 'Replace', 'pmc-gutenberg' ) }
					</ToolbarButton>
				</BlockControls>
			</>
		);
	}

	const children = (
		<AdSelect
			location={ location }
			provider={ provider }
			setAttributes={ setAttributes }
		/>
	);

	return <Placeholder children={ children } label={ label } />;
};

const Edit = compose( [
	withSelect( ( select, { attributes: { location, provider } } ) => {
		const { getEntityRecords, hasFinishedResolution } = select( 'core' );

		const locationsByProvider = getEntityRecords(
			PMC_ADM_ENTITY_KIND,
			PMC_ADM_ENTITY_NAME
		);

		const isResolving = ! hasFinishedResolution( 'getEntityRecords', [
			PMC_ADM_ENTITY_KIND,
			PMC_ADM_ENTITY_NAME,
		] );

		let hasSingleProvider = false;
		let locationTitle = location;
		let providerTitle = provider;

		if ( ! isResolving && Boolean( provider ) && Boolean( location ) ) {
			hasSingleProvider = 1 === locationsByProvider.length;

			const providerData = locationsByProvider.filter(
				( singleProvider ) => {
					return provider === singleProvider.id;
				}
			);

			if ( Boolean( providerData.length ) ) {
				const selectedProvider = providerData.shift();
				providerTitle = selectedProvider.title;
				locationTitle = selectedProvider.locations[ location ];
			}
		}

		return { hasSingleProvider, isResolving, locationTitle, providerTitle };
	} ),
	withDispatch( ( dispatch ) => {
		const { addEntities } = dispatch( 'core' );

		addEntities( [
			{
				name: PMC_ADM_ENTITY_NAME,
				kind: PMC_ADM_ENTITY_KIND,
				baseURL: `${ PMC_ADM_REST_BASE }/${ PMC_ADM_DEFAULT_VERSION }/${ PMC_ADM_ENTITY_NAME }`,
				label: __( 'PMC Ads Providers', 'pmc-gutenberg' ),
				key: 'id',
			},
		] );
	} ),
] )( Render );

export default Edit;
