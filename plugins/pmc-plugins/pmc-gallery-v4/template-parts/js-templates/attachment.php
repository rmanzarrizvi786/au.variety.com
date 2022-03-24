<?php

/**
 * Js template for attachments.
 */
?>

<script type="text/html" id="tmpl-attachment">
	<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">
		<div class="thumbnail">
			<# if ( data.uploading ) { #>
				<div class="media-progress-bar">
					<div style="width: {{ data.percent }}%"></div>
				</div>
				<# } else if ( 'image'===data.type && data.sizes ) { #>
					<div class="centered">
						<img src="{{ data.size.url }}" draggable="false" alt="" />
					</div>
					<# } else { #>
						<div class="centered">
							<# if ( data.image && data.image.src && data.image.src !==data.icon ) { #>
								<img src="{{ data.image.src }}" class="thumbnail" draggable="false" />
								<# } else { #>
									<img src="{{ data.icon }}" class="icon" draggable="false" />
									<# } #>
						</div>
						<div class="filename">
							<div>{{ data.filename }}</div>
						</div>
						<# } #>
		</div>
		<# if ( data.buttons.close ) { #>
			<a class="close media-modal-icon" href="javascript:" title="<?php esc_attr_e('Remove', 'pmc-gallery-v4'); ?>" />
			<# } #>
	</div>

	<# if ( data.buttons.check ) { #>
		<a class="check" href="javascript:" title="<?php esc_attr_e('Deselect', 'pmc-gallery-v4'); ?>" tabindex="-1">
			<div class="media-modal-icon"></div>
		</a>
		<# } #>
			<# var maybeReadOnly=data.can.save || data.allowLocalEdits ? '' : 'readonly' ; if ( data.describe ) { if ( 'image'===data.type ) { #>
				<textarea value="{{ data.caption }}" class="describe caption" data-setting="caption" placeholder="<?php esc_attr_e('Caption this image&hellip;', 'pmc-gallery-v4'); ?>" {{ maybeReadOnly }}>
				{{ data.caption }}
				</textarea>
				<# } else { #>
					<input type="text" value="{{ data.title }}" class="describe" data-setting="title" <# if ( 'video'===data.type ) { #>
					placeholder="<?php esc_attr_e('Describe this video&hellip;', 'pmc-gallery-v4'); ?>"
					<# } else if ( 'audio'===data.type ) { #>
						placeholder="<?php esc_attr_e('Describe this audio file&hellip;', 'pmc-gallery-v4'); ?>"
						<# } else { #>
							placeholder="<?php esc_attr_e('Describe this media file&hellip;', 'pmc-gallery-v4'); ?>"
							<# } #> {{ maybeReadOnly }} />
								<# } } #>
</script>