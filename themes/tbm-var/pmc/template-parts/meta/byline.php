<span class="post-meta__author" aria-label="Author" itemprop="author" itemscope itemtype="https://schema.org/Person">
	<?php

	$post_id = ( ! empty( $post_id ) && intval( $post_id ) > 0 ) ? intval( $post_id ) : get_the_ID();

	echo wp_kses(
		\PMC\Core\Inc\Meta\Byline::get_instance()->get_the_byline( $post_id ),
		[
			'span' => true,
			'div' => [
				'class' => true,
			],
			'svg' => [
				'viewbox' => true,
				'xmlns'   => true,
				'height'  => true,
				'width'   => true,
			],
			'title' => true,
			'path' => [
				'd' => true,
			],
			'img' => [
					'class'  => true,
					'height' => true,
					'id'     => true,
					'src'    => true,
					'srcset' => true,
					'width'  => true,
			],
			'a' => [
				'href'  => true,
				'class' => true,
				'title' => true,
				'rel'   => true,
				'itemprop' => true,
				'itemscope' => true,
				'itemtype' => true,
			],
		]
	);
	?>
</span>
