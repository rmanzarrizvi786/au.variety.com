<?php
/**
 *
 * Class Variety_Production_Grid
 *
 * @since ?
 *
 * @version 2017-08-16 Milind More CDWE-474
 *
 * @package pmc-variety-2017
 */

use PMC\Global_Functions\Traits\Singleton;

class Variety_Vscore_Top {

	use Singleton;

	const PAGE_TEMPLATE = 'page-scorecard';

	/**
	 * Construct method.
	 */
	protected function __construct() {

		// intialize Hooks
		$this->_setup_hooks();

	}

	/**
	 * Setup Hooks.
	 */
	protected function _setup_hooks() {

		add_action( 'wp_enqueue_scripts', array( $this, 'do_enqueue_scripts' ) );
		add_filter( 'variety_get_protected_data_vscore_top', array( $this, 'get_cached_vscore_top_data' ), 10, 2 );
		add_filter( 'body_class', array( $this, 'get_body_classes' ) );
		add_filter( 'theme_page_templates', array( $this, 'load_page_templates' ) );

	}

	/**
	 * Load Page Templates
	 *
	 * Adds the Vscore Top 250 page templates to the page attributes template selector
	 *
	 * @param array $page_templates List of existing page templates.
	 *
	 * @return array
	 */
	public function load_page_templates( $page_templates ) {

		// Add production grid page template.
		$page_templates['plugins/variety-vscore-top/templates/page-vscore-top.php'] = esc_html__( 'Vscore Top 250', 'pmc-variety' );
		return $page_templates;

	}

	/**
	 * Adds body classes for page template.
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function get_body_classes( $classes ) {

		if ( ! empty( $classes ) && ! empty( $GLOBALS['page_template'] ) && self::PAGE_TEMPLATE === $GLOBALS['page_template'] ) {
			$classes[] = 'premier';
			$classes[] = 'vscore-top';
		}

		return $classes;
	}

	/**
	 * Filter function to get Vscore Top grid.
	 *
	 * @param string $data
	 * @param array $data_args
	 *
	 * @return boolean | array
	 */
	public function get_cached_vscore_top_data( $data, $data_args = false ) {

		if ( empty( $data_args ) ) {
			return false;
		}

		ksort( $data_args );
		$cache_key = 'vscore_top_data_' . md5( wp_json_encode( $data_args ) );

		$pmc_cache = new \PMC_Cache( $cache_key );

		// Expires in 10 minutes.
		$cache_data = $pmc_cache->expires_in( 600 )
								->updates_with( array( $this, 'get_vscore_top' ), array( $data_args ) )
								->get();

		if ( ! empty( $cache_data ) && is_array( $cache_data ) && ! is_wp_error( $cache_data ) ) {
			return $cache_data;
		}

		return array();

	}

	/**
	 * Enqueue scripts.
	 */
	public function do_enqueue_scripts() {

		// Only enqueue our javascript when the 'vscore top' page template is in use
		if ( ! empty( $GLOBALS['page_template'] ) && self::PAGE_TEMPLATE === $GLOBALS['page_template'] ) {

			pmc_js_libraries_enqueue_script( 'pmc-chosen' );
			wp_enqueue_script( 'vscore-top-script', VARIETY_VSCORE_TOP_URL . '/assets/js/variety-vscore-top.js' );
			wp_enqueue_script( 'production-grid-scrollto', VARIETY_VSCORE_TOP_URL . '/assets/js/jquery.scrollto.js' );

		}

	}

	/**
	 * Fetches vscore top data from API endpoint.
	 *
	 * @param array $args
	 *
	 * @return boolean|string
	 */
	public function get_vscore_top( $args ) {

		$result = array(
			'header_text' => esc_html__( 'Error', 'pmc-variety' ),
			'grid' => '',
		);

		$data = array(
			'0' => array(
				'title' => esc_html__( 'Error', 'pmc-variety' ),
			),
		);

		$page_num        = ( ! empty( $args['page_num'] ) && is_numeric( $args['page_num'] ) ) ? intval( $args['page_num'] ) : '';
		$page_size       = ( ! empty( $args['page_size'] ) && is_numeric( $args['page_size'] ) ) ? intval( $args['page_size'] ) : '';
		$sort_column     = ( ! empty( $args['sort_column'] ) ) ? sanitize_text_field( $args['sort_column'] ) : '';
		$sort_direction  = ( ! empty( $args['sort_direction'] ) ) ? sanitize_text_field( $args['sort_direction'] ) : '';
		$gender          = ( ! empty( $args['gender'] ) ) ? sanitize_text_field( $args['gender'] ) : '';
		$age             = ( ! empty( $args['age'] ) ) ? sanitize_text_field( $args['age'] ) : '';
		$ethnicity       = ( ! empty( $args['race'] ) ) ? sanitize_text_field( $args['race'] ) : '';
		$country         = ( ! empty( $args['country'] ) ) ? sanitize_text_field( $args['country'] ) : '';

		$request_url = 'https://www.varietyinsight.com/grid_api/variety-vscore-top-api.php';

		$query_args = array(
			'tmpsec'         => 'ee19a11a89de27cd4d9ccd6a5ad4ca35',
			'gender'         => $gender,
			'page_size'      => $page_size,
			'sort_direction' => $sort_direction,
			'sort_column'    => $sort_column,
			'page_num'       => $page_num,
			'age'            => $age,
			'race'           => $ethnicity,
			'country'        => $country,
		);

		$request_url  = add_query_arg( $query_args, $request_url );
		$request_url  = apply_filters( 'varietyinsight_vscore_top_actors_endpoint', $request_url );
		$raw_response = wpcom_vip_file_get_contents( $request_url );

		if ( empty( $raw_response ) || is_wp_error( $raw_response ) ) {
			return false;
		}

		$response = json_decode( $raw_response, true );

		if ( empty( $response['results'] ) ) {
			return false;
		}

		$data = $response['results'];

		// Setup the grid
		if ( ! empty( $response ) && ! empty( $response['total_rows'] ) && intval( $response['total_rows'] ) > 0 ) {

			ob_start();

			foreach ( $data as $entry ) {

				$escaped_photo_html = '';
				if ( ! empty( $entry['Photo'] ) ) {
					$escaped_photo_html = sprintf( '<img class="pg_logo_border" src="%s" /><br />', esc_url( 'https://www.varietyinsight.com/talentimages/' . $entry['Photo'] ) );
				}

				$entry['Photo'] = $escaped_photo_html;

				PMC::render_template(
					CHILD_THEME_PATH . '/plugins/variety-vscore-top/templates/vscore-top-row.php',
					compact( 'entry' )
				);

			}

			$result['grid'] .= ob_get_contents();

			ob_end_clean();

		} else {

			$result['grid'] = sprintf( '<tr valign="middle"><td colspan=9>%s</td></tr>', esc_html__( 'Your query returned no results.', 'pmc-variety' ) );

		}

		// Setup the pagination text
		$result['pagination_html'] = '';
		$total_pages               = intval( $response['total_pages'] );
		$current_page              = PMC::numeric_range( $response['current_page'], 0, $total_pages );

		// Sanity check on the numbers returned
		if ( PMC::numeric_range( $total_pages, 0, 100 ) !== $total_pages ) {

			wp_die( 'Problem with number of page results.' );

		}

		// Condense pagination if greater than 9 pages
		if ( $total_pages > 9 ) {

			if ( $current_page < 5 ) {

				// first 4 pages
				for ( $pagenum = 1; $pagenum < 10; $pagenum++ ) {

					if ( $pagenum !== $current_page ) {

						$result['pagination_html'] .= sprintf( ' <span id="pg-pagination-page" data-pagenum="%1$s">%1$s</span>', intval( $pagenum ) );

					} else {

						$result['pagination_html'] .= sprintf( ' %s ', intval( $pagenum ) );

					}
				}

				$result['pagination_html'] .= ' ...';

			} elseif ( $current_page >= ($total_pages - 5) ) {

				// pages (total pages - 5) through end
				for ( $pagenum = $total_pages; $pagenum > ($total_pages - 7); $pagenum-- ) {

					if ( $pagenum !== $current_page ) {

						$result['pagination_html'] = sprintf( ' <span id="pg-pagination-page" data-pagenum="%1$s">%1$s</span>', intval( $pagenum ) ) . $result['pagination_html'];

					} else {

						$result['pagination_html'] = sprintf( ' %s ', intval( $pagenum ) ) . $result['pagination_html'];

					}
				}

				$result['pagination_html'] = '... ' . $result['pagination_html'];

			} else {

				// page 5 through (total pages - 5)
				$beginning = intval( $current_page ) - 4;
				$end       = intval( $current_page ) + 4;

				$result['pagination_html'] .= '... ';

				for ( $x = 1; $x < 4; $x++ ) {

					$pagenum = $x + $beginning;
					$result['pagination_html'] .= sprintf( ' <span id="pg-pagination-page" data-pagenum="%1$s">%1$s</span>', intval( $pagenum ) );

				}

				$result['pagination_html'] .= sprintf( ' %s ', intval( $pagenum ) );

				for ( $x = 0; $x < 3; $x++ ) {

					$pagenum = $x + $end - 3;
					$result['pagination_html'] .= sprintf( ' <span id="pg-pagination-page" data-pagenum="%1$s">%1$s</span>', intval( $pagenum ) );

				}

				$result['pagination_html'] .= ' &hellip;';

			}
		} else {

			for ( $pagenum = 1; $pagenum < $total_pages + 1; $pagenum++ ) {

				if ( $pagenum === $current_page ) {

					$result['pagination_html'] .= sprintf( ' %s ', intval( $pagenum ) );

				} else {

					$result['pagination_html'] .= sprintf( ' <span id="pg-pagination-page" data-pagenum="%1$s">%1$s</span>', intval( $pagenum ) );

				}
			}
		}

		// Next page text
		if ( $total_pages > 1 && $current_page !== $total_pages ) {

			$next_page = intval( $current_page ) + 1;
			$result['pagination_html'] .= sprintf( ' <span class="pg-page-link"><span id="pg-pagination-page" data-pagenum="%1$s">%2$s &gt; &gt; </span></span>', intval( $next_page ), esc_html__( 'Next Page', 'pmc-variety' ) );

		}

		// Previous page text
		if ( '1' !== $current_page ) {

			$prev_page = intval( $current_page ) - 1;
			$result['pagination_html'] = sprintf( '<span class="pg-page-link"><span id="pg-pagination-page" data-pagenum="%1$s">&lt;&lt; %2$s</span> </span>', intval( $prev_page ), esc_html__( 'Previous Page', 'pmc-variety' ) ) . $result['pagination_html'];

		} else {

			$result['pagination_html'] = esc_html__( 'Page: ', 'pmc-variety' ) . $result['pagination_html'];

		}

		$result['pagination_html']     = $response['total_rows'] . sprintf( ' %s<br /><br />' , esc_html__( 'Results Returned', 'pmc-variety' ) ) . $result['pagination_html'];
		$result['header_text_results'] = $response['total_rows'] . sprintf( ' %s', esc_html__( 'Results Returned', 'pmc-variety' ) );

		return $result;
	}

}
