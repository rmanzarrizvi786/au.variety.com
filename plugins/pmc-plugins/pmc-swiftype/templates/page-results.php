<section id="swiftype-search-result" class="swiftype">

	<div data-st-fix-misspelling="misspelling"></div>
	<div data-st-manage-conditionals="conditional"></div>
	<div class="container block-group">
		<label class="search_form-a-screen-reader-only" for="st-search-form-input">Search</label>
		<div class="search_form block">
			<div data-st-search-form="search_form"></div>
		</div>

		<div class="header block">

			<div class="block-group">
				<div class="left-header block width-30">&nbsp;</div>
				<div class="right-header block width-70">
					<span class="st-no-misspelling">
						<?php esc_html_e( 'We found', 'pmc-swiftype' ); ?> <b>0</b> <?php esc_html_e( 'results for', 'pmc-swiftype' ); ?> "<b><span data-st-misspelled-word></span></b>".
					</span>
					<span class="st-no-results">
						<?php esc_html_e( 'Showing results', 'pmc-swiftype' ); ?>
						<span data-st-page-start></span> -
						<span data-st-page-end></span> <?php esc_html_e( 'of', 'pmc-swiftype' ); ?>
						<span data-st-total-results="total"></span>
						<span class="st-has-query"><?php esc_html_e( 'for', 'pmc-swiftype' ); ?>
							<span data-st-query></span>
						</span>
					</span>
					<span class="st-not-loading"><?php esc_html_e( 'loading', 'pmc-swiftype' ); ?>...</span>
					<span class="st-not-failed-loading"><?php esc_html_e( 'there was a problem with the search', 'pmc-swiftype' ); ?></span>
					<span class="st-has-results inverse"><?php esc_html_e( 'no results found', 'pmc-swiftype' ); ?>...</span>
				</div>
			</div>
		</div>

		<div class="main-container block block-group">
			<div class="left-main block width-30">
				<label class="st-section-title"><?php esc_html_e( 'Sort By', 'pmc-swiftype' ); ?></label>
				<div data-st-sort-selector="sort"></div>


				<?php
				$swiftype_date_filter = apply_filters( 'pmc_swiftype_date_filters', array() );

				foreach ( $swiftype_date_filter as $key => $value ) {

					/* phpcs:disable
					 * Allow filter names as keys or values, e.g.
					 *
					 * array(
					 * 	'tags_facet:checkbox-facet',
					 * 	'author_facet:checkbox-facet',
					 * )
					 *
					 * or
					 *
					 * array(
					 * 	'tags_facet:checkbox-facet' => array(
					 * 		'facet attribute A' => 'blah',
					 * 		'facet attribute B' => 1234,
					 * 	),
					 * 	'author_facet:checkbox-facet',
					 * )
					 * phpcs:enable
					 */
					if ( is_string( $key ) ) {
						$filter_name = $key;
					} else {
						$filter_name = $value;
					}

					$filter_name = explode( ':', $filter_name );

					if ( ! empty( $swiftype['date_filters']['date_options:radio-options']['title'] ) && 'date_options' === $filter_name[0] ) :
						printf( '<div class="st-section-title">%s</div>', esc_html( $swiftype['date_filters']['date_options:radio-options']['title'] ), 'pmc-swiftype' );
					endif;

					if ( 2 === count( $filter_name ) ) :
						printf(
							'<div id="%s" %s="%s"></div>',
							esc_attr( $filter_name[0] ),
							esc_attr( 'data-st-' . $filter_name[1] ),
							esc_attr( $filter_name[0] )
						);
					endif;
				}
				?>
			</div>

			<div class="main block width-70">
				<div data-st-results-liquid="result"></div>
				<script type="text/liquid" id="result">
					{% if result.image %}
						<div class="result-image">
							<img src="{{ result.image }}" data-lazy-src="{{ result.image }}" alt="" />
						</div>
					{% else %}
						<div class="result-image">
							<?php if ( ! empty( $swiftype['placeholder_image'] ) ) { ?>
							<img src="<?php echo esc_url( $swiftype['placeholder_image'] ); ?>" data-lazy-src="<?php echo esc_url( $swiftype['placeholder_image'] ); ?>" alt="" />
							<?php } ?>
						</div>
					{% endif %}

					<div class="result-content">
						<div class="result-title">
							<a href="{{ url }}">{{ result | highlight: 'title' | unescape }}</a>
						</div>

						<div class="byline">

							{% if result.author %}
							<span class="icon">
								<i class="fa fa-user"></i>
								{{ result.author | pp_array }}
							</span>
							{% endif %}

							{% if result.published_at %}
							<span class="icon">
								<i class="fa fa-calendar"></i>
								{{ result.published_at | date: "%h %d, %Y" }}
							</span>
							{% endif %}

							{% if result.content_type or result.topics %}
							<span class="icon">
								{% if result.content_type %}
									<i class="fa fa-pencil"></i>
									{{ result.content_type | pp_array }}
								{% else if result.topics %}
									<i class="fa fa-tags"></i>
									{{ result.topics | pp_array }}
								{% endif %}
							</span>
							{% endif %}

						</div>

						<div class="text-block">
							{{ result.body | truncate: "250" }}
						</div>
					</div>
				</script>
			</div>
		</div>

		<div class="footer block st-no-results">
			<span data-st-previous-page>&lt; <?php esc_html_e( 'Previous', 'pmc-swiftype' ); ?></span>
			<span data-st-pagination-range="pagination"></span>
			<span data-st-next-page><?php esc_html_e( 'Next', 'pmc-swiftype' ); ?> &gt;</span>
		</div>
	</div> <!-- end .container -->


	<!-- These two components mixin behavior only. -->
	<!--  <span data-st-defaults="defaults"></span> -->
	<div data-st-simple-routing="routing"></div>

</section>
