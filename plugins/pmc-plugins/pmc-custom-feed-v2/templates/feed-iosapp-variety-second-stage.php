<?php
/**
 * RSS2 Feed Template for displaying RSS2 Featured Carousel feed.
 *
 * @package WordPress
 */

global $feed, $post, $pmc_custom_feed_qs;
if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
$more = 1;

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

do_action( 'pmc_custom_feed_start', $GLOBALS['feed'], $feed_options, basename( __FILE__ ) );

$image_size = PMC_Custom_Feed::get_instance()->get_feed_config( 'image_size' );
if ( empty( $image_size ) ) {
	$image_size = 'featured-second';
} else {
	$image_size = 'pmc_custom_image' . $image_size;
}

$data = null;

$post_id_qs = get_query_var( "fpid" );

if ( is_numeric( $post_id_qs ) ) {

	$post = get_post( intval( $post_id_qs ) );

	$post_type_object = get_post_type_object( $post->post_type );

	if ( !isset( $post_type_object->public ) || !$post_type_object->public ) {
		$post = "";
	}

	if ( !empty( $post ) ) {
		setup_postdata( $post );
		$authors = PMC_Custom_Feed_Helper::get_authors( $post->ID );
		if ( !empty( $authors ) ) {
			$author = reset( $authors );
			$author_name = $author->display_name;
			$author_url  = $author->website;
		} else {
			$author_name = '';
			$author_url  = '';
		}

		$data[$post->ID] = array(
			'title'      => get_the_title(),
			'url'        => get_permalink(),
			'date'       => get_the_date( 'r' ),
			'author'     => $author_name,
			'author-url' => $author_url,
			'excerpt'    => get_the_excerpt(),
			'category'   => '',
		);

		$terms = get_the_terms( $post, 'category' );
		if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
			$term                        = reset( $terms );
			$data[$post->ID]['category'] = $term->name;
		}
	}
}

$post_count = intval( PMC_Custom_Feed::get_instance()->get_feed_config( 'count' ) );

if ( empty( $data ) && class_exists( 'PMC_Carousel' ) ) {

	$data = pmc_render_carousel( PMC_Carousel::modules_taxonomy_name, 'inside-variety', $post_count, $image_size );

}

$thumb_size = apply_filters( 'pmc_custom_feed_thumb_size', array( 120, 90 ) );

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';
?>

<rss version="2.0"
	 xmlns:content="http://purl.org/rss/1.0/modules/content/"
	 xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	 xmlns:dc="http://purl.org/dc/elements/1.1/"
	 xmlns:atom="http://www.w3.org/2005/Atom"
	 xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	 xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	 xmlns:media="http://search.yahoo.com/mrss/"
	 xmlns:pmc="http://pmc.com/"
<?php do_action( 'pmc_iphone_app_rss2_dtd' ); ?>
	 >

	<channel>
		<title><?php

bloginfo_rss( 'name' );
wp_title_rss();
?></title>
		<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
		<link><?php bloginfo_rss( 'url' ) ?></link>
		<language><?php bloginfo_rss( 'language' ); ?></language>
		<lastBuildDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ); ?></lastBuildDate>
		<language><?php echo get_option( 'rss_language' ); ?></language>
		<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
		<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
		<?php

		if ( !is_null( $data ) && count( $data ) > 0 ) {

			foreach ( $data as $item_id => $item ) {

				// we need to set current post for the related story to work properly
				$post = get_post($item_id);
				$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );
				if ( empty( $post ) ) {
					continue;
				}
				setup_postdata( $post );

				$content = get_post_field( 'post_content', $item_id );
				if ( !empty( $content ) ) {
					$content         = apply_filters( 'the_content', PMC::strip_shortcodes( $content ) );
					$content         = str_replace( ']]>', ']]&gt;', $content );
					$item['excerpt'] = $content;
				}
				?>
				<item>
					<title><?php echo esc_html( $item['title'] ); ?></title>
					<link><?php echo esc_url( $item['url'] ); ?></link>
					<pubDate><?php echo esc_attr( $item['date'] ); ?></pubDate>
					<dc:creator><?php echo esc_attr( $item['author'] ); ?></dc:creator>
					<category><![CDATA[<?php echo esc_attr( $item['category'] ); ?>]]></category>
					<guid isPermaLink="false"><?php

			if ( $item_id > 0 ) {
//Post
				the_guid( $item_id );
			} else {
//category
				echo esc_url( $item['category-url'] );
			}
				?></guid>
					<?php

					if ( isset( $item['image'] ) && !empty( $item['image'] ) ) {
						echo "<featured>";
						echo esc_url( $item['image'] );
						echo "</featured>";
					}
					?>



					<description><![CDATA[<?php
					if ( has_post_thumbnail() ){
						$img_attrs = PMC::get_attachment_attributes( get_post_thumbnail_id( $item_id ), $image_size, $item_id );
						if( ! empty( $img_attrs['width'] ) && function_exists( 'wpcom_vip_get_resized_remote_image_url' ) ) {
							$img_attrs['src'] = wpcom_vip_get_resized_remote_image_url( $img_attrs['src'], $img_attrs['width'], $img_attrs['height'], true );
						}
						$img_attrs['class'] = 'attachment-main-article-thumb wp-post-image';
						?>
						<div class="main-article-thumbnail-wrapper">
							<?php echo PMC::get_image_html( $img_attrs ); ?>
						</div><?php
					}

						$content = apply_filters( 'the_content_feed', $item['excerpt'], 'rss2' );
						$content = $content . PMC_Custom_Feed_Helper::render_variety_video_url();
						$content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post, $feed_options, basename( __FILE__ ) );
						echo $content;
					?>]]></description>
				<?php rss_enclosure(); ?>
				<?php do_action( 'pmc_iphone_app_rss2_item', $item_id ); ?>
				<?php do_action( 'rss2_item' ); ?>
				</item>
				<?php

				do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
			}
		}
		?>
	</channel>
</rss>
<?php
	do_action( 'pmc_custom_feed_end', $GLOBALS['feed'], $feed_options, basename( __FILE__ ) );

//EOF
