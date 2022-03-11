<ul class="gallery__share-buttons">
	<li><a href="#" class="gallery__share-facebook" target="_blank" title="<?php esc_attr_e( 'Share on Facebook', 'pmc-core' ); ?>">
			<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/svg/facebook.php', [ 'width' => 20, 'height' => 30 ], true ); ?>
		</a></li>
	<li><a href="#" class="gallery__share-twitter" target="_blank" title="<?php esc_attr_e( 'Tweet', 'pmc-core' ); ?>">
			<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/svg/twitter.php', [ 'width' => 20, 'height' => 30 ], true ); ?>
		</a></li>
	<li><a href="#" data-pin-custom="true" class="gallery__share-pinterest" target="_blank" title="<?php esc_attr_e( 'Pin it', 'pmc-core' ); ?>">
			<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/svg/pinterest.php', [ 'width' => 20, 'height' => 30 ], true ); ?>
		</a></li>
</ul>
