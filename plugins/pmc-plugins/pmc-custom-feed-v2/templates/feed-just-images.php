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

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( '', array(
	'post_status' => 'inherit',
	'date_query' => array(
		array( 'after' => 'February 10, 2016' ),
	),
) );

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
echo '<?xml version="1.0" encoding="'.esc_attr( get_option( 'blog_charset' ) ).'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	xmlns:media="http://search.yahoo.com/mrss/"
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

	<?php foreach( $posts as $post ) : ?>

		<?php
			$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );

			if ( empty( $post ) ) {
				continue;
			}

			setup_postdata( $post );

			$image_meta = wp_get_attachment_metadata( $post->ID );
			$image_meta = $image_meta['image_meta'];

			// Determine the image title
			// Fallback order goes like so..
			// + post->post_title
			// + image metadata title field
			// + post->post_content
			// + image metadata caption field

			$image_title = $post->post_title;

			if ( empty( $image_title ) ) {
				if ( empty( $image_meta['title'] ) ) {
					if ( empty( $post->post_content ) ) {
						if ( ! empty( $image_meta['caption'] ) ) {
							$image_title = $image_meta['caption'];
						}
					} else {
						$image_title = $post_content;
					}
				} else {
					$image_title = $image_meta['title'];
				}
			}

			// Determine the image credit
			$image_credit = get_post_meta( $post->ID, '_image_credit', true );

			// Fallback to the image meta data if no credit has been entered
			if ( empty( $image_credit ) ) {
				$image_credit = $image_meta['credit'];
			}

			// Determine the image alt text
			$image_alt_text = get_post_meta( $post->ID, '_wp_attachment_image_alt', true );

			$original_image = wp_get_attachment_image_src( $post->ID, 'full' );
		?>

		<?php if ( is_array( $original_image ) ) : ?>

		<item>
			<title>

				<?php if ( ! empty( $image_title ) ) : ?>

					<?php echo esc_html( $image_title ); ?>

				<?php endif; ?>

			</title>
			<pubDate><?php echo get_post_time( 'D, d M Y H:i:s +0000', true, $post ); ?></pubDate>
			<guid><?php echo the_permalink(); ?></guid>
			<description><![CDATA[
				<img
					src="<?php echo esc_url( $original_image[0] ); ?>"
					alt="<?php echo esc_attr( empty( $image_alt_text ) ? $image_title : $image_alt_text ); ?>"
					width="<?php echo esc_attr( $original_image[1] ); ?>"
					height="<?php echo esc_attr( $original_image[2] ); ?>" />

				<?php if ( ! empty( $image_credit ) ) : ?>

					<p>Credit: <?php echo esc_html( $image_credit ); ?></p>

				<?php endif; ?>

				<?php if ( ! empty( $post->post_content ) ) : ?>

					<p>Description: <?php echo wp_kses_post( $post->post_content ); ?></p>

				<?php endif; ?>

				<?php if ( ! empty( $post->post_excerpt ) ) : ?>

					<p>Caption: <?php echo wp_kses_post( $post->post_excerpt ); ?></p>

				<?php endif; ?>

				<?php if ( ! empty( $image_alt_text ) ) : ?>

					<p>Alt Text: <?php echo esc_html( $image_alt_text ); ?></p>

				<?php endif; ?>

			]]></description>
		</item>

		<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options ); ?>

		<?php endif; ?>

	<?php endforeach; ?>

</channel>
</rss>
<?php
	do_action('pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );

// EOF
