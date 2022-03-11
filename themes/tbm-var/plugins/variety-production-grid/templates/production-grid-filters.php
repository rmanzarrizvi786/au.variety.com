<?php
/**
 * The template part for page template product grid.
 *
 * @since 2017-08-16 Milind More CDWE-473
 *
 * @package pmc-variety-2017
 */
?>
<div class="production-grid">
	<p>
		<?php esc_html_e( 'Production Charts bring you the latest pre-production and production commitments across television and film. Our charts allow you to filter by location, medium and genre. Stay up to the minute with what is going on where.', 'pmc-variety' ); ?>
	</p>
	<div id="pg_selections" class="section-network-selection">
		<label class="pilots"><?php esc_html_e( 'Filter By:', 'pmc-variety' ); ?></label>

		<select id="pg-type-select" class="pg-filter-select" data-placeholder="Type">
			<option value="tvfilm"><?php esc_html_e( 'TV and Film', 'pmc-variety' ); ?></option>
			<option value="tv"><?php esc_html_e( 'TV', 'pmc-variety' ); ?></option>
			<option value="film"><?php esc_html_e( 'Film', 'pmc-variety' ); ?></option>
		</select><!-- #pg_selections-->

		<select id="pg-genre-select" class="pg-filter-select" data-placeholder="Genre">
			<option value='0'><?php esc_html_e( 'All Genres', 'pmc-variety' ); ?></option>
			<option value="1"><?php esc_html_e( '2-hour Backdoor Pilot', 'pmc-variety' ); ?></option>
			<option value="16"><?php esc_html_e( 'Action', 'pmc-variety' ); ?></option>
			<option value="2"><?php esc_html_e( 'Alternative', 'pmc-variety' ); ?></option>
			<option value="3"><?php esc_html_e( 'Animation', 'pmc-variety' ); ?></option>
			<option value="14"><?php esc_html_e( 'Animation Feature', 'pmc-variety' ); ?></option>
			<option value="4"><?php esc_html_e( 'Comedy', 'pmc-variety' ); ?></option>
			<option value="10"><?php esc_html_e( 'Daytime Soap', 'pmc-variety' ); ?></option>
			<option value="15"><?php esc_html_e( 'Documentary Feature', 'pmc-variety' ); ?></option>
			<option value="5"><?php esc_html_e( 'Drama', 'pmc-variety' ); ?></option>
			<option value="17"><?php esc_html_e( 'Horror', 'pmc-variety' ); ?></option>
			<option value="6"><?php esc_html_e( 'Longform', 'pmc-variety' ); ?></option>
			<option value="12"><?php esc_html_e( 'News', 'pmc-variety' ); ?></option>
			<option value="18"><?php esc_html_e( 'Sci Fi / Fantasy', 'pmc-variety' ); ?></option>
			<option value="7"><?php esc_html_e( 'Special', 'pmc-variety' ); ?></option>
			<option value="11"><?php esc_html_e( 'Sports', 'pmc-variety' ); ?></option>
			<option value="8"><?php esc_html_e( 'Talk', 'pmc-variety' ); ?></option>
			<option value="19"><?php esc_html_e( 'Thriller', 'pmc-variety' ); ?></option>
		</select><!-- pg-genre-select -->

		<select id="pg-location-select" class="pg-filter-select" data-placeholder="Location">
			<option value=""><?php esc_html_e( 'All Locations', 'pmc-variety' ); ?></option>
			<option value="United States"><?php esc_html_e( 'United States', 'pmc-variety' ); ?></option>
			<option value="Canada"><?php esc_html_e( 'Canada', 'pmc-variety' ); ?></option>
			<option value="United Kingdom"><?php esc_html_e( 'United Kingdom', 'pmc-variety' ); ?></option>
			<option value="">---------------</option>
			<option value="Albuquerque"><?php esc_html_e( 'Albuquerque', 'pmc-variety' ); ?></option>
			<option value="Atlanta"><?php esc_html_e( 'Atlanta', 'pmc-variety' ); ?></option>
			<option value="Austin"><?php esc_html_e( 'Austin', 'pmc-variety' ); ?></option>
			<option value="Baton Rouge"><?php esc_html_e( 'Baton Rouge', 'pmc-variety' ); ?></option>
			<option value="Boston"><?php esc_html_e( 'Boston', 'pmc-variety' ); ?></option>
			<option value="Brooklyn"><?php esc_html_e( 'Brooklyn', 'pmc-variety' ); ?></option>
			<option value="Brussels"><?php esc_html_e( 'Brussels', 'pmc-variety' ); ?></option>
			<option value="Burbank"><?php esc_html_e( 'Burbank', 'pmc-variety' ); ?></option>
			<option value="Chicago"><?php esc_html_e( 'Chicago', 'pmc-variety' ); ?></option>
			<option value="Dallas"><?php esc_html_e( 'Dallas', 'pmc-variety' ); ?></option>
			<option value="Detroit"><?php esc_html_e( 'Detroit', 'pmc-variety' ); ?></option>
			<option value="Dublin"><?php esc_html_e( 'Dublin', 'pmc-variety' ); ?></option>
			<option value="Las Vegas"><?php esc_html_e( 'Las Vegas', 'pmc-variety' ); ?></option>
			<option value="Los Angeles"><?php esc_html_e( 'Los Angeles', 'pmc-variety' ); ?></option>
			<option value="London"><?php esc_html_e( 'London', 'pmc-variety' ); ?></option>
			<option value="Montreal"><?php esc_html_e( 'Montreal', 'pmc-variety' ); ?></option>
			<option value="Nashville"><?php esc_html_e( 'Nashville', 'pmc-variety' ); ?></option>
			<option value="New Orleans"><?php esc_html_e( 'New Orleans', 'pmc-variety' ); ?></option>
			<option value="New York"><?php esc_html_e( 'New York', 'pmc-variety' ); ?></option>
			<option value="Paris"><?php esc_html_e( 'Paris', 'pmc-variety' ); ?></option>
			<option value="Pittsburgh"><?php esc_html_e( 'Pittsburgh', 'pmc-variety' ); ?></option>
			<option value="Portland"><?php esc_html_e( 'Portland', 'pmc-variety' ); ?></option>
			<option value="San Francisco"><?php esc_html_e( 'San Francisco', 'pmc-variety' ); ?></option>
			<option value="Stamford"><?php esc_html_e( 'Stamford', 'pmc-variety' ); ?></option>
			<option value="Toronto"><?php esc_html_e( 'Toronto', 'pmc-variety' ); ?></option>
			<option value="Vancouver"><?php esc_html_e( 'Vancouver', 'pmc-variety' ); ?></option>
			<option value="Whittier"><?php esc_html_e( 'Whittier', 'pmc-variety' ); ?></option>
			<option value="Wilmington"><?php esc_html_e( 'Wilmington', 'pmc-variety' ); ?></option>
		</select><!-- #pg-location-select -->

		<select id="pg-status-select" class="pg-filter-select" data-placeholder="Status">
			<option value="A"><?php esc_html_e( 'All Statuses', 'pmc-variety' ); ?></option>
			<option value="Production"><?php esc_html_e( 'Production', 'pmc-variety' ); ?></option>
			<option value="Pre-Production"><?php esc_html_e( 'Pre Production', 'pmc-variety' ); ?></option>
		</select><!-- #pg-status-select -->

		<div class="data-provided-by">
			<div id="pg-header-text-results"></div>
			<div id="pg-header-logo-div">
				<label class="provider"><?php esc_html_e( 'Data provided by:', 'pmc-variety' ); ?></label>
				<a target="_blank" onclick="Variety_Scorecard.TrackView( 'pilots-scorecard', 'variety-insight-production-charts', 'click' );" href="https://www.varietyinsight.com">
					<img class="pg_insight_icon" src="<?php echo esc_url( VARIETY_THEME_URL . '/plugins/variety-scorecard/images/variety_insight_logo.gif' ); ?>" alt="<?php esc_attr_e( 'Variety Insight', 'pmc-variety' ); ?>" title="<?php esc_attr_e( 'Variety Insight', 'pmc-variety' ); ?>"/>
				</a>
			</div>
		</div>

	</div><!-- pg_selections -->

	<div id="scorecard-table" class="section">
		<a class="anchor" id="anchor-scorecard" name="page-anchor"></a>
		<div class="section-table">

			<table id="table-production-grid" class="scorecard">
				<thead>
					<tr>
						<th id='pg-header-text' colspan="7"></th>
					</tr>
					<tr class="table-header">
						<th id='col-title'><?php esc_html_e( 'Type/Title', 'pmc-variety' ); ?> <span id='col-title-asc'>&#9650;</span><span id='col-title-des'>&#9660;</span></th>
						<th id='col-studio'><?php esc_html_e( 'Studio(s)', 'pmc-variety' ); ?> <span id='col-studio-asc'>&#9650;</span><span id='col-studio-des'>&#9660;</span></th>
						<th id='col-genre'><?php esc_html_e( 'Genre/Arena', 'pmc-variety' ); ?> <span id='col-genre-asc'>&#9650;</span><span id='col-genre-des'>&#9660;</span></th>
						<th id='col-dates'><?php esc_html_e( 'Shoot Dates', 'pmc-variety' ); ?> <span id='col-dates-asc'>&#9650;</span><span id='col-dates-des'>&#9660;</span></th>
						<th id='col-location'><?php esc_html_e( 'Location(s)', 'pmc-variety' ); ?><br /><?php esc_html_e( 'And Contact', 'pmc-variety' ); ?> <span id='col-location-asc'>&#9650;</span><span id='col-location-des'>&#9660;</span></th>
						<th id='col-commitment'><?php esc_html_e( 'Commitment', 'pmc-variety' ); ?><br /><?php esc_html_e( 'Type', 'pmc-variety' ); ?> <span id='col-commitment-asc'>&#9650;</span><span id='col-commitment-des'>&#9660;</span></th>
						<th id='col-status'><?php esc_html_e( 'Production', 'pmc-variety' ); ?><br /><?php esc_html_e( 'Status', 'pmc-variety' ); ?> <span id='col-status-asc'>&#9650;</span><span id='col-status-des'>&#9660;</span></th>
					</tr>
				</thead>
				<tbody id="pg-table-body-loading">
					<tr>
						<td colspan="7">
							<img src="<?php echo PMC::esc_url_ssl_friendly( get_stylesheet_directory_uri() . "/plugins/variety-production-grid/assets/img/ajax-loader.gif" ); ?>" />
							<label><?php esc_html_e( 'Loading...', 'pmc-variety' ); ?>
							</label>
						</td>
					</tr>
				</tbody>
				<tbody id="pg-table-body">
				</tbody>
			</table> <!-- table-production-grid -->

		</div> <!-- .section-table -->

		<div class="pagination">
			<div class="pagesize-selection">
				<label><?php esc_html_e( 'Display', 'pmc-variety' ); ?></label>

				<select class='pg-filter-select' id="pg-page-size-select">
					<option value="50"><?php esc_html_e( '50 results', 'pmc-variety' ); ?></option>
					<option value="100"><?php esc_html_e( '100 results', 'pmc-variety' ); ?></option>
				</select>

			</div><!-- .pagesize-selection -->
			<div id="pg-pagination">
			</div>
		</div> <!-- .pagination -->

	</div><!-- #scorecard-table -->

	<div id="pg-please-login">

		<p>
			<?php esc_html_e( 'Already a Variety PREMIER Member? To access the Production Charts', 'pmc-variety' ); ?>
			<a href="<?php echo esc_url( home_url( '/digital-subscriber-access/#r=/production-charts/', 'https' ) ); ?>"><?php esc_html_e( 'sign in', 'pmc-variety' ); ?></a>.
			<?php esc_html_e( 'Learn more about PREMIER and', 'pmc-variety' ); ?> <a href="<?php echo esc_url( home_url( '/join-premier/', 'https' ) ); ?>"><?php esc_html_e( 'subscribe', 'pmc-variety' ); ?></a>.
		</p>

	</div><!-- #pg-please-login -->

</div><!-- .production-grid -->
