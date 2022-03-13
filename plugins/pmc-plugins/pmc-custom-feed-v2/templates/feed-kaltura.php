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

PMC_Custom_Feed_Helper::handle_shortcode_tag_for_feed();
header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
$more = 1;

$pmc_custom_feed_qs= null;

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action('pmc_custom_feed_start', $feed, $feed_options, basename(__FILE__) );

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed );

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:media="http://search.yahoo.com/mrss/"
    >

    <channel>
        <title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
        <link><?php bloginfo_rss('url') ?></link>
        <description><?php bloginfo_rss("description") ?></description>
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
			<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
			<guid isPermaLink="false"><?php echo PMC_Custom_Feed_Helper::pmc_feed_add_query_string( get_the_guid() ); ?></guid>
			<link><?php echo PMC_Custom_Feed_Helper::pmc_feed_add_query_string( get_permalink() ); ?></link>
			<pubDate><?php echo get_post_time('Y-m-d H:i:s +0000', true); ?></pubDate>
			<media:content  url="<?php echo esc_url( get_post_meta( $post->ID, '_pmc-entv-video-url' , true ) ); ?>" width="<?php echo  (int)get_post_meta( $post->ID, '_pmc-entv-video-width' , true ); ?>" bitrate="<?php echo  (int)get_post_meta( $post->ID, '_pmc-entv-video-bitrate' , true ); ?>" type="<?php echo esc_attr( get_post_meta( $post->ID, '_pmc-entv-video-type' , true ) ); ?>" medium="video" duration="<?php echo  (int)get_post_meta( $post->ID, '_pmc-entv-video-duration' , true ); ?>" lang="en" />
			<media:title><?php echo get_the_title_rss() ?></media:title>
			<media:description><![CDATA[<?php the_excerpt_rss(); ?>]]></media:description>
			<media:keywords><![CDATA[<?php
					$i=0;
					$tags = get_the_tags();
					if( !empty( $tags ))
					foreach( $tags as $tag ){
						if( 0 != $i ){
							echo ',';
						}else{
							$i++;
						}
						echo $tag->name;
					}
				?>]]></media:keywords>

			<media:thumbnail url="<?php
				$image_id = get_post_thumbnail_id();
				$image_url = wp_get_attachment_image_src($image_id, 'thumbnail', true);
					if( isset( $image_url[0] ) )
						echo $image_url[0];
			?>"></media:thumbnail>
			<?php
			$categories = get_the_category( $post->ID );
			if ( !empty( $categories ) ) {
				foreach ( $categories as $cat ) {
					?>
					<media:category label="<?php echo esc_html( $cat->name ); ?>">
						<?php echo esc_html( $cat->name ); ?>
					</media:category>
				<?php
				}
			}
			?>
            <dc:creator><?php the_author() ?></dc:creator>
           <?php rss_enclosure(); ?>

        </item>
        <?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
		endforeach; ?>
    </channel>
</rss>
<?php
	do_action('pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );

// EOF
