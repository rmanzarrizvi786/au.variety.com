<?php
/**
 * Template part for Top Videos Slider.
 *
 * @package pmc-top-videos-v2
 * @since 2018-04-23
 */

if ( 0 === $videos->current_post % 4 ) { ?>
<ul class="slide">
<?php } ?>

	<li class="<?php echo esc_attr( 'svideo_id_' . $post->ID ); ?> <?php echo $current_video->ID === $post->ID ? 'active' : ''; ?> video-card" >

	<div class="image-container <?php echo empty( $video_thumbnail ) ? 'no-image' : 'image'; ?>">
			<a
				href="<?php echo esc_url( $permalink ); ?>"
				class="thumb"
				title="<?php echo esc_html( $title ); ?>">

				<span class="duration">
					<?php echo esc_html( $video_duration ); ?>
					<i class="fa fa-caret-right"></i>
				</span>
				<?php if ( ! empty( $video_thumbnail ) ) { ?>
					<img
						src="<?php echo esc_url( $video_thumbnail ); ?>"
						width="146" height="82"
						alt="<?php echo esc_attr( $title . ' Image' ); ?>"
						class="full">
				<?php } ?>
			</a>
		</div>
		<h3>
			<a href="<?php echo esc_url( $permalink ); ?>" title="<?php echo esc_html( $title ); ?>">
				<?php echo esc_html( $title ); ?>
			</a>
		</h3>
	</li>
<?php if ( 3 === $videos->current_post % 4 || $videos->current_post + 1 === $videos->post_count ) { ?>
</ul>
<?php } ?>
