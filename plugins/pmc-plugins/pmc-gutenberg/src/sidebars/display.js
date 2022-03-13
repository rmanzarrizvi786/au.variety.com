import { __ } from '@wordpress/i18n';
import { Panel, PanelBody, PanelRow } from '@wordpress/components';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { image } from '@wordpress/icons';
import { registerPlugin } from '@wordpress/plugins';

/* Internal Dependencies */
import ExcerptOverride from './display/excerpt-override';
import TitleOverride from './display/title-override';
import ExcludeFromHomepage from './display/exclude-from-homepage';
import ExcludeFromLandingPages from './display/exclude-from-landing-pages';
import ExcludeFromSectionFronts from './display/exclude-from-section-fronts';
import ExcludeFromRiver from './display/exclude-from-river';

const FieldOverrides = () => {
	return (
		<Panel>
			<PanelBody
				title={ __( 'Field Overrides', 'pmc-gutenberg' ) }
				initialOpen={ true }
			>
				<PanelRow>
					<TitleOverride />
				</PanelRow>
				<PanelRow>
					<ExcerptOverride />
				</PanelRow>
			</PanelBody>
		</Panel>
	);
};

const PostOptions = () => {
	return (
		<Panel>
			<PanelBody
				title={ __( 'Exclusions', 'pmc-gutenberg' ) }
				initialOpen={ true }
			>
				<PanelRow>
					<ExcludeFromHomepage />
				</PanelRow>
				<PanelRow>
					<ExcludeFromLandingPages />
				</PanelRow>
				<PanelRow>
					<ExcludeFromRiver />
				</PanelRow>
				<PanelRow>
					<ExcludeFromSectionFronts />
				</PanelRow>
			</PanelBody>
		</Panel>
	);
};

// Sidebar
const DisplaySidebar = () => {
	const sidebarName = 'pmc-display';
	const sidebarLabel = __( 'Display', 'pmc-gutenberg' );

	return (
		<>
			<PluginSidebar
				name={ sidebarName }
				title={ sidebarLabel }
				icon={ image }
			>
				<PostOptions />
				<FieldOverrides />
			</PluginSidebar>
			<PluginSidebarMoreMenuItem target={ sidebarName } icon={ image }>
				{ sidebarLabel }
			</PluginSidebarMoreMenuItem>
		</>
	);
};
registerPlugin( 'pmc-display', { render: DisplaySidebar } );
