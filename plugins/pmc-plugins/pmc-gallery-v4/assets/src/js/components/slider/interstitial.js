import React, { Fragment } from 'react';

import Arrow from './arrow';
import Advert from './../advert';

import { debounce } from 'underscore';

/**
 * Single slide component for gallery.
 */
class Interstitial extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.xDown = null;
		this.yDown = null;
		this.timer = null;
		this.lockSeconds = 2;

		this.onClick = this.onClick.bind( this );
		this.onTouchStart = this.onTouchStart.bind( this );
		this.onTouchMove = this.onTouchMove.bind( this );

		/**
		 * A little delay with debounce to allow the ad to load/reload
		 * and to prevent multiple calls to start the timer if component
		 * updates more than once in a row, due to any reason.
		 */
		this.debouncedStartTimer = debounce( this.startTimer, 100 );

		this.state = {
			remainingSeconds: this.lockSeconds,
			timerShowed: false,
		};
	}

	/**
	 * When the component updates.
	 *
	 * @return {void}
	 */
	componentDidUpdate() {
		if ( this.props.showInterstitial ) {
			this.debouncedStartTimer();
		} else {
			this.resetTimer();
		}
	}

	/**
	 * Start timer for interstitial ad.
	 *
	 * @return {void}
	 */
	startTimer() {
		// Prevents accidental timer start.
		if ( null !== this.timer || this.lockSeconds !== this.state.remainingSeconds ) {
			return;
		}

		this.props.toggleNavigationLock( true );

		this.setState( {
			timerShowed: true,
		} );

		let counter = this.lockSeconds;

		this.timer = setInterval( () => {
			counter--;

			if ( 0 === counter ) {
				clearInterval( this.timer );
				this.timer = null;
				this.props.toggleNavigationLock( false );
			}

			this.setState( {
				remainingSeconds: counter,
			} );
		}, 1000 );
	}

	/**
	 * Reset timer for interstitial ad.
	 *
	 * @return {void}
	 */
	resetTimer() {
		if ( this.lockSeconds !== this.state.remainingSeconds ) {
			this.setState( {
				remainingSeconds: this.lockSeconds,
				timerShowed: false,
			} );

			this.props.toggleNavigationLock( false );
		}
	}

	/**
	 * On Click on arrow or skip ad.
	 *
	 * @param {Object} event Event object.
	 *
	 * @return {void}
	 */
	onClick( event ) {
		event.preventDefault();

		if ( this.props.navigationLocked ) {
			return;
		}

		this.props.toggleInterstitial( false );
	}

	/**
	 * Handles touch start event.
	 *
	 * @param {object} event Event object.
	 *
	 * @return {void}
	 */
	onTouchStart( event ) {
		const touches = event.touches || event.originalEvent.touches;
		const firstTouch = touches[ 0 ] ? touches[ 0 ] : null;

		if ( this.props.navigationLocked ) {
			return;
		}

		if ( ! firstTouch || ! firstTouch.clientX ) {
			return;
		}

		this.xDown = firstTouch.clientX;
		this.yDown = firstTouch.clientY;
	}

	/**
	 * Handles touch move event.
	 *
	 * @param {object} event Event object.
	 *
	 * @return {void}
	 */
	onTouchMove( event ) {
		if ( this.props.navigationLocked || ! this.xDown || ! this.yDown || ! event.touches || ! event.touches[ 0 ] || ! event.touches[ 0 ].clientX ) {
			return;
		}

		const xUp = event.touches[ 0 ].clientX;
		const yUp = event.touches[ 0 ].clientY;

		const xDiff = this.xDown - xUp;
		const yDiff = this.yDown - yUp;

		if ( Math.abs( xDiff ) > Math.abs( yDiff ) ) {
			if ( xDiff > 0 ) {
				this.props.toggleInterstitial( false );
			} else {
				this.props.toggleInterstitial( false );
			}
		}

		this.xDown = null;
		this.yDown = null;
	}

	render() {
		const { advert, i10n, adsProvider, canLoadInterstitialAd, navigationLocked } = this.props;
		const containerClass = ( navigationLocked || ! this.state.timerShowed ) ? 'c-gallery-slide-interstitial__container c-gallery-slide-interstitial--disabled' : 'c-gallery-slide-interstitial__container';
		const time = this.state.remainingSeconds > 9 ? this.state.remainingSeconds : `0${ this.state.remainingSeconds }`;

		const timer = ( seconds ) => {
			return (
				<Fragment>
					<span className="c-gallery-slide-interstitial__skip-in" >{ i10n.skipIn }</span>
					<time className="c-gallery-slide-interstitial__timer">
						<span> 00 : </span>
						<span>{ seconds }</span>
					</time>
				</Fragment>
			);
		};

		return (
			<div role="presentation" onClick={ this.onClick } onTouchStart={ this.onTouchStart } onTouchMove={ this.onTouchMove } className={ containerClass } >
				<a href="/" className="c-gallery-slide-interstitial__skip-link">
					{ ( ! navigationLocked && this.state.timerShowed ) && i10n.skipAd }
					{ ( ! navigationLocked && ! this.state.timerShowed ) && timer( `0${ this.lockSeconds }` ) }
					{ navigationLocked && timer( time ) }
				</a>
				{ canLoadInterstitialAd && (
					<Advert
						advert={ advert }
						wrapperClass="c-gallery-slide__interstitial-advert u-gallery-center"
						adsProvider={ adsProvider }
					/>
				) }
				<Arrow type="interstitial" to="prev" onClick={ this.onClick } arrowClass="u-gallery-arrow u-gallery-arrow--prev" />
				<Arrow type="interstitial" to="next" onClick={ this.onClick } arrowClass="u-gallery-arrow u-gallery-arrow--next" />
			</div>
		);
	}
}

Interstitial.defaultProps = {
	advert: {},
	canLoadInterstitialAd: false,
	i10n: {
		skipAd: '',
		skipIn: '',
	},
};

export default Interstitial;
