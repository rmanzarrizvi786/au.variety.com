import { TextControl } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';

const Render = ( { canonicalUrl, setMetaValue } ) => {
	return (
		<TextControl
			label="Canonical Override"
			help="The Canonical URL, if different from the posts URL"
			value={ canonicalUrl }
			onChange={ ( url ) => setMetaValue( url ) }
		/>
	);
};

const CanonicalOverride = compose(
	withSelect( ( select ) => {
		return {
			canonicalUrl: select( 'core/editor' ).getEditedPostAttribute(
				'meta'
			)._pmc_canonical_override,
		};
	} ),
	withDispatch( ( dispatch ) => {
		return {
			setMetaValue: ( metaValue ) => {
				dispatch( 'core/editor' ).editPost( {
					meta: { _pmc_canonical_override: metaValue },
				} );
			},
		};
	} )
)( Render );

export default CanonicalOverride;
