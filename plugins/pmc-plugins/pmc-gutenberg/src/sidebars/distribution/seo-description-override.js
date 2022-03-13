import { __ } from '@wordpress/i18n';
import { TextareaControl } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import WordCount from '../../components/word-count';

const Render = ( { seoDescription, setMetaValue } ) => {
	return (
		<div>
			<TextareaControl
				label={ __(
					'SEO Description (200 char max)',
					'pmc-gutenberg'
				) }
				help={ __(
					'This text will be used as description meta information. Left empty, a description is automatically generated.',
					'pmc-gutenberg'
				) }
				value={ seoDescription }
				onChange={ ( description ) => setMetaValue( description ) }
			/>
			<WordCount text={ seoDescription } />
		</div>
	);
};

const DescriptionOverride = compose(
	withSelect( ( select ) => {
		return {
			seoDescription: select( 'core/editor' ).getEditedPostAttribute(
				'meta'
			).mt_seo_description,
		};
	} ),
	withDispatch( ( dispatch ) => {
		return {
			setMetaValue: ( metaValue ) => {
				dispatch( 'core/editor' ).editPost( {
					meta: { mt_seo_description: metaValue },
				} );
			},
		};
	} )
)( Render );

export default DescriptionOverride;
