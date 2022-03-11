<?php

global $page_template;
$page_template = 'page-access-digital';
get_header();

global $paged;
$paged      = ( ! empty( $paged ) ) ? intval( $paged ) : 1;
$page_count = Variety_Digital_Feed::get_instance()->get_number_of_issue_pages();
$next_page  = $paged + 1;
$prev_page  = $paged - 1;
?>
	<div class="lrv-u-padding-a-2 u-padding-lr-050@mobile-max lrv-u-font-family-primary lrv-u-text-align-center u-background-color-accent-c-100@mobile-max">

		<h1 class="lrv-u-align-items-center lrv-u-font-size-26@mobile-max lrv-u-font-size-40 lrv-u-font-size-46@desktop-xl lrv-u-font-weight-normal">
			<?php
			esc_html_e(
				'Browse current and past Variety digital editions',
				'pmc-variety'
			);
			?>
		</h1>
		<div class="u-border-color-brand-secondary-40 lrv-u-border-t-1 lrv-u-padding-tb-050"/>
		<div class="lrv-u-font-size-18 lrv-u-font-size-14@mobile-max lrv-u-font-family-secondary">
			<?php
			esc_html_e(
				'For an optimal viewing experience, please use Safari, Firefox or Chrome
            when reading digitized issues of Variety',
				'pmc-variety'
			);
			?>
		</div>


		<?php
		$issues = Variety_Digital_Feed::get_instance()->get_issues( $paged );
		?>
		<?php if ( ! empty( $issues ) ) : ?>

			<?php
			\PMC::render_template(
				CHILD_THEME_PATH . '/template-parts/page/issue-item-list.php',
				[
					'paged'  => $paged,
					'issues' => $issues,
				],
				true
			);
			?>
		<?php else : ?>
			<h2> No issues found </h2>
		<?php endif; ?>


		<div>
			<?php
			if ( $page_count >= $paged ) {
				$big        = 999999999; // need an unlikely integer
				$page_links = paginate_links(
					[
						'base'      => str_replace(
							$big,
							'%#%',
							esc_url( get_pagenum_link( $big ) )
						),
						'format'    => '?paged=%#%',
						'current'   => max( 1, get_query_var( 'paged' ) ),
						'total'     => $page_count,
						'type'      => 'array',
						'prev_text' => __(
							'<span style="background-color: #abdddb;">&larr;</span> Newer Issues',
							'pmc-variety'
						),
						'next_text' => __(
							'Older Issues <span style="background-color: #abdddb;">&rarr;</span>',
							'pmc-variety'
						),
					]
				);
				?>
				<ul class="lrv-a-unstyle-list lrv-u-flex lrv-u-justify-content-center lrv-u-font-size-12 u-letter-spacing-200 lrv-u-text-transform-uppercase u-padding-tb-050@mobile-max u-background-white@mobile-max u-padding-lr-050@mobile-max">
					<?php

					if ( 1 === $paged ) {
						?>
						<li class="lrv-u-margin-r-auto lrv-u-margin-l-2"></li>
						<?php
					}

					if ( ! empty( $page_links ) ) {
						foreach ( $page_links as $page_link ) {
							$classes = '';
							if ( - 1 < strpos( $page_link, 'next' ) ) {
								$classes = 'lrv-u-margin-l-auto';
							}
							if ( - 1 < strpos( $page_link, 'prev' ) ) {
								$classes = 'lrv-u-margin-r-auto';
							}

							$page_link = str_replace(
								'page-numbers',
								'page-numbers u-color-pale-sky-2 lrv-u-font-weight-bold lrv-u-font-family-secondary',
								$page_link
							);
							$page_link = str_replace(
								'current',
								'current u-text-decoration-underline',
								$page_link
							);

							if ( 1 > strpos( $page_link, 'next' ) && 1 > strpos( $page_link, 'prev' ) ) {
								$page_link = str_replace(
									'page-numbers',
									'page-numbers lrv-u-padding-lr-025 lrv-a-hidden@mobile-max',
									$page_link
								);
							}
							?>
							<li class="<?php echo esc_attr( $classes ); ?>"><?php echo wp_kses_post( $page_link ); ?></li>
							<?php
						}
					}
					if ( $page_count === $paged ) {
						?>
						<li class="lrv-u-margin-l-auto"></li>
						<?php
					}
					?>

				</ul>
				<?php
			}
			?>
		</div>

	</div>

<?php
get_footer();
//EOF
