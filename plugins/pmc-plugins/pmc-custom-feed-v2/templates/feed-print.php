<?php
/**
 * Feed Template for displaying print feed.
 *
 * @package WordPress
 */

global $feed, $post, $pmc_custom_feed_qs;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

# we need to use a variable that cannot be conflict with registered post type "issue"
$issue_slug = \PMC::filter_input( INPUT_GET, 'i', FILTER_SANITIZE_STRING );

// specific issue requested
if ( !empty( $issue_slug ) ) {
	// get the print issues taxonomy to search for the issue number, typecast for safety
	$issue_slug = sanitize_title_with_dashes( $issue_slug );
	$print_issue_term = get_term_by( 'slug', $issue_slug, 'print-issues' );
}
else
{
	// issue not supply, try to get the information from current issue marker
	$term = get_term_by( 'slug', 'issue-marker', 'print-issues' );
	if ( !empty($term) ) {
		// get the parent term of the print issue marker
		$print_issue_term = get_term_by( 'term_id', $term->parent, 'print-issues' );
	}
}

if ( !empty( $print_issue_term ) ) {
	$print_issue_term_id = $print_issue_term->term_id;
	// Parse slug into volume, issue, month, day, year
	$tokens = explode( '-', $print_issue_term->slug );
	if ( count($tokens) == 5 ) {
		list ($print_volume,$print_issue,$month,$day,$year) = $tokens;
		$date = strtotime( "$month $day $year");
		if ( !empty( $date ) ) {
			$print_issue_date = date( "Y-m-d", $date );
		}
	}
}

// set to default issue information if value not define
if ( !isset($print_volume) ) {
	$print_volume = 0;
}
if ( !isset( $print_issue ) ) {
	$print_issue = 0;
}
if ( !isset( $print_issue_term_id ) ) {
	$print_issue_term_id = 0;
}
if ( !isset($print_issue_date) ) {
	$print_issue_date = get_lastpostmodified();
}

// encode the value just in case we have funny characters
$xml_safe_print_volume = htmlspecialchars($print_volume);
$xml_safe_print_issue = htmlspecialchars($print_issue);

// piece together post args for feed
$args = array(
		'post_status' => 'publish,future',	// we want published and scheduled print posts
		'tax_query'   => array(	// filter by specific print issue taxonomy
			array(
				'taxonomy' => 'print-issues',
				'field' => 'term_id',
				'terms' => $print_issue_term_id
			)
		),
	);

$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
do_action('pmc_custom_feed_start', $feed, $feed_options, basename(__FILE__) );

$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed, $args );

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';
?><!DOCTYPE ISSUE PUBLIC "-//Variety//DTD onlinetext DTD//EN"
        "http://static.variety.com/global/dtd/onlinetext/v1/onlinetext.dtd">
<ISSUE
	PUBLICATION="Variety"
	PUBDATE="<?php echo mysql2date( 'm/d/Y', $print_issue_date, false ); ?>"
	VOLUME="<?php echo $xml_safe_print_volume; ?>"
	ISSUE="<?php echo $xml_safe_print_issue; ?>"
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
		PUBLICATION="Variety" PUBDATE="<?php echo get_post_time( 'm/d/Y' ); ?>"
		VOLUME="<?php echo $xml_safe_print_volume; ?>"
		ISSUE="<?php echo $xml_safe_print_issue; ?>"
		PAGE="NO" AUTHTYPE="STAFF" AUTHNAME="<?php
			if ( function_exists( 'coauthors' ) ) {
				coauthors();
			} else {
				the_author();
			} ?>"
		LANGUAGE="EN" SECRIGHTS="YES" RESOURCEID="<?php echo get_the_ID(); ?>"
		PUBTIME="<?php echo get_post_time( 'H:i:s A T' ); ?>"
		SECTION="<?php $categories = get_the_category( $post->ID );
						if ( !empty( $categories ) ) {
							foreach ( $categories as $cat ) {
								echo htmlspecialchars( $cat->name );
								break;
							}
						} ?>"
		SUBSECTION="<?php
						if ( function_exists( 'variety_vertical_get_primary' ) ) {
							$vertical = variety_vertical_get_primary( $post->ID );
							if ( !empty( $vertical ) ) {
								echo htmlspecialchars( $vertical->name );
							}
						}
			 ?>"
		>
		<HEADLINE><?php echo htmlspecialchars( get_the_title_rss() ) ?></HEADLINE>
		<BYLINE><?php
		if ( function_exists( 'coauthors' ) ) {
			coauthors();
		} else {
			the_author();
		}
		?></BYLINE>
		<DECKHEAD><?php
				$sub_heading = htmlspecialchars(get_post_meta( $post->ID, '_variety-sub-heading', true ));
				if ( !empty( $sub_heading ) ) {
					echo trim( $sub_heading );
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
						return '<p>'.htmlspecialchars( $match[1]).'</p>';
					},
					$content );
				?>
		<ABSTRACT><?php if ( !empty( $post->post_excerpt ) ) echo htmlspecialchars( strip_tags(get_the_excerpt()) ); ?></ABSTRACT>
<?php
	echo $xml_with_p;
?>
		<COPYRIGHT>
			<HOLDER>Variety Media, LLC</HOLDER>
			<STATEMENT>&#169; 2013 Variety Media, LLC, a subsidiary of Penske Business Media. All rights reserved.</STATEMENT>
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
