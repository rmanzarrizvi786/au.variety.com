<?php
/**
 * Created by JetBrains PhpStorm.
 * User: adaezeesiobu
 * Date: 2/29/12
 * Time: 1:11 PM
 * To change this template use File | Settings | File Templates.
 */

global $feed, $post, $pmc_custom_feed_qs;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
$more = 1;

$pmc_custom_feed_qs= null;

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action('pmc_custom_feed_start', $feed, $feed_options, basename(__FILE__) );
PMC_Custom_Feed_Helper::handle_shortcode_tag_for_feed();

// PPT-3967 - this is less than ideal; it should be handled in `PMC_Custom_Feed_Helper::pmc_feed_get_posts()`, but this is how it was done in Custom Feeds v1
$args = apply_filters( 'pmc_syndicate_post_feed-yahoo', array() );
$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed, $args );
PMC_Custom_Feed_Helper::add_filter_for_host();

$thumb_size = !empty( $GLOBALS['_wp_additional_image_sizes']['yahoo-thumb'] ) ? 'yahoo-thumb' : 'full';

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:media="http://search.yahoo.com/mrss/"
	xmlns:mi="http://schemas.ingestion.microsoft.com/common/"
	>

	<channel>
		<title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
		<link><?php bloginfo_rss('url'); ?></link>
		<description><?php bloginfo_rss("description"); ?></description>
		<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
		<language><?php bloginfo_rss( 'language' ); ?></language>
<?php
		foreach( $posts as $post ) :
			$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );
			if ( empty( $post ) ) {
				continue;
			}

			setup_postdata( $post );
?>
		<item>
				<title><?php echo get_the_title_rss(); ?></title>
				<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
				<dc:creator><?php if ( function_exists( 'coauthors' ) ) {
						coauthors();
					} else {
						the_author();
					} ?></dc:creator>
				<pubDate><?php echo get_post_time( 'D, d M Y H:i:s +0000', true, $post ); ?></pubDate>
				<link><?php echo PMC_Custom_Feed_Helper::the_permalink_rss( PMC_Custom_Feed_Helper::pmc_feed_add_query_string( get_permalink(), false ) ); ?></link>
				<?php PMC_Custom_Feed_Helper::render_media_content( $post->ID, 'full' ); ?>
				<?php the_category_rss('rss2'); ?>
				<guid isPermaLink="false"><?php echo PMC_Custom_Feed_Helper::pmc_feed_add_query_string( get_the_guid() ); ?></guid>
				<content:encoded><![CDATA[<?php
					PMC_Custom_Feed_Helper::render_first_image_in_gallery($post, $thumb_size); // @todo Corey Gilmore hook onto the pmc_custom_feed_content filter?
					PMC_Custom_Feed_Helper::render_image_in_post( $post, 'img', 'featuredorfirst', $thumb_size ); // @todo Corey Gilmore hook onto the pmc_custom_feed_content filter?
					$required_shortcodes = apply_filters( 'pmc_custom_feed_yahoo_required_shortcodes', ['jwplayer', 'youtube', 'buy-now'], $post, $feed_options );
					$content = PMC_Custom_Feed_Helper::get_instance()->process_required_shortcodes( get_the_content(), $feed_options, $required_shortcodes );
					$content = apply_filters( 'the_content', PMC::strip_shortcodes( $content ) );
					$content = str_replace( ']]>', ']]&gt;', $content );
					$content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post,$feed_options, basename(__FILE__) );
					$content = apply_filters( 'pmc_custom_feed_content_' . $feed, $content, $feed, $post, $feed_options, basename(__FILE__) );

					echo $content;
				?>]]></content:encoded>
				<?php rss_enclosure(); ?>
		</item>
		<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options ); ?>
		<?php endforeach; ?>
    </channel>
</rss>
<?php
	do_action('pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );

// EOF
