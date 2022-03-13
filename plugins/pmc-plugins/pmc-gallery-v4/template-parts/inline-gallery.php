<?php
/**
 * Inline gallery template.
 */

use PMC\Gallery\View;

if ( ! empty( $attachments ) ) {
	?>
	<div class="c-gallery-inline">
		<div class="c-gallery-inline__nav">
			<div class="c-gallery-inline__nav-head">01</div>
			<div class="c-gallery-inline__nav-arrows">
				<div class="c-gallery-inline__nav-arrow c-gallery-inline__nav-left">
					<svg class="c-gallery-inline__icon-arrow" width="14" height="14" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
						<path d="M13.7 4.7l-6 6c-.2.2-.4.3-.7.3-.3 0-.5-.1-.7-.3l-6-6c-.4-.4-.4-1 0-1.4.4-.4 1-.4 1.4 0L7 8.6l5.3-5.3c.4-.4 1-.4 1.4 0 .4.4.4 1 0 1.4z"></path>
					</svg>
				</div>
				<div class="c-gallery-inline__nav-arrow c-gallery-inline__nav-right">
					<svg class="c-gallery-inline__icon-arrow" width="14" height="14" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
						<path d="M13.7 4.7l-6 6c-.2.2-.4.3-.7.3-.3 0-.5-.1-.7-.3l-6-6c-.4-.4-.4-1 0-1.4.4-.4 1-.4 1.4 0L7 8.6l5.3-5.3c.4-.4 1-.4 1.4 0 .4.4.4 1 0 1.4z"></path>
					</svg>
				</div>
			</div>
		</div>

		<div class="c-gallery-inline__slider">
			<?php
			foreach ( $attachments as $image ) {
				?>
				<div class="c-gallery-inline__item">
					<figure class="c-gallery-inline__figure" title="<?php echo esc_attr( $image->post_title ); ?>">
						<?php View::get_gallery_inline_image( $image->ID ); ?>
					</figure>
					<div class="c-gallery-inline__caption">
						<p class="c-gallery-inline__title">
							<?php echo wp_kses_post( wp_get_attachment_caption( $image->ID ) ); ?>
						</p>
						<p class="c-gallery-inline__source">
							<?php echo wp_kses_post( get_post_meta( $image->ID, '_image_credit', true ) ); ?>
						</p>
					</div>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	<?php
}
