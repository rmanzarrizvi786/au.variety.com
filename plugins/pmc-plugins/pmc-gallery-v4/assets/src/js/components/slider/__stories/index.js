import React from 'react';
import { storiesOf } from '@storybook/react';
import Slider from '../index';

const dummyGallery = [
	{
		caption: '',
		image: 'https://via.placeholder.com/1500',
		slug: '',
		title: '',
		url: '',
	},
	{
		caption: '',
		image: 'https://via.placeholder.com/1200',
		slug: '',
		title: '',
		url: '',
	}
];

storiesOf( 'Slider', module ).add( 'Slider Component', () => (
	<div>
		<div id="gallery-container" className="c-gallery" >
			<main className="c-gallery__main" style={ { display: 'block' } } >
				<div className="c-gallery__slider">
					<Slider
						slides={ dummyGallery }
						i10n={ {} }
						onSlideChange={ () => {} }
						galleryIndex={ 0 }
					/>
				</div>
			</main>
		</div>
	</div>
) );
