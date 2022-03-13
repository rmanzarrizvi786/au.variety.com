/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { useInstanceId } from '@wordpress/compose';
import {
	Button,
	Placeholder,
	Spinner,
	VisuallyHidden,
	SelectControl,
} from '@wordpress/components';

/**
 * Block dependencies
 */
import { postWithFeaturedImage } from './post-featured-image';

const Search = ( { onChange } ) => {
	const instanceId = useInstanceId( Search );

	return (
		<div>
			<label htmlFor={ `pmc-story-card__search-${ instanceId }` }>
				{ __( 'Next, search for a post by title: ', 'pmc-gutenberg' ) }
			</label>
			<input
				style={ { width: '100%' } }
				id={ `pmc-story-card__search-${ instanceId }` }
				type="search"
				placeholder={ __( 'Search for a post', 'pmc-gutenberg' ) }
				onChange={ ( event ) => onChange( event.target.value ) }
				autoComplete="off"
			/>
		</div>
	);
};

const Results = ( {
	postType,
	onChangePostID,
	onMorePosts,
	maxPosts,
	keywords,
} ) => {
	const { posts, morePostsAvailable, isLoading } = useSelect(
		( select ) => {
			const entityKind = 'postType';
			const entityName = postType;
			const perPage = 100;

			// Additional query options:
			// https://wordpress.stackexchange.com/questions/328277/what-are-all-the-query-parameters-for-getentityrecords
			const query = {
				per_page: perPage,
				orderby: 'date',
				order: 'desc',
				status: [ 'publish', 'draft', 'future' ],
				search: keywords,
			};

			const { getEntityRecords, getMedia } = select( 'core' );
			const { isResolving } = select( 'core/data' );

			let posts = []; // eslint-disable-line no-shadow

			for (
				let page = 1;
				page <= Math.ceil( maxPosts / perPage );
				page++
			) {
				const next = getEntityRecords( entityKind, entityName, {
					...query,
					page,
				} );
				if ( next ) {
					posts = posts.concat( next );
				}
			}

			return {
				// KUDOS: https://github.com/WordPress/gutenberg/blob/master/packages/block-library/src/latest-posts/edit.js
				posts: ! Array.isArray( posts )
					? posts
					: posts
							.slice( 0, maxPosts )
							.map( ( post ) =>
								postWithFeaturedImage( { post, getMedia } )
							),

				morePostsAvailable: () => {
					return posts.length > maxPosts;
				},
				isLoading: () => {
					const resolving = [ false ];
					for (
						let page = 1;
						page <= Math.ceil( maxPosts / perPage );
						page++
					) {
						resolving.push(
							isResolving( 'core', 'getEntityRecords', [
								entityKind,
								entityName,
								{ ...query, page },
							] )
						);
					}
					for ( const post of posts.slice( 0, maxPosts ) ) {
						if ( post ) {
							resolving.push(
								isResolving( 'core', 'getMedia', [
									post.featured_media,
								] )
							);
						}
					}
					return resolving.reduce(
						( accumulator, currentValue ) =>
							accumulator || currentValue
					);
				},
			};
		},
		[ keywords, maxPosts ]
	);

	if ( posts && posts.length > 0 ) {
		return (
			<fieldset className="pmc-story-card-search-results__fieldset">
				<VisuallyHidden as="legend">
					{ __( 'Choose a post', 'pmc-gutenberg' ) }
				</VisuallyHidden>
				<ol className="pmc-story-card-search-results__list">
					{ posts.map( ( post ) => {
						if ( ! post ) return '';
						return (
							<li
								className="pmc-story-card-search-results__item"
								key={ post.id }
							>
								<Button
									isSecondary
									onClick={ () => {
										onChangePostID( post.id );
									} }
									className="pmc-story-card-search-results__button"
								>
									<span className="pmc-story-card-search-results__title">
										{ post.title.raw }

										<i className="pmc-story-card-search-results__title-label">
											{ 'publish' !== post.status
												? ' - ' + post.status
												: '' }
										</i>
									</span>
									<span className="pmc-story-card-search-results__image-container">
										<img
											className="pmc-story-card-search-results__image"
											src={ post.featuredImageSourceUrl }
											alt={ post.featuredImageAltText }
										/>
									</span>
								</Button>
							</li>
						);
					} ) }
				</ol>
				{ morePostsAvailable() && (
					<Button
						className="pmc-story-card-search-results__more-button"
						isPrimary
						disabled={ isLoading() }
						isBusy={ isLoading() }
						onClick={ () => {
							onMorePosts();
						} }
					>
						More Posts
					</Button>
				) }
			</fieldset>
		);
	} else if ( isLoading() ) {
		return <Spinner />;
	} else if ( keywords ) {
		return (
			<p className="pmc-story-card-search-results__note">
				{ `No posts found for “${ keywords }”.` }
			</p>
		);
	}

	return (
		<p className="pmc-story-card-search-results__note">
			{ 'No posts found.' }
		</p>
	);
};

const SetupState = ( {
	onChangePostType,
	postType,
	onChangePostID,
	postTypeSelectOptions,
} ) => {
	const postsPerPage = 5;

	const [ keywords, setKeywords ] = useState( null );
	const [ maxPosts, setMaxPosts ] = useState( postsPerPage );

	function loadMorePosts() {
		setMaxPosts( maxPosts + postsPerPage );
	}

	return (
		<Placeholder
			className="pmc-story-card-setup"
			icon="format-aside"
			label={ __( 'Story Setup', 'pmc-gutenberg' ) }
			instructions={ __(
				'Choose a post to display in this story card.',
				'pmc-gutenberg'
			) }
			isColumnLayout
		>
			<SelectControl
				label={ __(
					'First, select a post type to search: ',
					'pmc-gutenberg'
				) }
				value={ postType }
				onChange={ onChangePostType }
				// These would come from localized data
				options={ postTypeSelectOptions }
			/>

			<Search onChange={ setKeywords } />

			<div
				style={
					{
						minHeight: '200px',
					} /* SHIM: Avoid layout changes while results are loading */
				}
			>
				<Results
					keywords={ keywords }
					maxPosts={ maxPosts }
					postType={ postType }
					onChangePostID={ onChangePostID }
					onMorePosts={ loadMorePosts }
				/>
			</div>
		</Placeholder>
	);
};

export { SetupState };
