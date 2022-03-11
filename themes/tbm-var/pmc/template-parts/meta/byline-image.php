<?php if ( $byline_image ) : ?>
	<div class="byline-social__author-image" itemprop="image">
		<?php echo wp_kses_post( $byline_image ); ?>
	</div>
<?php endif; ?>
