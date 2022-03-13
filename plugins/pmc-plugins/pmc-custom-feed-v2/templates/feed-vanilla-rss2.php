<?php
/**
 * Based off the included WordPress RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @author Corey Gilmore
 *
 */

global $feed, $post, $pmc_custom_feed_qs;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

do_action( 'pmc_custom_feed_start', $feed, $feed_options, basename( __FILE__ ) );

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts();
	// @note Corey Gilmore If you want to call something like `PMC_Custom_Feed_Helper::add_filter_for_host()`, use the `pmc_custom_feed_start` action.

$more = 1;
header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';

?>
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
	<?php
		do_action('pmc_custom_feed_item_start', $post, $feed, $feed_options, basename(__FILE__) );
		// @note Corey Gilmore Use the `pmc_custom_feed_item_start` action for things like `PMC_Custom_Feed_Helper::handle_custom_post_type_start()`

		$excerpt = get_the_excerpt();
		$excerpt = apply_filters('the_excerpt_rss', $excerpt);
		$excerpt = apply_filters( 'pmc_custom_feed_excerpt', $excerpt, $feed, $post,$feed_options, basename(__FILE__) );
		$excerpt = apply_filters( 'pmc_custom_feed_excerpt_' . $feed, $excerpt, $feed, $post, $feed_options, basename(__FILE__) );

		$content = get_the_content_feed('rss2');
		$content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post,$feed_options, basename(__FILE__) );
		$content = apply_filters( 'pmc_custom_feed_content_' . $feed, $content, $feed, $post, $feed_options, basename(__FILE__) );

		$author_display_names = (array)PMC_Custom_Feed_Helper::get_author_display_names( $post->ID )

	?>
		<title><?php the_title_rss() ?></title>
		<link><?php the_permalink_rss() ?></link>
		<comments><?php comments_link_feed(); ?></comments>
		<pubDate><?php echo get_post_time( 'D, d M Y H:i:s +0000', true, $post ); ?></pubDate>
		<dc:creator><![CDATA[<?php echo esc_html( $author_display_names[0] ); ?>]]></dc:creator>
		<author>
			<?php foreach( $author_display_names as $author_display_name ) : ?>
				<name><![CDATA[<?php echo esc_html( $author_display_name ); ?>]]></name>
			<?php endforeach; ?>
		</author>
		<?php the_category_rss('rss2') ?>

		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<description><![CDATA[<?php
			echo $excerpt;
			// @TODO: This a custom feed v1 function, do we still need this?
			PMC_Custom_Feed_Helper::get_feed_tracking();
		?>]]></description>
	<?php if ( strlen( $content ) > 0 ) : ?>
		<content:encoded><![CDATA[<?php
			echo $content;
			// @TODO: This a custom feed v1 function, do we still need this?
			PMC_Custom_Feed_Helper::get_feed_tracking();
		?>]]></content:encoded>
	<?php else : ?>
		<content:encoded><![CDATA[<?php
			echo $excerpt;
			// @TODO: This a custom feed v1 function, do we still need this?
			PMC_Custom_Feed_Helper::get_feed_tracking();
		?>]]></content:encoded>
	<?php endif; ?>
<?php rss_enclosure(); ?>
	<?php do_action('rss2_item'); ?>
	</item>
	<?php endforeach; ?>
</channel>
</rss>
<?php
	do_action('pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );

// EOF
