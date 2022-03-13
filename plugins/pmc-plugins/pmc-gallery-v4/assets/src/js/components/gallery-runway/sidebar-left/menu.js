import React from 'react';
import { isEmpty } from 'underscore';

/**
 * Sidebar menu.
 */
class Menu extends React.Component {
	/**
	 * Toggle class on click.
	 *
	 * @param {Object} event Event object.
	 *
	 * @return {void}
	 */
	static onSubMenuClick( event ) {
		event.preventDefault();

		event.currentTarget.parentElement.classList.toggle( 'c-gallery-runway-nav__active' );
	}

	render() {
		const menu = this.props.menu;

		if ( isEmpty( menu ) ) {
			return null;
		}

		return (
			<ul className="c-gallery-runway-nav__menu" >
				{ ! isEmpty( menu.season ) && (
					<li className="c-gallery-runway-nav__menu-item"><a href={ menu.season.link }>{ menu.season.name }</a></li>
				) }

				{ ! isEmpty( menu.city ) && (
					<li className="c-gallery-runway-nav__menu-item"><a href={ menu.city.link }>{ menu.city.name }</a></li>
				) }

				{ ! isEmpty( menu.collections ) && (
					<li className="c-gallery-runway-nav__menu-item collections">
						<a onClick={ this.constructor.onSubMenuClick } className={ ! isEmpty( menu.collections.subMenu ) ? 'c-gallery-runway-nav__submenu-link' : '' } href="/">{ menu.collections.name }</a>
						{ ! isEmpty( menu.collections.subMenu ) && (
							<ul className="c-gallery-runway-nav__sub-menu" >
								{ menu.collections.subMenu.map( ( item ) => {
									const currentClass = item.isCurrent ? 'c-gallery-runway-nav__sub-menu-item--current' : '';
									return <li key={ item.ID } className={ [ 'c-gallery-runway-nav__sub-menu-item', currentClass ].join( ' ' ) }><a href={ item.link }>{ item.name }</a></li>;
								} ) }
							</ul>
						) }
					</li>
				) }

				{ ! isEmpty( menu.associatedRunwayGalleries.subMenu ) && (
					<li className="c-gallery-runway-nav__menu-item associatedRunwayGalleries">
						<a onClick={ this.constructor.onSubMenuClick } className="c-gallery-runway-nav__submenu-link" href="/">{ menu.associatedRunwayGalleries.name }</a>
						<ul className="c-gallery-runway-nav__sub-menu" >
							{ menu.associatedRunwayGalleries.subMenu.map( ( item ) => {
								const currentClass = item.isCurrent ? 'c-gallery-runway-nav__sub-menu-item--current' : '';
								return <li key={ item.ID } className={ [ 'c-gallery-runway-nav__sub-menu-item', currentClass ].join( ' ' ) }><a href={ item.link }>{ item.name }</a></li>;
							} ) }
						</ul>
					</li>
				) }
			</ul>
		);
	}
}

Menu.defaultProps = {
	menu: {
		season: {},
		city: {},
		collections: {},
		associatedRunwayGalleries: {},
	},
};

export default Menu;
