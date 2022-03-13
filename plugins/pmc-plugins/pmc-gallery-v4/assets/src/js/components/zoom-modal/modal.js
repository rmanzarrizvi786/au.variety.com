import React from 'react';
import { createPortal } from 'react-dom';

/**
 * Modal component which is supposed to live outside of the app.
 * The modal root has been added in the footer using wp_footer WP hook.
 * Portals provide a first-class way to render children into a DOM node that exists outside the DOM hierarchy of the parent component.
 *
 * @see https://reactjs.org/docs/portals.html
 */
class Modal extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.modalRoot = document.getElementById( 'pmc-gallery-modal' );
		this.el = document.createElement( 'div' );
	}

	/**
	 * After component mounts
	 */
	componentDidMount() {
		/**
		 * The portal element is inserted in the DOM tree after
		 * the Modal's children are mounted, meaning that children
		 * will be mounted on a detached DOM node. If a child
		 * component requires to be attached to the DOM tree
		 * immediately when mounted, for example to measure a
		 * DOM node, or uses 'autoFocus' in a descendant, add
		 * state to Modal and only render the children when Modal
		 * is inserted in the DOM tree.
		 */

		if ( this.modalRoot ) {
			this.modalRoot.appendChild( this.el );
		}
	}

	/**
	 * When the component is about to un mount.
	 */
	componentWillUnmount() {
		if ( this.modalRoot ) {
			this.modalRoot.removeChild( this.el );
		}
	}

	render() {
		return createPortal(
			this.props.children,
			this.el,
		);
	}
}

export default Modal;
