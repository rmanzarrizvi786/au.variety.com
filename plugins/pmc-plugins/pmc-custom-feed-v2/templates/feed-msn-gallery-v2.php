<?php
/**
 * Atom Feed Template for displaying Atom Gallery Posts feed.
 *
 * @package WordPress
 */

global $feed, $post, $pmc_custom_feed_qs;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

// UTM parameters from feed configs.
$feed_options_utm_params     = PMC\Custom_Feed\PMC_Feed_UTM_Params::get_instance()->get_utm_params( $feed_options );
$feed_options_utm_params_str = http_build_query( $feed_options_utm_params );

$utm_params = ! empty( $feed_options_utm_params ) ? '#' . $feed_options_utm_params_str : '';

$ms_gallery_posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed );

if ( empty( $ms_gallery_posts ) ) {
	return;
}

foreach( $ms_gallery_posts as $ms_gallery_post ) {
	if ( 'pmc-gallery' == get_post_type( $ms_gallery_post ) ) {
		$gallery_posts[] = $ms_gallery_post;
	}
}

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

do_action('pmc_custom_feed_start', $feed, $feed_options, basename(__FILE__) );

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
?>
<rss
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:media="http://search.yahoo.com/mrss/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:dcterms="http://purl.org/dc/terms/"
	xmlns:mi="http://schemas.ingestion.microsoft.com/common/"
	version="2.0">
	<channel>
		<?php PMC_Custom_Feed_Helper::render_feed_title(); ?>
		<link><?php self_link(); ?></link>
		<description><?php bloginfo_rss("description") ?></description>
		<lastBuildDate><?php echo mysql2date( 'Y-m-d\TH:i:s\Z', get_lastpostmodified( 'GMT' ), false ); ?></lastBuildDate>
		<?php
		if ( ! empty( $gallery_posts ) ) {
		// avoid using global $post variable foreach loop
		foreach ( $gallery_posts as $current_post ) :
			// This call to related articles would change $post variable to custom post's single post and not post from our $posts araya
			$related_posts = pmc_related_articles( $current_post->ID );

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
			<item>
				<guid isPermaLink="false"><?php echo PMC_Custom_Feed_Helper::esc_xml( get_the_guid() ); ?></guid>
				<title><?php the_title_rss() ?></title>
				<pubDate><?php echo get_post_time( 'Y-m-d\TH:i:s\Z', true, $post ); ?></pubDate>
				<dcterms:modified><?php echo get_post_modified_time( 'Y-m-d\TH:i:s\Z', true, $post ); ?></dcterms:modified>
				<?php PMC_Custom_Feed_Helper::render_rss_author( $post, true );
				PMC_Custom_Feed_Helper::render_msn_excerpt();
				PMC_Custom_Feed_Helper::render_media_keywords( true );
				the_category_rss( 'rss2' );
				PMC_Custom_Feed_MS::render_gallery_image_nodes( $post, $feed_options );
				?>

				<?php
				if ( count( $related_posts ) > 0 ) {
					$i = 0;
					foreach ( $related_posts as $related_post ) {
						if ( $i++ > 2 ) {
							break;
						}
						?>
						<atom:link rel="related" title="<?php echo esc_attr( $related_post->title ); ?>" href="<?php echo esc_url( $related_post->link ) . $utm_params; ?>">
						<?php
						if ( isset( $feed_options['msfeed'] ) && true === $feed_options['msfeed'] ) {

							$thumbnail_url = get_the_post_thumbnail_url( $related_post->post_id, 'thumbnail' );

							if ( ! empty( $thumbnail_url ) ) {
								printf(
									'<media:thumbnail url="%s" />',
									esc_url( $thumbnail_url )
								);
							}
						}
						echo '</atom:link>';
					}
				}
				?>


			</item>
			<?php
		endforeach; } ?>
			<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options );

		?>
	</channel>
</rss>

<?php do_action( 'pmc_custom_feed_end', $feed, $feed_options, basename( __FILE__ ) );

	// EOF
