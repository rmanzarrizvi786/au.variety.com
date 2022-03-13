import { RichText } from '@wordpress/block-editor';
import { Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import { FeaturedImage } from './featured-image';

const Title = ( { value, onChange } ) => {
	return (
		<RichText
			value={ value }
			onChange={ onChange }
			allowedFormats={ [ 'core/bold', 'core/italic' ] }
			className="pmc-story-card-preview__title"
		/>
	);
};

const Excerpt = ( { value, onChange } ) => {
	return (
		<RichText
			value={ value }
			onChange={ onChange }
			allowedFormats={ [] }
			className="pmc-story-card-preview__excerpt"
		/>
	);
};

const EditState = ( {
	postType,
	postID,
	contentOverride,
	hasContentOverride,
	hasDisplayedExcerpt,
	hasFullWidthImage,
	alignment,
	title,
	excerpt,
	featuredImageID,
	onContentOverrideUpdate,
	onChangeTitle,
	onChangeExcerpt,
	viewMoreText,
} ) => {
	const post = useSelect(
		( select ) => {
			const { getEntityRecord } = select( 'core' );
			const entityKind = 'postType';
			const entityName = postType;

			// @TODO:  Handle the case where the postType attribute of this story card
			//         doesn’t match the postID. For example, search for the postID
			//         throughout all of the available post types until a match is found.
			//         https://stackoverflow.com/questions/53404030/wordpress-gutenberg-withselect-get-list-of-post-types
			const selectedPost = getEntityRecord(
				entityKind,
				entityName,
				postID
			);

			return selectedPost;
		},
		[ postID ]
	);

	if ( ! post ) {
		return <Spinner />;
	}

	const imageID = featuredImageID ?? post.featured_media ?? false;

	if ( ! excerpt ) {
		excerpt = post.excerpt ? post.excerpt.rendered : '';
	}

	return (
		<div className="pmc-story-card-edit" style={ { textAlign: alignment } }>
			<Title
				value={ title || post.title.raw }
				onChange={ onChangeTitle }
			/>
			{ Boolean( imageID ) && (
				<FeaturedImage
					imageID={ imageID }
					hasFullWidthImage={ hasFullWidthImage }
				/>
			) }
			{ Boolean( hasDisplayedExcerpt ) && (
				<Excerpt value={ excerpt } onChange={ onChangeExcerpt } />
			) }
			<p className="pmc-story-card-preview__link">
				<a href={ post.link }>{ viewMoreText }</a>
			</p>

			{ postID && hasContentOverride && (
				<RichText
					value={ contentOverride }
					placeholder={ __(
						'Enter excerpt override…',
						'pmc-gutenberg'
					) }
					onChange={ onContentOverrideUpdate }
					tagName="p"
				/>
			) }
		</div>
	);
};

export { EditState };
