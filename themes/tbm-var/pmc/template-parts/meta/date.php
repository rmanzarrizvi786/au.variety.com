<span class="post-meta__date">
	<?php PMC::render_template( 'template-parts/svg/clock.php', [], true, [ 'is_relative_path' => true ] ); ?>
	<time datetime="<?php echo get_the_date( 'Y-m-d', $post_id ); ?>" itemprop="datePublished">
		<?php echo esc_html( \PMC\Core\Inc\Theme::get_instance()->get_relative_post_date() ); ?>
	</time>
</span>
