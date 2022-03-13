<?php
/**
 * Breaking News Feed Template for displaying rss Posts feed.
 *
 * @package WordPress
 */

global $feed, $post, $pmc_custom_feed_qs;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

$more = 1;

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action( 'pmc_custom_feed_start', $feed, $feed_options, basename( __FILE__ ) );

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts();
header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
echo '<?xml version="1.0" encoding="' . esc_attr( get_option( 'blog_charset' ) ) . '"?>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	<?php do_action( 'rss2_ns' ); ?>
>

<channel>
	<title><?php echo PMC_Custom_Feed_Helper::esc_xml( apply_filters( 'pmc_custom_feed_title', get_bloginfo_rss( 'name' ) . get_wp_title_rss() ) ); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss( 'url' ); ?></link>
	<ttl>60</ttl>
	<?php PMC_Custom_Feed_Helper::maybe_render_feed_logo(); ?>
	<description><?php bloginfo_rss( 'description' ); ?></description>
	<lastBuildDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ) ); ?></lastBuildDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<sy:updatePeriod><?php echo esc_html( apply_filters( 'rss_update_period', 'hourly' ) ); ?></sy:updatePeriod>
	<sy:updateFrequency><?php echo esc_html( apply_filters( 'rss_update_frequency', '1' ) ); ?></sy:updateFrequency>
	<?php do_action( 'rss2_head' ); ?>
	<?php
	foreach( $posts as $post ) :
		$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );
		if ( empty( $post ) ) {
			continue;
		}

		setup_postdata( $post );
		?>
	<item>
		<title><?php the_title_rss(); ?></title>
		<link><?php the_permalink_rss(); ?></link>
		<comments><?php comments_link_feed(); ?></comments>
		<pubDate><?php echo esc_html( get_post_time( 'c', true, $post ) ); ?></pubDate>
		<dc:creator>
		<?php
		if ( function_exists( 'coauthors' ) ) {
			coauthors();
		} else {
			the_author();
		}
		?>
		</dc:creator>
		<?php the_category_rss( 'rss2' ); ?>

		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<?php PMC_Custom_Feed_Helper::get_image_specific_for_feed( $post->ID, 'media:thumbnail' ); ?>
	<?php if ( strlen( $post->post_content ) > 0 ) : ?>
		<description>
		<?php
				PMC_Custom_Feed_Helper::render_image_in_post( $post, 'img', 'featuredorfirst' );
				$content = apply_filters( 'the_content', PMC::strip_shortcodes( get_the_content() ) );
				$content = apply_filters( 'the_content_feed', $content, 'rss2' );
				$content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post, $feed_options, basename( __FILE__ ) );
				echo PMC_Custom_Feed_Helper::esc_xml_cdata( $content );
        ?>
		</description>
	<?php else : ?>
		<description><?php PMC_Custom_Feed_Helper::esc_xml_cdata( the_excerpt_rss() ); ?></description>
	<?php endif; ?>

		<wfw:commentRss><?php echo PMC_Custom_Feed_Helper::esc_xml( get_post_comments_feed_link( null, 'rss2' ) ); ?></wfw:commentRss>
		<slash:comments><?php echo PMC_Custom_Feed_Helper::esc_xml( get_comments_number() ); ?></slash:comments>
		<?php
		if ( 'pmc-gallery' === $post->post_type && ! empty( $feed_options['is-google-newsstand-gallery'] ) ) {

			$gallery_items = PMC_Custom_Feed_Helper::get_gallery_images( $post->ID );

			if ( ! empty( $gallery_items ) && is_array( $gallery_items ) ) {

				echo '<section class="type:slideshow">';

				foreach ( $gallery_items as $gallery_item ) :

					if ( ! empty( $gallery_item['image'] ) ) :
						?>
						<figure>
							<img src="<?php echo PMC_Custom_Feed_Helper::esc_xml( $gallery_item['image'] ); ?>" />
								<figcaption>
								<?php
									echo PMC_Custom_Feed_Helper::esc_xml( strip_tags( $gallery_item['caption'] ) ); // @codingStandardsIgnoreLine.
								?>
								</figcaption>
							</figure>

						<?php
					endif;

				endforeach;

				echo '</section>';

			}
		}
		?>
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


// EOF
