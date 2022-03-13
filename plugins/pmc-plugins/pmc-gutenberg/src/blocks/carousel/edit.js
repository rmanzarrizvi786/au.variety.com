/**
 * External Dependencies.
 */

import apiFetch from '@wordpress/api-fetch';
import { registerBlockStyle, unregisterBlockStyle } from '@wordpress/blocks';
import { Placeholder, SelectControl, Spinner } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal Dependencies.
 */
import CurationPreview from './carousel-preview';
import { PMC_CAROUSEL_CURATION_TAXONOMIES_ENDPOINT } from './constants';
import TermsSelect from './terms-select';

/**
 * The setup state for carousel.
 */

const SetupState = ( { setAttributes, curationTaxonomy } ) => {
	const [ curationTaxonomies, setCurationTaxonomies ] = useState( [] );

	useEffect( () => {
		apiFetch( {
			path: PMC_CAROUSEL_CURATION_TAXONOMIES_ENDPOINT,
		} )
			.then( ( res ) => setCurationTaxonomies( res ) )
			.catch( () =>
				setCurationTaxonomies( [
					{
						label: __( 'Select curation', 'pmc-gutenberg' ),
						value: null,
					},
				] )
			);
	}, [] );

	if ( ! curationTaxonomies.length ) {
		return <Spinner />;
	}

	const onChangeTerm = ( value ) => {
		setAttributes( { termId: value } );
	};

	const onChangeCuration = ( value ) => {
		setAttributes( { curationTaxonomy: value } );
	};

	return (
		<Placeholder
			icon="buddicons-activity"
			label={ __( 'PMC Carousel Setup', 'pmc-gutenberg' ) }
			instructions={ __( 'Select curation.', 'pmc-gutenberg' ) }
			isColumnLayout
		>
			<div className="lrv-a-grid lrv-a-cols2">
				<SelectControl
					label={ __( 'Curation', 'pmc-gutenberg' ) }
					value={ curationTaxonomy }
					onChange={ onChangeCuration }
					options={ curationTaxonomies }
				/>

				{ Boolean( curationTaxonomy ) && (
					<TermsSelect
						taxonomy={ curationTaxonomy }
						onChange={ onChangeTerm }
					/>
				) }
			</div>
		</Placeholder>
	);
};

/**
 * The edit function for carousel.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 * @param {Object}   root0
 * @param {Object}   root0.attributes
 * @param {string}   root0.attributes.curationTaxonomy
 * @param {string}   root0.attributes.termId
 * @param {string}   root0.attributes.className
 * @param {boolean}  root0.isSelected
 * @param {Function} root0.setAttributes
 */
const Edit = ( {
	attributes: { curationTaxonomy, termId, className },
	isSelected,
	setAttributes,
} ) => {
	const blockStyles = [
		{
			name: '3-up',
			label: __( '3 Up', 'pmc-gutenberg' ),
			isDefault: true,
			meta: {
				perPage: 3,
			},
		},
		{
			name: '4-up',
			label: __( '4 Up', 'pmc-gutenberg' ),
			meta: {
				perPage: 4,
			},
		},
		{
			name: 'gallery',
			label: __( 'Gallery', 'pmc-gutenberg' ),
			meta: {
				perPage: 4,
			},
		},
		{
			name: 'story-river',
			label: __( 'Story River', 'pmc-gutenberg' ),
			meta: {
				perPage: 8,
			},
		},
	];

	const inlineVideoStyle = {
		name: 'inline-video',
		label: __( 'Inline Video Player', 'pmc-gutenberg' ),
	};

	if ( isSelected && 'vcategory' === curationTaxonomy ) {
		blockStyles.push( inlineVideoStyle );
	}

	blockStyles.forEach( ( style ) =>
		registerBlockStyle( 'pmc/carousel', style )
	);

	if ( isSelected && 'vcategory' !== curationTaxonomy ) {
		unregisterBlockStyle( 'pmc/carousel', inlineVideoStyle.name );
	}

	return termId ? (
		<CurationPreview
			termId={ termId }
			curationTaxonomy={ curationTaxonomy }
			className={ className }
			blockStyles={ blockStyles }
		/>
	) : (
		<SetupState
			curationTaxonomy={ curationTaxonomy }
			setAttributes={ setAttributes }
		/>
	);
};

export { Edit };
