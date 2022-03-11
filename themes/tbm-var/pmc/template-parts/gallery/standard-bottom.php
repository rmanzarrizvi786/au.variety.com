<div class="gallery__bottom">
	<div class="gallery__bottom-position">
		<p class="thumbnail-text"><?php esc_html_e( 'Thumbnails', 'pmc-core' ); ?></p>
		<div class="gallery__bottom-count">
			<span class="gallery__bottom-current-pic">1</span>
			<div class="gallery__bottom-divider">/</div>
			<span class="gallery__bottom-total-pics">-</span>
			<a href="#" class="gallery__viewthumbs">
				<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/svg/view-thumbnails.php', [ 'width' => 27, 'height' => 26 ], true ); ?>
			</a>
		</div>
	</div>
	<!-- This should also not display if info is set to display in standard-gallery within gallery__right-wrapper.php -->
	<?php if ( ! PMC::is_mobile() ) : ?>
		<div class="gallery__bottom-image-info">
			<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/center-info.php', [ 'gallery' => $gallery ], true ); ?>
		</div>
	<?php endif; ?>
</div>
