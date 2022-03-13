<?php
/**
 * This feed will allow international licensees to see published and future content and start to translate it.
 * Access to this feed is restricted to a specific role capability, plus administrators.
 *
 * @author Fardin Pakravan
 *
 */
if ( ! current_user_can( 'pmc_view_custom_feeds_for_translators' ) ) {
	return false;
}

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

$more = 1;
global $feed, $post, $more, $pmc_custom_feed_qs;
$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

add_filter( 'pmc_custom_feed_esc_xml_strict', '__return_true' );
do_action( 'pmc_custom_feed_start', $feed, $feed_options, basename( __FILE__ ) );

if ( ! empty( $feed_options['show-future-posts-only'] ) ) {
	$args = [
		'post_status' => 'copy-editor, ready-to-publish, future',
	];
} else {
	$args = [
		'post_status' => 'publish, copy-editor, ready-to-publish, future',
	];
}

$last_build_date = mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false );
$update_period   = apply_filters( 'rss_update_period', 'hourly' );
$update_freq     = apply_filters( 'rss_update_frequency', '1' );
$posts           = PMC_Custom_Feed_Helper::pmc_feed_get_posts( '', $args );


echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>'; ?>

	<rss version="2.0"
			 xmlns:content="http://purl.org/rss/1.0/modules/content/"
			 xmlns:wfw="http://wellformedweb.org/CommentAPI/"
			 xmlns:dc="http://purl.org/dc/elements/1.1/"
			 xmlns:atom="http://www.w3.org/2005/Atom"
			 xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
			 xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
			 xmlns:media="http://search.yahoo.com/mrss/"
	>

		<channel>

			<title><?php bloginfo_rss( 'name' ); ?></title>

			<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml"/>

			<link><?php bloginfo_rss( 'url' ); ?></link>

			<description><?php bloginfo_rss( 'description' ); ?></description>

			<lastBuildDate><?php echo PMC_Custom_Feed_Helper::esc_xml( $last_build_date ); // WPCS: XSS ok. ?></lastBuildDate>

			<language><?php bloginfo_rss( 'language' ); ?></language>

			<sy:updatePeriod><?php echo PMC_Custom_Feed_Helper::esc_xml( $update_period ); // WPCS: XSS ok. ?></sy:updatePeriod>

			<sy:updateFrequency><?php echo PMC_Custom_Feed_Helper::esc_xml( $update_freq ); // WPCS: XSS ok. ?></sy:updateFrequency>

			<?php
			foreach ( $posts as $post ) :

				$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );

				if ( empty( $post ) ) {
					continue;
				}

				$pub_date        = get_post_time( 'D, d M Y H:i:s +0000', true, $post );
				$comments_link   = get_post_comments_feed_link( null, 'rss2' );
				$comments_number = get_comments_number();

				// Append post stauts to the post body.
				$post->post_content .= '<p><strong>Status: ' . $post->post_status . '</strong></p>';

				setup_postdata( $post );
			?>

				<item>

					<title><?php the_title_rss() ?></title>

					<link><?php the_permalink_rss() ?></link>

					<comments><?php comments_link_feed(); ?></comments>

					<pubDate><?php echo PMC_Custom_Feed_Helper::esc_xml( $pub_date ); // WPCS: XSS ok. ?></pubDate>

					<?php
					// Display the <dc:creator> node
					PMC_Custom_Feed_Helper::render_rss_author( $post, true );
					?>

					<?php
					PMC_Custom_Feed_Helper::render_media_keywords( true );
					the_category_rss( 'rss2' );

					// Render the media tags.
					do_action( 'display_post_media_content_nodes', $feed_options, $post );
					do_action( 'pmc_custom_feed_display_gallery_media_content_nodes', $feed_options, $post );
					?>

					<guid isPermaLink="false"><?php the_guid(); ?></guid>

					<?php PMC_Custom_Feed_Helper::get_image_specific_for_feed( $post->ID, 'media:thumbnail' ); ?>

					<?php if ( strlen( $post->post_content ) > 0 ) : ?>
						<description>
							<?php
							$content = apply_filters( 'the_content', get_the_content() );
							$content = preg_replace_callback( '/<img[^>]+\>/', function ( $match ) {
								return '<p>' . $match[ 0 ] . '</p>';
							}, $content );
							$content = apply_filters( 'the_content_feed', $content, 'rss2' );
							$content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post, $feed_options, basename( __FILE__ ) );

							echo '<p>';
							PMC_Custom_Feed_Helper::render_image_in_post( $post, 'img', 'featuredorfirst' );
							echo '</p>';

							echo PMC_Custom_Feed_Helper::esc_xml_cdata( $content ); // WPCS: XSS ok.
							?>
						</description>
					<?php else : ?>
						<description>
							<?php echo PMC_Custom_Feed_Helper::esc_xml_cdata( get_the_excerpt() ); // WPCS: XSS ok. ?>
						</description>
					<?php endif; ?>

					<wfw:commentRss><?php echo PMC_Custom_Feed_Helper::esc_xml( $comments_link ); // WPCS: XSS ok. ?></wfw:commentRss>

					<slash:comments><?php echo PMC_Custom_Feed_Helper::esc_xml( $comments_number ); // WPCS: XSS ok. ?></slash:comments>

					<?php rss_enclosure(); ?>
					<?php do_action( 'rss2_item' ); ?>

				</item>

				<?php
				do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
				endforeach;
				?>

		</channel>
	</rss>

<?php
do_action( 'pmc_custom_feed_end', $feed, $feed_options, basename( __FILE__ ) );
