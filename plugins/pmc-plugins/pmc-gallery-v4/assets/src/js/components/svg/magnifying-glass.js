import React from 'react';  // eslint-disable-line

const MagnifyingGlass = ( props = {} ) => {
	return (
		<svg className="gallery-icon__magnifying-glass gallery-icon" viewBox="612 66 21 20" xmlns="http://www.w3.org/2000/svg">
			<title>Magnifying Glass</title>
			<defs>
				<polygon points="20 19.7 0.3 19.7 0.3 0.1 20 0.1 20 19.7" />
			</defs>
			<g transform="translate(612 66)" fill="none">
				<path d="M18.1 9.9C18.1 14.3 14.5 17.8 10.2 17.8 5.8 17.8 2.3 14.3 2.3 9.9 2.3 5.6 5.8 2 10.2 2 14.5 2 18.1 5.6 18.1 9.9" fill="#FFF" />
				<mask fill="white">
					<use href="#path-1" />
				</mask>
				<path d="M10.2 17.8C5.8 17.8 2.3 14.2 2.3 9.9 2.3 5.6 5.8 2 10.2 2 14.5 2 18 5.6 18 9.9 18 14.2 14.5 17.8 10.2 17.8L10.2 17.8ZM10.2 0.1C4.8 0.1 0.3 4.5 0.3 9.9 0.3 15.3 4.8 19.7 10.2 19.7L18 19.7C19.1 19.7 20 18.8 20 17.8L20 9.9C20 4.5 15.6 0.1 10.2 0.1L10.2 0.1ZM11.2 5L9.2 5 9.2 8.9 5.3 8.9 5.3 10.9 9.2 10.9 9.2 14.8 11.2 14.8 11.2 10.9 15.1 10.9 15.1 8.9 11.2 8.9 11.2 5Z" mask="url(#mask-2)" fill={ props.color } />
			</g>
		</svg>
	);
};

export default MagnifyingGlass;
