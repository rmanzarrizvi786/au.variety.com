<?php
/**
 * RSS2 Feed Template for displaying RSS2 Video feed.
 *
 * @package WordPress
 */

global $feed, $post, $more;
if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

$more = 1;
$thumb_size = apply_filters( 'pmc_custom_feed_thumb_size', array( 120, 90 ) );

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action('pmc_custom_feed_start', $feed, $feed_options, basename(__FILE__) );

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed );

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';
?>
<rss version="2.0"
	 xmlns:content="http://purl.org/rss/1.0/modules/content/"
	 xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	 xmlns:dc="http://purl.org/dc/elements/1.1/"
	 xmlns:atom="http://www.w3.org/2005/Atom"
	 xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	 xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	 xmlns:media="http://search.yahoo.com/mrss/"
	 xmlns:mmc="http://variety.com/mmc-dtd/"
	 >

	<channel>
		<title><?php bloginfo_rss( 'name' );
		wp_title_rss(); ?></title>
		<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
		<link><?php bloginfo_rss( 'url' ) ?></link>
		<description><?php bloginfo_rss( "description" ) ?></description>
		<lastBuildDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ); ?></lastBuildDate>
		<language><?php bloginfo_rss( 'language' ); ?></language>
		<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
		<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
		<?php do_action( 'rss2_head' ); ?>
		<?php

		$retain_orig_post = $post;
		foreach ( $posts as $post ) :

			$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );
			if ( empty( $post ) ) {
				continue;
			}
			setup_postdata( $post );

			?>
			<item>
				<title><?php the_title_rss() ?></title>
				<link><?php the_permalink_rss() ?></link>
				<pubDate><?php echo get_post_time( 'D, d M Y H:i:s +0000', true, $post ); ?></pubDate>
				<guid isPermaLink="false"><?php the_guid(); ?></guid>
				<?php


				if (  is_string( $thumb_size ) ) {
					global $_wp_additional_image_sizes;

					if ( isset( $_wp_additional_image_sizes[ $thumb_size ] ) ) {
						$thumb_size = array(
							$_wp_additional_image_sizes[ $thumb_size ]['width'],
							$_wp_additional_image_sizes[ $thumb_size ]['height']
						);
					} else {
						$thumb_size = array( 120, 90 );
					}
				}

				if( has_post_thumbnail() ){

					$image = wp_get_attachment_image_src( get_post_thumbnail_id() , $thumb_size );


					if ( isset( $image[0]) && isset( $thumb_size[0] ) && isset( $thumb_size[1] ) ) {
						$thumb = wpcom_vip_get_resized_remote_image_url( $image[0], $thumb_size[0], $thumb_size[1], true );
					} else {
						$thumb = false;
					}
					if ( $thumb ) { ?>
						<media:thumbnail url="<?php
							echo esc_url( $thumb );
						?>" width="<?php
							echo esc_attr( $thumb_size[0] );
						?>" height="<?php
							echo esc_attr( $thumb_size[1] );
						?>" />
					<?php }
				}

				$video_url = get_post_meta( $post->ID, '_variety_top_video_link', true );
				$feed_url = get_permalink($post) . 'feed/gallery-item';

				if ( !empty( $video_url ) ) {
					?>
					<media:content url="<?php echo esc_url( $video_url ); ?>"/>
					<?php
				}else{
					?>
					<media:content type="application/rss+xml" url="<?php echo esc_url($feed_url); ?>"/>
					<?php
				}
?>
				<?php // VIP: fix fatal for undefined function
					if ( function_exists( 'variety_feed_get_gallery_images_count' ) ) {
						if( variety_feed_get_gallery_images_count() >0 ){ ?>
				<mmc:galleryImageCount><?php echo intval(variety_feed_get_gallery_images_count()); ?></mmc:galleryImageCount>

			<?php } } ?>
			</item>
			<?php

			do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
		endforeach;
		$post = $retain_orig_post;
		?>
	</channel>
</rss>
<?php
	do_action('pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );

// EOF
