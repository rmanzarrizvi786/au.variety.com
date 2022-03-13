<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed for Facebook Instant Articles.
 *
 * @package WordPress
 */

global $feed, $post;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
$more = 1;

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action( 'pmc_custom_feed_start', $feed, $feed_options, basename( __FILE__ ) );

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>'; ?>

	<rss version="2.0"
	     xmlns:content="http://purl.org/rss/1.0/modules/content/"
	     xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	     xmlns:atom="http://www.w3.org/2005/Atom"
		>

		<channel>
			<title><?php bloginfo_rss( 'name' );
				wp_title_rss(); ?></title>
			<link><?php bloginfo_rss( 'url' ) ?></link>
			<description><?php bloginfo_rss( "description" ) ?></description>
			<language><?php bloginfo_rss( 'language' ); ?></language>
			<lastBuildDate><?php echo mysql2date( 'c', get_lastpostmodified( 'GMT' ), false ); ?></lastBuildDate>
			<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
			<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
			<?php do_action( 'rss2_head' ); ?>
			<?php
		$post_counter = 0;
		$feed_page = 0;
		$limit        = ( ! empty( $feed_options['count'] ) ) ? min( intval( $feed_options['count'] ), 30 ) : 30;

		while( $post_counter < $limit ) {
		$feed_page = $feed_page + 1;
		$params    = array( 'paged' => $feed_page );
		$posts     = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed, $params );

		if ( empty( $posts ) ) {
			break;
		} else {
			foreach ( $posts as $post ) {

				if( $post_counter > $limit ) {
					break;
				}
				$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );
				if ( empty( $post ) || PMC_Custom_Feed_Facebook_Instant_Articles::get_instance()->post_inappropriate_for_instant_articles( $post, $feed_options ) ) {
					continue;
				}
				$post_counter = $post_counter + 1;

				setup_postdata( $post );
				?>
				<item>
					<title><?php the_title_rss() ?></title>
					<link><?php the_permalink_rss() ?></link>
					<guid isPermaLink="false"><?php the_guid(); ?></guid>
					<pubDate><?php echo esc_html( get_post_time( 'c', true, $post ) ); ?></pubDate>
					<?php PMC_Custom_Feed_Facebook_Instant_Articles::get_instance()->render_authors( $post->ID, 'rss' ); // output already escaped ?>
					<?php the_category_rss( 'rss2' ) ?>
					<description>
						<![CDATA[<?php echo wp_kses_post( PMC_Custom_Feed_Helper::get_excerpt( 'read_more_label', array(
							'label'  => 'Read full story',
							'target' => '_blank'
						) ) ); ?>]]>
					</description>
					<content:encoded><![CDATA[
						<!doctype html>
						<html lang="en" prefix="op: http://media.facebook.com/op#">
						<head>
							<meta charset="utf-8">
							<meta property="op:markup_version" content="v1.0">
							<meta property="fb:article_style" content="default">
							<meta property="fb:use_automatic_ad_placement" content="true">
							<?php PMC\SEO_Tweaks\Helpers::canonical( true, false, $post->ID ); ?>
						</head>
						<body>
						<article>
							<header>
								<h1><?php the_title_rss() ?></h1>

								<?php PMC_Custom_Feed_Facebook_Instant_Articles::get_instance()->render_sub_title( $post ); // output already escaped?>

								<?php PMC_Custom_Feed_Facebook_Instant_Articles::get_instance()->render_kicker( $post );  // output already escaped ?>

								<time class="op-published" datetime="<?php echo esc_attr( get_post_time( "c", true, $post ) ); ?>">
									<?php echo esc_html( get_post_time( 'D, F j, Y g:ia T', true, $post ) ); ?>
								</time>

								<time class="op-modified" dateTime="<?php echo esc_attr( get_post_modified_time( "c", true, $post ) ); ?>">
									<?php echo esc_html( get_post_modified_time( 'D, F j, Y g:ia T', true, $post ) ); ?>
								</time>

								<?php PMC_Custom_Feed_Facebook_Instant_Articles::get_instance()->render_publish_time( $post ); // output already escaped ?>

								<?php PMC_Custom_Feed_Facebook_Instant_Articles::get_instance()->render_authors( $post->ID, 'html5' ); // output already escaped ?>

								<?php PMC_Custom_Feed_Facebook_Instant_Articles::get_instance()->render_cover_image_in_post( $post ); // output already escaped ?>

								<details>
									<summary><?php echo wp_kses_post( PMC_Custom_Feed_Helper::get_excerpt( 'read_more_label', array(
											'label'  => 'Read full story',
											'target' => '_blank'
										) ) ); ?></summary>
									<?php echo wp_kses_post( PMC_Custom_Feed_Helper::get_excerpt( 'read_more_label', array(
										'label'  => 'Read full story',
										'target' => '_blank'
									) ) ); ?>
								</details>
								<?php PMC_Custom_Feed_Facebook_Instant_Articles::get_instance()->render_facebook_audience_network_ad(); // output already escaped ?>
							</header>
							<?php
							$content = get_the_content();
							$content = apply_filters( 'pmc_custom_feed_facebook_instant_articles_content', $content, $post, $feed_options );
							$content = apply_filters( 'the_content', strip_shortcodes( $content ) );
							$content = str_replace( ']]>', ']]&gt;', $content );
							$content = apply_filters( 'the_content_feed', $content, 'rss2' );
							$content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post, $feed_options, basename( __FILE__ ) );
							echo $content; // content is run through filters and already escaped
							?>
						</article>
						</body>
						</html>
						]]>
					</content:encoded>
					<?php rss_enclosure(); ?>
					<?php do_action( 'rss2_item' ); ?>
				</item>
				<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
					}
				}
				unset( $posts );
			}
			unset( $post_counter, $feed_page, $limit );?>
		</channel>
	</rss>
<?php
do_action( 'pmc_custom_feed_end', $feed, $feed_options, basename( __FILE__ ) );
// EOF
