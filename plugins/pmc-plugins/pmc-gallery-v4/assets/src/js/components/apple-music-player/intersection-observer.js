import { useEffect } from 'react';

function useIntersectionObserver( {
	musicPlayerElement,
	onVisible,
	enabled,
} ) {
	useEffect( () => {
		if ( true !== enabled ) return;

		const options = {
			root: null, // viewport,
			rootMargin: '0px',
			threshold: 0.1, // 1.0
		};

		const callback = ( entries ) => {
			entries.forEach( entry => {
				if ( entry.isIntersecting ) {
					onVisible();
				}
			} );
		};

		try {
			// https://developer.mozilla.org/docs/Web/API/Intersection_Observer_API
			const observer = new window.IntersectionObserver( callback, options );
			observer.observe( musicPlayerElement.current );

			return () => {
				observer.unobserve( musicPlayerElement.current );
			};
		} catch ( e ) {
			console.error( e ); // eslint-disable-line
		}
	}, [ musicPlayerElement, onVisible, enabled ] );
}

export {
	useIntersectionObserver,
};
