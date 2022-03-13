<?php
/**
 * Template part for Top Videos Information Metabox.
 *
 * @package pmc-top-videos-v2
 * @since 2018-04-23
 */

?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="pmc_top_video_source">
					<?php esc_html_e( 'Video URL or Shortcode', 'pmc-top-videos-v2' ); ?>
				</label>
			</th>
			<td>
				<input
					type="text"
					id="pmc_top_video_source"
					name="pmc_top_video_source"
					class="widefat"
					value="<?php echo esc_attr( $video_source ); ?>" />

				<p class="description">
					<?php esc_html_e( 'Enter a video URL supported by', 'pmc-top-videos-v2' ); ?>

					<a href="<?php echo esc_url( 'https://wordpress.org/support/article/embeds/#okay-so-what-sites-can-i-embed-from' ); ?>" title="<?php esc_attr_e( 'WordPress oEmbed', 'pmc-top-videos-v2' ); ?>" target="_blank">
						<?php esc_html_e( 'oEmbed', 'pmc-top-videos-v2' ); ?>
					</a>

					<?php esc_html_e( '(e.g. YouTube, Vimeo, etc.), or JW Player shortcode. JWPlayer shortcodes must be formatted like [jwplayer videoID] (ex: [jwplayer rhBqkhKq])', 'pmc-top-videos-v2' ); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="pmc_top_video_duration">
					<?php esc_html_e( 'Video Duration', 'pmc-top-videos-v2' ); ?>
				</label>
			</th>
			<td>
				<input
					type="text"
					id="pmc_top_video_duration"
					name="pmc_top_video_duration"
					value="<?php echo esc_attr( $video_duration ); ?>" />

				<p class="description">
					<?php esc_html_e( 'This should be in hh:mm:ss format (e.g. 00:21:34 for a video which is 21 minutes 34 seconds in length).', 'pmc-top-videos-v2' ); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>
