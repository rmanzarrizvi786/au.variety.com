import React from 'react';
import { storiesOf } from '@storybook/react';
import Header from './../index';

storiesOf( 'Header', module ).add( 'Header Component', () => (
	<div>
		<div id="gallery-container" className="c-gallery" >
			<header className="c-gallery__header">
				<Header
					siteTitle="Site Title"
					siteUrl="http://example.com"
					i10n={ {
						backToArticle: "Back to Article"
					} }
					slideTitle="Slide Title"
				/>
			</header>
		</div>
		<p>👉 Site logo can be added from the theme or it shows site title as fallback.</p>
		<p>👉 The slide title changes with slide change.</p>
		<p>👉 Social icons can be configured from the theme.</p>
	</div>
) );
