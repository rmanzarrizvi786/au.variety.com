<?php
/**
 * The template part for page template vscore top 250.
 *
 * @since 2017-08-16 Milind More CDWE-474
 *
 * @package pmc-variety-2017
 */
?>
<div id="vscore-top">
	<p>
		<?php esc_html_e( 'The Entertainment Industry’s Actor Measurement Tool. Scores are calculated using a composite of box office performance, television ratings, social media discussion, awards recognition, and projects currently in development.', 'pmc-variety' ); ?>
	</p>	
	<div id="pg_selections" class="section-network-selection">
		<label class="pilots"><?php esc_html_e( 'Filter By:', 'pmc-variety' ); ?></label>
		<select id="pg-gender-select" class="pg-filter-select" data-placeholder="Gender">
			<option value="both"><?php esc_html_e( 'Male & Female', 'pmc-variety' ); ?></option>
			<option value="M"><?php esc_html_e( 'Male', 'pmc-variety' ); ?></option>
			<option value="F"><?php esc_html_e( 'Female', 'pmc-variety' ); ?></option>
		</select>
		<select id="pg-age-select" class="pg-filter-select" data-placeholder="Age">
			<option value='0'><?php esc_html_e( 'All Ages', 'pmc-variety' ); ?></option>
			<option value="1"><?php esc_html_e( '1 - 10', 'pmc-variety' ); ?></option>
			<option value="2"><?php esc_html_e( '11 - 20', 'pmc-variety' ); ?></option>
			<option value="3"><?php esc_html_e( '21 - 30', 'pmc-variety' ); ?></option>
			<option value="4"><?php esc_html_e( '31 - 40', 'pmc-variety' ); ?></option>
			<option value="5"><?php esc_html_e( '41 - 50', 'pmc-variety' ); ?></option>
			<option value="6"><?php esc_html_e( '51 - 60', 'pmc-variety' ); ?></option>
			<option value="7"><?php esc_html_e( '61 - 70', 'pmc-variety' ); ?></option>
			<option value="8"><?php esc_html_e( '71 - 80', 'pmc-variety' ); ?></option>
			<option value="9"><?php esc_html_e( '81 - 90', 'pmc-variety' ); ?></option>
			<option value="10"><?php esc_html_e( '91 - 100', 'pmc-variety' ); ?></option>
		</select>
		<select id="pg-ethnicity-select" class="pg-filter-select" data-placeholder="Ethnicity">
			<option value="0"><?php esc_html_e( 'All Ethnicities', 'pmc-variety' ); ?></option>
			<option value="1"><?php esc_html_e( 'White/Caucasian', 'pmc-variety' ); ?></option>
			<option value="2"><?php esc_html_e( 'Black/African American', 'pmc-variety' ); ?></option>
			<option value="3"><?php esc_html_e( 'Hispanic/Latino', 'pmc-variety' ); ?></option>
			<option value="4"><?php esc_html_e( 'Native Hawaiian/Pacific Islander', 'pmc-variety' ); ?></option>
			<option value="5"><?php esc_html_e( 'Asian', 'pmc-variety' ); ?></option>
			<option value="6"><?php esc_html_e( 'American Indian/Alaska Native', 'pmc-variety' ); ?></option>
			<option value="7"><?php esc_html_e( 'South Asian', 'pmc-variety' ); ?></option>
			<option value="8"><?php esc_html_e( '2+ Race', 'pmc-variety' ); ?></option>
		</select>
		<select id="pg-country-select" class="pg-filter-select" data-placeholder="Country">
			<option value="0"><?php esc_html_e( 'All Countries', 'pmc-variety' ); ?></option>
			<option value="1"><?php esc_html_e( 'Australia', 'pmc-variety' ); ?></option>
			<option value="2"><?php esc_html_e( 'Canada', 'pmc-variety' ); ?></option>
			<option value="3"><?php esc_html_e( 'Colombia', 'pmc-variety' ); ?></option>
			<option value="4"><?php esc_html_e( 'France', 'pmc-variety' ); ?></option>
			<option value="5"><?php esc_html_e( 'Germany', 'pmc-variety' ); ?></option>
			<option value="6"><?php esc_html_e( 'Hong Kong', 'pmc-variety' ); ?></option>
			<option value="7"><?php esc_html_e( 'Ireland', 'pmc-variety' ); ?></option>
			<option value="8"><?php esc_html_e( 'Mexico', 'pmc-variety' ); ?></option>
			<option value="9"><?php esc_html_e( 'South Africa', 'pmc-variety' ); ?></option>
			<option value="10"><?php esc_html_e( 'Spain', 'pmc-variety' ); ?></option>
			<option value="11"><?php esc_html_e( 'United Kingdom', 'pmc-variety' ); ?></option>
			<option value="12"><?php esc_html_e( 'United States', 'pmc-variety' ); ?></option>
			<option value="13"><?php esc_html_e( 'Zimbabwe', 'pmc-variety' ); ?></option>
		</select>

		<div class="data-provided-by">
			<div id="pg-header-text-results"></div>

			<div id="pg-header-logo-div">
				<label class="provider"><?php esc_html_e( 'Data provided by:', 'pmc-variety' ); ?></label>
				<a target="_blank" onclick="Variety_Scorecard.TrackView( 'pilots-scorecard', 'variety-insight-vscore-top-250', 'click' );" href="https://www.varietyinsight.com/vscore/index.php?source=vscoretop250">
					<img class="pg_insight_icon" src="<?php echo esc_url( VARIETY_THEME_URL . '/plugins/variety-scorecard/images/variety_insight_logo.gif' ); ?>" alt="<?php esc_attr_e( 'Vscore', 'pmc-variety' ); ?>" title="<?php esc_attr_e( 'Vscore', 'pmc-variety' ); ?>"/>
				</a>
			</div><!-- #pg-header-logo -->

		</div><!--.data-provided-by-->
	</div><!-- #pg-selections-->

	<div id="scorecard-table" class="section">

		<a class="anchor" id="anchor-scorecard" name="page-anchor"></a>

		<div class="section-table">
			<table id="table-production-grid" class="scorecard">
				<thead>
					<tr>
					<?php
					$columns = array(
						'name'         => 'Name',
						'vscore'       => 'Vscore',
						'age'          => 'Age',
						'gender'       => 'Gender',
						'ethnicity'    => 'Ethnicity',
						'country'      => 'Country',
						'film-score'   => 'Film Score',
						'tv-score'     => 'TV Score',
						'social-score' => 'Social Score',
					);

					foreach ( $columns as $col_name => $col_title ) {
						printf( '<th id="%1$s">%2$s <span id="%3$s">&#9650;</span><span id="%4$s">&#9660;</span></th>', esc_attr( 'col-' . $col_name ),  esc_html( $col_title ), esc_attr( 'col-' . $col_name . '-asc' ), esc_attr( 'col-' . $col_name . '-des' ) );
					}
					?>
					</tr>
				</thead>
				<tbody id="pg-table-body-loading">
					<tr>
						<td colspan="9">
							<img src="<?php echo PMC::esc_url_ssl_friendly( get_stylesheet_directory_uri() . "/plugins/variety-vscore-top/assets/img/ajax-loader.gif " ); ?>" />
							<label><?php esc_html_e( 'Loading...', 'pmc-variety' ); ?></label>
						</td>
					</tr>
				</tbody>
				<tbody id="pg-table-body">
				</tbody>
			</table>
		</div><!-- .section-table -->

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

	</div> <!-- #scorecard-table -->

	<div id="vst-please-login">
		<p>
			<?php esc_html_e( 'Already a Variety PREMIER Member? To access the Vscore Top 250', 'pmc-variety' ); ?> <a href="<?php echo esc_url( home_url( '/digital-subscriber-access/#r=/vscore-top-250/', 'https' ) ); ?>"><?php esc_html_e( 'sign in', 'pmc-variety' ); ?></a>. <?php esc_html_e( 'Learn more about PREMIER and', 'pmc-variety' ); ?> <a href="<?php echo esc_url( home_url( '/join-premier/', 'https' ) ); ?>"><?php esc_html_e( 'subscribe', 'pmc-variety' ); ?></a>.
		</p>
	</div> <!--#vst-please-login -->
</div><!-- #vscore-top -->
