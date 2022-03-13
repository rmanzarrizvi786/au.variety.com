<?php
/**
 * Univision Uvideo Feed Template for displaying Video Posts feed.
 */

global $feed, $post;

if ( ! \PMC_Custom_Feed::get_instance()->is_feed() ) {
	return; // @codeCoverageIgnore
}

//Set our header
header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

// Gather the feed's options
$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

// Apply a hookable action before the feed
do_action( 'pmc_custom_feed_start', $feed, $feed_options, basename( __FILE__ ) );

// Fetch our feed's posts
$posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed ); // @codeCoverageIgnore

// If the following was in plain HTML, apache/php would see it as
// a php shorttag and try to parse it. The following is a workaround
echo '<?xml version="1.0" encoding="'. esc_attr( get_option( 'blog_charset' ) ) .'"?'.'>'; ?>

<request>
	<type>ingest_videos</type>
	<params>
		<video_list>
			<?php
			// loop through each post returned
			foreach( $posts as $post ) {

				// Allow $post to be filtered before the feed output
				$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );

				if ( empty( $post ) ) {
					continue;
				}
			?>

			<video>
				<!-- Asset ID assigned by the *caller -->
				<internal_id></internal_id>

				<!-- MCP upload ID. Leave blank to create a new video -->
				<upload_id></upload_id>

				<!-- The filename used by the user -->
				<source_file_name><?php echo esc_url( get_post_meta( $post->ID, '_pmc-entv-video-url' , true ) ) ?></source_file_name>

				<!-- 'Long Form' or 'Clip' -->
				<video_type><?php echo esc_html( get_post_meta( $post->ID, '_pmc-entv-video-type' , true ) ) ?></video_type>

				<!-- Set this to true if CMS should create a preview video and generate snapshots -->
				<analyze>true</analyze>

				<!-- Set this to true if CMS should syndicate the video to the syndicators that match the business rules. Admin can override this flag per partner, so even if this is set to true, CMS may refuse to syndicate per admin settings. -->
				<syndicate>false</syndicate>

				<!-- The URL of the master copy of the video. Must be publicly accessible -->
				<master_video_url><?php echo esc_url( get_post_meta( $post->ID, '_pmc-entv-video-url' , true ) ) ?></master_video_url>

				<!-- Title, description and comma separated tags -->
				<title><?php echo esc_html( get_the_title_rss() ) ?></title>

				<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>

				<tags><![CDATA[<?php
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
				?>]]></tags>

				<episode_no></episode_no>

				<season></season>

				<!-- Info on the video itself. If analyze flag is set, these will be overriden -->
				<width><?php
					echo (int) get_post_meta( $post->ID, '_pmc-entv-video-width' , true ) ?></width>

				<height><?php echo (int) get_post_meta( $post->ID, '_pmc-entv-video-height' , true ) ?></height>

				<duration><?php echo (int) get_post_meta( $post->ID, '_pmc-entv-video-duration' , true ) ?></duration>

				<!--Program name must match one of the the allowed programs for this partner. You can use the "list_programs" function to learn which programs you have access to -->
				<program_name>Default program</program_name>

				<!-- One of: TV-Y, TV-Y7, TV-G, TV-PG, TV-14, TV-MA -->
				<rating><?php echo esc_html( get_post_meta( $post->ID, '_pmc-entv-video-rating' , true ) ) ?></rating>

				<!-- Comma separated combination of V,L,S,D,FV (stands for violence, fantasy, sexual content, explicit dialogue and fantasy violence) -->
				<subratings><?php echo esc_html( get_post_meta( $post->ID, '_pmc-entv-video-subrating' , true ) ) ?></subratings>

				<!-- ISO 639-1 code (2 letters) -->
				<language><?php echo esc_html( get_post_meta( $post->ID, '_pmc-entv-video-language' , true ) ) ?></language>

				<copyright>
					<owner>Variety Latino</owner>
					<year>2014</year>
					<email>info@varietylatino.com</email>
					<address>11175 Santa Monica Blvd., Los Angeles, CA 90025</address>
					<phone>3103215000</phone>
				</copyright>
			</video><?php

			// Apply a hookable action to the end of the feed item
			do_action( 'pmc_custom_feed_post_end', $post, $feed_options );

			} // end foreach post ?>
		</video_list>
	</params>
</request>
<?php
	// Apply a hookable action to the end of the feed
	do_action( 'pmc_custom_feed_end', $feed, $feed_options, basename( __FILE__ ) );
?>
