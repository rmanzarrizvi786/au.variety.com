import React from 'react';  // eslint-disable-line

const Share = props => {
	return (
		<svg
			className="gallery-icon__share gallery-icon"
			style={ props.style }
			viewBox="0 0 19 21"
			xmlns="http://www.w3.org/2000/svg"
		>
			<title>Share</title>
			<path fillRule="evenodd" d="M15.23 14.218c-.76 0-1.44.3-1.96.77l-7.13-4.15c.05-.23.09-.46.09-.7 0-.24-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7l-7.05 4.11c-.54-.5-1.25-.81-2.04-.81-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92 0-1.61-1.31-2.92-2.92-2.92z" />
		</svg>
	);
};

export default Share;
