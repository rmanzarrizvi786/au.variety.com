<?php
/**
 * Atom Feed Template for displaying Atom Posts feed.
 *
 * @package PMC_Custom_Feed_V2/Templates
 */

global $feed, $post, $pmc_custom_feed_qs;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

do_action( 'pmc_custom_feed_start', $feed, $feed_options, basename( __FILE__ ) );

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts();

header( 'Content-Type: ' . feed_content_type( 'atom' ) . '; charset=' . get_option( 'blog_charset' ), true );

echo '<?xml version="1.0" encoding="' . esc_attr( get_option( 'blog_charset' ) ) . '"?' . '>'; ?>

<feed
	xmlns="http://www.w3.org/2005/Atom"
	xmlns:media="http://search.yahoo.com/mrss/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	<?php if ( isset( $feed_options['msfeed'] ) && true === $feed_options['msfeed'] ) { ?>
	xmlns:mi="http://schemas.ingestion.microsoft.com/common/"
	<?php } ?>
	>
	<?php PMC_Custom_Feed_Helper::render_feed_title() ?>
	<updated><?php echo esc_html( mysql2date( 'Y-m-d\TH:i:s\Z', get_lastpostmodified( 'GMT' ), false ) ); ?></updated>
	<id><?php self_link(); ?></id>
	<link rel="self" type="application/atom+xml" href="<?php self_link(); ?>"/>

<?php

$site_logo = apply_filters( 'atom-feed-site-logo-image', false );

if ( ! empty( $site_logo ) ) {
	echo '<logo>' . PMC_Custom_Feed_Helper::esc_xml( $site_logo ) . '</logo>';
}

// avoid using global $post variable foreach loop.
foreach ( $posts as $current_post ) {

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

	// restore global $post to current post and setup current post data.
	$post = $current_post;

	// Note $current_post variable should not be use beyond this point
	// the following function call will modified global $post variable base on post type.
	$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );

	if ( empty( $post ) ) {
		continue;
	}

	setup_postdata( $post );

?>
	<entry>
		<?php PMC_Custom_Feed_Helper::render_post_title(); ?>
		<link rel="alternate" type="<?php bloginfo_rss( 'html_type' ); ?>" href="<?php the_permalink_rss() ?>" />

		<?php

		if ( ! empty( $related_links ) && is_array( $related_links ) ) {

			$i = 0;

			foreach ( $related_links as $item ) {

				if ( $i++ > 2 ) {
					break;
				}

				printf(
					'<link rel="related" title="%s" href="%s">',
					PMC_Custom_Feed_Helper::esc_xml( $item['title'] ),
					PMC_Custom_Feed_Helper::esc_xml( $item['url'] )
				);

				if ( isset( $feed_options['msfeed'] ) && true === $feed_options['msfeed'] ) {

					$thumbnail_url = get_the_post_thumbnail_url( $item['id'], 'thumbnail' );

					if ( ! empty( $thumbnail_url ) ) {
						printf(
							'<media:thumbnail url="%s" type="%s" />',
							PMC_Custom_Feed_Helper::esc_xml( $thumbnail_url, 'url' ),
							PMC_Custom_Feed_Helper::esc_xml( get_post_mime_type( get_post_thumbnail_id() ), 'attr' )
						);
					}
				}

				echo '</link>';
			}

		}

		?>

		<id><?php echo PMC_Custom_Feed_Helper::esc_xml( get_the_guid() ); ?></id>
		<updated><?php echo PMC_Custom_Feed_Helper::esc_xml( get_post_modified_time( 'Y-m-d\TH:i:s\Z', true, $post ) ); ?></updated>
		<published><?php echo PMC_Custom_Feed_Helper::esc_xml( get_post_time( 'Y-m-d\TH:i:s\Z', true, $post ) ); ?></published>

		<?php
		if ( isset( $feed_options['msfeed'] ) && true === $feed_options['msfeed'] ) {
			echo '<mi:dateTimeWritten>' . PMC_Custom_Feed_Helper::esc_xml( get_post_time( 'Y-m-d\TH:i:s\Z', true, $post ) ) . '</mi:dateTimeWritten>';
		}

		do_action( 'pmc_custom_feed_item', $post, $feed_options );

		$author_names = PMC_Custom_Feed_Helper::get_author_display_names( $current_post->ID );

		if ( ! empty( $author_names ) ) {

			$name = array_shift( $author_names );
		?>
			<author>
				<name><?php echo PMC_Custom_Feed_Helper::esc_xml( $name ); ?></name>
			</author>
			<?php
			foreach ( $author_names as $name ) {
				?>
				<contributor>
					<name><?php echo PMC_Custom_Feed_Helper::esc_xml( $name ); ?></name>
				</contributor>
		<?php
			}

		}

		PMC_Custom_Feed_Helper::render_media_content();
		PMC_Custom_Feed_Helper::render_atom_excerpt( 'summary' );
		PMC_Custom_Feed_Helper::render_atom_content( 'content', basename( __FILE__ ) );

		?>
	</entry>
	<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options );

} // End foreach().

?>
</feed>
<?php

do_action( 'pmc_custom_feed_end', $feed, $feed_options, basename( __FILE__ ) );

// EOF
