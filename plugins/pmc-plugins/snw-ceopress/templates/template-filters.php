<div class="alignleft actions bulkactions">
	<form action="tools.php">
		<p class="search-box">
			<select id="ceo-issue-filter" type="search" name="only_show" class="ceopress-select-js" data-key="print_issue_slug">
				<option value="">Filter by issue</option>
				<?php

				if ( ! empty( $print_issues->items ) ) {
					foreach ( $print_issues->items as $key => $value ) {

						if ( '1' === $value->status ) {
							printf(
								'<option value="%s"%s>%s</option>',
								esc_attr( sanitize_title( $value->decorated_label ) ),
								selected( $slug, sanitize_title( $value->decorated_label ), false ),
								( ! empty( $value->label ) ? esc_html( $value->label ) : esc_html( $value->decorated_label ) )
							);
						}
					}
				}
				?>
			</select>
		</p>
		<p class="search-box">
			<select id="ceo-status-filter" type="search" name="only_show" class="ceopress-select-js" data-key="print_status_slug">
				<option value="">Filter by status</option>
				<?php
				if ( ! empty( $print_statuses->items ) ) {
					foreach ( $print_statuses->items as $key => $value ) {

						if ( '1' === $value->is_print ) {
							printf(
								'<option value="%s"%s>%s</option>',
								esc_attr( $value->slug ),
								selected( $slug, $value->slug, false ),
								esc_html( $value->name )
							);
						}
					}
				}

				?>
			</select>
		</p>
		<p class="search-box">
			<select id="ceo-section-filter" type="search" name="only_show" class="ceopress-select-js" data-key="print_section_slug">
				<option value="">Filter by section</option>
				<?php
				if ( ! empty( $print_sections->items ) ) {
					foreach ( $print_sections->items as $key => $value ) {

						if ( '1' === $value->is_print ) {
							printf(
								'<option value="%s"%s>%s</option>',
								esc_attr( $value->slug ),
								selected( $slug, $value->slug, false ),
								esc_html( $value->name )
							);
						}
					}
				}

				?>
			</select>
		</p>
		<p class="search-box ceo-search">
			<input id="ceo-search" type="search" name="s" placeholder="Search for title" value="<?php echo esc_attr( $search ); ?>">
			<input class="button" type="submit" value="Search">
			<input type="hidden" name="page" value="ceo-feed">
		</p>
	</form>
</div>

