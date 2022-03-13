<?php
/**
 * RSS2 Feed Template for Hearst, PMCEED-852.
 */

global $feed, $post, $pmc_custom_feed_qs;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

$more = 1;

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action('pmc_custom_feed_start', $feed, $feed_options, basename(__FILE__) );

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts();
header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	xmlns:media="http://search.yahoo.com/mrss/"
	xmlns:pmcFeed="http://pmc.com/rss/modules/feed"
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

		$current_post = $post;

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

		$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );
		if ( empty( $post ) ) {
			continue;
		}

		// Ignore the post if its flagged as inappropriate for syndication.
		if ( has_term( 'inappropriate-for-syndication', '_post-options', $post ) ) {
			continue;
		}

		setup_postdata( $post );
?>
		<item>
			<title><?php the_title_rss() ?></title>
			<link><?php the_permalink_rss(); ?></link>
			<?php
			if ( ! empty( $related_links ) && is_array( $related_links ) ) {

				$i = 0;

				echo '<related>';

				foreach ( $related_links as $item ) {

					if ( $i++ > 2 ) {
						break;
					}

					printf(
						'<link rel="related" title="%s" href="%s">',
						PMC_Custom_Feed_Helper::esc_xml( $item['title'] ),
						PMC_Custom_Feed_Helper::esc_xml( $item['url'] )
					);

					$thumbnail_url = get_the_post_thumbnail_url( $item['id'], 'thumbnail' );

					if ( ! empty( $thumbnail_url ) ) {
						printf(
							'<media:thumbnail url="%s" type="%s" />',
							esc_url( $thumbnail_url ),
							esc_attr( get_post_mime_type( get_post_thumbnail_id() ) )
						);
					}

					echo '</link>';
				}

				echo '</related>';

			}
			?>
			<?php do_action( 'pmc_custom_feed_item', $post, $feed_options ); ?>
			<comments><?php comments_link_feed(); ?></comments>
			<pubDate><?php echo get_post_time( 'D, d M Y H:i:s +0000', true, $post ); ?></pubDate>
			<authors>
				<?php

					$authors = PMC_Custom_Feed_Helper::get_authors( $post->ID );

					if ( ! empty( $authors ) && is_array( $authors ) ) {
						foreach( $authors as $author ) {
							printf(
								'<link title="%s" href="%s" />',
								PMC_Custom_Feed_Helper::esc_xml( $author->display_name ),
								esc_url( get_author_posts_url( $author->ID, $author->user_nicename ) )
							);
						}
					}

				?>
			</authors>
			<?php the_category_rss('rss2') ?>
			<guid isPermaLink="false"><?php the_guid(); ?></guid>
			<?php
			PMC_Custom_Feed_Helper::get_image_specific_for_feed( $post->ID, "media:thumbnail" );

			$sub_heading = htmlspecialchars( get_post_meta( $post->ID, '_variety-sub-heading', true ) );

			if ( ! empty( $sub_heading ) ) :

			?>
			<pmcFeed:subHeading>
				<![CDATA[<?php echo PMC_Custom_Feed_Helper::esc_xml( $sub_heading ); ?>]]>
			</pmcFeed:subHeading>
			<?php endif; //End subheading.

				// Include Rollingstone's reviews data if it's a review post.
				if (
						class_exists( '\PMC\Review\Review' ) &&
						class_exists( '\PMC\Review\Json_Data' ) &&
						in_category( \PMC\Review\Review::get_instance()->get_review_category_slugs(), $post ) &&
						! empty( \PMC\Review\Json_Data::get_instance()->get_review_type() )
				) :

					// Using this instance to use the getter methods.
					$pmc_reivew_fields_instance = \PMC\Review\Fields::get_instance();

					$review_type    = \PMC\Review\Json_Data::get_instance()->get_review_type();
					$review_name    = $pmc_reivew_fields_instance->get( $pmc_reivew_fields_instance::TITLE );
					$director       = $pmc_reivew_fields_instance->get( $pmc_reivew_fields_instance::ARTIST );
					$review_snippet = $pmc_reivew_fields_instance->get( $pmc_reivew_fields_instance::SNIPPET );
					$rating         = $pmc_reivew_fields_instance->get( $pmc_reivew_fields_instance::RATING );
					$rating_out_of  = $pmc_reivew_fields_instance->get( $pmc_reivew_fields_instance::RATING_OUT_OF );
					$canonical      = $pmc_reivew_fields_instance->get( $pmc_reivew_fields_instance::CANONICAL_LINK );
					$review_image   = $pmc_reivew_fields_instance->get( $pmc_reivew_fields_instance::IMAGE );
					$release_date   = intval( $pmc_reivew_fields_instance->get( $pmc_reivew_fields_instance::RELEASE_DATE ) );
					$release_date   = ( ! empty( $release_date ) ) ? date( 'Y-m-d', $release_date ) : '';
					$extra_fields   = apply_filters( 'pmc_review_extra_fields', [] );

					// For generating tags based on the type of review.
					switch( $review_type ) {

						case 'movie-reviews':

							$review_name_tag  = '<pmcFeed:reviewTitle role="Film Name"><![CDATA[%s]]></pmcFeed:reviewTitle>';
							$director_tag     = '<pmcFeed:artist role="Director"><![CDATA[%s]]></pmcFeed:artist>';
							$release_date_tag = '<pmcFeed:releaseDate role="Theatrical Release Date"><![CDATA[%s]]></pmcFeed:releaseDate>';
							$review_image_tag = '<pmcFeed:image role="Film Image"><![CDATA[%s]]></pmcFeed:image>';
							break;

						case 'music-album-reviews':

							$review_name_tag  = '<pmcFeed:reviewTitle role="Album Title"><![CDATA[%s]]></pmcFeed:reviewTitle>';
							$director_tag     = '<pmcFeed:artist role="Artist"><![CDATA[%s]]></pmcFeed:artist>';
							$release_date_tag = '<pmcFeed:releaseDate role="Release Date"><![CDATA[%s]]></pmcFeed:releaseDate>';
							$review_image_tag = '<pmcFeed:image role="Album Cover Image"><![CDATA[%s]]></pmcFeed:image>';
							break;

						default:

							$review_name_tag  = '<pmcFeed:reviewTitle role="Review Title"><![CDATA[%s]]></pmcFeed:reviewTitle>';
							$director_tag     = '<pmcFeed:artist role="Artist"><![CDATA[%s]]></pmcFeed:artist>';
							$release_date_tag = '<pmcFeed:releaseDate role="Release Date"><![CDATA[%s]]></pmcFeed:releaseDate>';
							$review_image_tag = '<pmcFeed:image role="Cover Image"><![CDATA[%s]]></pmcFeed:image>';
							break;

					}

				?>
				<pmcFeed:reviewInfo>

					<?php if ( ! empty( $review_name ) && ! empty( $review_name_tag ) ) :

						echo sprintf( $review_name_tag, PMC_Custom_Feed_Helper::esc_xml( $review_name ) );

					endif; ?>

					<?php if ( ! empty( $rating ) && ! empty( $rating_out_of ) ) : ?>
						<pmcFeed:rating>
							<![CDATA[<?php echo PMC_Custom_Feed_Helper::esc_xml( $rating ) . ' / ' . PMC_Custom_Feed_Helper::esc_xml( $rating_out_of ); ?>]]>
						</pmcFeed:rating>
					<?php endif; ?>

					<?php if ( ! empty( $review_snippet ) ) : ?>
						<pmcFeed:reviewSnippet>
							<![CDATA[<?php echo PMC_Custom_Feed_Helper::esc_xml( $review_snippet ); ?>]]>
						</pmcFeed:reviewSnippet>
					<?php endif; ?>
					<?php
						if ( ! empty( $director ) && ! empty( $director_tag ) ) :

						echo sprintf( $director_tag, PMC_Custom_Feed_Helper::esc_xml( $director ) );

						endif;

						if ( ! empty( $release_date ) && ! empty( $release_date_tag ) ) :

							echo sprintf( $release_date_tag, PMC_Custom_Feed_Helper::esc_xml( $release_date ) );

						endif;

						if ( ! empty( $review_image ) && ! empty( $review_image_tag ) ) :

							echo sprintf( $review_image_tag, esc_url( $review_image ) );

						endif;

						if ( ! empty( $canonical ) ) : ?>
						<pmcFeed:canonicalLink>
							<![CDATA[<?php echo PMC_Custom_Feed_Helper::esc_xml( $canonical ); ?>]]>
						</pmcFeed:canonicalLink>
					<?php endif;?>

					<?php if ( ! empty( $extra_fields['cast'] ) ) : ?>
						<pmcFeed:cast>
							<![CDATA[<?php echo PMC_Custom_Feed_Helper::esc_xml( $extra_fields['cast'] ); ?>]]>
						</pmcFeed:cast>
					<?php endif; ?>

					<?php if ( ! empty( $extra_fields['running_time'] ) ) : ?>
						<pmcFeed:runningTime>
							<![CDATA[<?php echo PMC_Custom_Feed_Helper::esc_xml( $extra_fields['running_time'] ); ?>]]>
						</pmcFeed:runningTime>
					<?php endif; ?>

				</pmcFeed:reviewInfo>
			<?php endif; ?>
			<?php if ( strlen( $post->post_content ) > 0 ) : ?>
				<description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
				<content:encoded><![CDATA[<?php
						$content = get_the_content();

						/**
						 * This filter is used in variety's config to include reviews meta in post content.
						 */
						$content = apply_filters( 'pmc_custom_feed_variety_review_meta', $content, $post, $feed_options );

						$required_shortcodes = apply_filters( 'pmc_custom_feed_hearst_required_shortcodes', [ 'caption', 'jwplayer', 'youtube', 'pmc-related-link' ], $post, $feed_options );

						$content = PMC_Custom_Feed_Helper::get_instance()->process_required_shortcodes( $content, $feed_options, $required_shortcodes );
						$content = apply_filters( 'the_content', PMC::strip_shortcodes( $content ) );
						$content = str_replace( ']]>', ']]&gt;', $content );
						$content = apply_filters( 'the_content_feed', $content, 'rss2' );
						$content = apply_filters( 'pmc_custom_feed_content', $content, $feed, $post, $feed_options, basename( __FILE__ ) );

						echo $content;
				?>]]></content:encoded>
			<?php endif; ?>
				<wfw:commentRss><?php echo esc_url( get_post_comments_feed_link(null, 'rss2') ); ?></wfw:commentRss>
				<slash:comments><?php echo get_comments_number(); ?></slash:comments>
			<?php rss_enclosure(); ?>
			<?php do_action('rss2_item'); ?>
		</item>
		<?php do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
	endforeach; ?>
</channel>
</rss>
<?php
	do_action( 'pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );

// EOF
