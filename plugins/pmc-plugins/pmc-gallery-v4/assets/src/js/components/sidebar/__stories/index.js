import React from 'react';
import { storiesOf } from '@storybook/react';
import Sidebar from "../index";

storiesOf( 'Sidebar', module ).add( 'Sidebar Component', () => (
	<div>
		<div style={ { width: '30%', float: 'right' } } id="gallery-container" className="c-gallery" >
			<aside className="c-gallery__sidebar">
				<Sidebar
					title="Slide Title"
					description="Slide Description, it can contain <span>html</span>"
					caption="<p>Slide Caption</p>"
					imageCredit="Image Credit"
					advert="Advert"
				/>
			</aside>
		</div>
	</div>
) );
