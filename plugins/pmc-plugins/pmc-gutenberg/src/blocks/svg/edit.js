/* global pmcGutenbergSvgOptions */

import { BlockControls } from '@wordpress/block-editor';
import {
	Placeholder,
	SelectControl,
	ToolbarButton,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { image as icon } from '@wordpress/icons';
import ServerSideRender from '@wordpress/server-side-render';

const Edit = ( { attributes: { slug }, name, setAttributes } ) => {
	if ( Boolean( slug ) ) {
		return (
			<>
				<ServerSideRender attributes={ { slug } } block={ name } />

				<BlockControls group="other">
					<ToolbarButton
						onClick={ () => {
							setAttributes( { slug: '' } );
						} }
					>
						{ __( 'Replace', 'pmc-gutenberg' ) }
					</ToolbarButton>
				</BlockControls>
			</>
		);
	}

	if ( 1 === pmcGutenbergSvgOptions.length ) {
		return (
			<>
				{ __(
					'There are no SVGs configured for this post type.',
					'pmc-gutenberg'
				) }
			</>
		);
	}

	return (
		<Placeholder icon={ icon } label={ __( 'SVG', 'pmc-gutenberg' ) }>
			<SelectControl
				value={ slug }
				options={ pmcGutenbergSvgOptions }
				onChange={ ( value ) => {
					setAttributes( { slug: value } );
				} }
			/>
		</Placeholder>
	);
};

export default Edit;
