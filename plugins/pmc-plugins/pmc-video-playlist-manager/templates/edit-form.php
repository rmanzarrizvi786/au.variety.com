<?php
// use dynamic function to avoid re-define errors.
$func_get_field = function ( $key, $default = '' ) use ( $video ) {
	$var = empty( $video->post_content[ $key ] ) ? $default : $video->post_content[ $key ];

	if ( ! is_array( $var ) ) {
		return esc_attr( $var );
	}

	return $var;
}; ?>

<div id="provider-<?php echo esc_attr( empty( $video->ID ) ? '' : $video->ID ); ?>" class="pvm-provider">

	<span>
		<?php
		esc_html_e( 'This page will allow you to control what content is displayed within the video module found at the end of article pages.', 'pmc-video-playlist-manager' );
		echo '<br>';
		esc_html_e( 'The module will display 5 latest videos minimum, and a maximum of 10 from the playlist selected.', 'pmc-video-playlist-manager' );
		?>
	</span>
	<hr>

	<form action="" method="post" class="pvm-form">
		<input type="hidden" name="action" value="pvm_crud">
		<input type="hidden" name="method" value="<?php echo empty( $video ) ? 'add' : 'edit'; ?>">
		<?php if ( ! empty( $video->ID ) ) { ?>
			<input type="hidden" name="id" value="<?php echo intval( $video->ID ); ?>">
		<?php } ?>

		<br clear="both">

		<div class="pvm-input">
			<label for="get_pvm-status"><strong><?php esc_html_e( 'Module Status', 'pmc-video-playlist-manager' ); ?></strong></label>
			<input type="checkbox" name="status" id="get_pvm-status" <?php echo ( ! empty( $video->post_status ) && 'publish' === $video->post_status ) ? esc_attr( 'checked' ) : ''; ?> value="enabled">
			<span class="description"><?php esc_html_e( 'Check to enable this module', 'pmc-video-playlist-manager' ); ?></span>
		</div>

		<div class="pvm-input">
			<label for="get_pvm-title"><strong><?php esc_html_e( 'Module Header', 'pmc-video-playlist-manager' ); ?></strong></label>
			<input type="text" class="full-width" name="title" id="get_pvm-title" placeholder="<?php esc_attr_e( 'Related Videos', 'pmc-video-playlist-manager' ); ?>" value="<?php echo empty( $video->post_title ) ? '' : esc_attr( $video->post_title ); ?>">
		</div>

		<div class="pvm-input form-required">
			<label for="get_pvm-playlist"><strong><?php esc_html_e( 'Select Playlist*', 'pmc-video-playlist-manager' ); ?></strong></label>
			<input type="text" name="playlist" id="get_pvm-playlist" class="playlist-search-autocomplete full-width" placeholder="<?php esc_attr_e( 'Search playlist name (require)', 'pmc-video-playlist-manager' ); ?>" autocomplete="off" value="<?php echo esc_attr( $func_get_field( 'playlist' ) ); ?>">
		</div>

		<div class="pvm-input form-required">
			<?php
			$video_count = $func_get_field( 'video-count', 5 );
			$min         = ( $video_count < 5 ) ? $video_count : 5;
			$max         = ( $video_count < 5 ) ? $video_count : 10;

			?>
			<label for="get_pvm-video-count"><strong><?php esc_html_e( 'Number of videos to display*', 'pmc-video-playlist-manager' ); ?></strong></label>
			<input type="number" name="video-count" class="full-width" id="get_pvm-video-count" size="1" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" value="<?php echo esc_attr( $video_count ); ?>">
		</div>

		<div class="pvm-input">
			<label for="get-featured-video"><strong><?php esc_html_e( 'Select featured video', 'pmc-video-playlist-manager' ); ?></strong></label>

			<select name="featured-video" id="get-featured-video" class="full-width" <?php echo ( empty( $featured_videos ) ) ? 'disabled' : ''; ?>>
				<?php
				if ( ! empty( $featured_videos ) ) {
					foreach ( $featured_videos as $id => $featured_video ) {
						?>
						<option value="<?php echo esc_attr( $id ); ?>"<?php selected( $func_get_field( 'featured-video', '' ), $id ); ?>><?php echo esc_html( $featured_video ); ?></option>
						<?php
					}
				}
				?>
			</select>
		</div>

		<div class="pvm-input form-required pvm-tax-relation">
			<?php
			$targeting_data = $func_get_field(
				'targeting_data', array(
					array(
						'taxonomy' => '',
						'terms'    => '',
					),
				)
			);

			foreach ( $targeting_data as $target_record ) {
				?>
				<div class="add-tax-relation-row">
					<label for="get_pvm-target-tax"><strong><?php esc_html_e( 'Targeted Page*', 'pmc-video-playlist-manager' ); ?></strong></label>
					<select name="target-tax[]" class="pvm-target-tax">
						<?php foreach ( $targeted_taxonomies as $target => $slug ) { ?>
							<option value="<?php echo esc_attr( $target ); ?>" <?php selected( $target_record['taxonomy'], $target ); ?>><?php echo esc_html( $target ); ?></option>
						<?php } ?>
					</select>

					<input type="text" name="target-term[]" class="pvm-target-term full-width" autocomplete="off" value="<?php echo esc_attr( html_entity_decode( $target_record['terms'] ) ); ?>">
					<a href="javascript:;" class="add-tax-relation button">+</a>
				</div>
				<?php
			}
			?>
		</div>

		<fieldset class="pvm-input pvm-timeframe">
			<legend>
				<strong><?php esc_html_e( 'Timeframe', 'pmc-video-playlist-manager' ); ?></strong>
				<?php esc_html_e( '(optional)', 'pmc-video-playlist-manager' ); ?>
			</legend>
			<span class="description">
				<?php esc_html_e( 'Current Time: ', 'pmc-video-playlist-manager' ); ?><?php echo esc_html( PMC_TimeMachine::create( $manager->timezone )->now() ); ?>
			</span><br>

			<?php
			$start_date = $func_get_field( 'start' );
			$end_date   = $func_get_field( 'end' );

			if ( empty( $start_date ) ) {
				$start_date = '';
			}

			if ( empty( $end_date ) ) {
				$end_date = '';
			}
			?>

			<div>
				<label for="get_pvm-start"><strong><?php esc_html_e( 'Start Time', 'pmc-video-playlist-manager' ); ?></strong></label>
				<input type="text" name="start" id="get_pvm-start" placeholder="YYYY-MM-DD HH:MM" class="timeframe-start" value="<?php echo esc_attr( $start_date ); ?>">
				<br>
				<label for="get_pvm-end"><strong><?php esc_html_e( 'End Time', 'pmc-video-playlist-manager' ); ?></strong></label>
				<input type="text" name="end" id="get_pvm-end" placeholder="YYYY-MM-DD HH:MM" class="timeframe-end" value="<?php echo esc_attr( $end_date ); ?>">
				<span class="error-msg" style="display: none;"> <?php esc_html_e( 'End time cannot be before start time.', 'pmc-video-playlist-manager' ); ?></span>
			</div>
		</fieldset>

		<div class="pvm-input form-required">
			<label for="get_pvm-priority"><strong><?php esc_html_e( 'Priority', 'pmc-video-playlist-manager' ); ?></strong></label>
			<input type="number" name="priority" id="get_pvm-priority" size="1" min="1" max="10" class="full-width" value="<?php echo esc_attr( $func_get_field( 'priority', 10 ) ); ?>">
			<br>
			<span class="description"><?php esc_html_e( 'Lower number has higher priority. 9 will override 10', 'pmc-video-playlist-manager' ); ?></span>
		</div>

		<?php submit_button( __( 'Save', 'pmc-video-playlist-manager' ), 'primary', 'submit', false ); ?>
		<a href="javascript:;" class="pvm-form-cancel button"><?php esc_html_e( 'Cancel', 'pmc-video-playlist-manager' ); ?></a>
		<span class="error-message"></span>

		<?php if ( ! empty( $video ) ) { ?>
			<p>
			<?php
			$creator = get_userdata( $video->post_author );
			if ( ! empty( $creator ) ) {
				?>
				<strong>Created by:</strong> <em><?php echo esc_html( $creator->user_nicename ); ?></em>
				on
				<em><?php echo esc_html( PMC_TimeMachine::create( $manager->timezone )->from_time( 'Y-m-d H:i:s', $video->post_date )->format_as( 'jS M Y H:i' ) ); ?></em>
				<?php
			}

			$log = $manager->get_last_modified_log( $video->ID, false );

			if ( ! empty( $log ) && is_array( $log ) ) {
				?>
				<p>
					<strong><?php esc_html_e( 'Last Modified by:', 'pmc-video-playlist-manager' ); ?></strong>
					<?php
					foreach ( $log as $last_modified_time => $video_user ) {
						$video_user = get_userdata( $video_user );

						$format             = ( is_numeric( $last_modified_time ) ) ? 'U' : 'Y-m-d H:i:s';
						$last_modified_time = PMC_TimeMachine::create( $manager->timezone )->from_time( $format, $last_modified_time )->format_as( 'jS M Y H:i' );
						?>
						<br>
						<em>
							<?php
							if ( ! empty( $video_user->user_nicename ) ) {
								echo esc_html( $video_user->user_nicename );
							}
							?>
						</em>
						on <em><?php echo esc_html( $last_modified_time ); ?></em>
						<?php
					}
					?>
				</p>
				<?php
			}
			?>
			</p>
			<br clear="both">
		<?php } ?>
		<div class="clear"></div>

	</form>
</div>
