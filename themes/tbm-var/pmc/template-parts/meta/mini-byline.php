<span class="post-meta__author" aria-label="Author" itemprop="author" itemscope itemtype="https://schema.org/Person">
	<?php
	echo wp_kses(
		\PMC\Core\Inc\Meta\Byline::get_instance()->get_the_mini_byline( $post_id ),
		[
			'a' => [
				'href'  => true,
				'class' => true,
				'title' => true,
				'rel'   => true,
			],
		]
	);
	?>
</span>
