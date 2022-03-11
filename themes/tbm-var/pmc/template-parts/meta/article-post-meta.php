<div class="post-meta">
	<?php PMC::render_template(
		PMC_CORE_PATH . '/template-parts/meta/byline.php', [
			'post_id' => isset( $post_id ) ? $post_id : get_the_ID(),
		], true
	); ?>
	<?php PMC\Social_Share_Bar\Frontend::get_instance()->render(); ?>
</div>
