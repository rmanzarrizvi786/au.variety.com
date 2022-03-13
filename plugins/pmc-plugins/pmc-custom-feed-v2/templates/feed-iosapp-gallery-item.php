<?php
/**
 * RSS2 Feed Template for displaying Images in a Gallery Post.
 *
 * @package WordPress
 */

global $feed, $post, $more;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

if ( !function_exists( 'variety_feed_get_gallery_images' ) ) {
//	echo '<!-- Variety custom feed specified, but required functions were not found -->';

	return false;
}

$gallery_post = PMC_Custom_Feed_Helper::pmc_feed_get_posts();

if ( empty( $gallery_post ) ) {
	return;
}

if ( 'pmc-gallery' != get_post_type( $gallery_post[0] ) ) {
	echo '<!-- This post is not a Gallery -->';

	return false;
}

header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);

$more = 1;
//takes a defined imagesize like 'gallery-thumb'
$thumb_size = apply_filters( 'pmc_custom_feed_thumb_size', array( 120,90 ) );
$content_size =  apply_filters( 'pmc_custom_feed_content_size', array( 800,1400 ) );

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action('pmc_custom_feed_start', $feed, $feed_options, basename(__FILE__) );

$post = $gallery_post[0];
$query = variety_feed_get_gallery_images();

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
	xmlns:pmc="http://variety.com/rss2-dtd/"
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
		<?php
		do_action('rss2_head');
		// The Images
		if ( !is_null($query) && $query->have_posts() ) {
			?>
				<pmc:galleryImageCount><?php echo variety_feed_get_gallery_images_count(); ?></pmc:galleryImageCount>
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();

				$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );

				if ( empty( $post ) ) {
					continue;
				}

				list($thumb_src, $thumb_width, $thumb_height) = wp_get_attachment_image_src( get_the_id(), $thumb_size );
				list($content_src, $content_width, $content_height) = wp_get_attachment_image_src( get_the_id(), $content_size );

				if ( empty( $thumb_src ) || empty( $content_src ) ) {
					continue;
				}
				?>
				<item>
					<title><![CDATA[ <?php the_title_rss() ?> ]]></title>
					<link><?php echo esc_url($content_src); ?></link>
					<pubDate><?php echo get_post_time( 'D, d M Y H:i:s +0000', true, $post ); ?></pubDate>
					<guid isPermaLink="false"><?php the_guid(); ?></guid>
					<media:title><![CDATA[ <?php the_title_rss() ?> ]]></media:title>
					<media:content url="<?php echo esc_url($content_src); ?>" medium="image"/>
					<media:thumbnail url="<?php echo esc_url($thumb_src); ?>" width="<?php echo esc_attr($thumb_width); ?>" height="<?php echo esc_attr($thumb_height); ?>" />
				</item>
				<?php
				do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
			}
		}
		wp_reset_postdata();
		?>
	</channel>
</rss>
