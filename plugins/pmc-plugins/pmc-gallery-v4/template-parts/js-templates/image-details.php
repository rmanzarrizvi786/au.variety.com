<?php
/**
 * Template for the Image details, used for example in the editor.
 */

$alt_text_description = sprintf(
/* translators: 1: Link to tutorial, 2: Additional link attributes, 3: Accessibility text. */
	__( '<a href="%1$s" %2$s>Describe the purpose of the image%3$s</a>. Leave empty if the image is purely decorative.', 'pmc-gallery-v4' ),
	esc_url( 'https://www.w3.org/WAI/tutorials/images/decision-tree' ),
	'target="_blank" rel="noopener noreferrer"',
	sprintf(
		'<span class="screen-reader-text"> %s</span>',
		/* translators: Accessibility text. */
		__( '(opens in a new tab)', 'pmc-gallery-v4' )
	)
);

$allowed_tags = [
	'a'    => [
		'href'   => true,
		'target' => true,
		'rel'    => true,
	],
	'span' => [
		'class' => true,
	],
];

?>

<script type="text/html" id="tmpl-image-details">
	<div class="media-embed">
		<div class="embed-media-settings">
			<div class="column-settings">
				<span class="setting alt-text has-description">
					<label for="image-details-alt-text" class="name"><?php esc_html_e( 'Alternative Text', 'pmc-gallery-v4' ); ?></label>
					<input type="text" id="image-details-alt-text" data-setting="alt" value="{{ data.model.alt }}" aria-describedby="alt-text-description" />
				</span>
				<p class="description" id="alt-text-description"><?php echo wp_kses( $alt_text_description, $allowed_tags ); ?></p>

				<?php
				/** This filter is documented in wp-admin/includes/media.php */
				if ( ! apply_filters( 'disable_captions', '' ) ) :
					?>
					<span class="setting caption">
						<label for="image-details-caption" class="name"><?php esc_html_e( 'Caption', 'pmc-gallery-v4' ); ?></label>
						<textarea id="image-details-caption" data-setting="caption">{{ data.model.caption }}</textarea>
					</span>
				<?php endif; ?>

				<span class="setting credit">
					<label for="image-details-credit" class="name"><?php esc_html_e( 'Image Credit', 'pmc-gallery-v4' ); ?></label>
					<input type="text" id="image-details-credit" data-setting="imageCredit" value="{{ data.model.image_credit }}" aria-describedby="image-credit-text" />
				</span>

				<h2><?php esc_html_e( 'Display Settings', 'pmc-gallery-v4' ); ?></h2>
				<fieldset class="setting-group">
					<legend class="legend-inline"><?php esc_html_e( 'Align', 'pmc-gallery-v4' ); ?></legend>
					<span class="setting align">
						<span class="button-group button-large" data-setting="align">
							<button class="button" value="left">
								<?php esc_html_e( 'Left', 'pmc-gallery-v4' ); ?>
							</button>
							<button class="button" value="center">
								<?php esc_html_e( 'Center', 'pmc-gallery-v4' ); ?>
							</button>
							<button class="button" value="right">
								<?php esc_html_e( 'Right', 'pmc-gallery-v4' ); ?>
							</button>
							<button class="button active" value="none">
								<?php esc_html_e( 'None', 'pmc-gallery-v4' ); ?>
							</button>
						</span>
					</span>
				</fieldset>

				<# if ( data.attachment ) { #>
					<# if ( 'undefined' !== typeof data.attachment.sizes ) { #>
						<span class="setting size">
							<label for="image-details-size" class="name"><?php esc_html_e( 'Size', 'pmc-gallery-v4' ); ?></label>
							<select id="image-details-size" class="size" name="size"
								data-setting="size"
								<# if ( data.userSettings ) { #>
									data-user-setting="imgsize"
								<# } #>>
								<?php
								/** This filter is documented in wp-admin/includes/media.php */
								$sizes = apply_filters(
									'image_size_names_choose',
									array(
										'thumbnail' => __( 'Thumbnail', 'pmc-gallery-v4' ),
										'medium'    => __( 'Medium', 'pmc-gallery-v4' ),
										'large'     => __( 'Large', 'pmc-gallery-v4' ),
										'full'      => __( 'Full Size', 'pmc-gallery-v4' ),
									)
								);

								foreach ( $sizes as $value => $name ) :
									?>
									<#
									var size = data.sizes['<?php echo esc_js( $value ); ?>'];
									if ( size ) { #>
										<option value="<?php echo esc_attr( $value ); ?>">
											<?php echo esc_html( $name ); ?> &ndash; {{ size.width }} &times; {{ size.height }}
										</option>
									<# } #>
								<?php endforeach; ?>
								<option value="<?php echo esc_attr( 'custom' ); ?>">
									<?php esc_html_e( 'Custom Size', 'pmc-gallery-v4' ); ?>
								</option>
							</select>
						</span>
					<# } #>
						<div class="custom-size wp-clearfix<# if ( data.model.size !== 'custom' ) { #> hidden<# } #>">
							<span class="custom-size-setting">
								<label for="image-details-size-width"><?php esc_html_e( 'Width', 'pmc-gallery-v4' ); ?></label>
								<input type="number" id="image-details-size-width" aria-describedby="image-size-desc" data-setting="customWidth" step="1" value="{{ data.model.customWidth }}" />
							</span>
							<span class="sep" aria-hidden="true">&times;</span>
							<span class="custom-size-setting">
								<label for="image-details-size-height"><?php esc_html_e( 'Height', 'pmc-gallery-v4' ); ?></label>
								<input type="number" id="image-details-size-height" aria-describedby="image-size-desc" data-setting="customHeight" step="1" value="{{ data.model.customHeight }}" />
							</span>
							<p id="image-size-desc" class="description"><?php esc_html_e( 'Image size in pixels', 'pmc-gallery-v4' ); ?></p>
						</div>
				<# } #>

				<span class="setting link-to">
					<label for="image-details-link-to" class="name"><?php esc_html_e( 'Link To', 'pmc-gallery-v4' ); ?></label>
					<select id="image-details-link-to" data-setting="link">
					<# if ( data.attachment ) { #>
						<option value="file">
							<?php esc_html_e( 'Media File', 'pmc-gallery-v4' ); ?>
						</option>
						<option value="post">
							<?php esc_html_e( 'Attachment Page', 'pmc-gallery-v4' ); ?>
						</option>
					<# } else { #>
						<option value="file">
							<?php esc_html_e( 'Image URL', 'pmc-gallery-v4' ); ?>
						</option>
					<# } #>
						<option value="custom">
							<?php esc_html_e( 'Custom URL', 'pmc-gallery-v4' ); ?>
						</option>
						<option value="none">
							<?php esc_html_e( 'None', 'pmc-gallery-v4' ); ?>
						</option>
					</select>
				</span>
				<span class="setting">
					<label for="image-details-link-to-custom" class="name"><?php esc_html_e( 'URL', 'pmc-gallery-v4' ); ?></label>
					<input type="text" id="image-details-link-to-custom" class="link-to-custom" data-setting="linkUrl" />
				</span>

				<div class="advanced-section">
					<h2><button type="button" class="button-link advanced-toggle"><?php esc_html_e( 'Advanced Options', 'pmc-gallery-v4' ); ?></button></h2>
					<div class="advanced-settings hidden">
						<div class="advanced-image">
							<span class="setting title-text">
								<label for="image-details-title-attribute" class="name"><?php esc_html_e( 'Image Title Attribute', 'pmc-gallery-v4' ); ?></label>
								<input type="text" id="image-details-title-attribute" data-setting="title" value="{{ data.model.title }}" />
							</span>
							<span class="setting extra-classes">
								<label for="image-details-css-class" class="name"><?php esc_html_e( 'Image CSS Class', 'pmc-gallery-v4' ); ?></label>
								<input type="text" id="image-details-css-class" data-setting="extraClasses" value="{{ data.model.extraClasses }}" />
							</span>
						</div>
						<div class="advanced-link">
							<span class="setting link-target">
								<input type="checkbox" id="image-details-link-target" data-setting="linkTargetBlank" value="_blank" <# if ( data.model.linkTargetBlank ) { #>checked="checked"<# } #>>
								<label for="image-details-link-target" class="checkbox-label"><?php esc_html_e( 'Open link in a new tab', 'pmc-gallery-v4' ); ?></label>
							</span>
							<span class="setting link-rel">
								<label for="image-details-link-rel" class="name"><?php esc_html_e( 'Link Rel', 'pmc-gallery-v4' ); ?></label>
								<input type="text" id="image-details-link-rel" data-setting="linkRel" value="{{ data.model.linkRel }}" />
							</span>
							<span class="setting link-class-name">
								<label for="image-details-link-css-class" class="name"><?php esc_html_e( 'Link CSS Class', 'pmc-gallery-v4' ); ?></label>
								<input type="text" id="image-details-link-css-class" data-setting="linkClassName" value="{{ data.model.linkClassName }}" />
							</span>
						</div>
					</div>
				</div>
			</div>
			<div class="column-image">
				<div class="image">
					<img src="{{ data.model.url }}" draggable="false" alt="" />
					<# if ( data.attachment && window.imageEdit ) { #>
						<div class="actions">
							<input type="button" class="edit-attachment button" value="<?php echo esc_attr( 'Edit Original' ); ?>" />
							<input type="button" class="replace-attachment button" value="<?php echo esc_attr( 'Replace' ); ?>" />
						</div>
					<# } #>
				</div>
			</div>
		</div>
	</div>
</script>
