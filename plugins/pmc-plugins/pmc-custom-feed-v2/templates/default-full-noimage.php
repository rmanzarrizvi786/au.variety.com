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

$pmc_custom_feed_qs= null;

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action('pmc_custom_feed_start', $feed, $feed_options, basename(__FILE__) );

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed );

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:media="http://search.yahoo.com/mrss/"
>

<channel>
	<title><?php echo PMC_Custom_Feed_Helper::esc_xml( apply_filters( 'pmc_custom_feed_title', get_bloginfo_rss( 'name' ) . get_wp_title_rss() ) ); ?></title>
	<link><?php bloginfo_rss('url') ?></link>
	<?php PMC_Custom_Feed_Helper::maybe_render_feed_logo(); ?>
	<description><?php bloginfo_rss("description") ?></description>
	<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
<?php
	foreach( $posts as $post ) :

		$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );
		if ( empty( $post ) ) {
			continue;
		}
		setup_postdata($post);
?>
	<item>
		<title><?php echo get_the_title_rss() ?></title>
                <description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
                <?php PMC_Custom_Feed_Helper::render_rss_author(); ?>
                <pubDate><?php echo get_post_time( 'D, d M Y H:i:s +0000', true, $post ); ?></pubDate>
		<?php do_action( 'pmc_custom_feed_item', $post, $feed_options ); ?>
		<link><?php echo PMC_Custom_Feed_Helper::pmc_feed_add_query_string( get_permalink() ); ?></link>
		<?php
		$render_featured_or_first_image_in_post_params = apply_filters( 'render_featured_or_first_image_in_post_params', array(
			$post->ID,
			true,
		), $feed_options );
		if ( !empty( $render_featured_or_first_image_in_post_params ) ) {
			call_user_func_array( array( 'PMC_Custom_Feed_Helper', 'render_featured_or_first_image_in_post' ), $render_featured_or_first_image_in_post_params );
		}
		?>
		<?php the_category_rss('rss2') ?>
		<guid isPermaLink="false"><?php echo PMC_Custom_Feed_Helper::pmc_feed_add_query_string( get_the_guid() ); ?></guid>
		<?php PMC_Custom_Feed_Helper::get_image_specific_for_feed( $post->ID, "media:thumbnail" ); ?>
                <media:group>
                    <?php echo PMC_Custom_Feed_Helper::render_image_in_post( $post, 'media:content', 'checkgallery' ); ?>
                </media:group>
                <content:encoded><![CDATA[<?php
                $content = PMC_Custom_Feed_Helper::remove_image_tag( get_the_content_feed('rss2') );
                $content = preg_replace('/<a[^>]*>\s*<\/a>/i', '', $content );
                $content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post, $feed_options, basename( __FILE__ ) );
				echo $content;
        ?>]]></content:encoded><?php rss_enclosure(); ?>

	</item>
	<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
	endforeach; ?>
</channel>
</rss>
<?php
	do_action('pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );

// EOF
