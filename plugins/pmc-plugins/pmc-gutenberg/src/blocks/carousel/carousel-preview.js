/**
 * External dependencies.
 */
import apiFetch from '@wordpress/api-fetch';
import { Spinner } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import { getBlockConfig } from '../helpers/config';

const ItemsPreview = ( { items, curationTaxonomy, currentStyleName } ) => {
	const CarouselItem = ( { title, url, image } ) => {
		return (
			<div className="lrv-u-background-color-white lrv-u-padding-a-050 lrv-u-height-100p">
				<div className="lrv-a-crop-16x9">
					<img className="lrv-a-crop-img" src={ image } alt="" />
				</div>
				<span className="lrv-u-font-size-16 lrv-u-font-family-basic lrv-u-font-weight-bold lrv-u-line-height-small lrv-u-margin-tb-050 lrv-u-display-block">
					<a href={ url }>{ title }</a>
				</span>
			</div>
		);
	};

	const GalleryItem = ( { title, url, image } ) => {
		return (
			<div className="lrv-u-height-100p">
				<div className="lrv-a-crop-16x9">
					<img className="lrv-a-crop-img" src={ image } alt="" />
				</div>
				<span className="lrv-u-font-size-16 lrv-u-font-family-basic lrv-u-font-weight-bold lrv-u-line-height-small lrv-u-margin-tb-050 lrv-u-display-block">
					<a href={ url }>{ title }</a>
				</span>
			</div>
		);
	};

	const VideoItem = ( { title, url, image } ) => {
		return (
			<div className="lrv-u-background-color-grey-lightest lrv-u-padding-a-050 lrv-a-glue-parent lrv-u-height-100p">
				<div className="lrv-a-crop-16x9">
					<img className="lrv-a-crop-img" src={ image } alt="" />
					<span
						style={ { zIndex: 0 } }
						className="lrv-u-text-transform-uppercase lrv-a-glue lrv-a-glue--t-0 lrv-a-glue--r-0 lrv-u-padding-a-025 lrv-u-font-size-14 lrv-u-font-weight-bold lrv-u-background-color-grey-darkest lrv-u-color-white"
					>
						{ __( 'Video', 'pmc-gutenberg' ) }
					</span>
				</div>
				<span className="lrv-u-font-size-16 lrv-u-font-family-basic lrv-u-font-weight-bold lrv-u-line-height-small lrv-u-margin-tb-050 lrv-u-display-block">
					<a href={ url }>{ title }</a>
				</span>
			</div>
		);
	};

	const Item = ( { title, excerpt, url, image } ) => {
		const isGallery = currentStyleName?.includes( 'gallery' );
		const isPlaylist = 'vcategory' === curationTaxonomy && ! isGallery;
		const isStory = ! isGallery && ! isPlaylist;

		return (
			<>
				{ isGallery && (
					<GalleryItem title={ title } url={ url } image={ image } />
				) }
				{ isPlaylist && (
					<VideoItem title={ title } url={ url } image={ image } />
				) }
				{ isStory && (
					<CarouselItem
						url={ url }
						title={ title }
						excerpt={ excerpt }
						image={ image }
					/>
				) }
			</>
		);
	};

	return items.map( ( { title, excerpt, url, image } ) => (
		<Item
			key={ title }
			title={ decodeEntities( title ) }
			url={ url }
			excerpt={ excerpt ?? '' }
			image={ image }
		/>
	) );
};

const CurationPreview = ( {
	termId,
	curationTaxonomy,
	className,
	blockStyles,
} ) => {
	const [ isLoading, setIsLoading ] = useState( true );
	const [ isError, setIsError ] = useState( false );
	const [ carouselItems, setCarouselItems ] = useState( [] );

	/**
	 * Get data of the current style so we can indicate it in the UI.
	 */
	const currentStyle = blockStyles.filter( ( { name } ) => {
		return className?.includes( name );
	} )[ 0 ];

	/**
	 * Transform data from video posts into the format required by the template.
	 *
	 * @param {Object} responseItem A single item from the response for videos.
	 * @return {Object} Data in the format required for the template.
	 */
	const transformVideoData = ( responseItem ) => {
		const {
			id,
			link: url,
			title: { rendered: title },
			featured_image: image,
		} = responseItem;

		return {
			id,
			title: decodeEntities( title ),
			url,
			image,
		};
	};

	/**
	 * Fetch posts and update state accordingly.
	 */
	const fetchData = () => {
		const isPlaylist = 'vcategory' === curationTaxonomy;
		// Set up data configured by the theme
		const blockConfig = getBlockConfig( 'pmc/carousel' );
		const postType =
			( blockConfig.video && blockConfig.video.post_type ) ??
			'pmc_top_video';

		let path;
		if ( isPlaylist ) {
			path = addQueryArgs( `/wp/v2/${ postType }`, {
				vcategory: termId,
				per_page: currentStyle?.meta?.perPage,
			} );
		} else {
			path = addQueryArgs( `/pmc/carousel/v1/carousel/${ termId }`, {
				per_page: currentStyle?.meta?.perPage || 4,
			} );
		}

		apiFetch( {
			path,
		} )
			.then( ( response ) => {
				const items = Object.keys( response ).map( ( key ) => {
					return isPlaylist
						? transformVideoData( response[ key ] )
						: response[ key ];
				} );

				setIsLoading( false );
				setCarouselItems( items );
			} )
			.catch( ( e ) => {
				console.error( e ); // eslint-disable-line no-console

				setIsLoading( false );
				setIsError( true );
			} );
	};

	useEffect( () => {
		fetchData();
	}, [] );

	return (
		<>
			{ /* Loading state */ }
			{ isLoading && <Spinner /> }

			{ /* Error */ }
			{ isError && (
				<p>{ __( 'Error loading content.', 'pmc-gutenberg' ) }</p>
			) }

			{ /* Success */ }
			{ ! isLoading && ! isError && Boolean( carouselItems.length ) && (
				<div
					className={ `lrv-a-grid lrv-a-cols3 lrv-a-cols4@desktop lrv-a-glue-parent lrv-u-background-color-grey-lightest lrv-u-padding-a-050 ${ className }` }
				>
					{ className && (
						<div
							className={ `lrv-u-padding-tb-050 lrv-u-padding-lr-1 lrv-u-background-color-brand-primary lrv-u-color-white lrv-a-glue lrv-a-glue--t-0 lrv-a-glue--l-0 lrv-a-font-secondary-xs` }
						>
							{ __( 'Carousel Style: ', 'pmc-gutenberg' ) +
								currentStyle?.label }
						</div>
					) }
					<ItemsPreview
						items={ carouselItems }
						curationTaxonomy={ curationTaxonomy }
						currentStyleName={ currentStyle?.name }
					/>
				</div>
			) }

			{ /* No content */ }
			{ ! isLoading && ! Boolean( carouselItems.length ) && (
				<p>{ __( 'No items to display.', 'pmc-gutenberg' ) }</p>
			) }
		</>
	);
};

export default CurationPreview;
