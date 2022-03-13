import { SelectControl, Spinner } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { __, _x, sprintf } from '@wordpress/i18n';

import { PMC_ADM_ENTITY_KIND, PMC_ADM_ENTITY_NAME } from './constants';

const Render = ( {
	hasSingleProvider,
	isResolving,
	location,
	locationOptions,
	provider,
	providerOptions,
	providerTitle,
	setAttributes,
	singleProvider,
} ) => {
	if ( isResolving ) {
		return <Spinner />;
	}

	if ( hasSingleProvider ) {
		setAttributes( { provider: singleProvider } );
	}

	let locationHelp;
	if ( hasSingleProvider ) {
		locationHelp = __(
			'Select an ad location to display in this block.',
			'pmc-gutenberg'
		);
	} else {
		locationHelp = sprintf(
			/* translators: 1. Provider title. */
			__(
				'Select an ad location from those available for the %1$s provider.',
				'pmc-gutenberg'
			),
			providerTitle
		);
	}

	return (
		<div className="pmc-ad-select-wrapper">
			{ ! hasSingleProvider && (
				<SelectControl
					label={ _x(
						'Provider',
						'PMC Ad providers',
						'pmc-gutenberg'
					) }
					help={ __(
						'Select the ad provider for this block.',
						'pmc-gutenberg'
					) }
					options={ providerOptions }
					value={ provider }
					onChange={ ( value ) =>
						setAttributes( { provider: value } )
					}
				/>
			) }

			{ Boolean( provider ) && (
				<SelectControl
					label={ _x(
						'Location',
						'PMC Ad locations for chosen provider',
						'pmc-gutenberg'
					) }
					help={ locationHelp }
					options={ locationOptions }
					value={ location }
					onChange={ ( value ) =>
						setAttributes( { location: value } )
					}
				/>
			) }
		</div>
	);
};

const AdSelect = compose( [
	withSelect( ( select, { provider } ) => {
		const { getEntityRecords, hasFinishedResolution } = select( 'core' );

		const locationsByProvider = getEntityRecords(
			PMC_ADM_ENTITY_KIND,
			PMC_ADM_ENTITY_NAME
		);

		const isResolving = ! hasFinishedResolution( 'getEntityRecords', [
			PMC_ADM_ENTITY_KIND,
			PMC_ADM_ENTITY_NAME,
		] );

		const locationOptions = [ { label: '', value: '' } ];
		let providerOptions;
		let providerTitle = provider;

		let hasSingleProvider = false;
		let singleProvider;

		if ( ! isResolving && Boolean( locationsByProvider ) ) {
			hasSingleProvider =
				! isResolving && Boolean( locationsByProvider )
					? 1 === locationsByProvider.length
					: false;

			if ( hasSingleProvider ) {
				singleProvider = locationsByProvider[ 0 ].id;
			} else {
				providerOptions = locationsByProvider.map(
					( { id, title } ) => {
						return { label: title, value: id };
					}
				);

				providerOptions.unshift( { label: '', value: '' } );
			}

			if ( Boolean( provider ) ) {
				const providerData = locationsByProvider.filter(
					( providerToCheck ) => providerToCheck.id === provider
				);

				if ( Boolean( providerData.length ) ) {
					const selectedProvider = providerData.shift();
					// const locationsToAdd = se.locations;
					const { locations: locationsToAdd } = selectedProvider;
					providerTitle = selectedProvider.title;

					Object.entries( locationsToAdd ).forEach(
						( [ value, label ] ) =>
							locationOptions.push( {
								label,
								value,
							} )
					);
				}
			}
		}

		return {
			hasSingleProvider,
			isResolving,
			locationOptions,
			providerOptions,
			providerTitle,
			singleProvider,
		};
	} ),
] )( Render );

export default AdSelect;
