import React from 'react';
import { LazyLoadImage, trackWindowScroll } from 'react-lazy-load-image-component';
import { isEmpty, has, keys, each, last, debounce } from 'underscore';

/**
 * Responsive image component to be used in all galleries.
 */
class ResponsiveImage extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		if ( isEmpty( this.props.sizes ) ) {
			return;
		}

		this.setupMagnifyImage = this.setupMagnifyImage.bind( this );
		this.updateFigureWidth = this.updateFigureWidth.bind( this );
		this.setupMagnifyImageDebounced = debounce( this.setupMagnifyImage, 50 );
		this.updateFigureWidthDebounced = debounce( this.updateFigureWidth, 100 );

		/**
		 * Viewport and image size mapping.
		 * Must be in ascending order.
		 *
		 * @type {Object}
		 */
		this.sizeMapping = ! isEmpty( this.props.sizeMapping ) ? this.props.sizeMapping : {
			small: {
				viewport: 414,
				imageSize: 414,
				name: 'pmc-gallery-s',
			},
			medium: {
				viewport: 1024,
				imageSize: 800,
				name: 'pmc-gallery-m',
			},
			large: {
				viewport: 1440,
				imageSize: 1024,
				name: 'pmc-gallery-l',
			},
			xLarge: {
				viewport: 2560,
				imageSize: 1440,
				name: 'pmc-gallery-xl',
			},
		};

		this.magnifyImageLinkRef = React.createRef();
		this.figureRef = React.createRef();

		this.updateImageStatus = this.updateImageStatus.bind( this );

		this.state = {
			imageStatus: 'loading',
		};

		this.imageAttributes = this.getImageAttributes();
	}

	/**
	 * When component mounts.
	 *
	 * @return {void}
	 */
	componentDidMount() {
		if ( this.props.magnifyImage ) {
			window.addEventListener( 'resize', this.updateFigureWidthDebounced );
		}
	}

	/**
	 * When component is about to unmount.
	 *
	 * @return {void}
	 */
	componentWillUnmount() {
		if ( this.props.magnifyImage ) {
			window.removeEventListener( 'resize', this.updateFigureWidthDebounced );
		}
	}

	/**
	 * Get image attributes.
	 *
	 * @return {array} Image attributes.
	 */
	getImageAttributes() {
		const attributes = {
			src: '',
			srcSet: '',
			alt: this.props.alt,
			sizes: '',
			width: this.props.fullWidth,
			height: this.props.fullHeight,
		};
		const lastkey = last( keys( this.sizeMapping ) );

		each( this.sizeMapping, ( mapping, sizeKey ) => {
			attributes.sizes += `(max-width: ${ mapping.viewport }px) ${ this.getImageSize( mapping ) }px`;
			attributes.sizes += lastkey === sizeKey ? '' : ', ';
		} );

		const { sizes } = this.props;
		const lastSizeKey = last( keys( sizes ) );

		each( sizes, ( image, sizeKey ) => {
			attributes.srcSet += `${ image.src } ${ image.width }w`;
			attributes.srcSet += lastSizeKey === sizeKey ? '' : ', ';
		} );

		// Set default size.
		attributes.src = has( sizes, 'pmc-gallery-l' ) ? sizes[ 'pmc-gallery-l' ].src : attributes.src;

		attributes.srcSet = attributes.srcSet.trim();

		return attributes;
	}

	/**
	 * Calculate the correct image size for sizes params.
	 *
	 * @param {object} mapping Mapping.
	 *
	 * @return {string} image size without unit.
	 */
	getImageSize( mapping ) {
		let size = '';
		const sizes = this.props.sizes;
		const maxSize = sizes[ 'pmc-gallery-xl' ].width;

		// Get the image size which is closest to mapping.imageSize but not greater than it.
		if ( mapping.imageSize >= maxSize ) {
			size = maxSize;
		} else {
			size = mapping.imageSize;
		}

		return size;
	}

	/**
	 * Update image status.
	 *
	 * @param {string} status Image status.
	 *
	 * @return {void}
	 */
	updateImageStatus( status ) {
		this.setState( {
			imageStatus: status,
		} );
	}

	/**
	 * When the component updates.
	 *
	 * @return {void}
	 */
	componentDidUpdate() {
		this.setupMagnifyImageDebounced();
	}

	/**
	 * Setup magnify image.
	 *
	 * @return {void}
	 */
	setupMagnifyImage() {
		const figure = this.figureRef.current;
		const imageLink = this.magnifyImageLinkRef.current;
		const magnifyActiveClass = 'magnify-active';

		// Bail out if its not magnify image or the image is not ready yet.
		if ( ! figure || ! imageLink || 'loaded' !== this.state.imageStatus || ! jQuery.fn.swinxyzoom ) {
			return;
		}

		/**
		 * jQuery is the last thing I want to use with React however unfortunately here we didn't have an alternative to the
		 * library currently being used on WWD. We first created it in react-image-magnify but it was not as smooth as this one
		 * and it did not have all the features we wanted.
		 */
		const $imageLink = jQuery( imageLink );

		if ( ! $imageLink.hasClass( magnifyActiveClass ) ) {
			this.updateFigureWidth();

			$imageLink.swinxyzoom( {
				mode: 'window',
				size: '100%',
				controls: false,
			} );

			$imageLink.addClass( magnifyActiveClass );
		}
	}

	/**
	 * Update figure width.
	 *
	 * @return {void}
	 */
	updateFigureWidth() {
		const figure = this.figureRef.current;
		const imageLink = this.magnifyImageLinkRef.current;

		if ( ! figure || ! imageLink || 'loaded' !== this.state.imageStatus || ! jQuery.fn.swinxyzoom ) {
			return;
		}

		figure.style.width = figure.offsetWidth + 'px'; // Zoom lib requires width to its parent.

		if ( imageLink.classList.contains( 'magnify-active' ) ) {
			const image = figure.querySelector( '.sxy-zoom-bg' );

			if ( image ) {
				figure.classList.add( 'calculating-image-width' );
				figure.style.width = image.offsetWidth + 'px';
				figure.classList.remove( 'calculating-image-width' );
			}
		}
	}

	render() {
		const { ID, alt, classes, canLoadImage, magnifyImage, sizes, children } = this.props;
		let figureClass = `${ classes.figure } c-gallery-slide--${ this.state.imageStatus } ${ classes.figure }--${ this.state.imageStatus }`;
		const xxlImage = sizes[ 'pmc-gallery-xxl' ];

		figureClass = magnifyImage ? `${ figureClass } c-gallery-slide--magnify-image-figure` : figureClass;

		let image = '';

		if ( canLoadImage ) {
			image = (
				<LazyLoadImage
					scrollPosition={ this.props.scrollPosition }
					afterLoad={ () => {
						this.updateImageStatus( 'loaded' );
					} }
					className={ classes.img }
					{ ...this.imageAttributes }
					alt={ alt }
					delayTime={ 1000 }
					threshold={ 300 }
				/>
			);

			if ( magnifyImage ) {
				image = (
					<a ref={ this.magnifyImageLinkRef } className="c-gallery-slide__magnify-image-link" href={ xxlImage.src }>
						{ image }
					</a>
				);
			}
		}

		return (
			<figure ref={ this.figureRef } key={ ID } role="presentation" onClick={ this.props.onFigureClick } className={ figureClass } >
				{ image }
				{ 'error' === this.state.imageStatus && (
					<span className="u-gallery-broken-image" >
						<span className="u-gallery-broken-image__icon" />
						<span className="u-gallery-broken-image__alt">{ alt }</span>
					</span>
				) }
				{ children }
			</figure>
		);
	}
}

ResponsiveImage.defaultProps = {
	sizes: {},
	alt: '',
	ID: '',
	onFigureClick: () => {},
	classes: {
		figure: '',
		img: '',
	},
	sizeMapping: {},
	canLoadImage: true,
	magnifyImage: false,
	isMediumSize: false,
	children: null,
};

export default trackWindowScroll( ResponsiveImage );
