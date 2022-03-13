import React from 'react';

import Modal from './modal';

import PhotoSwipe from 'photoswipe/dist/photoswipe';
import { default as PhotoSwipeUI } from 'photoswipe/dist/photoswipe-ui-default';

class ZoomModal extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.zoomModalRef = React.createRef();
		this.openClass = 'pmc-gallery-zoom-modal-open';

		this.zoom = {};

		this.options = {
			index: 0,
			escKey: true,
			closeOnScroll: false,
			history: false,
			shareEl: false,
			counterEl: false,
			arrowEl: false,
			clickToCloseNonZoomable: false,
			tapToToggleControls: false,
		};

		this.items = [
			{
				src: this.props.image.src,
				w: this.props.image.width,
				h: this.props.image.height,
			},
		];
	}

	/**
	 * When component mounts.
	 *
	 * @return {void}
	 */
	componentDidMount() {
		this.root = document.querySelector( 'html' );
		this.root.classList.add( this.openClass );

		this.createZoom();
	}

	/**
	 * Create zoom.
	 *
	 * @return {void}
	 */
	createZoom() {
		const modal = this.zoomModalRef.current;

		if ( modal ) {
			this.zoom = new PhotoSwipe( modal, PhotoSwipeUI, this.items, this.options );
			this.zoom.init();

			// Photo-swipe calls destroy() after close event to we do not have to worry about unbinding.
			this.zoom.listen( 'close', () => {
				this.props.toggleZoomModal( null, false );
				this.root.classList.remove( this.openClass );
			} );
		}
	}

	render() {
		return (
			<Modal>
				<div role="dialog" ref={ this.zoomModalRef } className="pswp c-gallery-zoom-modal" aria-hidden="true">
					<div className="pswp__bg" />
					<div className="pswp__scroll-wrap">
						<div className="pswp__container">
							<div className="pswp__item" />
							<div className="pswp__item" />
							<div className="pswp__item" />
						</div>
						<div className="pswp__ui pswp__ui--hidden">
							<div className="pswp__top-bar">
								<div className="pswp__counter" />
								<button className="pswp__button pswp__button--close" title="Close (Esc)" />
								<button className="pswp__button pswp__button--fs" title="Toggle fullscreen" />
								<button className="pswp__button pswp__button--zoom" title="Zoom in/out" />
								<div className="pswp__preloader">
									<div className="pswp__preloader__icn">
										<div className="pswp__preloader__cut">
											<div className="pswp__preloader__donut" />
										</div>
									</div>
								</div>
							</div>
							<div className="pswp__caption">
								<div className="pswp__caption__center" />
							</div>
						</div>
					</div>
				</div>
			</Modal>
		);
	}
}

ZoomModal.defaultProps = {
	image: {},
	alt: '',
};

export default ZoomModal;
