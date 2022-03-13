import React from 'react';
import DOMPurify from 'dompurify';
import { isEmpty } from 'underscore';
import Advert from '../advert/index';

DOMPurify.setConfig( { ADD_ATTR: [ 'target' ] } );

/**
 * Sidebar component for gallery.
 */
class Sidebar extends React.Component {
	render() {
		const { title, description, caption, imageCredit, advert, adsProvider, canLoadAds, timestamp, isMediumSize, galleryTitle, sponsored, sponsoredStyle, styles, isMobile, galleryMobileBottomAdvert, type } = this.props;

		const titleClass = isEmpty( sponsored ) ? 'c-gallery-sidebar__slide-title' : 'c-gallery-sidebar__slide-title c-gallery-header__title-with-sponsored-text';

		const ad = ( isMobile &&
				galleryMobileBottomAdvert ) ? galleryMobileBottomAdvert : advert;

		return ( <div className="c-gallery-sidebar">
			<div className="c-gallery-sidebar__top" style={ styles[ 'horizontal-sidebar-style' ] }>
				{ isMediumSize && ( <h1 className={ titleClass } style={ styles[ 'horizontal-sidebar-gallery-title-style' ] }>
					{ galleryTitle }
					{ ! isEmpty( sponsored ) && ( <span className="c-gallery-header__sponsored"><span className="c-gallery-header__sponsored-text" style={ sponsoredStyle }>{ sponsored }</span></span> ) }
				</h1> ) }
				{ title && 'runway' !== type &&
				( <h2 className="c-gallery-sidebar__title" style={ styles[ 'horizontal-sidebar-slide-title-style' ] } dangerouslySetInnerHTML={ {
					__html: DOMPurify.sanitize( title ),
				} } /> ) }
				{ ! isEmpty( timestamp ) && timestamp.date && 'runway' !== type && ( <time className="c-gallery-sidebar__timestamp entry-date published" style={ styles[ 'horizontal-sidebar-timestamp-style' ] } dateTime={ timestamp.datetime ||
						'' }>{ timestamp.date }</time> ) }
				{ description && 'runway' !== type && ( <div className="c-gallery-sidebar__description" style={ styles[ 'horizontal-sidebar-description-style' ] } dangerouslySetInnerHTML={ { __html: DOMPurify.sanitize( description ) } } /> ) }
				{ caption && 'runway' !== type && ( <div className="c-gallery-sidebar__caption" style={ styles[ 'horizontal-sidebar-caption-style' ] } dangerouslySetInnerHTML={ { __html: DOMPurify.sanitize( caption ) } } /> ) }
				{ imageCredit && 'runway' !== type &&
				( <p className="c-gallery-sidebar__image-credit" style={ styles[ 'horizontal-sidebar-image-credit-style' ] }>{ imageCredit }</p> ) }
			</div>
			<div className="c-gallery-sidebar__bottom">
				{ canLoadAds && <Advert advert={ ad } adsProvider={ adsProvider } /> }
			</div>
		</div> );
	}
}

Sidebar.defaultProps = {
	title: '',
	description: '',
	caption: '',
	imageCredit: '',
	advert: '',
	adsProvider: '',
	isMediumSize: false,
	timestamp: {},
	galleryTitle: '',
	sponsored: '',
	sponsoredStyle: {},
	styles: {},
	galleryMobileBottomAdvert: '',
	isMobile: false,
};

export default Sidebar;
