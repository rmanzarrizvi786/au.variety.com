<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package WordPress
 */

global $feed, $post, $more;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

if( !function_exists('bgr_gallery_get_children') || !function_exists('get_bgr_image_sizes') ) {
	echo '<!-- BGR custom feed specified, but required functions were not found -->';
	return false;
}

header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);

$more = 1;
$thumb_size = get_bgr_image_sizes('all', 'gallery-feed-river-thumb');
$content_size = get_bgr_image_sizes('all', 'gallery-feed-single-item');

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action('pmc_custom_feed_start', $feed, $feed_options, basename(__FILE__) );

$posts = bgr_gallery_get_children($post->ID);

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';

?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	xmlns:media="http://search.yahoo.com/mrss/"
	xmlns:bgr="http://bgr.com/bgr-dtd/"
	xmlns:mmc="http://bgr.com/mmc-dtd/"
>

<channel>
	<title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss('url') ?></link>
	<description><?php bloginfo_rss("description") ?></description>
	<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
	<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
	<?php do_action('rss2_head'); ?>
<?php
		foreach( $posts as $post ) :

			$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );
			if ( empty( $post ) ) {
				continue;
			}
			setup_postdata($post);

			$image = get_the_image(array(
				'post_id'				=> $post->ID,
				'link_to_post'	=> false,
				'format'				=> 'array',
				'size'					=> 'full',
				'echo'					=> false,
			));
			// @todo Corey Gilmore 2012-10-17 placeholder image
			if( isset($image['src']) ) {
				$thumb = wpcom_vip_get_resized_remote_image_url( $image['src'], $thumb_size[0], $thumb_size[1], true );
				$main_image = wpcom_vip_get_resized_remote_image_url( $image['src'], $content_size[0], $content_size[1], true );
			} else {
				$thumb = false;
				$main_image = false;
			}

			if( !$main_image ) continue; // skip missing images

?>
	<item>
		<title><![CDATA[ <?php the_title_rss() ?> ]]></title>
		<link><?php echo esc_url($main_image); ?></link>
		<pubDate><?php echo get_post_time( 'D, d M Y H:i:s +0000', true, $post ); ?></pubDate>
		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<media:title><![CDATA[ <?php the_title_rss() ?> ]]></media:title>
		<media:content url="<?php echo esc_url($main_image); ?>" medium="image"/>
		<media:thumbnail url="<?php echo esc_url($thumb); ?>" width="<?php echo esc_attr($thumb_size[0]); ?>" height="<?php echo esc_attr($thumb_size[1]); ?>" />
	</item>
<?php
		do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
		endforeach;
?>
</channel>
</rss>
<?php
	do_action('pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );

// EOF
