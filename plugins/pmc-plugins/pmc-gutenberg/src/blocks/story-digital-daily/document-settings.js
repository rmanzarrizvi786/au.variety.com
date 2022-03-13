/**
 * External dependencies.
 */
import { get } from 'lodash';

/**
 * WordPress dependencies.
 */
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, Dashicon, PanelRow, Spinner } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const metaKey = '_pmc_digital_daily_print_support_pdf_id';
const settingsName = 'pmc-story-digital-daily-document-settings';

const Render = ( { media, mediaId, onRemove, onUpdate, postType } ) => {
	if ( 'digital-daily' !== postType ) {
		return null;
	}

	const allowedTypes = [ 'application/pdf' ];

	let mediaSourceUrl, mediaTitle;
	if ( media ) {
		mediaSourceUrl = get( media, [ 'source_url' ] );
		mediaTitle = get( media, [ 'title', 'rendered' ] );
	}

	return (
		<PluginDocumentSettingPanel
			name={ settingsName }
			title={ __( 'Print PDF', 'pmc-gutenberg' ) }
			className={ settingsName }
			icon="printer"
			opened={ true }
		>
			<PanelRow>
				{ !! mediaId && ! media && <Spinner /> }

				{ !! mediaId && media && (
					<a href={ mediaSourceUrl } target="_blank" rel="noreferrer">
						<Dashicon icon="media-document" />
						<br />
						{ mediaTitle }
					</a>
				) }

				{ ! mediaId && ! media && (
					<MediaUploadCheck>
						<MediaUpload
							title={ __( 'Print PDF', 'pmc-gutenberg' ) }
							onSelect={ onUpdate }
							allowedTypes={ allowedTypes }
							value={ media?.id }
							render={ ( { open } ) => (
								<Button onClick={ open } isSecondary>
									{ __(
										'Select or Upload PDF',
										'pmc-gutenberg'
									) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
				) }
			</PanelRow>

			{ !! mediaId && media && (
				<PanelRow>
					<MediaUploadCheck>
						<MediaUpload
							title={ __( 'Print PDF', 'pmc-gutenberg' ) }
							onSelect={ onUpdate }
							allowedTypes={ allowedTypes }
							value={ media?.id }
							render={ ( { open } ) => (
								<>
									<Button onClick={ open } isLink>
										{ __( 'Replace', 'pmc-gutenberg' ) }
									</Button>

									<Button
										onClick={ onRemove }
										isLink
										isDestructive
									>
										{ __( 'Remove', 'pmc-gutenberg' ) }
									</Button>
								</>
							) }
						/>
					</MediaUploadCheck>
				</PanelRow>
			) }
		</PluginDocumentSettingPanel>
	);
};

const DigitalDailyStoryBlockDocumentSettings = compose(
	withSelect( ( select ) => {
		const { getMedia } = select( 'core' );
		const { getCurrentPostType, getEditedPostAttribute } = select(
			'core/editor'
		);

		const meta = getEditedPostAttribute( 'meta' ) ?? {};
		const { [ metaKey ]: mediaId } = meta;
		const media = !! mediaId
			? getMedia( mediaId, { context: 'view' } )
			: null;

		return { media, mediaId, postType: getCurrentPostType() };
	} ),
	withDispatch( ( dispatch ) => {
		const { editPost } = dispatch( 'core/editor' );

		return {
			onRemove: () => {
				const meta = { [ metaKey ]: null };

				editPost( { meta } );
			},
			onUpdate: ( media ) => {
				const meta = { [ metaKey ]: media.id };

				editPost( { meta } );
			},
		};
	} )
)( Render );

registerPlugin( settingsName, {
	render: DigitalDailyStoryBlockDocumentSettings,
} );
