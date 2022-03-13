/* globals ga, pmc */

import React from 'react';
import deepmerge from 'deepmerge';
import DOMPurify from 'dompurify';
import { addQueryArgs } from '@wordpress/url';
import { debounce, isEmpty, map, each, has, extend, isString, isObject } from 'underscore';

// Import svg icons.
import Facebook from './../svg/facebook';
import Pinterest from './../svg/pinterest';
import Twitter from './../svg/twitter';
import Tumblr from './../svg/tumblr';
import WhatsApp from './../svg/whatsapp';
import Reddit from './../svg/reddit';
import Linkedin from './../svg/linkedin';

class SocialIcons extends React.Component {
	/**
	 * Constructor
	 *
	 * @param {Object} props Component props.
	 *
	 * @return {void}
	 */
	constructor( props ) {
		super( props );

		this.getIcons = this.getIcons.bind( this );
		this.onIconClick = this.onIconClick.bind( this );
		this.onMouseOverDebounced = debounce( this.constructor.onIconMouseOver, 500 );
	}

	/**
	 * Get default Icons.
	 *
	 * @return {Array} Social icons array.
	 */
	getDefaultIcons() {
		const url = this.props.location;
		const title = this.props.slideTitle;
		const prefix = this.props.linkClassPrefix;

		return {
			facebook: {
				icon: <Facebook />,
				shareLink: 'https://www.facebook.com/sharer/sharer.php',
				params: {
					u: url,
					title,
				},
				attributes: {
					target: '_blank',
					rel: 'noopener noreferrer',
					className: `${ prefix }-facebook`,
				},
				shareable: '',
			},
			twitter: {
				icon: <Twitter />,
				shareLink: 'https://twitter.com/intent/tweet/',
				params: {
					text: title,
					url,
					via: this.props.twitterUserName,
				},
				attributes: {
					target: '_blank',
					rel: 'noopener noreferrer',
					className: `${ prefix }-twitter`,
				},
				shareable: '',
			},
			pinterest: {
				icon: <Pinterest />,
				shareLink: 'http://pinterest.com/pin/create/button/',
				params: {
					url,
				},
				attributes: {
					target: '_blank',
					rel: 'noopener noreferrer',
					className: `${ prefix }-pinterest`,
					'data-pin-custom': true,
					'data-pin-log': 'button_pinit',
					'data-pin-href': this.props.pinterestUrl,
				},
				shareable: this.props.pinterestUrl, // If this is set above params will not be used.
			},
			tumblr: {
				icon: <Tumblr />,
				shareLink: 'https://www.tumblr.com/widgets/share/tool/preview',
				params: {
					shareSource: 'legacy',
					canonicalUrl: '',
					url,
					posttype: 'link',
					title,
				},
				attributes: {
					target: '_blank',
					rel: 'noopener noreferrer',
					className: `${ prefix }-tumblr`,
				},
				shareable: '',
			},
			whatsapp: {
				icon: <WhatsApp />,
				shareLink: 'https://wa.me/',
				params: {
					text: `${ title } ${ url }`,
				},
				attributes: {
					target: '_blank',
					rel: 'noopener noreferrer',
					className: `${ prefix }-whatsapp`,
				},
				shareable: '',
			},
			reddit: {
				icon: <Reddit />,
				shareLink: 'http://www.reddit.com/submit',
				params: {
					url,
					title,
				},
				attributes: {
					target: '_blank',
					rel: 'noopener noreferrer',
					className: `${ prefix }-reddit`,
				},
				shareable: '',
			},
			linkedin: {
				icon: <Linkedin />,
				shareLink: 'http://www.linkedin.com/shareArticle',
				params: {
					mini: true,
					url,
					title,
					summary: '',
					source: this.props.twitterUserName,
				},
				attributes: {
					target: '_blank',
					rel: 'noopener noreferrer',
					className: `${ prefix }-linkedin`,
				},
				shareable: '',
			},
		};
	}

	/**
	 * Get user icons ready to be rendered extended by default configs.
	 *
	 * @return {Object} Icons.
	 */
	getIcons() {
		const icons = {};
		const defaultIcons = this.getDefaultIcons();

		if ( isEmpty( this.props.socialIcons ) ) {
			return icons;
		}

		each( this.props.socialIcons, ( config, name ) => {
			const _config = isEmpty( config ) || config instanceof Array ? {} : extend( {}, config );
			icons[ name ] = has( defaultIcons, name ) ? deepmerge( defaultIcons[ name ], _config ) : _config;
			icons[ name ].shareable = icons[ name ].shareable ? icons[ name ].shareable : addQueryArgs( icons[ name ].shareLink, icons[ name ].params );
		} );

		return icons;
	}

	/**
	 * On social icon click.
	 *
	 * @param {string} name Social icon name.
	 *
	 * @return {void}
	 */
	onIconClick( name ) {
		if ( 'undefined' !== typeof ga ) {
			// To convert absolute permalink to relative
			const iconUrl = document.createElement( 'a' );
			iconUrl.href = this.props.location;

			ga( 'send', 'social', name, 'click', iconUrl.pathname );

			// See if an additional social tracking event should be generated.
			if ( 'undefined' !== typeof pmc && pmc.hooks ) {
				const event = pmc.hooks.apply_filters( 'pmc_event_tracking_social_data', null, name );

				if ( null !== event ) {
					ga( 'send', {
						hitType: event.hitType,
						eventCategory: event.eventCategory,
						eventAction: event.eventAction,
						eventLabel: event.eventLabel,
						nonInteraction: event.nonInteraction,
					} );
				}
			}
		}
	}

	/**
	 * On icon mouse over
	 *
	 * @param {string} name Social icon name.
	 *
	 * @return {void}
	 */
	static onIconMouseOver( name ) {
		if ( 'undefined' !== typeof ga ) {
			ga( 'send', 'event', 'social_bar', 'mouse-over', name, 1, { nonInteraction: true } );
		}
	}

	render() {
		const socialIcons = this.getIcons();

		if ( isEmpty( socialIcons ) ) {
			return null;
		}

		return (
			<ul className={ this.props.ulClassName } >
				{ ! isEmpty( socialIcons ) && (
					map( socialIcons, ( socialIcon, name ) => {
						return (
							<li key={ name } className={ this.props.liClassName } >
								<a onClick={ () => this.onIconClick( name ) } onMouseOver={ () => this.onMouseOverDebounced( name ) } onFocus={ () => this.onMouseOverDebounced( name ) } { ...socialIcon.attributes } href={ socialIcon.shareable }>
									{ isString( socialIcon.icon ) && (
										<span dangerouslySetInnerHTML={ {
											__html: DOMPurify.sanitize( socialIcon.icon ),
										} } />
									) }
									{ isObject( socialIcon.icon ) && socialIcon.icon }
								</a>
							</li>
						);
					} )
				) }
				{ this.props.children }
			</ul>
		);
	}
}

SocialIcons.defaultProps = {
	socialIcons: {
		facebook: {},
		twitter: {},
		tumblr: {},
		pinterest: {},
	},
	location: '',
	slideTitle: '',
	linkClassPrefix: 'u-gallery-social-icon u-gallery',
	ulClassName: '',
	liClassName: '',
	twitterUserName: '',
	pinterestUrl: '',
	type: '',
};

export default SocialIcons;
