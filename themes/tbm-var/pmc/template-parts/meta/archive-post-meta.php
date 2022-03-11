<div class="post-meta">
	<?php PMC::render_template(
		 PMC_CORE_PATH . '/template-parts/meta/date.php', [
			'post_id' => isset( $post_id ) ? $post_id : get_the_ID(),
		], true
	); ?>
	<?php PMC::render_template(
		PMC_CORE_PATH . '/template-parts/meta/mini-byline.php', [
			'post_id' => isset( $post_id ) ? $post_id : get_the_ID(),
		], true
	); ?>
</div>
