<?php
/**
 * Feed Template for displaying factiva-embargoed  feed.
 *
 * @package WordPress
 */

global $feed, $post, $pmc_custom_feed_qs;
if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action('pmc_custom_feed_start', $feed, $feed_options, basename(__FILE__) );

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed );
$site_name = apply_filters( 'pmc_feed_legacy_format_site_name', defined( 'PMC_SITE_NAME' ) ? ucfirst( PMC_SITE_NAME ) : '' );
$copyright_holder = apply_filters('pmc_feed_legacy_format_copyright_holder' , '' );
$copyright_statement = apply_filters('pmc_feed_legacy_format_copyright_statement' , '' );

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';
?><!DOCTYPE ISSUE PUBLIC "-//Variety//DTD onlinetext DTD//EN"
        "http://static.variety.com/global/dtd/onlinetext/v1/onlinetext.dtd">
<ISSUE
	PUBLICATION="<?php echo esc_attr( $site_name ) ; ?>"
	PUBDATE="<?php echo mysql2date( 'm/d/Y', get_lastpostmodified(), false ); ?>"
	VOLUME="0"
	ISSUE="0"
	>
<?php
	foreach ( $posts as $post ) :
		$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );
		if ( empty( $post ) ) {
			continue;
		}
		setup_postdata( $post );
?>
	<ARTICLE
		PUBLICATION="<?php echo esc_attr($site_name) ; ?>" PUBDATE="<?php echo get_post_time( 'm/d/Y', false, $post ); ?>"
		VOLUME="0"
		ISSUE="0"
		PAGE="NO" AUTHTYPE="STAFF" AUTHNAME="<?php
			if ( function_exists( 'coauthors' ) ) {
				coauthors();
			} else {
				the_author();
			} ?>"
		LANGUAGE="EN" SECRIGHTS="YES" RESOURCEID="<?php echo get_the_ID(); ?>"
		PUBTIME="<?php echo get_post_time( 'H:i:s A T', false, $post ); ?>"
		SECTION="<?php $categories = get_the_category( $post->ID );
						if ( !empty( $categories ) ) {
							foreach ( $categories as $cat ) {
								echo esc_attr( $cat->name );
								break;
							}
						} ?>"
		SUBSECTION="<?php echo esc_attr( apply_filters('pmc_feed_legacy_format_primary_vertical' , '', $post ) ); ?>"
		>
		<HEADLINE><?php echo esc_html( get_the_title_rss() ) ?></HEADLINE>
		<BYLINE><?php
		if ( function_exists( 'coauthors' ) ) {
			coauthors();
		} else {
			the_author();
		}
		?></BYLINE>
		<DECKHEAD><?php
			$sub_heading = trim( apply_filters( 'pmc_feed_legacy_format_sub_heading', '', $post ) );
			if ( !empty( $sub_heading ) ) {
					echo esc_html( $sub_heading );
				}
				?></DECKHEAD>
<?php
				$content = PMC::strip_shortcodes( get_the_content() );
				// We want to strip all other tags not need before the_content filter to avoid <figure><img ..></p> bug from autop filter
				$content = strip_tags( $content, '<p>' );

				// there shouldn't be any usage of <p/>, translate to new line representing paragraph
				// and lets the other filter fix the html code block
				$content = preg_replace( '@<p([^\\>]*)/>@', "\n\n", $content);

				$content = apply_filters( 'the_content', $content );
				$content = str_replace( ']]>', ']]&gt;', $content );
				$content = apply_filters( 'the_content_feed', $content, 'rss2' );
				// do a secondary clean up as the filer might add some extra tag
				$content = strip_tags( $content, '<p>' );
				$content = force_balance_tags ( $content );
				$regex = '/<p>(.*?)<\/p>/si';
				$xml_with_p = $return_content = preg_replace_callback( $regex,
					function ( $match ) {
						return '<p>'.esc_html( $match[1]).'</p>';
					},
					$content );
				?>
		<ABSTRACT><?php if ( !empty( $post->post_excerpt ) ) echo esc_html( strip_tags(get_the_excerpt()) ); ?></ABSTRACT>
<?php
	echo $xml_with_p;
?>
		<COPYRIGHT>
			<HOLDER><?php echo PMC_Custom_Feed_Helper::esc_xml( $copyright_holder );?></HOLDER>
			<STATEMENT><?php echo PMC_Custom_Feed_Helper::esc_xml( $copyright_statement );?></STATEMENT>
		</COPYRIGHT>
	</ARTICLE>
<?php
		do_action( 'pmc_custom_feed_post_end', $post, $feed_options );
	endforeach;
	?>
</ISSUE>
<?php
	do_action('pmc_custom_feed_end', $feed, $feed_options, basename(__FILE__) );

// EOF
