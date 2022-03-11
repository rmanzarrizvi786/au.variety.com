<?php
$trending_widget_data  = $data;
$trending_posts_limit  = isset( $trending_widget_data['count'] ) ? absint( $trending_widget_data['count'] ) : 3;
$trending_widget_title = ! empty( $trending_widget_data['title'] ) ? $trending_widget_data['title'] : __( 'Most Popular', 'pmc-core' );
$period                = isset( $trending_widget_data['period'] ) ? absint( $trending_widget_data['period'] ) : 30;
$type                  = isset( $trending_widget_data['type'] ) ? $trending_widget_data['type'] : 'most_viewed';
$image_size            = isset( $trending_widget_data['image_size'] ) ? $trending_widget_data['image_size'] : 'thumbnail';

$trending = \PMC\Core\Inc\Top_Posts::get_posts( $trending_posts_limit, 365, $period, $type );

if ( empty( $trending ) ) {
	return;
}

?>

<section class="module module--popular">
	<h2 class="module__heading"><?php echo esc_html( $trending_widget_title ); ?></h2>
	<?php foreach ( $trending as $i => $post ) : ?>
		<article class="module-article">
			<div class="module-article__thumbnail featured-image">
				<a href="<?php echo esc_url( $post['post_permalink'] ); ?>">
					<?php echo get_the_post_thumbnail( $post['post_id'], $image_size ); ?>
				</a>
			</div>
			<div class="module-article__heading">
				<h3>
					<a href="<?php echo esc_url( $post['post_permalink'] ); ?>"><?php echo esc_html( $post['post_title'] ); ?></a>
				</h3>
			</div>
		</article>
	<?php endforeach ?>
</section>
