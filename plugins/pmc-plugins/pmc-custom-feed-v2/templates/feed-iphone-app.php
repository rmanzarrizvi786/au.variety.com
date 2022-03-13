<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package WordPress
 */

global $feed, $post, $pmc_custom_feed_qs;
if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
$more = 1;

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

do_action( 'pmc_custom_feed_start', $GLOBALS['feed'], $feed_options, basename( __FILE__ ) );

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed );

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
	<?php do_action( 'pmc_iphone_app_rss2_dtd' ); ?>
>

<channel>
	<title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss('url') ?></link>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
	<language><?php echo get_option('rss_language'); ?></language>
	<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
	<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
<?php
	foreach( $posts as $post ) :

		$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );

		if ( empty( $post ) ) {
			continue;
		}

		setup_postdata( $post );

		$item_id = get_the_ID();
?>
	<item>
		<title><?php the_title_rss() ?></title>
		<link><?php the_permalink_rss() ?></link>
		<comments><?php echo get_bloginfo_rss( 'url' ) . '/comments_app/' . $post->ID."/"; ?></comments>
		<pubDate><?php echo get_post_time( 'D, d M Y H:i:s +0000', true, $post ); ?></pubDate>
		<dc:creator><?php
			if ( function_exists( 'coauthors' ) ) {
				coauthors();
			} else {
				the_author();
			}
			?></dc:creator>
		<?php the_category_rss('rss2') ?>

		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<?php

		$thumb = PMC_Custom_Feed_Helper::get_image_url_specific_for_feed( $post->ID );

		if ( isset( $thumb ) ) {
			echo "<media:thumbnail>";
			echo $thumb;
			echo "</media:thumbnail>";
		}
		?>
	<?php if ( strlen( $post->post_content ) > 0 ) :
	?>
		<description><![CDATA[<?php
			if ( has_post_thumbnail() ){ ?>
				<div class="main-article-thumbnail-wrapper">
					<?php the_post_thumbnail( 'main-article-thumb' ); ?>
				</div>
	<?php 	}

			$images = get_children(array(
				'post_type' 		=> 'attachment',
				'post_mime_type' 	=> 'image',
				'post_parent' 		=> get_the_ID(),
				'post__not_in' 	=> array( get_post_thumbnail_id() ),
				'orderby'			=> 'menu_order',
				'order'				=> 'ASC',
			));
			if( ! empty( $images ) ){
	?>
				<div id="secondary-images">
	<?php
					foreach( $images as $attachment_id => $attachment ) {
						echo '<span>';
						echo wp_get_attachment_image( $attachment_id, 'left-sidebar-images' );
						echo '</span>';
					}
	?>
				</div>
	<?php
			}

			$content = apply_filters('the_content', get_the_content() );
			$content = str_replace(']]>', ']]&gt;', $content);
			$content = apply_filters('the_content_feed', $content, 'rss2');
			$content = $content . PMC_Custom_Feed_Helper::render_variety_video_url();
			$content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post, $feed_options, basename( __FILE__ ) );
			echo $content;
    ?>]]></description>
	<?php else : ?>
		<description><![CDATA[<?php the_excerpt_rss();
            PMC_Custom_Feed_Helper::render_variety_video_url();
            ?>]]></description>
	<?php endif; ?>

		<wfw:commentRss><?php echo esc_url( get_post_comments_feed_link(null, 'rss2') ); ?></wfw:commentRss>
		<slash:comments><?php echo get_comments_number(); ?></slash:comments>
<?php rss_enclosure(); ?>
	<?php do_action( 'pmc_iphone_app_rss2_item', $item_id ); ?>
	</item>
	<?php
			do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
			endforeach;
	?>
</channel>
</rss>
<?php
	do_action( 'pmc_custom_feed_end', $GLOBALS['feed'], $feed_options, basename( __FILE__ ) );

//EOF
