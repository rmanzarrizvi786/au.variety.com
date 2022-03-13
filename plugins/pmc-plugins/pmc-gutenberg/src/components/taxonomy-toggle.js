import { without } from 'lodash';

import { useEffect, useState } from '@wordpress/element';
import { Spinner, ToggleControl } from '@wordpress/components';
import { compose, withState } from '@wordpress/compose';
import apiFetch from '@wordpress/api-fetch';
import { withSelect, withDispatch } from '@wordpress/data';

const fetchTermId = ( {
	currentTerms,
	isChecked,
	taxonomyRestBase,
	termId,
	termSlug,
	setIsChecked,
	setTermId,
} ) => {
	if ( 'number' === typeof termId && 'boolean' === typeof isChecked ) {
		return;
	}

	if ( ! taxonomyRestBase ) {
		return;
	}

	apiFetch( {
		path: `/wp/v2/${ taxonomyRestBase }`,
	} ).then( ( terms ) => {
		const newTermId = terms.filter(
			( term ) => term.slug === termSlug
		)[ 0 ].id;

		setTermId( newTermId );
		setIsChecked( currentTerms.includes( newTermId ) );
	} );
};

const Render = ( props ) => {
	const {
		currentTerms,
		help,
		label,
		updateTerms,
		taxonomyRestBase,
		termSlug,
	} = props;
	const [ isChecked, setIsChecked ] = useState();
	const [ termId, setTermId ] = useState();

	useEffect( () => {
		fetchTermId( {
			currentTerms,
			isChecked,
			taxonomyRestBase,
			termId,
			termSlug,
			setIsChecked,
			setTermId,
		} );
	} );

	if ( 'number' !== typeof termId && 'boolean' !== typeof isChecked ) {
		return <Spinner />;
	}

	return (
		<ToggleControl
			label={ label }
			help={ help[ isChecked ] }
			checked={ isChecked }
			onChange={ ( value ) => {
				setIsChecked( value );
				updateTerms( termId );
			} }
		/>
	);
};

const TaxonomyToggle = compose( [
	withState(),
	withSelect( ( select, { taxonomySlug } ) => {
		const { getTaxonomy } = select( 'core' );
		const taxonomy = getTaxonomy( taxonomySlug );

		if ( ! taxonomy ) {
			return {
				currentTerms: [],
				taxonomyRestBase: null,
			};
		}

		const currentTerms = select( 'core/editor' ).getEditedPostAttribute(
			taxonomy.rest_base
		);

		return {
			currentTerms,
			taxonomyRestBase: taxonomy.rest_base,
		};
	} ),
	withDispatch( ( dispatch, { currentTerms, taxonomyRestBase } ) => {
		return {
			updateTerms: ( termId ) => {
				const hasTerm = currentTerms.indexOf( termId ) !== -1;
				const newTerms = hasTerm
					? without( currentTerms, termId )
					: [ ...currentTerms, termId ];

				dispatch( 'core/editor' ).editPost( {
					[ taxonomyRestBase ]: newTerms,
				} );
			},
		};
	} ),
] )( Render );

export default TaxonomyToggle;
