<?php
/**
 * Template part for Top Videos Information Metabox.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-09-01 Milind More CDWE-499
 */

?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="variety_top_video_source">
					<?php esc_html_e( 'Video URL or Shortcode', 'pmc-variety' ); ?>
				</label>
			</th>
			<td>
				<input
					type="text"
					id="variety_top_video_source"
					name="variety_top_video_source"
					class="widefat"
					value="<?php echo esc_attr( $video_source ); ?>" />

				<p class="description">
					<?php esc_html_e( 'Enter a video URL supported by', 'pmc-variety' ); ?>

					<a href="<?php echo esc_url( 'https://wordpress.org/support/article/embeds/#okay-so-what-sites-can-i-embed-from' ); ?>" title="<?php esc_attr_e( 'WordPress oEmbed', 'pmc-variety' ); ?>" target="_blank">
						<?php esc_html_e( 'oEmbed', 'pmc-variety' ); ?>
					</a>

					<?php esc_html_e( '(e.g. YouTube, Vimeo, etc.), or JW Player shortcode. JWPlayer shortcodes must be formatted like [jwplayer videoID] (ex: [jwplayer rhBqkhKq])', 'pmc-variety' ); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="variety_top_video_duration">
					<?php esc_html_e( 'Video Duration', 'pmc-variety' ); ?>
				</label>
			</th>
			<td>
				<input
					type="text"
					id="variety_top_video_duration"
					name="variety_top_video_duration"
					value="<?php echo esc_attr( $video_duration ); ?>" />

				<p class="description">
					<?php esc_html_e( 'This should be in hh:mm:ss format (e.g. 00:21:34 for a video which is 21 minutes 34 seconds in length).', 'pmc-variety' ); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>
