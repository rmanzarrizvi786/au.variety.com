<?php
/**
 * Js template for second attachment.
 */
?>

<script type="text/html" id="tmpl-attachment">
	<#
	var created_data_label = 'Created time is not available.';
	if ( 'undefined' !== typeof data.attachment_created_timestamp && 0 != data.attachment_created_timestamp ) {
		data.attachment_created_timestamp = parseInt(data.attachment_created_timestamp,0);
		var date = new Date( data.attachment_created_timestamp * 1000 ); // Convert into Seconds.
		created_data_label = date.toUTCString();
		created_data_label = created_data_label.replace('GMT','');
		created_data_label = created_data_label.trim();
		created_data_label = 'Created on ' + created_data_label;
	}
	#>
	<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">
		<div class="thumbnail">
			<# if ( data.uploading ) { #>
			<div class="media-progress-bar"><div style="width: {{ data.percent }}%"></div></div>
			<# } else if ( 'image' === data.type && data.sizes ) { #>
			<div class="centered">
				<img src="{{ data.size.url }}" draggable="false" alt="" />
			</div>
			<# } else { #>
			<div class="centered">
				<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
				<img src="{{ data.image.src }}" class="thumbnail" draggable="false" alt="" />
				<# } else if ( data.sizes && data.sizes.medium ) { #>
				<img src="{{ data.sizes.medium.url }}" class="thumbnail" draggable="false" alt="" />
				<# } else { #>
				<img src="{{ data.icon }}" class="icon" draggable="false" alt="" />
				<# } #>
			</div>
			<div class="filename">
				<div>{{ data.filename }}</div>
			</div>
			<# } #>
		</div>
		<# if ( 'undefined' !== typeof data.attachment_created_timestamp && 0 != data.attachment_created_timestamp ) { #>
		<span href="javascript:" class="button-link attachment-calender media-modal-icon imgedit-help-toggle" title="{{created_data_label}}">
			<span class="dashicons dashicons-calendar" />
			<span class="screen-reader-text"><?php esc_html_e( 'Created Date', 'pmc-gallery-v4' ); ?></span>
		</span>
		<# } #>
		<# if ( data.buttons.close ) { #>
		<button type="button" class="button-link attachment-close media-modal-icon"><span class="screen-reader-text"><?php esc_html_e( 'Remove', 'pmc-gallery-v4' ); ?></span></button>
		<# } #>
	</div>
	<# if ( data.buttons.check ) { #>
	<button type="button" class="button-link check" tabindex="-1"><span class="media-modal-icon" /><span class="screen-reader-text"><?php esc_html_e( 'Deselect', 'pmc-gallery-v4' ); ?></span></button>
	<# } #>
	<#
	var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly';
	if ( data.describe ) {
	if ( 'image' === data.type ) { #>
	<input type="text" value="{{ data.caption }}" class="describe" data-setting="caption" placeholder="<?php esc_attr_e( 'Caption this image&hellip;', 'pmc-gallery-v4' ); ?>" {{ maybeReadOnly }} />
	<# } else { #>
	<input type="text" value="{{ data.title }}" class="describe" data-setting="title"
	<# if ( 'video' === data.type ) { #>
	placeholder="<?php esc_attr_e( 'Describe this video&hellip;', 'pmc-gallery-v4' ); ?>"
	<# } else if ( 'audio' === data.type ) { #>
	placeholder="<?php esc_attr_e( 'Describe this audio file&hellip;', 'pmc-gallery-v4' ); ?>"
	<# } else { #>
	placeholder="<?php esc_attr_e( 'Describe this media file&hellip;', 'pmc-gallery-v4' ); ?>"
	<# } #> {{ maybeReadOnly }} />
	<# }
	} #>
</script>
