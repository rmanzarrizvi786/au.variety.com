import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import WordCount from '../../components/word-count';

const Render = ( { seoTitle, setMetaValue } ) => {
	return (
		<div>
			<TextControl
				label={ __( 'SEO Title (70 Char max)', 'pmc-gutenberg' ) }
				help={ __(
					'The text entered here will alter the <title> tag using the wp_title() function. Use %title% to include the original title or leave empty to keep original title.',
					'pmc-gutenberg'
				) }
				value={ seoTitle }
				onChange={ ( title ) => setMetaValue( title ) }
			/>
			<WordCount text={ seoTitle } />
		</div>
	);
};

const TitleOverride = compose(
	withSelect( ( select ) => {
		return {
			seoTitle: select( 'core/editor' ).getEditedPostAttribute( 'meta' )
				.mt_seo_title,
		};
	} ),
	withDispatch( ( dispatch ) => {
		return {
			setMetaValue: ( metaValue ) => {
				dispatch( 'core/editor' ).editPost( {
					meta: { mt_seo_title: metaValue },
				} );
			},
		};
	} )
)( Render );

export default TitleOverride;
