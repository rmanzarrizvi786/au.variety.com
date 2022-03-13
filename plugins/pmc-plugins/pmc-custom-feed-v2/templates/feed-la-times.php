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

$more = 1;

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action('pmc_custom_feed_start', $feed, $feed_options, basename(__FILE__) );

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts();
header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . esc_attr( get_option( 'blog_charset' ) ), true );
echo '<?xml version="1.0" encoding="'.esc_attr( get_option( 'blog_charset' ) ).'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:media="http://search.yahoo.com/mrss/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	<?php do_action('rss2_ns'); ?>
>

<channel>
	<title><?php echo PMC_Custom_Feed_Helper::esc_xml( apply_filters( 'pmc_custom_feed_title', get_bloginfo_rss( 'name' ) ) ); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php echo esc_html( get_bloginfo_rss( 'url' ) ); ?></link>
	<?php PMC_Custom_Feed_Helper::maybe_render_feed_logo(); ?>
	<description><?php echo esc_html( get_bloginfo_rss( 'description' ) ); ?></description>
	<lastBuildDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ); ?></lastBuildDate>
	<language><?php echo esc_html( get_bloginfo_rss( 'language' ) ); ?></language>
	<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
	<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
	<?php do_action( 'rss2_head' ); ?>

	<?php foreach( $posts as $post ) : ?>

		<?php
			$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );
			if ( empty( $post ) ) {
				continue;
			}
			setup_postdata( $post );
		?>

		<item>
			<title><?php the_title_rss() ?></title>
			<link><?php the_permalink_rss() ?></link>
			<comments><?php comments_link_feed(); ?></comments>
			<pubDate><?php echo get_post_time( 'D, d M Y H:i:s +0000', true, $post ); ?></pubDate>
			<dc:creator>
				<?php if ( function_exists( 'coauthors' ) ) : ?>
					<?php coauthors(); ?>
				<?php else : ?>
					<?php the_author(); ?>
				<?php endif; ?>
			</dc:creator>

			<?php the_category_rss( 'rss2' ); ?>

			<guid isPermaLink="false"><?php the_guid(); ?></guid>

			<?php
				// Display the post's <media:content> tags
				do_action( 'display_post_media_content_nodes', $feed_options, $post );
			?>

			<?php if ( strlen( $post->post_content ) > 0 ) : ?>

				<?php
					// Prepare the_content for display

					// Apply the the_content filter to our content (just like it's being printed on the frontend)
					// Also remove any/all shortcodes in the content
					$content = apply_filters( 'the_content', PMC::strip_shortcodes( get_the_content() ) );

					// Properly encode CDATA characters
					$content = str_replace( ']]>', ']]&gt;', $content );

					// Apply the the_content_feed filter to our content, and treat that content as RSSv2
					$content = apply_filters( 'the_content_feed', $content, 'rss2' );

					// Apply any last modifications to the content before it's printed
					// This filter is where we escape the content in a safe manor for output
					// see class-pmc-custom-feed-msn.php
					$content_safe = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post, $feed_options, basename( __FILE__ ) );
				?>

				<description><![CDATA[<?php

					// Display the post content

					// $content_safe has been escaped during assembly above
					echo $content_safe;

				?>]]></description>

			<?php else : ?>
				<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
			<?php endif; ?>

			<wfw:commentRss><?php echo esc_url( get_post_comments_feed_link( null, 'rss2' ) ); ?></wfw:commentRss>
			<slash:comments><?php echo get_comments_number(); ?></slash:comments>

			<?php rss_enclosure(); ?>

			<?php do_action( 'rss2_item' ); ?>
		</item>

		<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options ); ?>

	<?php endforeach; ?>

	<?php wp_reset_postdata(); ?>

</channel>
</rss><?php

do_action( 'pmc_custom_feed_end', $feed, $feed_options, basename( __FILE__ ) );

// EOF