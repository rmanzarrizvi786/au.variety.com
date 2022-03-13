import { TextareaControl } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import WordCount from '../../components/word-count';

const Render = ( { excerptOverride, setMetaValue } ) => {
	return (
		<div>
			<TextareaControl
				label="Excerpt (Dek)"
				help="Override the post excerpt."
				value={ excerptOverride }
				onChange={ ( description ) => setMetaValue( description ) }
			/>
			<WordCount text={ excerptOverride } />
		</div>
	);
};

const ExcerptOverride = compose(
	withSelect( ( select ) => {
		return {
			excerptOverride: select( 'core/editor' ).getEditedPostAttribute(
				'meta'
			).override_post_excerpt,
		};
	} ),
	withDispatch( ( dispatch ) => {
		return {
			setMetaValue: ( metaValue ) => {
				dispatch( 'core/editor' ).editPost( {
					meta: { override_post_excerpt: metaValue },
				} );
			},
		};
	} )
)( Render );

export default ExcerptOverride;
