<?php
/**
 * Template part for Scorecard Table.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-09-01 Milind More CDWE-499
 */

?>

<div id="scorecard-table" class="section">
	<a class="anchor" id="anchor-scorecard" name="page-anchor"></a>

	<div class="section-network-selection">
		<label class="pilots"><?php esc_html_e( 'View pilots by: ', 'pmc-variety' ); ?></label>
		<select id="scorecard-network-select">
			<?php
			echo wp_kses(
				Variety_Scorecard::get_instance()->get_network_select_option( $options['network_id'] ),
				$allowed_tags
			);
			?>
		</select>

		<select id="scorecard-genre-select">
			<?php
			echo wp_kses(
				Variety_Scorecard::get_instance()->get_genre_select_option( $options['genre_id'] ),
				$allowed_tags
			);
			?>
		</select>

		<select id="scorecard-status-select">
			<?php
			echo wp_kses(
				Variety_Scorecard::get_instance()->get_status_select_option( $options['status_id'] ),
				$allowed_tags
			);
			?>
		</select>

		<label class="provider">
			<?php esc_html_e( 'Data provided by: ', 'pmc-variety' ); ?>

			<a target="_blank" onclick="Variety_Scorecard.TrackView('pilots-scorecard','variety-insight','click');"
				href="https://www.varietyinsight.com">
				<?php
				$insights_title = __( 'Variety Insight', 'pmc-variety' );

				printf(
					'<img src="%s" alt="%s" title="%s" />',
					esc_url( VARIETY_THEME_URL . '/plugins/variety-scorecard/images/variety_insight_logo.gif' ),
					esc_html( $insights_title ),
					esc_html( $insights_title )
				);
				?>
			</a>
		</label>

	</div>
	<div class="clear-fix"></div>

	<div class="section-table">
		<table id="table-scorecard" class="scorecard">
			<?php
			$table_content = Variety_Scorecard::get_instance()->get_thead();
			$table_content .= Variety_Scorecard::get_instance()->get_tbody( $options );
			echo wp_kses_post( $table_content );
			?>
		</table>
	</div>

	<div class="pagination">
		<div class="pagesize-selection">
			<label><?php esc_html_e( 'Display', 'pmc-variety' ); ?></label>
			<select id="scorecard-pagesize-select">
				<option value="30"><?php esc_html_e( '30 results', 'pmc-variety' ); ?></option>
				<option value="60"><?php esc_html_e( '60 results', 'pmc-variety' ); ?></option>
				<option value="100"><?php esc_html_e( '100 results', 'pmc-variety' ); ?></option>
			</select>
		</div>

		<div id="scorecard-pagination">
			<?php
			// SEO pagination link.
			echo wp_kses_post( Variety_Scorecard::get_instance()->get_pagination( array(
				'base' => $options['pagingation-base'],
			) ) );
			?>
		</div>

	</div>
</div>
