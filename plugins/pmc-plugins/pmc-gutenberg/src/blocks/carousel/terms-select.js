import { SelectControl, Spinner } from '@wordpress/components';
import { withSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';

const Render = ( { terms, taxonomyNicename, onChange, isFinished } ) => {
	const termOptions = ( () => {
		const termData = ( term ) => {
			const { name, id } = term;

			return {
				label: name,
				value: id,
			};
		};

		let options = [
			{
				label: sprintf(
					/* translators: 1. Taxonomy nice name. */
					__( 'Select %1$s', 'pmc-gutenberg' ),
					taxonomyNicename
				),
			},
		];

		if ( isFinished ) {
			options = [ ...options, ...terms.map( termData ) ];
		}

		return options;
	} )();

	return (
		<>
			{ ! isFinished && <Spinner /> }

			{ isFinished && (
				<SelectControl
					label={ taxonomyNicename }
					help={ __(
						'Once you select a term, you will see a preview of the content. If you need to change the term selected, create a new block.',
						'pmc-gutenberg'
					) }
					value={ __( 'Choose term.', 'pmc-gutenberg' ) }
					onChange={ onChange }
					options={ termOptions }
				/>
			) }
		</>
	);
};

const TermsSelect = withSelect( ( scopedSelect, { taxonomy } ) => {
	const {
		getEntityRecords,
		hasFinishedResolution,
		getTaxonomy,
	} = scopedSelect( 'core' );

	const query = { per_page: 100 };

	const terms = getEntityRecords( 'taxonomy', taxonomy, query );
	const isFinished = hasFinishedResolution( 'getEntityRecords', [
		'taxonomy',
		taxonomy,
		query,
	] );

	const taxonomyObj = getTaxonomy( taxonomy );
	// eslint-disable-next-line camelcase
	const taxonomyNicename = taxonomyObj?.labels.singular_name ?? taxonomy;

	return {
		terms,
		taxonomyNicename,
		isFinished,
	};
} )( Render );

export default TermsSelect;
