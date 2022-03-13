import { InspectorControls } from '@wordpress/block-editor';
import {
	Panel,
	PanelBody,
	PanelRow,
	SelectControl,
	Spinner,
} from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withDispatch, withSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import {
	JW_PLAYER_REST_PROXY_BASE,
	JW_PLAYER_REST_PROXY_DEFAULT_VERSION,
} from './constants';
const playersEntityKind = 'pmcJwPlayer';
const playersEntityName = 'players';

/**
 * Render block's inspector controls.
 *
 * @param {Object}   root0
 * @param {boolean}  root0.isResolving   If entity records are available.
 * @param {string}   root0.playerId      ID of selected player.
 * @param {Array}    root0.players       Array of available players.
 * @param {Function} root0.setAttributes Function to set block's props.
 * @return {JSX.Element} Inspector controls component.
 */
const Render = ( { isResolving, playerId, players, setAttributes } ) => {
	return (
		<InspectorControls>
			<Panel>
				<PanelBody
					title={ __( 'Overrides', 'pmc-gutenberg' ) }
					initialOpen={ false }
				>
					<PanelRow>
						{ isResolving && <Spinner /> }

						{ ! isResolving && (
							<SelectControl
								label={ __( 'Select player', 'pmc-gutenberg' ) }
								help={ __(
									'Override the player used for this video.',
									'pmc-gutenberg'
								) }
								onChange={ ( value ) =>
									setAttributes( { playerId: value } )
								}
								options={ players }
								value={ playerId }
							/>
						) }
					</PanelRow>
				</PanelBody>
			</Panel>
		</InspectorControls>
	);
};

/**
 * Retrieve players from EntityProvider registered below.
 */
const BlockInspectorControls = compose( [
	withSelect( ( select ) => {
		const { getEntityRecords, hasFinishedResolution } = select( 'core' );

		const players = getEntityRecords(
			playersEntityKind,
			playersEntityName
		);

		const isResolving = ! hasFinishedResolution( 'getEntityRecords', [
			playersEntityKind,
			playersEntityName,
		] );

		return { isResolving, players };
	} ),
	withDispatch( ( dispatch ) => {
		const { addEntities } = dispatch( 'core' );

		addEntities( [
			{
				name: playersEntityName,
				kind: playersEntityKind,
				baseURL: `${ JW_PLAYER_REST_PROXY_BASE }/${ JW_PLAYER_REST_PROXY_DEFAULT_VERSION }/players`,
				label: __( 'JW Player Players', 'pmc-gutenberg' ),
				key: 'value',
			},
		] );
	} ),
] )( Render );

export default BlockInspectorControls;
