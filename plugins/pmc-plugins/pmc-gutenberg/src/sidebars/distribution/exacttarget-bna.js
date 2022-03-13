/**
 * ExactTarget Breaking News Alerts.
 *
 * Renders two components--one for the Distribution sidebar and another for the
 * prepublish panel--that allow authorized users to configure and send BNAs to
 * a site's configured lists.
 *
 * Depending on the post's status, a notice is shown to the user either atop the
 * editor window, or in the prepublish panel.
 *
 * This file is rather lengthy, owing in part to the many elements shared
 * between both panels. It is structured as follows:
 *   1. Dependencies.
 *   2. Constants defining shared data.
 *   3. Helper methods.
 *   4. Component for Distribution sidebar.
 *   5. Component for prepublish panel.
 *   6. Subscribe handlers that:
 *      1. Prevent users from disabling the prepublish panel.
 *      2. Register an EntityProvider.
 *      3. Submit BNA data for processing.
 */

/**
 * Dependencies.
 */
import { without } from 'lodash';

import { __, _n, _x, sprintf } from '@wordpress/i18n';
import {
	CheckboxControl,
	Notice,
	PanelRow,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { compose } from '@wordpress/compose';
import {
	dispatch,
	select,
	subscribe,
	withDispatch,
	withSelect,
} from '@wordpress/data';
import { PluginPrePublishPanel } from '@wordpress/edit-post';
import { RawHTML, useState } from '@wordpress/element';
import { registerPlugin } from '@wordpress/plugins';

/**
 * Common data.
 */
const entityKind = 'pmcExactTarget';
const entityName = 'BNA';
const publishLockName = 'pmcEtBnaAck';
const restEndpoint = 'pmc/exacttarget/v1/bna';

const noticeId = publishLockName;

const resetTransientEdits = {
	selectedAlerts: [],
	sendOverride: false,
	subjectOverride: '',
};

/**
 * Generate text for confirmation notice displayed to user.
 *
 * @param {Array}   alerts         List of alerts to send to.
 * @param {boolean} includeConfirm Include text requesting user confirmation.
 * @return {string} Notice text.
 */
const buildNotice = ( alerts, includeConfirm = true ) => {
	const alertsFormatted = alerts
		.sort()
		.join( _x( ', ', 'Separator used to join BNA list', 'pmc-gutenberg' ) );

	let noticeText = '';

	if ( includeConfirm ) {
		noticeText = sprintf(
			// translators: 1. List name or names for BNA.
			_n(
				'A BNA will be sent to this list: %1$s. Confirm this action below.',
				'A BNA will be sent to these lists: %1$s. Confirm this action below.',
				alerts.length,
				'pmc-gutenberg'
			),
			alertsFormatted
		);
	} else {
		noticeText = sprintf(
			// translators: 1. List name or names for BNA.
			_n(
				'A BNA will be sent to this list: %1$s.',
				'A BNA will be sent to these lists: %1$s.',
				alerts.length,
				'pmc-gutenberg'
			),
			alertsFormatted
		);
	}

	return noticeText;
};

/**
 * Render method for sidebar panel.
 *
 * @param {Object}   root0
 * @param {boolean}  root0.allowCustomSubject If site supports custom subject.
 * @param {Array}    root0.bnas               Site's configured BNAs.
 * @param {boolean}  root0.isResolved         If entity provider has retrieved
 *                                            BNAs.
 * @param {Array}    root0.log                Log of previous sends.
 * @param {Array}    root0.selectedAlerts     Lists to send an alert to.
 * @param {boolean}  root0.sendOverride       If interval between sends should
 *                                            be lowered from five minutes to 30
 *                                            seconds.
 * @param {string}   root0.subjectOverride    Custom subject.
 * @param {Function} root0.updateSelections   Callback to handle list selection.
 * @param {Function} root0.updateSendOverride Callback to handle interval
 *                                            override.
 * @param {Function} root0.updateSubject      Callback to handle custom subject.
 * @return {JSX.Element} Sidebar panel component.
 */
const Render = ( {
	allowCustomSubject,
	bnas,
	isResolved,
	log,
	selectedAlerts,
	sendOverride,
	subjectOverride,
	updateSelections,
	updateSendOverride,
	updateSubject,
} ) => {
	if ( ! isResolved ) {
		return (
			<PanelRow>
				<Spinner />
			</PanelRow>
		);
	}

	return (
		<>
			<PanelRow>
				{ allowCustomSubject && (
					<TextControl
						label={ __( 'Subject', 'pmc-gutenberg' ) }
						value={ subjectOverride }
						onChange={ updateSubject }
					/>
				) }
			</PanelRow>

			<PanelRow>
				<div>
					{ bnas.map( ( label, key ) => (
						<div
							key={ key }
							className="editor-post-taxonomies__hierarchical-terms-choice"
						>
							<CheckboxControl
								label={ label }
								key={ label }
								checked={ selectedAlerts.includes( label ) }
								onChange={ ( checked ) => {
									updateSelections(
										label,
										checked,
										selectedAlerts
									);
								} }
							/>
						</div>
					) ) }
				</div>
			</PanelRow>

			<PanelRow>
				<CheckboxControl
					label={ __(
						'Special Event Coverage Override',
						'pmc-gutenberg'
					) }
					help={ __(
						'Check this box if you want to send breaking news within five minutes of previous send.',
						'pmc-gutenberg'
					) }
					checked={ sendOverride }
					onChange={ updateSendOverride }
				/>
			</PanelRow>

			{ Boolean( log.length ) && (
				<PanelRow>
					<div>
						<p>{ __( 'Alert Log:', 'pmc-gutenberg' ) }</p>
						<ul>
							{ log.map( ( entry, key ) => (
								<li key={ key }>
									{ /* Until Gutenberg introduces the Translate component, RawHTML is the only way to support translations that contain HTML. */ }
									{ /* See https://github.com/WordPress/gutenberg/issues/13156, https://github.com/WordPress/gutenberg/issues/18614 */ }
									<RawHTML>
										{ sprintf(
											/* translators: 1. Timestamp, 2. Username, 3. Lists sent to. */
											__(
												'<strong>%1$s</strong> - sent by <strong><em>%2$s</em></strong> to:',
												'pmc-gutenberg'
											),
											entry.timestamp,
											entry.username
										) }
									</RawHTML>
									<ol>
										{ entry.lists.map(
											( list, listKey ) => (
												<li key={ listKey }>
													{ list }
												</li>
											)
										) }
									</ol>
								</li>
							) ) }
						</ul>
					</div>
				</PanelRow>
			) }
		</>
	);
};

/**
 * Compose sidebar panel component.
 */
const ExactTargetBNA = compose( [
	withSelect( ( scopedSelect ) => {
		const {
			getEntityRecord,
			getEntityRecordEdits,
			hasFinishedResolution,
		} = scopedSelect( 'core' );
		const { getCurrentPost } = scopedSelect( 'core/editor' );

		const { id: postId, status: postStatus } = getCurrentPost();

		const entityArgs = [ entityKind, entityName, postId ];

		const settings = getEntityRecord( ...entityArgs );
		const edits = getEntityRecordEdits( ...entityArgs );

		const isResolved = hasFinishedResolution(
			'getEntityRecord',
			entityArgs
		);

		const canFallBack = 'draft' === postStatus || 'future' === postStatus;
		let selectedAlerts = [],
			subjectOverride = '';

		if ( edits?.selectedAlerts ) {
			selectedAlerts = edits.selectedAlerts;
		} else if ( settings?.selectedAlerts && canFallBack ) {
			selectedAlerts = settings?.selectedAlerts;
		}

		if ( edits?.subjectOverride ) {
			subjectOverride = edits?.subjectOverride;
		} else if ( settings?.subjectOverride && canFallBack ) {
			subjectOverride = settings.subjectOverride;
		}

		return {
			allowCustomSubject: Boolean( settings?.allowSubject ),
			bnas: settings?.alerts || [],
			isResolved,
			log: settings?.log || [],
			selectedAlerts,
			subjectOverride,
			sendOverride: Boolean( edits?.sendOverride ),
		};
	} ),
	withDispatch( ( scopedDispatch, props, { select: scopedSelect } ) => {
		const { editEntityRecord } = scopedDispatch( 'core' );
		const { lockPostSaving, unlockPostSaving } = scopedDispatch(
			'core/editor'
		);
		const {
			createSuccessNotice,
			createWarningNotice,
			removeNotice,
		} = scopedDispatch( 'core/notices' );
		const {
			getCurrentPostId,
			isCurrentPostPublished,
			isCurrentPostScheduled,
		} = scopedSelect( 'core/editor' );

		const postId = getCurrentPostId();
		const entityArgs = [ entityKind, entityName, postId ];

		let selectedAlerts = [];

		const noticeOptions = {
			id: noticeId,
			isDismissible: false,
			actions: [
				{
					label: __( 'Acknowledge', 'pmc-gutenberg' ),
					onClick: () => {
						const persistentNoticeOptions = JSON.parse(
							JSON.stringify( noticeOptions )
						);
						persistentNoticeOptions.actions = [];

						createSuccessNotice(
							buildNotice( selectedAlerts, false ),
							persistentNoticeOptions
						);
						unlockPostSaving( publishLockName );
					},
					isPrimary: true,
					noDefaultClasses: true,
				},
				{
					label: __( 'Cancel', 'pmc-gutenberg' ),
					onClick: () => {
						editEntityRecord( ...entityArgs, resetTransientEdits );
						unlockPostSaving( publishLockName );
						removeNotice( noticeOptions.id );
					},
				},
			],
		};

		return {
			unlockPostSaving,
			updateSelections: ( label, checked, existing ) => {
				const updates = {};

				if ( checked ) {
					updates.selectedAlerts = Array.prototype.concat( existing, [
						label,
					] );
				} else {
					updates.selectedAlerts = without( existing, label );
				}

				if ( updates?.selectedAlerts?.length ) {
					lockPostSaving( publishLockName );

					if (
						isCurrentPostPublished() ||
						isCurrentPostScheduled()
					) {
						selectedAlerts = updates.selectedAlerts;
						createWarningNotice(
							buildNotice( updates.selectedAlerts ),
							noticeOptions
						);
					}
				} else {
					unlockPostSaving( publishLockName );
					removeNotice( noticeOptions.id );
				}

				editEntityRecord( ...entityArgs, updates );
			},
			updateSendOverride: ( checked ) => {
				editEntityRecord( ...entityArgs, {
					sendOverride: checked,
				} );
			},
			updateSubject: ( subjectOverride ) => {
				editEntityRecord( ...entityArgs, {
					subjectOverride,
				} );
			},
		};
	} ),
] )( Render );

export default ExactTargetBNA;

/**
 * Render method for prepublish panel.
 *
 * @param {Object}   root0
 * @param {Function} root0.editEntityRecord Callback to edit post's BNA settings.
 * @param {Function} root0.lockPostSaving   Callback to prevent post publishing.
 * @param {number}   root0.postId           Post ID.
 * @param {Array}    root0.selectedAlerts   Lists to send an alert to.
 * @param {boolean}  root0.shouldDisplay    Whether panel should render.
 * @param {Function} root0.unlockPostSaving Callback to allow post publishing.
 * @return {JSX.Element|null}               Prepublish panel component.
 */
const RenderStatusPrePublish = ( {
	editEntityRecord,
	lockPostSaving,
	postId,
	selectedAlerts,
	shouldDisplay,
	unlockPostSaving,
} ) => {
	const [ acknowledged, handleAcknowledgement ] = useState( false );

	if ( ! shouldDisplay ) {
		return null;
	}

	if ( ! acknowledged ) {
		lockPostSaving( publishLockName );
	}

	const actions = [
		{
			label: __( 'Acknowledge', 'pmc-gutenberg' ),
			onClick: () => {
				handleAcknowledgement( true );
				unlockPostSaving( publishLockName );
			},
			isPrimary: true,
			noDefaultClasses: true,
		},
		{
			label: __( 'Cancel', 'pmc-gutenberg' ),
			onClick: () => {
				editEntityRecord(
					entityKind,
					entityName,
					postId,
					resetTransientEdits
				);
				handleAcknowledgement( false );
				unlockPostSaving( publishLockName );
			},
		},
	];

	return (
		<PluginPrePublishPanel>
			<Notice
				status={ acknowledged ? 'success' : 'warning' }
				isDismissible={ false }
				actions={ ! acknowledged ? actions : [] }
			>
				<p>{ buildNotice( selectedAlerts, ! acknowledged ) }</p>
			</Notice>
		</PluginPrePublishPanel>
	);
};

/**
 * Compose prepublish panel component.
 */
const ExactTargetBNANoticePrePublish = compose( [
	withSelect( ( scopedSelect ) => {
		const {
			getEntityRecord,
			getEntityRecordEdits,
			hasFinishedResolution,
		} = scopedSelect( 'core' );
		const {
			getCurrentPost,
			isCurrentPostPublished,
			isCurrentPostScheduled,
		} = scopedSelect( 'core/editor' );

		const { id: postId, status: postStatus } = getCurrentPost();

		const entityArgs = [ entityKind, entityName, postId ];

		const settings = getEntityRecord( ...entityArgs );
		const edits = getEntityRecordEdits( ...entityArgs );
		const isResolved = hasFinishedResolution(
			'getEntityRecord',
			entityArgs
		);

		let selectedAlerts = [];

		if ( edits?.selectedAlerts ) {
			selectedAlerts = edits.selectedAlerts;
		} else if ( settings?.selectedAlerts && 'draft' === postStatus ) {
			selectedAlerts = settings?.selectedAlerts;
		}

		return {
			isResolved,
			postId,
			selectedAlerts,
			shouldDisplay:
				! isCurrentPostPublished() &&
				! isCurrentPostScheduled() &&
				isResolved &&
				0 !== selectedAlerts.length,
		};
	} ),
	withDispatch( ( scopedDispatch ) => {
		const { editEntityRecord } = scopedDispatch( 'core' );
		const { lockPostSaving, unlockPostSaving } = scopedDispatch(
			'core/editor'
		);

		return {
			editEntityRecord,
			lockPostSaving,
			unlockPostSaving,
		};
	} ),
] )( RenderStatusPrePublish );

registerPlugin( 'pmc-exacttarget-bna-notice-prepublish', {
	render: ExactTargetBNANoticePrePublish,
} );

/**
 * Prevent users from hiding the pre-publish sidebar.
 *
 * TODO: move this somewhere else?
 */
subscribe( () => {
	const { isPublishSidebarEnabled } = select( 'core/editor' );

	if ( isPublishSidebarEnabled() ) {
		return;
	}

	const { enablePublishSidebar } = dispatch( 'core/editor' );

	enablePublishSidebar();
} );

/**
 * Register our entity provider to be available for both panels.
 */
const unsubscribeEntityRegistration = subscribe( () => {
	const { addEntities } = dispatch( 'core' );

	// Do this first to prevent recursion in `addEntities()` call.
	unsubscribeEntityRegistration();

	addEntities( [
		{
			name: entityName,
			kind: entityKind,
			baseURL: restEndpoint,
			label: __( 'Breaking News Alerts', 'pmc-gutenberg' ),
			key: 'id',
		},
	] );
} );

/**
 * Save BNA settings after post save finishes, but before Gutenberg finishes
 * saving legacy metaboxes. Gutenberg does not provide a way to perform this
 * action after both saves complete.
 *
 * @see https://github.com/WordPress/gutenberg/issues/17632
 */
let wasSavingPost = false;
let wasAutosavingPost = false;
let savingEditedRecords = false;
subscribe( () => {
	if ( savingEditedRecords ) {
		return;
	}

	const {
		getCurrentPostId,
		didPostSaveRequestFail,
		isSavingPost,
		isAutosavingPost,
	} = select( 'core/editor' );

	const resetVars = () => {
		wasSavingPost = isSavingPost();
		wasAutosavingPost = isAutosavingPost();
		savingEditedRecords = false;
	};

	if (
		isAutosavingPost() ||
		wasAutosavingPost ||
		isSavingPost() ||
		! wasSavingPost
	) {
		resetVars();
		return;
	}

	if ( didPostSaveRequestFail() ) {
		resetVars();
		return;
	}

	const { hasEditsForEntityRecord } = select( 'core' );

	const postId = getCurrentPostId();
	const entityArgs = [ entityKind, entityName, postId ];

	if ( ! hasEditsForEntityRecord( ...entityArgs ) ) {
		resetVars();
		return;
	}

	const { saveEditedEntityRecord } = dispatch( 'core' );
	const { removeNotice } = dispatch( 'core/notices' );

	savingEditedRecords = true;

	saveEditedEntityRecord( ...entityArgs );
	removeNotice( noticeId );

	resetVars();
} );
