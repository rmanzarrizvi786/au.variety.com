import React from 'react';
import { debounce } from 'underscore';

class ListNavBar extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.renderElementRef = React.createRef();

		this.getProgressBarScale = this.getProgressBarScale.bind( this );
		this.getRangesJSX = this.getRangesJSX.bind( this );
		this.updateRangeData = this.updateRangeData.bind( this );
		this.scrollToIndex = this.scrollToIndex.bind( this );
		this.onResizeDebounced = debounce( this.onResize, 100 );

		this.state = {
			rangeData: null,
		};
	}

	/**
	 * When component mounts.
	 *
	 * @return {void}
	 */
	componentDidMount() {
		this.updateRangeData();
		window.addEventListener( 'resize', this.onResize.bind( this ) );
	}

	/**
	 * When component is about to unmount.
	 *
	 * @return {void}
	 */
	componentWillUnmount() {
		window.removeEventListener( 'resize', this.onResize.bind( this ) );
	}

	/**
	 * Render Component.
	 *
	 * @return {*} Component object.
	 */
	render() {
		return (
			<>
				<div
					{ ...this.props.listNavBarConfig.progressBarElementAttributes }
					style={ {
						...this.props.listNavBarConfig.progressBarElementAttributes.style,
						transform: this.getProgressBarScale(),
					} }
				/>
				<div
					ref={ this.renderElementRef }
					{ ...this.props.listNavBarConfig.renderElementAttributes }
				>
					{ this.getRangesJSX() }
				</div>
			</>
		);
	}

	/**
	 * On resize
	 *
	 * @return {void}
	 */
	onResize() {
		this.updateRangeData();
	}

	/**
	 * Get progress bar scale
	 *
	 * @return {string} scaleX(decimal)
	 */
	getProgressBarScale() {
		const indexCurrent = parseInt( this.props.intersectSlideIndex );

		if ( ! indexCurrent ) {
			return 'scaleX(0)';
		}
		return `scaleX(${ ( indexCurrent + 1 ) / ( this.props.galleryCount ) })`;
	}

	/**
	 * Find best matching range data based on current viewport width
	 *
	 * @return {void}
	 */
	updateRangeData() {
		const parentElement = document.querySelector( this.props.listNavBarConfig.parentElementQuerySelector );
		const renderElement = this.renderElementRef.current;
		let viewportWidth = renderElement ? renderElement.offsetWidth : parentElement.offsetWidth;

		if ( viewportWidth < 300 ) {
			viewportWidth = window.innerWidth;
		}

		const closestMinWidth = Object
			.keys( this.props.listNavBarConfig.generatedRanges )
			.sort( ( a, b ) => b - a ) // Reverse numerical sort
			.find( minWidth => {
				return minWidth < viewportWidth;
			} );

		this.setState( {
			rangeData: this.props.listNavBarConfig.generatedRanges[ closestMinWidth ],
		} );
	}

	/**
	 * Get ranges JSX
	 *
	 * @return {JSX} ranges anchor elements
	 */
	getRangesJSX() {
		const indexCurrent = parseInt( this.props.intersectSlideIndex );

		return (
			Array.isArray( this.state.rangeData ) && this.state.rangeData.map( ( thisRange, thisRangeIndex ) => {
				const isActive = ( indexCurrent >= thisRange.indexStart && indexCurrent <= thisRange.indexEnd );

				return (
					<a
						onClick={ ( event ) => this.scrollToIndex( event, thisRange.indexStart ) }
						{ ...this.props.listNavBarConfig.rangeElementAttributes }
						{ ...( isActive ? this.props.listNavBarConfig.activeRangeElementAttributes : {} ) }
						style={ {
							...this.props.listNavBarConfig.rangeElementAttributes.style,
							...( isActive ? this.props.listNavBarConfig.activeRangeElementAttributes.style : {} ),
						} }
						key={ thisRangeIndex }
						href={ thisRange.link }
					>
						{ thisRange.positionDisplayStart } - { thisRange.positionDisplayEnd }
					</a>
				);
			} )
		);
	}

	/**
	 * Scroll to item index
	 *
	 * @param {object} event Event object.
	 * @param {number} index item index
	 *
	 * @return {void}
	 */
	scrollToIndex( event, index ) {
		const target = document.querySelector( `[data-slide-index="${ index }"]` );
		if ( target ) {
			event.preventDefault();
			window.scrollTo( {
				top: target.offsetTop,
			} );
		}
	}
}

export default ListNavBar;
