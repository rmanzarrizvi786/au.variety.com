import React, { Fragment } from 'react';

import { isEmpty, contains } from 'underscore';

import PinIt from './pintit';
import ResponsiveImage from './../responsive-image';

/**
 * Single slide component for gallery.
 */
class Slide extends React.Component {
	render() {
		const { ID, alt, slideIndex, slideIndexesToLoad, pinit, pinterestUrl, sizes, classes, magnifyImage, isMediumSize } = this.props;
		const canLoadImage = contains( slideIndexesToLoad, slideIndex );

		if ( isEmpty( sizes ) ) {
			return null;
		}

		return (
			<Fragment>
				<ResponsiveImage
					fullWidth={ this.props.fullWidth }
					fullHeight={ this.props.fullHeight }
					canLoadImage={ canLoadImage }
					magnifyImage={ magnifyImage }
					sizes={ sizes }
					isMediumSize={ isMediumSize }
					onFigureClick={ this.props.onSlideClick }
					alt={ alt }
					ID={ ID }
					classes={ classes }
				/>
				{ pinit && (
					<PinIt key={ 'pinterest-' + ID } pinterestUrl={ pinterestUrl } />
				) }
			</Fragment>
		);
	}
}

Slide.defaultProps = {
	sizes: {},
	alt: '',
	ID: '',
	slideIndex: 0,
	slideIndexesToLoad: [],
	pinit: false,
	pinterestUrl: '',
	isMediumSize: false,
	classes: {
		figure: 'c-gallery-slide',
		img: 'c-gallery-slide__image',
	},
};

export default Slide;
