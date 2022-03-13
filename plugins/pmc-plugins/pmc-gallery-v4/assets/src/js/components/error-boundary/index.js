import React from 'react';

/**
 * Error boundary component so gallery doesn't break other things in event of an error.
 *
 * @see https://reactjs.org/docs/error-boundaries.html
 */
class ErrorBoundary extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.state = { hasError: false };
	}

	/**
	 * Get derived state from error.
	 *
	 * @return {{hasError: boolean}} error object.
	 */
	static getDerivedStateFromError() {
		return { hasError: true };
	}

	/**
	 * When component catches error.
	 *
	 * @param {string} error Error.
	 * @param {string} info Error info.
	 *
	 * @return {void}
	 */
	componentDidCatch( error, info ) {
		console.warn( error, info ); // eslint-disable-line
	}

	render() {
		if ( this.state.hasError ) {
			return null;
		}

		return this.props.children;
	}
}

export default ErrorBoundary;
