import { TextControl } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import WordCount from '../../components/word-count';

const Render = ( { titleOverride, setMetaValue } ) => {
	return (
		<div>
			<TextControl
				label="Title (Hed)"
				help="Override the post title."
				value={ titleOverride }
				onChange={ ( title ) => setMetaValue( title ) }
			/>
			<WordCount text={ titleOverride } />
		</div>
	);
};

const TitleOverride = compose(
	withSelect( ( select ) => {
		return {
			titleOverride: select( 'core/editor' ).getEditedPostAttribute(
				'meta'
			).override_post_title,
		};
	} ),
	withDispatch( ( dispatch ) => {
		return {
			setMetaValue: ( metaValue ) => {
				dispatch( 'core/editor' ).editPost( {
					meta: { override_post_title: metaValue },
				} );
			},
		};
	} )
)( Render );

export default TitleOverride;
