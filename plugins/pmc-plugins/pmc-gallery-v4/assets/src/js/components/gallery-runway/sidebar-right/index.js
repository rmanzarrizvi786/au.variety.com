import React from 'react';

import Sidebar from './../../sidebar';
import SocialIcons from './../../social-icons';

/**
 * Sidebar right for runway gallery.
 */
class SidebarRight extends React.Component {
	render() {
		const { title, socialIcons, twitterUserName, pinterestUrl, location, type } = this.props;

		return (
			<aside className="c-gallery-runway__sidebar-right">
				<SocialIcons
					location={ location }
					slideTitle={ title }
					socialIcons={ socialIcons }
					twitterUserName={ twitterUserName }
					pinterestUrl={ pinterestUrl }
					linkClassPrefix="c-gallery-social-icons__icon"
					ulClassName="c-gallery-social-icons"
					liClassName="c-gallery-social-icons__icon"
					type={ type }
				/>
				<Sidebar
					{ ...this.props }
				/>
			</aside>
		);
	}
}

SidebarRight.defaultProps = {
	title: '',
	socialIcons: '',
	twitterUserName: '',
	pinterestUrl: '',
	type: '',
};

export default SidebarRight;
