/**
 * External dependencies.
 */
import { has, get } from 'lodash';

/**
 * WordPress dependencies.
 */
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import {
	Button,
	PanelRow,
	ResponsiveWrapper,
	Spinner,
} from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const metaKey = '_pmc_digital_daily_special_edition_header_image_id';
const settingsName =
	'pmc-story-digital-daily-special-edition-article-document-settings';

const Render = ( { media, mediaId, onRemove, onUpdate, postType } ) => {
	if ( 'digital-daily' !== postType ) {
		return null;
	}

	const allowedTypes = [ 'image' ];

	let mediaWidth, mediaHeight, mediaSourceUrl;
	if ( media ) {
		if ( has( media, [ 'media_details', 'sizes', 'landscape-small' ] ) ) {
			mediaWidth = get( media, [
				'media_details',
				'sizes',
				'landscape-small',
				'width',
			] );
			mediaHeight = get( media, [
				'media_details',
				'sizes',
				'landscape-small',
				'height',
			] );
			mediaSourceUrl = get( media, [
				'media_details',
				'sizes',
				'landscape-small',
				'source_url',
			] );
		} else {
			mediaWidth = get( media, [ 'media_details', 'width' ] );
			mediaHeight = get( media, [ 'media_details', 'height' ] );
			mediaSourceUrl = get( media, [ 'source_url' ] );
		}
	}

	return (
		<PluginDocumentSettingPanel
			name={ settingsName }
			title={ __( 'Special Edition Header', 'pmc-gutenberg' ) }
			className={ settingsName }
			icon="admin-settings"
			opened={ false }
		>
			<PanelRow>
				{ !! mediaId && ! media && <Spinner /> }

				{ !! mediaId && media && (
					<div className="editor-post-featured-image__preview">
						<ResponsiveWrapper
							naturalWidth={ mediaWidth }
							naturalHeight={ mediaHeight }
							isInline
						>
							<img src={ mediaSourceUrl } alt="" />
						</ResponsiveWrapper>
					</div>
				) }

				{ ! mediaId && ! media && (
					<MediaUploadCheck>
						<MediaUpload
							title={ __(
								'Special Edition Header Image',
								'pmc-gutenberg'
							) }
							onSelect={ onUpdate }
							allowedTypes={ allowedTypes }
							value={ media?.id }
							render={ ( { open } ) => (
								<Button onClick={ open } isSecondary>
									{ __( 'Select image', 'pmc-gutenberg' ) }
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
							title={ __(
								'Special Edition Header Image',
								'pmc-gutenberg'
							) }
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

const SpecialEditionDocumentSettings = compose(
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
	render: SpecialEditionDocumentSettings,
} );
