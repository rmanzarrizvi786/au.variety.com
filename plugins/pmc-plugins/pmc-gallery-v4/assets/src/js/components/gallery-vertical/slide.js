import React from 'react';
import DOMPurify from 'dompurify';
import SlideAlbumLayout from './slide-templates/slide-album';
import SlideFeaturedImageLayout from './slide-templates/slide-featured-image';
import SlideDefaultLayout from './slide-templates/slide-default';

DOMPurify.setConfig( { ADD_ATTR: [ 'target' ] } );

class Slide extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.state = {
			displaySocialIcons: ! props.socialIconsUseMenu,
		};

		this.toggleSocialIcons = this.toggleSocialIcons.bind( this );
	}

	/**
	 * Toggle social icons.
	 *
	 * @param {Object}  event Click event.
	 * @param {Boolean} state State
	 *
	 * @return {void}
	 */
	toggleSocialIcons( event, state ) {
		event.preventDefault();
		const displaySocialIcons = 'undefined' === typeof state ? ! this.state.displaySocialIcons : state;

		this.setState( {
			displaySocialIcons,
		} );
	}

	render() {
		const { template } = this.props;

		if ( 'item-album' === template ) {
			return (
				<SlideAlbumLayout
					{ ...this.props }
				/>
			);
		}

		if ( 'item-featured-image' === template ) {
			return (
				<SlideFeaturedImageLayout
					{ ...this.props }
				/>
			);
		}

		return (
			<SlideDefaultLayout
				{ ...this.props }
			/>
		);
	}
}

Slide.defaultProps = {
	i10n: {
		vertical: {
			photo: '',
		},
	},
	image: '',
	alt: '',
	image_credit: '',
	title: '',
	description: '',
	caption: '',
	socialIcons: {},
	socialIconsUseMenu: {},
	twitterUserName: '',
	slideIndex: 0,
	sizes: {},
};

export default Slide;
