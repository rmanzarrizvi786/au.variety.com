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
header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	<?php do_action('rss2_ns'); ?>
>

<channel>
	<title><?php echo PMC_Custom_Feed_Helper::esc_xml( apply_filters( 'pmc_custom_feed_title', get_bloginfo_rss( 'name' ) . get_wp_title_rss() ) ); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss('url') ?></link>
	<?php PMC_Custom_Feed_Helper::maybe_render_feed_logo(); ?>
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
		setup_postdata( $post );
?>
	<item>
		<title><?php the_title_rss() ?></title>
		<link><?php the_permalink_rss(); ?></link>
		<comments><?php comments_link_feed(); ?></comments>
		<pubDate><?php echo get_post_time( 'D, d M Y H:i:s +0000', true, $post ); ?></pubDate>
		<dc:creator><?php
			if ( function_exists( 'coauthors' ) ) {
					coauthors();
			}else{
					the_author();
			}
		?></dc:creator>
		<?php the_category_rss('rss2') ?>

		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<?php PMC_Custom_Feed_Helper::get_image_specific_for_feed( $post->ID, "media:thumbnail" ); ?>
	<?php if ( strlen( $post->post_content ) > 0 ) : ?>
		<description><![CDATA[<?php
				PMC_Custom_Feed_Helper::render_image_in_post( $post, 'img', 'featuredorfirst' );
				$required_shortcodes = apply_filters( 'pmc_custom_feed_rss2_required_shortcodes', ['jwplayer', 'youtube', 'buy-now'], $post, $feed_options );
				$content = PMC_Custom_Feed_Helper::get_instance()->process_required_shortcodes( get_the_content(), $feed_options, $required_shortcodes );
				$content = apply_filters( 'the_content', PMC::strip_shortcodes( $content ) );
				$content = str_replace( ']]>', ']]&gt;', $content );
				$content = apply_filters( 'the_content_feed', $content, 'rss2' );
				$content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post, $feed_options, basename( __FILE__ ) );
				echo $content;
        ?>]]></description>
	<?php else : ?>
		<description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
	<?php endif; ?>

		<wfw:commentRss><?php echo esc_url( get_post_comments_feed_link(null, 'rss2') ); ?></wfw:commentRss>
		<slash:comments><?php echo get_comments_number(); ?></slash:comments>
		<?php
		if ( 'pmc-gallery' === $post->post_type && ! empty( $feed_options['is-google-newsstand-gallery'] ) ) {

			$gallery_items = PMC_Custom_Feed_Helper::get_gallery_images( $post->ID );

			if ( ! empty( $gallery_items ) && is_array( $gallery_items ) ) {

				echo '<content:encoded><![CDATA[<section class="type:slideshow">';

				foreach ( $gallery_items as $gallery_item ) :

					if ( ! empty( $gallery_item['image'] ) ) :
						?>

								<figure>
									<img src="<?php echo esc_url( $gallery_item['image'] ); ?>" />
									<figcaption>
									<?php
										echo PMC_Custom_Feed_Helper::esc_xml( strip_tags( $gallery_item['caption'] ) ); // @codingStandardsIgnoreLine.
									?>
									</figcaption>
								</figure>

						<?php
					endif;

				endforeach;

				echo '</section>]]></content:encoded>';

			}
		}
		?>
<?php rss_enclosure(); ?>
	<?php do_action('rss2_item'); ?>
	<?php \PMC\Custom_Feed\PMC_Newsbreak::add_ga_tags_to_newsbreak_feeds(); ?>
	</item>
	<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
	endforeach; ?>
</channel>
</rss>
<?php
	do_action('pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );

// EOF
