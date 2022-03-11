<?php
/**
 * Settings Page Template
 *
 * Template for the V500 settings page, found
 * in the Settings WP admin menu.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

$variety_500_year = get_option( 'variety_500_year', date( 'Y' ) );
$video_id         = get_option( 'variety_500_home_video_id' );
$presented_by     = get_option( 'variety_500_presented_by' );
$sponsor_link     = get_option( 'variety_500_sponsor_link' );
$sponsor_logo     = get_option( 'variety_500_sponsor_logo' );
$sponsor_hero_logo = get_option( 'variety_500_sponsor_hero_logo' );
$sponsor_pixel    = get_option( 'variety_500_sponsor_pixel' );
$deep_dive_link   = get_option( 'variety_500_vi_cta_link' );
$profiles_string  = get_option( 'variety_500_spotlight_profiles' );
$instagram_string = get_option( 'variety_500_instagram_profiles' );
$interview_term   = get_option( 'variety_500_interviews_term' );
$interview_term   = ( ! empty( $interview_term ) ) ? $interview_term : '';
$profile_ids      = array();
$instagram_ids    = array();

if ( ! empty( $profiles_string ) && is_string( $profiles_string ) ) {
	$profile_ids = explode( ',', $profiles_string );
}

if ( ! empty( $instagram_string ) && is_string( $instagram_string ) ) {
	$instagram_ids = explode( ',', $instagram_string );
}

$args = array(
	'orderby'    => 'name',
	'order'      => 'ASC',
	'hide_empty' => 0,
);

$pmc_carousel_modules_terms = get_terms( 'pmc_carousel_modules', $args );

if ( is_wp_error( $pmc_carousel_modules_terms ) ) {
	$pmc_carousel_modules_terms = array();
}

$args = array(
	'taxonomy'        => 'vy500_year',
	'number'          => 10,
	'suppress_filter' => true,
	'orderby'         => 'term_id',
	'order'           => 'DESC',
);

$variety_500_years = get_terms( $args );

if ( empty( $variety_500_years ) || is_wp_error( $variety_500_years ) ) {
	$variety_500_years = array();
}

?>

<div class="wrap">
	<h1><?php esc_html_e( 'Variety 500 Settings', 'pmc-variety' ); ?></h1>

	<form method="post" action="options.php">
		<?php settings_fields( \Variety\Plugins\Variety_500\Settings::SETTINGS_GROUP ); ?>
		<?php do_settings_sections( \Variety\Plugins\Variety_500\Settings::SETTINGS_GROUP ); ?>

		<table class="form-table">
			<tr>
				<th style="padding: 0;">
					<h3 class="title"><?php esc_html_e( 'General Settings', 'pmc-variety' ); ?></h3>
				</th>
			</tr>
			<tr>
				<th><label for="variety-500-year"><?php esc_html_e( 'Variety 500 Year', 'pmc-variety' ); ?></label></th>
				<td>
					<select class="variety-500-year regular-text" name="variety_500_year">
						<?php if ( ! empty( $variety_500_years ) ) { ?>
							<option selected disabled><?php esc_html_e( 'Please select Year', 'pmc-variety' ); ?></option>
							<?php foreach ( $variety_500_years as $year ) { ?>
								<option value="<?php echo esc_attr( $year->slug ); ?>" <?php selected( $variety_500_year, $year->slug ); ?> ><?php echo esc_html( $year->name ); ?></option>
							<?php } ?>
						<?php } else { ?>
								<option value="<?php echo esc_attr( date( 'Y' ) ); ?>" ><?php echo esc_html( date( 'Y' ) ); ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>

				<th style="padding: 0;">
					<h3 class="title"><?php esc_html_e( 'Header', 'pmc-variety' ); ?></h3>
				</th>
			</tr>
			<tr>
				<?php $sponsored_by = ! empty( $presented_by ) ? $presented_by : ''; ?>
				<th><label for="sponsor-presented-by"><?php esc_html_e( 'Sponsor Lead-in Text', 'pmc-variety' ); ?></label></th>
				<td>
					<input class="sponsor-presented-by regular-text" name="variety_500_presented_by" type="text" placeholder="<?php esc_attr_e( 'presented by', 'pmc-variety' ); ?>" value="<?php echo esc_html( $presented_by ); ?>" />
				</td>
			</tr>
			<tr>
				<?php $sponsor_link = ! empty( $sponsor_link ) ? $sponsor_link : ''; ?>
				<th><label for="sponsor-link"><?php esc_html_e( 'Sponsor Link URL', 'pmc-variety' ); ?></label></th>
				<td>
					<input class="sponsor-link regular-text" name="variety_500_sponsor_link" type="text" placeholder="<?php echo esc_url( 'https://sponsor.com' ); ?>" value="<?php echo esc_url( $sponsor_link ); ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="sponsor-logo"><?php esc_html_e( 'Sponsor Logo', 'pmc-variety' ); ?></label></th>
				<td>
					<div class="sponsor-logo-img">
						<?php
						if ( ! empty( $sponsor_logo ) ) {
							printf( '<img src="%s" style="max-width: 300px;" />', esc_url( $sponsor_logo ) );
						}
						?>
					</div>
					<p class="hide-if-no-js">
						<a class="upload-sponsor-logo button <?php if ( ! empty( $sponsor_logo ) ) { echo 'hidden'; } ?>" href="#">
							<?php esc_html_e( 'Choose Logo', 'pmc-variety' ); ?>
						</a>
						<a class="remove-sponsor-logo button <?php if ( empty( $sponsor_logo )  ) { echo 'hidden'; } ?>" href="#">
							<?php esc_html_e( 'Remove Logo', 'pmc-variety' ); ?>
						</a>
					</p>
					<input class="sponsor-logo" name="variety_500_sponsor_logo" type="hidden" value="<?php echo esc_url( $sponsor_logo ); ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="sponsor-hero-logo"><?php esc_html_e( 'Sponsor Hero Logo', 'pmc-variety' ); ?></label></th>
				<td>
					<div class="sponsor-hero-logo-img">
						<?php
						if ( ! empty( $sponsor_hero_logo ) ) {
							printf( '<img src="%s" style="max-width: 300px;" />', esc_url( $sponsor_hero_logo ) );
						}
						?>
					</div>
					<p class="hide-if-no-js">
						<a class="upload-sponsor-hero-logo button <?php if ( ! empty( $sponsor_hero_logo ) ) { echo 'hidden'; } ?>" href="#">
							<?php esc_html_e( 'Choose Logo', 'pmc-variety' ); ?>
						</a>
						<a class="remove-sponsor-hero-logo button <?php if ( empty( $sponsor_hero_logo )  ) { echo 'hidden'; } ?>" href="#">
							<?php esc_html_e( 'Remove Logo', 'pmc-variety' ); ?>
						</a>
					</p>
					<input class="sponsor-hero-logo" name="variety_500_sponsor_hero_logo" type="hidden" value="<?php echo esc_url( $sponsor_hero_logo ); ?>" />
				</td>
			</tr>
			<tr>
				<th><label for="sponsor-pixel"><?php esc_html_e( 'Sponsor Pixel URL', 'pmc-variety' ); ?></label></th>
				<td><input class="sponsor-pixel regular-text" name="variety_500_sponsor_pixel" type="text" value="<?php echo esc_url( $sponsor_pixel ); ?>" /></td>
			</tr>
			<tr>
				<th style="padding: 0;">
					<h3 class="title"><?php esc_html_e( 'Home Page', 'pmc-variety' ); ?></h3>
				</th>
			</tr>
			<tr>
				<th><label for="home-video-id"><?php esc_html_e( 'Header Video YouTube ID', 'pmc-variety' ); ?></label></th>
				<td>
					<input class="home-video-id regular-text" name="variety_500_home_video_id" type="text" placeholder="ddJN6zWKwaw" value="<?php echo esc_attr( $video_id ); ?>" />
					<p class="description">
						<?php echo wp_kses_post( __( 'youtu.be/<span style="text-decoration: underline;">ddJN6zWKwaw</span><br />youtube.com/watch?v=<span style="text-decoration: underline;">ddJN6zWKwaw</span>', 'pmc-variety' ) ); ?>

					</p>
				</td>
			</tr>
			<tr>
				<th><label for="spotlight-profiles"><?php esc_html_e( 'Spotlight Profiles', 'pmc-variety' ); ?></label></th>
				<td>
					<input type="hidden" id="spotlight-profiles-input" name="variety_500_spotlight_profiles" value="<?php echo esc_attr( $profiles_string ); ?>" />
					<div class="sortable-actions">
						<p class="description"><?php esc_html_e( 'Choose up to 10 profiles to display in the Spotlight section.', 'pmc-variety' ); ?></p>
						<input id="profile-suggest" class="regular-text" name="#" type="text" value="" placeholder="Enter a Profile Name" />
					</div>

					<div id="spotlight-profiles" class="sortable">
						<div class="element hidden-element" data-post-id="0">
							<div class="handle"><span class="dashicons dashicons-menu"></span></div>
							<div class="element-title"></div>
							<div class="remove">
								<span class="dashicons dashicons-no-alt"></span>
							</div>
						</div>
						<?php if ( ! empty( $profile_ids ) && is_array( $profile_ids ) ) :
							foreach ( $profile_ids as $profile_id ) :
								if ( empty( $profile_id ) ) {
									continue;
								}
								$profile = get_post( $profile_id );
								if ( empty( $profile ) || ! is_object( $profile ) ) {
									continue;
								}
								?>
								<div class="element visible" data-post-id="<?php echo esc_html( $profile_id ); ?>">
									<div class="handle"><span class="dashicons dashicons-menu"></span></div>
									<div class="element-title"><?php echo esc_html( $profile->post_title ); ?></div>
									<div class="remove"><span class="dashicons dashicons-no-alt"></span></div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div><!-- #sortable -->
				</td>
			</tr>
			<tr>
				<th><label for="instagram-profiles"><?php esc_html_e( 'Instagram Profiles', 'pmc-variety' ); ?></label></th>
				<td>
					<input type="hidden" id="instagram-profiles-input" name="variety_500_instagram_profiles" value="<?php echo esc_attr( $instagram_string ); ?>" />
					<div class="sortable-actions">
						<p class="description"><?php esc_html_e( 'Choose up to 20 profiles to display in the Social Glimpse section.', 'pmc-variety' ); ?></p>
						<input id="instagram-suggest" class="regular-text" name="#" type="text" value="" placeholder="Enter a Profile Name" />
					</div>

					<div id="instagram-profiles" class="sortable">
						<div class="element hidden-element" data-post-id="0">
							<div class="handle"><span class="dashicons dashicons-menu"></span></div>
							<div class="element-title"></div>
							<div class="remove">
								<span class="dashicons dashicons-no-alt"></span>
							</div>
						</div>
						<?php if ( ! empty( $instagram_ids ) && is_array( $instagram_ids ) ) :
							foreach ( $instagram_ids as $instagram_id ) :
								if ( empty( $instagram_id ) ) {
									continue;
								}
								$profile = get_post( $instagram_id );
								if ( empty( $profile ) || ! is_object( $profile ) ) {
									continue;
								}
								?>
								<div class="element visible" data-post-id="<?php echo esc_html( $instagram_id ); ?>">
									<div class="handle"><span class="dashicons dashicons-menu"></span></div>
									<div class="element-title"><?php echo esc_html( $profile->post_title ); ?></div>
									<div class="remove"><span class="dashicons dashicons-no-alt"></span></div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div><!-- #sortable -->
				</td>
			</tr>
			<tr>
				<th><label for="stats-cache"><?php esc_html_e( 'Stats Cache', 'pmc-variety' ); ?></label></th>
				<td><a class="button" href="options-general.php?page=variety-500&clear-cache=true&_wpnonce=<?php echo esc_attr( wp_create_nonce( 'clear-cache' ) ); ?>"><?php esc_html_e( 'Clear Statistics Cache', 'pmc-variety' ); ?></a></td>
			</tr>
			<tr>
				<th style="padding: 0;">
					<h3 class="title"><?php esc_html_e( 'Profile Page', 'pmc-variety' ); ?></h3>
				</th>
			</tr>
			<tr>
				<?php $deep_dive_link = ! empty( $deep_dive_link ) ? $deep_dive_link : ''; ?>
				<th><label for="deep-dive-link"><?php esc_html_e( 'Variety Insights Link', 'pmc-variety' ); ?></label></th>
				<td>
					<input class="deep-dive-link regular-text" name="variety_500_vi_cta_link" type="text" value="<?php echo esc_url( $deep_dive_link ); ?>" />
					<p class="description"><?php esc_html_e( 'Enter a URL for the Variety Insights "Deep Dive" CTA', 'pmc-variety' ); ?></p>
				</td>
			</tr>
			<tr>
				<th style="padding: 0;">
					<h3 class="title"><?php esc_html_e( 'Interviews', 'pmc-variety' ); ?></h3>
				</th>
			</tr>
			<tr>
				<th><label for="interviews-terms-dropdown"><?php esc_html_e( 'Interviews Video Module', 'pmc-variety' ); ?></label></th>
				<td>
					<select name="variety_500_interviews_term" class="regular-text interviews-terms-dropdown" >
						<option value=""><?php esc_html_e( 'Please Select', 'pmc-variety' ); ?></option>
						<?php
						if ( ! empty( $pmc_carousel_modules_terms ) ) {
							foreach ( $pmc_carousel_modules_terms  as $term ) {
								printf( '<option value ="%1$s" %2$s>%3$s</option>', esc_attr( $term->slug ), selected( $interview_term, $term->slug, false ), esc_html( $term->name ) );
							}
						}
						?>
					</select>	
					<p class="description"><?php esc_html_e( 'Please choose carousel term for interviews video module on homepage', 'pmc-variety' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>
