<?php
/**
 * Template Name: Scorecard
 *
 * CDWE-477 -- Copied from pmc-variety-2014 theme
 *
 * @since   2017-08-17
 *
 * @package pmc-variety-2017
 */

global $page_template;
$page_template = 'page-scorecard';

$options = [
	'page'       => intval( get_query_var( 'pn', 0 ) ),
	'network_id' => 0,
];

if ( $options['page'] > 1 ) {

	add_filter(
		'wp_title',
		function ( $title ) use ( $options ) {

			return sprintf( '%s - Page %d', esc_attr( $title ), intval( $options['page'] ) );

		}
	);

	add_filter(
		'get_post_metadata',
		function ( $metadata, $object_id, $meta_key ) {

			if ( 'mt_seo_description' === $meta_key && is_page_template( 'page-scorecard.php' ) ) {

				return false;
			}

			return $metadata;

		},
		10,
		3
	);

	add_filter(
		'page_link',
		function ( $link ) use ( $options ) {

			return sprintf( '%s-%d/', untrailingslashit( $link ), intval( $options['page'] ) );
		}
	);

}

get_header();

if ( have_posts() ) {

	while ( have_posts() ) {
		the_post();

		?>

		<script>
			if ('undefined' !== typeof Variety_Scorecard && Variety_Scorecard) {
				Variety_Scorecard.page = <?php echo esc_js( intval( $options['page'] ) ); ?>;
				Variety_Scorecard.network_id = <?php echo esc_js( intval( $options['network_id'] ) ); ?>;
			}
		</script>

		<div id="content" class="l-page_content wrap">
			<div class="section">
				<h1>
					<?php
					the_title();

					if ( $options['page'] > 1 ) {
						echo ' (continued)';
					}
					?>
				</h1>
			</div>

			<div class="section">
				<div class="col1 indent content-wrapper">
					<div class="tab">

						<!-- Share Tools -->
						<?php
						$social_bar = PMC_Cheezcap::get_instance()->get_option( 'pmc_social_share_bar_enabled', false );

						if ( 'enabled' === $social_bar ) {
							\PMC\Social_Share_Bar\Frontend::get_instance()->render();
						}
						?>
						<!-- /Share Tools -->

					</div>

					<?php the_content(); ?>

				</div>

				<div class="col2">
					<?php dynamic_sidebar( 'scorecard-top-col2' ); ?>
				</div>
			</div>

			<?php
			// Add filters and Table.
			Variety_Scorecard::get_instance()->render_scorecard_html( $options );
			?>

			<div class="section">
				<div class="col1">

					<section id="scorecard-footer">
						<?php dynamic_sidebar( 'scorecard-bottom-col1' ); ?>
					</section>

				</div>
				<div class="col2">
					<?php dynamic_sidebar( 'scorecard-bottom-col2' ); ?>
				</div>
			</div>

		</div><!-- Closes #content -->
		<?php
	}
}

get_footer();
