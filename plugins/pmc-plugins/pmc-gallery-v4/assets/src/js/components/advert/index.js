/* globals blogherads */

import React from 'react';

/**
 * Sidebar ad component for gallery.
 */
class Advert extends React.Component {
	/**
	 * Prevent component from updating as the ads will be reloaded externally
	 * and updating the ad component might cause issues.
	 *
	 * @return {boolean} True or false.
	 */
	shouldComponentUpdate() {
		return false;
	}

	/**
	 * On component mount.
	 *
	 * @return {void}
	 */
	componentDidMount() {
		this.mayBeExecuteBoomerangAdScript();
	}

	/**
	 * Executes ad scripts the ad mark up has it.
	 *
	 * @return {void}
	 */
	mayBeExecuteBoomerangAdScript() {
		const { advert, adsProvider } = this.props;

		if (
			'boomerang' !== adsProvider ||
			'undefined' === typeof blogherads ||
			'object' !== typeof blogherads.adq ||
			! advert.data
		) {
			return;
		}

		advert.data.forEach( function( ad ) {
			blogherads.adq.push( function() {
				const slot = blogherads.defineSlot( ad.displayType, ad.divId );

				if ( 'string' === typeof ad.zone && '' !== ad.zone ) {
					slot.setSubAdUnitPath( ad.zone );
				}
				if ( 'object' === typeof ad.targeting ) {
					// Use Object.keys for data compatible with object & array
					Object.keys( ad.targeting ).forEach( function( item ) {
						if ( ad.targeting[ item ].key && ad.targeting[ item ].value ) {
							slot.addTargeting( ad.targeting[ item ].key, ad.targeting[ item ].value );
						}
					} );
				}
				if ( 'string' === typeof ad.lazyLoad && 'yes' === ad.lazyLoad ) {
					slot.setLazyLoadMultiplier( 2 );
				}
				if ( Array.isArray( ad.sizes ) ) {
					slot.addSize( ad.sizes );
				}

				// To prevent slot from competing with interstitial and affecting lazy loading
				// Block the gallery ad with failsafe = true, to timeout after 1.5s
				// @see pmcDisplayAds.display
				slot.blockDisplay( 'gallery', true );
				slot.display();
			} );
		} );
	}

	render() {
		const { advert, wrapperClass } = this.props;

		if ( ! advert.html ) {
			return null;
		}

		return (
			<div className={ wrapperClass } dangerouslySetInnerHTML={ {
				__html: advert.html,
			} } />
		);
	}
}

Advert.defaultProps = {
	advert: {
		adCode: '',
		boomerang: {
			id: '',
			displayType: '',
			targeting: {},
		},
	},
	adsProvider: 'boomerang',
	wrapperClass: 'c-gallery-sidebar__advert',
};

export default Advert;
