<?php
/**
 * MSN RSSv2 Template
 */

global $feed, $post, $pmc_custom_feed_qs;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

// Set the RSSv2 headers
header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

// Fetch the feed configuration
$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

// Announce when this template is being rendered
do_action( 'pmc_custom_feed_start', $feed, $feed_options, basename( __FILE__ ) );

// Printing here simply because the <? character causes IDE's to treat this as PHP
printf(
	"<?xml version='1.0' encoding='%s' ?>",
	esc_attr( get_option('blog_charset') )
); ?>

<rss version="2.0"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:media="http://search.yahoo.com/mrss/"
	<?php PMC_Custom_Feed_Helper::render_rss_namespace( $posts, $feed_options ); ?>>

	<channel>
		<?php
			// Print the feed title
			printf(
				'<title>%s</title>',
				esc_html( get_bloginfo( 'name' ) )
			);
		?>

		<link><?php esc_url( bloginfo_rss('url') ); ?></link>
		<?php PMC_Custom_Feed_Helper::maybe_render_feed_logo(); ?>
		<description><?php echo esc_html( bloginfo_rss( 'description' ) ); ?></description>
		<lastBuildDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ) ); ?></lastBuildDate>
		<language><?php echo esc_html( bloginfo_rss( 'language' ) ); ?></language>

		<?php
			// Fetch the posts to display
			if ( !empty( $feed_options['most-popular-posts'] ) ) {
				$posts = PMC_Custom_Feed_Popular_Posts::get_instance()->get_posts( $feed );
			} else {
				$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed );
			}

			// Announce when this <channel> is being rendered
			do_action( 'pmc_custom_feed_channel', $posts, $feed_options );

			foreach( $posts as $post ) :

				$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );

				if ( empty( $post ) ) {
					continue;
				}

				setup_postdata($post);
		?>

		<item>
			<title><?php echo esc_html( get_the_title_rss() ); ?></title>
			<description><![CDATA[<?php esc_html( the_excerpt_rss() ); ?>]]></description>
			<link><?php echo PMC_Custom_Feed_Helper::the_permalink_rss( PMC_Custom_Feed_Helper::pmc_feed_add_query_string( get_permalink(), false ) ); ?></link>
			<pubDate><?php echo esc_html( get_post_time( 'D, d M Y H:i:s +0000', true, $post ) ); ?></pubDate>
			<guid isPermaLink="false"><?php echo esc_url( PMC_Custom_Feed_Helper::pmc_feed_add_query_string( get_the_guid() ) ); ?></guid>

			<?php
				// Display the <dc:creator> node
				// This markup has been escaped during assembly.
				//
				// Note, because this node name contains a colon
				// in the <dc:creator> tag, wp_kses() is
				// unable to help us escape.
				PMC_Custom_Feed_Helper::render_rss_author( $post, true );
			?>

			<?php
				// Display the <media:keywords> node
				// This markup has been escaped during assembly.
				//
				// wp_kses is unable to help us here because
				// the <media:keywords> tag is treated as 'malformed'
				// and an empty string is returned.
				PMC_Custom_Feed_Helper::render_media_keywords();
			?>

			<?php
				// Display the <category> nodes
				// This markup has been escaped during assembly.
				//
				// wp_kses is unable to help us here because
				// the category nodes contents contains CDATA
				// which wp_kses strips out.
				PMC_Custom_Feed_Helper::the_category_rss( false );
			?>

			<?php
				// Display the post's <media:content> tags
				do_action( 'display_post_media_content_nodes', $feed_options, $post );
			?>

			<?php
				// Announce this feed item is being created
				// Other header-like values for the <item> may be output
				do_action( 'pmc_custom_feed_item', $post, $feed_options );
			?>

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

			<content:encoded><![CDATA[<?php

				// Display the post content

				// $content_safe has been escaped during assembly above
				echo $content_safe;

			?>]]></content:encoded>

			<?php rss_enclosure(); ?>

		</item>

		<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options ); ?>

		<?php endforeach; ?>

	</channel>
</rss>

<?php do_action('pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );


// EOF
