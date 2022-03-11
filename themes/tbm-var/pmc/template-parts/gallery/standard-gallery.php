<div class="gallery__container">
	<?php if ( PMC::is_mobile() ) : ?>
		<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/standard-mobile-header.php', [], true ); ?>
	<?php else: ?>
		<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/standard-top.php', [ 'gallery' => $gallery ], true ); ?>
	<?php endif; ?>
	<div class="gallery__mid-content">
		<div class="inner-flex">
			<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/standard-left.php', [ 'gallery' => $gallery ], true ); ?>
			<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/standard-center.php', [ 'gallery' => $gallery ], true ); ?>
		</div>
		<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/standard-bottom.php', [ 'gallery' => $gallery ], true ); ?>
	</div>
	<div class="gallery__right-wrapper">
			<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/center-info.php', [ 'gallery' => $gallery ], true ); ?>
		<div class="gallery__right">
			<?php dynamic_sidebar( 'gallery-right' ); ?>
		</div>
	</div>
	<?php if ( PMC::is_mobile() ) : ?>
		<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/gallery/standard-mobile-footer.php', [], true ); ?>
	<?php endif; ?>
</div>
