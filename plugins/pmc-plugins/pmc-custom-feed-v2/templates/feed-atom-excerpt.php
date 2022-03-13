<?php
/**
 * Atom Feed Template for displaying Atom Posts feed.
 *
 * @package WordPress
 */

global $feed, $post;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

header( 'Content-Type: ' . feed_content_type( 'atom' ) . '; charset=' . get_option( 'blog_charset' ), true );


$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action('pmc_custom_feed_start', $feed, $feed_options, basename(__FILE__) );

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed );

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>'; ?>

<feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
	<title><?php echo PMC_Custom_Feed_Helper::esc_xml( apply_filters( 'pmc_custom_feed_title', get_bloginfo_rss( 'name' ) . get_wp_title_rss() ) ); ?></title>
    <updated><?php echo mysql2date( 'Y-m-d\TH:i:s\Z', get_lastpostmodified( 'GMT' ), false ); ?></updated>
    <id><?php self_link(); ?></id>
    <link rel="self" type="application/atom+xml" href="<?php self_link(); ?>"/>

<?php
	$site_logo = apply_filters('atom-feed-site-logo-image', false );
	if ( !empty( $site_logo ) ) {
		echo '<logo>' . esc_html( $site_logo) .'</logo>';
	}

	// avoid using global $post variable foreach loop
	foreach ( $posts as $current_post ) :

		$related_links = [];

		if ( class_exists( 'PMC\Automated_Related_Links\Plugin' ) ) {
			$instance      = \PMC\Automated_Related_Links\Plugin::get_instance();
			$related_links = $instance->get_related_links( $current_post->ID );
		}

		if ( ( empty( $related_links ) || ! is_array( $related_links ) ) && function_exists( 'pmc_related_articles' ) ) {
			// This call to related articles would change $post variable to custom post's single post and not post from our $posts array.
			$related_posts = pmc_related_articles( $current_post->ID );
			$related_posts = ( ! empty( $related_posts ) && is_array( $related_posts ) ) ? $related_posts : [];

			foreach ( $related_posts as $item ) {
				$related_links[] = [
					'id'        => $item->post_id,
					'title'     => $item->title,
					'url'       => get_permalink( $item->post_id ),
					'automated' => true,
				];
			}

		}

		// restore global $post to current post and setup current post data
		$post = $current_post;

		// Note $current_post variable should not be use beyond this point
		// the following function call will modified global $post variable base on post type
		$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );
		if ( empty( $post ) ) {
			continue;
		}
		setup_postdata( $post );
?>
    <entry>
        <title><![CDATA[<?php the_title_rss() ?>]]></title>
        <link rel="alternate" type="<?php bloginfo_rss( 'html_type' ); ?>" href="<?php the_permalink_rss() ?>"/>
		<?php
		if ( ! empty( $related_links ) && is_array( $related_links ) ) {

			$i = 0;

			foreach ( $related_links as $item ) {

				if ( $i++ > 2 ) {
					break;
				}

				printf(
					'<link rel="related" title="%s" href="%s"/>',
					esc_attr( $item['title'] ),
					esc_url( $item['url'] )
				);

			}

		}
		?>
        <id><?php echo get_the_guid(); ?></id>
        <updated><?php echo get_post_modified_time( 'Y-m-d\TH:i:s\Z', true, $post ); ?></updated>
        <published><?php echo get_post_time( 'Y-m-d\TH:i:s\Z', true, $post ); ?></published>

		<?php

		$author_names = PMC_Custom_Feed_Helper::get_author_display_names( $current_post->ID );

		if ( !empty( $author_names ) ) {
			$name = array_shift( $author_names );
			?>
				<author><name><?php echo PMC_Custom_Feed_Helper::esc_xml( $name ); ?></name></author>
			<?php
			foreach ( $author_names as $name ) {
			?>
				<contributor><name><?php echo PMC_Custom_Feed_Helper::esc_xml( $name ); ?></name></contributor>
			<?php
			}
		}

		$src = '';
		if ( has_post_thumbnail() ) {
			$src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' );
		}
		if ( !empty( $src ) ) {
			$src = $src[0];
?>
            <media:content url="<?php echo esc_url( $src );?>"/>
<?php
			$image_credit = get_post_meta( get_post_thumbnail_id( $post->ID ), '_image_credit', true );
			if( ! empty( $image_credit ) ) {
?>
			<media:credit><?php echo esc_html($image_credit); ?></media:credit>
<?php
			}
			unset( $image_credit );
			PMC_Custom_Feed_Helper::render_media_title();
		}
?>
        <summary><![CDATA[<?php
			$nohtml_content = PMC_Custom_Feed_Helper::get_content( 'nohtml' );
			$content = explode( " ", $nohtml_content );
			$content = implode( " ", array_slice( $content, 0, 250 ) );
			$content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post, $feed_options, basename( __FILE__ ) );
			echo $content;
			?>]]>
        </summary>
    </entry>
	<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
endforeach; ?>
</feed>
<?php
	do_action('pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );

// EOF
