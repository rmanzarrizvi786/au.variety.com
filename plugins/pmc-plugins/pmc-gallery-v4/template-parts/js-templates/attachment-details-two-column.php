<?php
/**
 * JS template for attachment details two column.
 */
?>

<script type="text/html" id="tmpl-attachment-details-two-column">
	<div class="attachment-media-view {{ data.orientation }}">
		<# var hideOnBulkEdit = data.bulkEdit ? 'hidden' : ''; #>
		<# if( data.bulkEdit ) {#>
		<# data.modelIds.forEach(function(d){ var img = wp.media.model.Attachment.get(d);#>
		<# if( img.attributes.id === data.id ){ var selected = data.id === img.attributes.id ? 'selected' : '';}#>
		<div class="thumbnail thumbnail-{{ data.type }} {{selected}}">
			<# if ( data.uploading ) { #>
			<div class="media-progress-bar"><div></div></div>
			<# } else if ( data.sizes && img.attributes.sizes.thumbnail ) { #>
			<img class="details-image" src="{{ img.attributes.sizes.thumbnail.url }}" draggable="false" alt="" />
			<# } else if ( data.sizes && img.attributes.sizes.full ) { #>
			<img class="details-image" src="{{ img.attributes.sizes.full.url }}" draggable="false" alt="" />
			<# } #>
		</div>
		<# });#>
		<#} else{#>
		<div class="thumbnail thumbnail-{{ data.type }}">
			<# if ( data.uploading ) { #>
			<div class="media-progress-bar"><div></div></div>
			<# } else if ( data.sizes && data.sizes.large ) { #>
			<img class="details-image" src="{{ data.sizes.large.url }}" draggable="false" alt="" />
			<# } else if ( data.sizes && data.sizes.full ) { #>
			<img class="details-image" src="{{ data.sizes.full.url }}" draggable="false" alt="" />
			<# } else if ( -1 === jQuery.inArray( data.type, [ 'audio', 'video' ] ) ) { #>
			<img class="details-image icon" src="{{ data.icon }}" draggable="false" alt="" />
			<# } #>

			<# if ( 'audio' === data.type ) { #>
			<div class="wp-media-wrapper">
				<audio style="visibility: hidden" controls class="wp-audio-shortcode" width="100%" preload="none">
					<source type="{{ data.mime }}" src="{{ data.url }}"/>
				</audio>
			</div>
			<# } else if ( 'video' === data.type ) {
			var w_rule = '';
			if ( data.width ) {
			w_rule = 'width: ' + data.width + 'px;';
			} else if ( wp.media.view.settings.contentWidth ) {
			w_rule = 'width: ' + wp.media.view.settings.contentWidth + 'px;';
			}
			#>
			<div style="{{ w_rule }}" class="wp-media-wrapper wp-video">
				<video controls="controls" class="wp-video-shortcode" preload="metadata"
				<# if ( data.width ) { #>width="{{ data.width }}"<# } #>
				<# if ( data.height ) { #>height="{{ data.height }}"<# } #>
				<# if ( data.image && data.image.src !== data.icon ) { #>poster="{{ data.image.src }}"<# } #>>
				<source type="{{ data.mime }}" src="{{ data.url }}"/>
				</video>
			</div>
			<# } #>

			<div class="attachment-actions">
				<# if ( 'image' === data.type && ! data.uploading && data.sizes && data.can.save ) { #>
				<button type="button" class="button edit-attachment"><?php esc_html_e( 'Edit Image', 'pmc-gallery-v4' ); ?></button>
				<# } else if ( 'pdf' === data.subtype && data.sizes ) { #>
				<?php esc_html_e( 'Document Preview', 'pmc-gallery-v4' ); ?>
				<# } #>
			</div>
		</div>
		<#}#>
	</div>
	<div class="attachment-info">
		<# var lastModified = new Date(data.modified_gmt); #>
		<# var month = ['Jan','Feb','March', 'April', 'May', 'June', 'July', 'August', 'Sept.', 'Oct.', 'Nov.', 'Dec.'];#>
		<# var timeStart = lastModified.getTime(); #>
		<# var timeEnd = new Date().getTime(); #>
		<# var offSet = new Date().getTimezoneOffset() * 60000; #>
		<# var hourDiff = timeEnd - timeStart + offSet; #>
		<# var secDiff = hourDiff / 1000; //in s #>
		<# var minDiff = hourDiff / 60 / 1000; //in minutes #>
		<# var hDiff = hourDiff / 3600 / 1000; //in hours #>
		<# var humanReadable = {}; #>
		<# humanReadable.hours = Math.floor(hDiff); #>
		<# humanReadable.minutes = Math.floor(minDiff - 60 * humanReadable.hours); #>
		<# humanReadable.sec = Math.floor( secDiff - 60 * humanReadable.minutes ); #>
		<# var displayStr = '';#>
		<# if( humanReadable.hours && humanReadable.hours > 23 ){ displayStr = month[lastModified.getMonth()] + ' ' + lastModified.getDate() + ', ' + lastModified.getFullYear()+' '+lastModified.getHours()+':'+lastModified.getMinutes();}else if( humanReadable.hours && humanReadable.hours < 23 ){#>
		<#  displayStr = humanReadable.hours + ' hours ago.'; }else if( humanReadable.minutes && humanReadable.minutes < 59 ){#>
		<#  displayStr = humanReadable.minutes + ' minutes ago.'; }else if( humanReadable.sec && humanReadable.sec < 59 ){ #>
		<# displayStr = humanReadable.sec + ' seconds ago.'; }#>

		<div class="details ">
			<div>
				<div class="restricted-single-use-notice hidden"><?php esc_html_e( 'This is a Single Use Image - restricted to use for a specific post.', 'pmc-gallery-v4' ); ?></div>
				<div class="site-restricted-notice hidden"><?php esc_html_e( 'This image is restricted to use on this site only.', 'pmc-gallery-v4' ); ?></div>
			</div>
			<div class="left">
				<# if( data.bulkEdit ){#>
				<span class="total-editing "><?php esc_html_e( 'Number of Images Editing: ', 'pmc-gallery-v4' ); ?>{{data.modelIds.length}}</span>
				<#} else { #>
				<div class="filename"><strong><?php esc_html_e( 'File name:', 'pmc-gallery-v4' ); ?></strong> {{ data.filename }}</div>
				<div class="filename"><strong><?php esc_html_e( 'File type:', 'pmc-gallery-v4' ); ?></strong> {{ data.mime }}</div>
				<div class="uploadedby"><strong><?php esc_html_e( 'Uploaded by:', 'pmc-gallery-v4' ); ?></strong> {{ data.authorName }}</div>
				<div class="uploaded"><strong><?php esc_html_e( 'Uploaded on:', 'pmc-gallery-v4' ); ?></strong> {{ data.dateFormatted }}</div>
				<# if ( ! data.bulkEdit ) { #>
					<# if ( data.attachment_count ) { #>
					<div class="attachmentusedin"><strong><?php esc_html_e( 'Attachment used in', 'pmc-gallery-v4' ); ?></strong> {{ data.attachment_count }} <?php esc_html_e( 'galleries', 'pmc-gallery-v4' ); ?></div>
					<# } #>
				<# } #>
				<div class="file-size"><strong><?php esc_html_e( 'File size:', 'pmc-gallery-v4' ); ?></strong> {{ data.filesizeHumanReadable }}</div>
				<# if ( 'image' === data.type && ! data.uploading ) { #>
				<# if ( data.width && data.height ) { #>
				<div class="dimensions"><strong><?php esc_html_e( 'Dimensions:', 'pmc-gallery-v4' ); ?></strong> {{ data.width }} &times; {{ data.height }}</div>
				<# } #>
				<# } #>

				<# if ( data.fileLength ) { #>
				<div class="file-length"><strong><?php esc_html_e( 'Length:', 'pmc-gallery-v4' ); ?></strong> {{ data.fileLength }}</div>
				<# } #>

				<# if ( 'audio' === data.type && data.meta.bitrate ) { #>
				<div class="bitrate">
					<strong><?php esc_html_e( 'Bitrate:', 'pmc-gallery-v4' ); ?></strong> {{ Math.round( data.meta.bitrate / 1000 ) }}kb/s
					<# if ( data.meta.bitrate_mode ) { #>
					{{ ' ' + data.meta.bitrate_mode.toUpperCase() }}
					<# } #>
				</div>
				<# } #>
				<div class="compat-meta">
					<# if ( data.compat && data.compat.meta ) { #>{{{ data.compat.meta }}}<# } #> <?php // @codingStandardsIgnoreLine ?>
				</div>
				<# } #>
			</div>
			<div class="right">
						<span class="settings-save-status">
							<span class="spinner" />
							<span class="saved"><?php esc_html_e( 'Saved.', 'pmc-gallery-v4' ); ?></span>
							<span class="required"><?php esc_html_e( 'Please fill in the required fields.', 'pmc-gallery-v4' ); ?></span>
						</span>
				<# if( ! _.isEmpty(displayStr) ){ #>
				<span class="settings-modified">last saved {{displayStr}} </span>
				<# } #>
			</div>
		</div>

		<div class="settings">
			<label class="setting {{hideOnBulkEdit}}" data-setting="url">
				<span class="name"><?php esc_html_e( 'URL', 'pmc-gallery-v4' ); ?></span>
				<input type="text" value="{{ data.url }}" readonly />
			</label>
			<# var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly'; #>
			<?php if ( post_type_supports( 'attachment', 'title' ) ) : ?>
				<label class="setting" data-setting="title">
							<span class="name"><?php esc_html_e( 'Title', 'pmc-gallery-v4' ); ?>
								<a href="#" class="dashicons dashicons-editor-help imgedit-help-toggle" title="
									<?php
									// translators: %s is date.
									printf( esc_attr__( 'The Photo Title can be the similar to the Gallery Title. It indicates that the image is part of the series of images that makes up the gallery and should contain the focus keyphrase. Ex.: %s Grammy Awards Red Carpet', 'pmc-gallery-v4' ), esc_attr( date( 'Y' ) ) );
									?>
								" >
								</a>
							</span>
					<input type="text" value="{{ data.title }}" {{ maybeReadOnly }} />
				</label>
			<?php endif; ?>
			<# if ( 'audio' === data.type ) { #>
			<?php
			foreach ( array(
				'artist' => __( 'Artist', 'pmc-gallery-v4' ),
				'album'  => __( 'Album', 'pmc-gallery-v4' ),
			) as $key => $label ) :
				?>
				<label class="setting" data-setting="<?php echo esc_attr( $key ); ?>">
					<span class="name"><?php echo esc_html( $label ); ?></span>
					<input type="text" value="{{ data.<?php echo esc_attr( $key ); ?> || data.meta.<?php echo esc_attr( $key ); ?> || '' }}" />
				</label>
			<?php endforeach; ?>
			<# } #>
			<div class="setting" data-setting="caption">
						<span class="name"><?php esc_html_e( 'Caption', 'pmc-gallery-v4' ); ?>
							<a href="#" class="dashicons dashicons-editor-help imgedit-help-toggle" title="
								<?php
								// translators: %s is date.
								printf( esc_attr__( 'This is the text that accompanies the image. It should not be the same as the Photo Title. When possible, the photo caption should contain the focus keyphrase. If the photo is of a person or persons, their names should be in the caption. Ex: Adele and Beyonce at the %s Grammys', 'pmc-gallery-v4' ), esc_attr( date( 'Y' ) ) );
								?>
							" >
							</a>
						</span>
				<textarea {{ maybeReadOnly }}>{{ data.caption }}</textarea>
			</div>
			<# if ( 'image' === data.type ) { #>
			<label class="setting" data-setting="alt">
						<span class="name"><?php esc_html_e( 'Alt Text', 'pmc-gallery-v4' ); ?>
							<a href="#" class="dashicons dashicons-editor-help imgedit-help-toggle" title="
								<?php
								// translators: %s is date.
								printf( esc_attr__( 'The Alt Text tells the search engines what the photo is, is used in image search, and can bring more traffic to the gallery. When writing the Alt Text: Describe what the image is. Do not use punctuation or hyphens. If the image contains a person or persons, use their names. Ex: %s Grammy Awards Adele And Beyonce On The Red Carpet', 'pmc-gallery-v4' ), esc_attr( date( 'Y' ) ) );
								?>
							" >
							</a>
						</span>
				<input type="text" value="{{ data.alt }}" {{ maybeReadOnly }} />
			</label>
			<?php if ( 'yes' === cheezcap_get_option( 'pmc_gallery_enable_pinterest_description' ) ) : ?>
				<label class="setting" data-setting="pinterest_description">
					<span class="name"><?php esc_html_e( 'Pinterest Description', 'pmc-gallery-v4' ); ?>
					</span>
					<input type="text" value="{{ data.pinterest_description }}" {{ maybeReadOnly }} />
				</label>
			<?php endif; ?>
			<# } #>
			<label class="setting" data-setting="description">
						<span class="name"><?php esc_html_e( 'Description', 'pmc-gallery-v4' ); ?>
							<a href="#" class="dashicons dashicons-editor-help imgedit-help-toggle" title="<?php esc_attr_e( 'this is the Description.', 'pmc-gallery-v4' ); ?>">
							</a>
						</span>
				<textarea {{ maybeReadOnly }}>{{ data.description }}</textarea>
			</label>
			<# if ( data.uploadedToTitle && !data.bulkEdit ) { #>
			<label class="setting">
				<span class="name"><?php esc_html_e( 'Uploaded To', 'pmc-gallery-v4' ); ?></span>
				<# if ( data.uploadedToLink ) { #>
				<span class="value"><a href="{{ data.uploadedToLink }}">{{ data.uploadedToTitle }}</a></span>
				<# } else { #>
				<span class="value">{{ data.uploadedToTitle }}</span>
				<# } #>
			</label>
			<# } #>
			<div class="attachment-compat"></div>
		</div>

		<# if ( ! data.bulkEdit ) { #>
		<div class="actions">
			<a class="view-attachment" href="{{ data.link }}"><?php esc_html_e( 'View attachment page', 'pmc-gallery-v4' ); ?></a>
			<# if ( data.can.save ) { #> |
			<a href="post.php?post={{ data.id }}&action=edit"><?php esc_html_e( 'Edit more details', 'pmc-gallery-v4' ); ?></a>
			<# } #>
			<# if ( ! data.uploading && data.can.remove ) { #> |
			<?php if ( MEDIA_TRASH ) : ?>
				<# if ( 'trash' === data.status ) { #>
				<button type="button" class="button-link untrash-attachment"><?php esc_html_e( 'Untrash', 'pmc-gallery-v4' ); ?></button>
				<# } else { #>
				<button type="button" class="button-link trash-attachment"><?php echo esc_html_x( 'Trash', 'verb', 'pmc-gallery-v4' ); ?></button>
				<# } #>
			<?php else : ?>
				<button type="button" class="button-link delete-attachment"><?php esc_html_e( 'Delete Permanently', 'pmc-gallery-v4' ); ?></button>
			<?php endif; ?>
			<# } #>
		</div>
		<# } #>
	</div>
</script>
