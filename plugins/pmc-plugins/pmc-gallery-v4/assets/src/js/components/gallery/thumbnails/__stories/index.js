import React from 'react';
import { storiesOf } from '@storybook/react';
import Thumbnails from "../index";

let dummyGallery = [];

for ( let i = 0; i <= 10; i++ ) {
	dummyGallery.push( {
		caption: '',
		image: 'https://via.placeholder.com/200',
		slug: '',
		title: '',
		url: '',
	} );
}

storiesOf( 'Thumbnails', module ).add( 'Thumbnails Component', () => (
	<div>
		<div id="gallery-container" className="c-gallery" style={ { width: '20%' } } >
			<main className="c-gallery__main" style={ { display: 'flex' } } >
				<div className="c-gallery__thumbnails">
					<Thumbnails
						thumbnails={ dummyGallery }
						i10n={ {
							thumbnail: "Thumbnails"
						} }
					/>
				</div>
			</main>
		</div>
	</div>
) );
