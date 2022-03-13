<?php
/**
 * Template to show more posts for single posts.
 *
 * @author Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since  2017-05-23
 *
 * @package pmc-google-amp
 */

$current_post_id = get_the_ID();
$current_post_id = ( ! empty( $current_post_id ) ) ? $current_post_id : false;

$allow_image = true;

if ( 'without-image' === strtolower( $status ) ) {
	$allow_image = false;
}
?>
<div class="amp-category-posts-container <?php echo esc_attr( $status ); ?>">
	<div class="title">
		<h3>
			<?php
			/* translators: %1$s - Term name */
			printf( esc_html__( 'More %1$s', 'pmc-google-amp' ), esc_html( $term->name ) );
			?>
		</h3>
	</div>
	<div class="content">
		<ul class="list">
			<?php
			$count = 0;
			$default_placeholder = sprintf( '%s/assets/images/placeholder-thumbnail.png', untrailingslashit( PMC_GOOGLE_AMP_URL ) );

			foreach ( $posts as $index => $post ) {

				/**
				 * If post is current post or
				 * Total number of post are more than 4 than skip it.
				 */
				if ( $current_post_id === $post->ID || $count >= 4 ) {
					continue;
				}
				$count++;

				$amp_link = function_exists( 'amp_get_permalink' ) ? amp_get_permalink( $post->ID ) : get_permalink( $post->ID );
				?>
				<li>
					<a href="<?php echo esc_url( $amp_link ); ?>" title="<?php echo esc_attr( $post->post_title ); ?>">
						<?php if ( $allow_image ) { ?>
							<p class="post-image">
								<?php
								if ( has_post_thumbnail( $post->ID ) ) {
									echo get_the_post_thumbnail( $post->ID, $size );
								} else {
									printf( '<img src="%s" alt="%s">', esc_url( $default_placeholder ), esc_attr( $post->post_title ) );
								}
								?>
							</p>
						<?php } ?>
						<p class="post-title"><?php echo wp_kses_post( $post->post_title ); ?></p>
					</a>
				</li>
			<?php } ?>
		</ul>
		<div class="clear"></div>
	</div>
</div>
