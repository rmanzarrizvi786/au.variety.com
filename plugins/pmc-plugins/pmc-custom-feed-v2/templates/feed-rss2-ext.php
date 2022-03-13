<?php
global $feed, $post, $pmc_custom_feed_qs;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action( 'pmc_custom_feed_start', $feed, $feed_options, basename( __FILE__ ) );

// We need this code here to initialize the $posts variable that is used by render_rss_namespace
if ( !empty( $feed_options['most-popular-posts'] ) ) {
	$posts = PMC_Custom_Feed_Popular_Posts::get_instance()->get_posts( $feed );
} else {
	$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed );
}

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:media="http://search.yahoo.com/mrss/"
	<?php PMC_Custom_Feed_Helper::render_rss_namespace( $posts, $feed_options ); ?>
>

<channel>
	<?php PMC_Custom_Feed_Helper::render_feed_title() ?>
	<link><?php bloginfo_rss('url') ?></link>
	<?php PMC_Custom_Feed_Helper::maybe_render_feed_logo(); ?>
	<description><?php bloginfo_rss("description") ?></description>
	<lastBuildDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ) ); ?></lastBuildDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
<?php

	do_action( 'pmc_custom_feed_channel', $posts, $feed_options );

	foreach( $posts as $post ) :

		$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );

		if ( empty( $post ) ) {
			continue;
		}

		setup_postdata($post);

?>
	<item<?php PMC_Custom_Feed_Helper::render_attr( 'item' ); ?>>
		<title><?php echo get_the_title_rss() ?></title>
		<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
		<?php
		PMC_Custom_Feed_Helper::render_rss_author();
		?>
		<pubDate><?php echo esc_html( get_post_time( 'D, d M Y H:i:s +0000', true, $post ) ); ?></pubDate>
		<?php do_action( 'pmc_custom_feed_item', $post, $feed_options ); ?>
		<link><?php echo PMC_Custom_Feed_Helper::the_permalink_rss( PMC_Custom_Feed_Helper::pmc_feed_add_query_string( get_permalink(), false ) ); ?></link>
<?php
		$render_featured_or_first_image_in_post_params = apply_filters( 'render_featured_or_first_image_in_post_params', array(
			$post->ID,
			true,
		), $feed_options );
		if ( !empty( $render_featured_or_first_image_in_post_params ) ) {
			call_user_func_array( array( 'PMC_Custom_Feed_Helper', 'render_featured_or_first_image_in_post' ), $render_featured_or_first_image_in_post_params );
		}
?>
		<?php PMC_Custom_Feed_Helper::the_category_rss(); ?>
		<guid isPermaLink="false"><?php echo PMC_Custom_Feed_Helper::pmc_feed_add_query_string( get_the_guid() ); ?></guid>
		<?php PMC_Custom_Feed_Helper::get_image_specific_for_feed( $post->ID, "media:thumbnail" ); ?>
<?php
		ob_start();
		echo PMC_Custom_Feed_Helper::render_image_in_post( $post, 'media:content', 'checkgallery' );
		do_action('pmc_custom_feed_item_media_group', $post );
		$content = trim( ob_get_clean() );
		if ( !empty( $content ) ) {
			?>
			<media:group<?php PMC_Custom_Feed_Helper::render_attr( 'media_group' ); ?>>
			<?php echo $content; ?>
			</media:group>
			<?php
		}
?>
<content:encoded><![CDATA[<?php
		PMC_Custom_Feed_Helper::render_first_image_in_gallery($post);
		PMC_Custom_Feed_Helper::render_image_in_post( $post, 'img', 'featuredorfirst' );
		$required_shortcodes = apply_filters( 'pmc_custom_feed_rss2_ext_required_shortcodes', ['jwplayer', 'youtube'], $post, $feed_options );
		$content = PMC_Custom_Feed_Helper::get_instance()->process_required_shortcodes( get_the_content(), $feed_options, $required_shortcodes );

		$content = apply_filters( 'the_content', PMC::strip_shortcodes( $content ) );
		$content = str_replace( ']]>', ']]&gt;', $content );
		$content = apply_filters( 'the_content_feed', $content, 'rss2' );
		$content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post, $feed_options, basename( __FILE__ ) );
		echo $content;
?>]]></content:encoded>
<?php
	rss_enclosure();
?>
	</item>
	<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
	endforeach; ?>
</channel>
</rss>
<?php
do_action('pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );

// EOF
