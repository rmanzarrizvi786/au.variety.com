<?php
/*
Plugin Name: MMC Keep Post on Homepage
Description: Select a post and position, that post will always appear in that position on the homepage.
Author: Gabriel Koen, PMC
Version: 1.1.0.0
License: PMC Proprietary.  All rights reserved.

TODO: This could be smarter and save the previous publish date to a postmeta, then restore it when deactivating.
TODO: A smarter way still would be to leave the publish date alone, and in the archive template inject the selected post where it belongs. Didn't do that before because AdOps wanted the publish date to line up in the river, and because it would mess with pagination. But when displaying the post we could fake the date, and dynamically change the pagination when this plugin is activated.
*/

/**
 * Register the settings page
 *
 * @since 1.0.0.0
 * @version 1.1.0.0 2011-01-30 Gabriel Koen
 */
if ( ! function_exists('mmc_kph_admin') ) :
function mmc_kph_admin() {
	add_submenu_page( 'edit.php', 'Keep Post on Homepage', 'Keep Post on Homepage', 'edit_others_posts', 'mmc-keep-post-on-homepage', 'mmc_kph_admin_panel' );
}
add_action('admin_menu', 'mmc_kph_admin');
endif;

/**
 * Update the selected post's publish date so that it always shows up on the homepage in
 * the selected spot
 *
 * @since 1.0.0.0
 * @version 1.1.0.0 2011-01-30 Gabriel Koen
 */
if ( ! function_exists('mmc_kph_move_post') ) :
function mmc_kph_move_post( $postID, $post ) {
	// Don't do anything if this is an ajax or autosave request
	if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined('DOING_AJAX') && DOING_AJAX ) ) {
		return;
	}

	// Get options. Short circuit if option isn't set.
	$mmc_kph_options = get_option('mmc_kph_options');
	if ( ! $mmc_kph_options ) {
		return;
	}

	// Short circuit if disabled.
	// The plugin options have been validated & sanitized prior to saving them, so unless someone has manipulated them in the database directly there's no need for further checking.
	if ( $mmc_kph_options['mmc_kph_disabled'] ) {
		return;
	}

	// Only run on published or trashed posts
	if ( 'post' !== $post->post_type
		|| ( 'publish' !== $post->post_status && 'trash' !== $post->post_status )
	) {
		return;
	}

	// Make sure the flagged post exists and is published, otherwise bail
	if ( $postID == $mmc_kph_options['flagged_post_ID'] ) {
		$flagged_post = $post;
	} else {
		$flagged_post = get_post( $mmc_kph_options['flagged_post_ID'] );
	}
	if ( ! $flagged_post || 'publish' !== $flagged_post->post_status ) {
		return;
	}

	// Get latest posts, not including our current post
	$posts = get_posts(array(
		'post__not_in' => array($mmc_kph_options['flagged_post_ID']),
		'numberposts' => absint($mmc_kph_options['post_position'] + 1),
		'orderby' => 'post_date',
		'order' => 'DESC',
		'post_type' => 'post',
		'post_status' => 'publish',
	));

	// Get the previous post and next post dates so we can target the right position
	$previous_position = $mmc_kph_options['post_position']-1;
	if ( ! isset($posts[$previous_position]->post_date) ) {
		return; // There's no previous post, can't do anything
	}
	$sandwich_bottom = strtotime($posts[$previous_position]->post_date);

	$next_position = $mmc_kph_options['post_position']-2;
	if ( isset($posts[$next_position]->post_date) )
		$sandwich_top = strtotime($posts[$next_position]->post_date);
	else
		$sandwich_top = time(); // There's no next post, use the current time instead

	// Get a date that's just before our sandwich top and after our sandwich bottom so that it sits nicely in the middle
	$seconds = 1;
	$_break = false;
	do {
		$target_post_date = ($sandwich_top - $seconds);
		if ( $target_post_date > $sandwich_bottom )
			$_break = true;
		else
			$seconds++;

	} while ( $_break === false );

	// Calculate the post date
	$post_date = date('Y-m-d H:i:s', $target_post_date);

	// Calculate the post_date_gmt
	$offset = ( strtotime($flagged_post->post_date) - strtotime($flagged_post->post_date_gmt) );
	$post_date_gmt = date('Y-m-d H:i:s', $target_post_date + $offset);

	// Update our target post with the middle date

	if ( $flagged_post->post_date == $post_date ) {
		// flagged post is already set to this date, bail
		return;
	}

	$post_data = array(
		'ID' => $mmc_kph_options['flagged_post_ID'],
		'post_date' => $post_date,
		'post_date_gmt' => $post_date_gmt,
	);
	wp_update_post( $post_data );
}
add_action('save_post', 'mmc_kph_move_post', 1, 2);
endif;

/**
 * Output the admin page
 *
 * @since 1.0.0.0
 * @version 1.1.0.0 2011-01-30 Gabriel Koen
 */
if ( ! function_exists('mmc_kph_admin_panel') ) :
function mmc_kph_admin_panel() {
	global $wp_query;

	$mmc_kph_options_defaults = array(
		'mmc_kph_version' => 1,
		'post_position' => 5,
		'flagged_post_ID' => 0,
		'mmc_kph_disabled' => 1,
	);
	$mmc_kph_options = get_option( 'mmc_kph_options', $mmc_kph_options_defaults );
	?>
	<div class="wrap">
		<h2>Keep Post on Homepage</h2>
		<?php
			// Figure out if we're paginated
			$paged = ( isset($_GET['paged']) ) ? absint($_GET['paged']) : 0;

			// Set up default args for get_posts
			$get_posts_args = array(
				'numberposts' => 20,
				'offset' => ($paged > 1) ? ($paged*20) : 0,
				'orderby' => 'post_date',
				'order' => 'DESC',
				'post_type' => 'post',
				'post_status' => 'publish',
			);

			// Exclude sticky posts
			$sticky_posts = get_option( 'sticky_posts', array() );
			if ( ! empty($sticky_posts) ) {
				$get_posts_args['exclude'] = implode(',', $sticky_posts);
			}

			// Get posts from homepage
			$posts = get_posts($get_posts_args);

			$page_links = paginate_links( array(
				'base' => add_query_arg( 'paged', '%#%' ),
				'format' => '',
				'prev_text' => __('&laquo;'),
				'next_text' => __('&raquo;'),
				'total' => 30,
				'current' => ($paged > 1) ? $paged : 1,
			));

			if ( $page_links ) {
				// $paged should be 1 or greater
				$paged = ($paged > 1) ? $paged : 1;
				?>
				<div class="tablenav">
					<div class="tablenav-pages">
						<?php
						$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s' ) . '</span>%s',
							number_format_i18n( ( $paged - 1 ) * 20 + 1 ),
							number_format_i18n( $paged * 20 ),
							$page_links
						);
						echo $page_links_text;
						?>
					</div>
				</div>
				<?php
			}
		?>
		<form name="mmc_kph_settings_form" method="post" action="options.php">
			<?php settings_fields('mmc_kph_settings'); ?>
			<label><input type="checkbox" name="mmc_kph_options[mmc_kph_disabled]" value="1" <?php checked( '1', $mmc_kph_options['mmc_kph_disabled'] ); ?> /> Disable (Do not keep any posts stickied, this will clear any existing post)</label>
			<table id="keep-post-on-homepage" class="widefat">
				<thead>
					<tr>
						<th scope="col">Name</th>
						<th scope="col">Position</th>
					</tr>
				</thead>

				<tbody>

				<?php
				$count = count($posts);
				for ( $i = 0; $i < $count; $i++ ) {
					$class = ($i%2) ? '' : 'alternate';
					?>
					<tr id="<?php echo absint($posts[$i]->ID); ?>" class="<?php echo $class; ?>">
						<th scope="row"><?php echo esc_html( $posts[$i]->post_title ); ?></th>
						<td style="text-align: center;">
							<input type="radio" name="mmc_kph_options[flagged_post_ID]" value="<?php echo absint($posts[$i]->ID); ?>" <?php checked($posts[$i]->ID, $mmc_kph_options['flagged_post_ID'] ); ?> />
							<input type="text" name="mmc_kph_options[post_position][<?php echo absint($posts[$i]->ID); ?>]" size="6" value="<?php if ( $posts[$i]->ID == $mmc_kph_options['flagged_post_ID'] ) { echo absint($mmc_kph_options['post_position']); } ?>" />
						</td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="Submit" id="submit" value="Save &raquo;" /></p>
		</form>
		<div class="tablenav">

		<?php
		if ( $page_links )
			echo "<div class='tablenav-pages'>$page_links_text</div>";
		?>
		</div>
	</div>
	<?php
}
endif;

/**
 * Register the plugin settings with the WordPress settings API
 *
 * @since 1.0.0.0
 * @version 1.1.0.0 2011-01-30 Gabriel Koen
 */
if ( ! function_exists('mmc_kph_register_setting') ) :
function mmc_kph_register_setting() {
	register_setting( 'mmc_kph_settings', 'mmc_kph_options', 'mmc_kph_settings_validate' );
}
add_action('admin_init', 'mmc_kph_register_setting');
endif;

/**
 * Sanitize and validate settings
 *
 * @since 1.0.0.0
 * @version 1.1.0.0 2011-01-30 Gabriel Koen
 */
if ( ! function_exists('mmc_kph_settings_validate') ) :
function mmc_kph_settings_validate( $mmc_kph_options ) {
	// Code duplication ... yuck
	$mmc_kph_options_defaults = array(
		'mmc_kph_version' => 1,
		'post_position' => 5,
		'flagged_post_ID' => 0,
		'mmc_kph_disabled' => 1,
	);

	// Sanitize the posted options
	foreach ( $mmc_kph_options as $key => &$value ) {
		switch ( $key ) {
			case 'mmc_kph_disabled':
				$value = (bool) $value;
				break;

			case 'post_position':
				$value = array_filter( (array) $value );
				break;

			case 'flagged_post_ID':
			case 'mmc_kph_version':
				$value = absint( $value );
				break;
		}
	}

	// If disabled, reset all options to their defaults
	if ( isset($mmc_kph_options['mmc_kph_disabled']) && $mmc_kph_options['mmc_kph_disabled'] ) {
		add_settings_error( 'mmc_kph_options', 'mmc_kph', '"Disabled" was checked, the post to keep on the homepage has been cleared.', 'updated' );
		return $mmc_kph_options_defaults;
	} else {
		$mmc_kph_options['mmc_kph_disabled'] = false;
	}

	// Validate the posted options
	// Options will revert to their defaults if invalid.
	$flagged_post_ID = (isset($mmc_kph_options['flagged_post_ID'])) ? $mmc_kph_options['flagged_post_ID'] : $mmc_kph_options_defaults['flagged_post_ID'];

	if ( $flagged_post_ID < 1 ) {
		add_settings_error( 'mmc_kph_options', 'mmc_kph', 'No post was selected.' );
		add_settings_error( 'mmc_kph_options', 'mmc_kph', 'Keep Post on Homepage has been disabled to prevent unexpected posts showing up on the homepage.' );
		return $mmc_kph_options_defaults;
	}

	if ( ! isset($mmc_kph_options['post_position'][$flagged_post_ID])
		|| empty($mmc_kph_options['post_position'][$flagged_post_ID])
		|| $mmc_kph_options['post_position'][$flagged_post_ID] < 1
		|| $mmc_kph_options['post_position'][$flagged_post_ID] > get_option('posts_per_page')
		) {
		add_settings_error( 'mmc_kph_options', 'mmc_kph', 'Post position must be between 1-' . esc_html( get_option('posts_per_page') ) . ' or it won\'t show up.' );
		add_settings_error( 'mmc_kph_options', 'mmc_kph', 'Keep Post on Homepage has been disabled to prevent unexpected posts showing up on the homepage.' );
		return $mmc_kph_options_defaults;
	} else {
		// post_position expects a single value, not an array
		$mmc_kph_options['post_position'] = $mmc_kph_options['post_position'][$flagged_post_ID];
	}

	add_settings_error( 'mmc_kph_options', 'mmc_kph', 'Keep Post on Homepage has been updated.	The selected post will move into position the next time a post is saved.', 'updated' );

	return $mmc_kph_options;
}
endif;

/**
 * Output any status messages
 *
 * @since 1.1.0.0 2011-01-30 Gabriel Koen
 * @version 1.1.0.0 2011-01-30 Gabriel Koen
 */
if ( ! function_exists('mmc_kph_display_settings_errors') ) :
function mmc_kph_display_settings_errors() {
	settings_errors( 'mmc_kph_options' );
}
add_action( 'admin_notices', 'mmc_kph_display_settings_errors' );
endif;

//EOF
