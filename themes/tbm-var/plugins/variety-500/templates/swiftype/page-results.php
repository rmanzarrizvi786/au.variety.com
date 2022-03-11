<?php
$stats = \Variety\Plugins\Variety_500\Stats::get_instance()->get_stats();
$current_vy500_year = get_option( 'variety_500_year', date( 'Y' ) );  // need this to compare search results for "new" profile

$select_data_placeholder = __( 'Filter by Year, Media Category, Line of Work and Location', 'pmc-variety' );
$search_filter_label     = __( 'Filter your search by Year, Media Category, Line of Work and Location', 'pmc-variety' );
?>

<section id="swiftype-search-result" class="swiftype">

	<div data-st-fix-misspelling="misspelling"></div>
	<div data-st-manage-conditionals="conditional"></div>
	<div data-st-advanced-routing="advanced_routing"></div>

	<div class="l-search__form">
		<div class="c-search-form">
			<div data-st-search-form="search_form"></div>
			<label class="c-search-form__filter-label" for="filter"><?php echo esc_attr( $search_filter_label ); ?></label>
			<div class="c-search-form__filter-wrap">
				<select class="chosen-select c-search-form__filter" multiple="" tabindex="-1" id="search-filter" data-placeholder="<?php echo esc_attr( $select_data_placeholder ); ?>">
					<?php if ( \Variety\Plugins\Variety_500\Templates::is_vy500_year_ge_2018() ) : ?>
						<optgroup label="Year" data-field="vy500_year">
							<?php
							$years = \Variety\Plugins\Variety_500\Search::get_all_vy500_terms_for_search();
							if ( ! empty( $years ) && is_array( $years ) ) :
								foreach ( $years as $year ) :

									if ( ! is_a( $year, 'WP_Term' ) ) {
										continue;
									}

									printf( '<option value="%1$s">%2$s</option>', esc_attr( $year->slug ), esc_html( $year->name ) );
									echo "\n";
								endforeach;
							endif;
							?>
						</optgroup>
					<?php endif; ?>
					<optgroup label="Media Categories" data-field="media_category">
						<option value="Film">Film</option>
						<option value="Gaming">Gaming</option>
						<option value="Music">Music</option>
						<option value="Technology">Technology</option>
						<option value="Live Entertainment">Live Entertainment</option>
						<option value="TV">TV</option>
					</optgroup>
					<optgroup label="Line of Work" data-field="line_of_work">
						<option value="Artists">Artists</option>
						<option value="Backers">Backers</option>
						<option value="Dealmakers">Dealmakers</option>
						<option value="Execs">Execs</option>
						<option value="Moguls">Moguls</option>
						<option value="Producers">Producers</option>
					</optgroup>
					<optgroup label="Location" data-field="country_of_residence">
						<?php if ( ! empty( $stats['country_of_residence'] ) ) :
							$countries = array_keys( $stats['country_of_residence'] );
							foreach ( $countries as $country  ) :
								// Using esc_html to allow parentheses.
								?>
								<option value="<?php echo esc_html( $country ); ?>"><?php echo esc_html( $country ); ?></option>
							<?php endforeach;
						endif; ?>
					</optgroup>
					<optgroup label="Citizenship" data-field="country_of_citizenship">
						<?php if ( ! empty( $stats['country_of_citizenship'] ) ) :
							$countries = array_keys( $stats['country_of_citizenship'] );
							foreach ( $countries as $country  ) :
								// Using esc_html to allow parentheses.
								?>
								<option value="citizenship_<?php echo esc_html( $country ); ?>"><?php echo esc_html( $country ); ?></option>
							<?php endforeach;
						endif; ?>
					</optgroup>
				</select>
			</div>
		</div>
	</div><!-- .l-search__form -->

	<header class="l-search__header">

		<h2 class="c-search-results__heading">
			<span class="st-no-results">
				<?php esc_html_e( 'Search Results', 'pmc-swiftype' ); ?>
			</span>
			<span class="st-not-loading"><?php esc_html_e( 'loading', 'pmc-swiftype' ); ?></span>
			<span class="st-not-failed-loading"><?php esc_html_e( 'there was a problem with the search', 'pmc-swiftype' ); ?></span>
			<span class="st-has-results inverse"><?php esc_html_e( 'no results found', 'pmc-swiftype' ); ?></span>
		</h2>

	</header><!-- .l-search__header -->

	<div data-st-results-liquid="result"></div>
	<script type="text/liquid" id="result">
		{% comment %} assign php current_vy500_year to liquid variable {% endcomment %}
		{% assign currentYear = <?php echo wp_json_encode( $current_vy500_year ); ?> | plus:0 %}
		{% comment %}
		get first char (or item) in the result.vy500_year. Could be either string or an array
		{% endcomment %}
		{% assign firstYearChar = result.vy500_year | first %}
		{% comment %}
		if first char is a "2" then we know it is a string (one year nominated), not an array
		{% endcomment %}
		{% assign resultYear = false %}
		{% if '2' == firstYearChar %}
			{% assign resultYear = result.vy500_year %}
		{% endif %}
		<div class="c-profile-card c-profile-card--search-result">
			<figure class="c-profile-card__media">
				<a href="{{ url }}">
					{% if result.image %}
						<img src="{{ result.image }}" alt="{{ result.title }}">
					{% else %}
						<?php if ( ! empty( $swiftype['placeholder_image'] ) ) { ?>
							<img src="<?php echo esc_url( $swiftype['placeholder_image'] ); ?>" />
						<?php } ?>
					{% endif %}
				</a>
			</figure>
			{% if result.country_of_citizenship %}
				<div class="c-profile-card__country">
					{% assign countries_of_citizenship = result.country_of_citizenship | capitalize | split_country %}
					{% for country in countries_of_citizenship %}
						<span class="c-profile-card__country-flag"><img src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/plugins/variety-500/assets/images/flags/{{ country | strip | downcase | replace: " ", "_" | replace: ",", ",_" }}.png" alt="{{ country }}"></span>
					{% endfor %}
				</div>
			{% endif %}
			{% comment %} add .c-profile-new class if new profile {% endcomment %}
				<div class="c-profile-card__body{% if resultYear and resultYear == currentYear %} c-profile-new {% endif %}">
					<a href="{{ url }}">
						{% if result.companies %}
							<span class="c-profile-card__caption">{{ result.companies }}</span>
						{% endif %}
						<h4 class="c-profile-card__heading">{{ result.title }}</h4>
						{% if result.job_title %}
							<span class="c-profile-card__caption c-profile-card__caption--alt">{{ result.job_title }}</span>
						{% endif %}
						{% if result.brief_synopsis %}
							<span class="c-profile-card__synopsis c-profile-card__synopsis--alt">{{ result.brief_synopsis }}</span>
						{% endif %}
					</a>
				</div>
		</div>
	</script>

	<div class="l-search__load-more">
		<span data-st-previous-page class="c-search-results__previous">&lt; <?php esc_html_e( 'Previous', 'pmc-swiftype' ); ?></span>
		<span data-st-pagination-range="pagination" class="c-search-results__pagination"></span>
		<span data-st-next-page class="c-search-results__next"><?php esc_html_e( 'Next', 'pmc-swiftype' ); ?> &gt;</span>
	</div>

	<!-- These two components mixin behavior only. -->
	<!--  <span data-st-defaults="defaults"></span> -->

</section>
