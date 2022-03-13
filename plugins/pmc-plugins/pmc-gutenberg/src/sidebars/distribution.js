import { get } from 'lodash';

import { __ } from '@wordpress/i18n';
import { Panel, PanelBody, PanelRow } from '@wordpress/components';
import { select } from '@wordpress/data';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { globe } from '@wordpress/icons';
import { registerPlugin } from '@wordpress/plugins';

/* Internal Dependencies */
import CanonicalOverride from './distribution/canonical-override';
import ExactTargetBNA from './distribution/exacttarget-bna';
import SeoTitleOverride from './distribution/seo-title-override';
import SeoDescriptionOverride from './distribution/seo-description-override';
import ExcludeFromGoogleNews from './distribution/exclude-from-google-news';

const ExclusionsPanel = () => {
	return (
		<Panel>
			<PanelBody
				title={ __( 'Exclusions', 'pmc-gutenberg' ) }
				initialOpen={ false }
			>
				<PanelRow>
					<ExcludeFromGoogleNews />
				</PanelRow>
			</PanelBody>
		</Panel>
	);
};

const SEOPanel = () => {
	return (
		<Panel>
			<PanelBody
				title={ __( 'SEO', 'pmc-gutenberg' ) }
				initialOpen={ true }
			>
				<PanelRow>
					<SeoTitleOverride />
				</PanelRow>
				<PanelRow>
					<SeoDescriptionOverride />
				</PanelRow>
				<PanelRow>
					<CanonicalOverride />
				</PanelRow>
			</PanelBody>
		</Panel>
	);
};

const ExactTargetPanel = () => {
	const { getCurrentPost } = select( 'core/editor' );
	const supported = get(
		getCurrentPost(),
		[ '_links', 'pmc:exact-target-supported' ],
		false
	);

	if ( ! supported ) {
		return null;
	}

	return (
		<Panel>
			<PanelBody
				title={ __( 'Breaking News Alerts', 'pmc-gutenberg' ) }
				initialOpen={ false }
			>
				<ExactTargetBNA />
			</PanelBody>
		</Panel>
	);
};

// Sidebar
const DistributionSidebar = () => {
	const sidebarName = 'pmc-distribution';
	const sidebarLabel = __( 'Distribution', 'pmc-gutenberg' );

	return (
		<>
			<PluginSidebar
				name={ sidebarName }
				title={ sidebarLabel }
				icon={ globe }
			>
				<SEOPanel />
				<ExclusionsPanel />
				<ExactTargetPanel />
			</PluginSidebar>
			<PluginSidebarMoreMenuItem target={ sidebarName } icon={ globe }>
				{ sidebarLabel }
			</PluginSidebarMoreMenuItem>
		</>
	);
};
registerPlugin( 'pmc-distribution', { render: DistributionSidebar } );
