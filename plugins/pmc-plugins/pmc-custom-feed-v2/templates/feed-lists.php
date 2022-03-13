<?php
/**
 * RSSv2 Slideshow Template for list posts.
 *
 * @see https://partnerhub.msn.com/docs/example/vcurrent/example-rss-slideshow/AAsCx
 *
 * @package PMC_Custom_Feed_V2/Templates
 */

global $feed;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

// Set the RSSv2 headers.
header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

// Fetch the feed configuration.
$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

// Announce when this template is being rendered.
do_action( 'pmc_custom_feed_start', $feed, $feed_options, basename( __FILE__ ) );

// Printing here simply because the <? character causes IDE's to treat this as PHP.
printf(
	"<?xml version='1.0' encoding='%s' ?>",
	esc_attr( get_option( 'blog_charset' ) )
);
?>

	<rss
		xmlns:atom="http://www.w3.org/2005/Atom"
		xmlns:media="http://search.yahoo.com/mrss/"
		xmlns:mi="http://schemas.ingestion.microsoft.com/common/"
		xmlns:content="http://purl.org/rss/1.0/modules/content/"
		xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:dcterms="http://purl.org/dc/terms/"
		version="2.0">
		<?php PMC_Custom_Feed_Helper::render_rss_namespace( false, $feed_options ); ?>

		<channel>
			<?php
			// Print the feed title.
			printf(
				'<title>%s</title>',
				PMC_Custom_Feed_Helper::esc_xml( get_bloginfo( 'name' ) )
			);
			?>

			<link><?php echo PMC_Custom_Feed_Helper::esc_xml( get_bloginfo_rss( 'url' ) ); ?></link>
			<?php PMC_Custom_Feed_Helper::maybe_render_feed_logo(); ?>
			<description><?php echo PMC_Custom_Feed_Helper::esc_xml( get_bloginfo_rss( 'description' ) ); ?></description>
			<lastBuildDate><?php echo PMC_Custom_Feed_Helper::esc_xml( mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ) ); ?></lastBuildDate>
			<language><?php echo PMC_Custom_Feed_Helper::esc_xml( get_bloginfo_rss( 'language' ) ); ?></language>

			<?php
			// Fetch the posts to display.
			$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed );

			// Announce when this <channel> is being rendered.
			do_action( 'pmc_custom_feed_channel', $posts, $feed_options );

			// We need this instance to render the list items in html format
			$feed_lists_object = \PMC\Custom_Feed\Lists::get_instance();

			foreach ( $posts as $post ) :

				$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );

				if ( empty( $post ) ) {
					continue; // @codeCoverageIgnore
				}

				setup_postdata( $post );
				?>

				<item>
					<guid isPermaLink="false"><?php echo PMC_Custom_Feed_Helper::esc_xml( PMC_Custom_Feed_Helper::pmc_feed_add_query_string( get_the_guid() ) ); ?></guid>
					<title><?php echo PMC_Custom_Feed_Helper::esc_xml( get_the_title_rss() ); ?></title>
					<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
					<pubDate><?php echo PMC_Custom_Feed_Helper::esc_xml( get_post_time( 'D, d M Y H:i:s +0000', true, $post ) ); ?></pubDate>
					<link><?php echo PMC_Custom_Feed_Helper::the_permalink_rss( PMC_Custom_Feed_Helper::pmc_feed_add_query_string( get_permalink(), false ) ); ?></link>
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
					// Announce this feed item is being created
					// Other header-like values for the <item> may be output.
					do_action( 'pmc_custom_feed_item', $post, $feed_options );
					?>

					<content:encoded><?php
						$content = apply_filters( 'the_content', $feed_lists_object->get_html( $post ) );
						$content = apply_filters( 'the_content_feed', $content, 'rss2' );
						$content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post, $feed_options, basename( __FILE__ ) );
						echo PMC_Custom_Feed_Helper::esc_xml_cdata( $content ); //  WPCS: XSS ok, xml cdata encoded
					?></content:encoded>

					<?php rss_enclosure(); ?>

				</item>

				<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options ); ?>

			<?php endforeach; ?>

			<?php wp_reset_postdata(); ?>

		</channel>
	</rss>

<?php
do_action( 'pmc_custom_feed_end', $feed, $feed_options, basename( __FILE__ ) );
