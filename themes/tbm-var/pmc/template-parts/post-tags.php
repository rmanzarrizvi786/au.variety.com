<div class="tag-list">
	<?php PMC::render_template( PMC_CORE_PATH . '/template-parts/svg/tags.php', [], true ); ?>
	<ul class="tag-list__list">
		<?php
		$tags = \PMC\Core\Inc\Theme::get_instance()->get_post_terms( get_the_ID() );
		if ( is_array( $tags ) && ! empty( $tags['post_tag'] ) ) :
			foreach ( $tags['post_tag'] as $tag ) :
				$term_link = get_term_link( $tag->term_id );
				if ( is_string( $term_link ) ) :
					echo '<li class="tag-list__tag"><a href="' . esc_url( $term_link ) . '">' . esc_html( $tag->name ) . '</a></li>';
				endif;
			endforeach;
		endif;
		?>
	</ul>
</div>
