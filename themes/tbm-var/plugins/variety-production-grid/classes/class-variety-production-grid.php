<?php
/**
 * Class Variety_Production_Grid
 *
 * @since ?
 *
 * @version 2017-08-16 Milind More CDWE-473
 *
 * @package pmc-variety-2017
 */

use \PMC\Global_Functions\Traits\Singleton;

class Variety_Production_Grid {

	use Singleton;

	// Page template constant.
	const PAGE_TEMPLATE = 'page-production-scorecard';

	// Icon directory url.
	protected $_icon_dir_url = '';

	/**
	 * Construct method.
	 */
	protected function __construct() {

		$this->_setup_hooks();
		$this->_icon_dir_url = VARIETY_PRODUCTION_GRID_URL . '/assets/img/';

	}

	/**
	 * Initialize actions and filters.
	 */
	protected function _setup_hooks() {

		add_action( 'wp_enqueue_scripts', array( $this, 'do_enqueue_scripts' ) );
		add_filter( 'variety_get_protected_data_production_grid', array( $this, 'get_cached_production_grid_data' ), 10, 2 );
		add_filter( 'body_class', array( $this, 'get_body_classes' ) );
		add_filter( 'theme_page_templates', array( $this, 'load_page_templates' ) );
	}

	/**
	 * Load Page Templates
	 *
	 * Adds the production grid page templates to the page attributes template selector
	 *
	 * @param array $page_templates List of existing page templates.
	 *
	 * @return array
	 */
	public function load_page_templates( $page_templates ) {

		// Add production grid page template.
		$page_templates['plugins/variety-production-grid/templates/page-production-grid.php'] = esc_html__( 'Production Grid', 'pmc-variety' );
		return $page_templates;

	}


	/**
	 * Adds body classes for page template production grid.
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function get_body_classes( $classes ) {

		if ( ! empty( $classes ) && is_array( $classes ) &&
			 ! empty( $GLOBALS['page_template'] ) &&
			 self::PAGE_TEMPLATE === $GLOBALS['page_template'] ) {

			$classes[] = 'premier';
			$classes[] = 'production-grid';

		}

		return $classes;
	}

	/**
	 *  Enqueue scripts and styles.
	 */
	public function do_enqueue_scripts() {

		// Only enqueue our javascript when the 'production grid' page template is in use
		if ( ! empty( $GLOBALS['page_template'] ) && self::PAGE_TEMPLATE === $GLOBALS['page_template'] ) {

			pmc_js_libraries_enqueue_script( 'pmc-chosen' );
			wp_enqueue_script( 'production-grid-script', VARIETY_PRODUCTION_GRID_URL . '/assets/js/variety-production-grid.js' );
			wp_enqueue_script( 'production-grid-scrollto', VARIETY_THEME_URL . '/assets/build/js/vendor/jquery.scrollto.js' );

		}

	}

	/**
	 * Get response for production data grid.
	 *
	 * @param string $data
	 * @param array $data_args
	 *
	 * @return boolean|array
	 */
	public function get_cached_production_grid_data( $data, $data_args = false ) {

		if ( empty( $data_args ) ) {
			return false;
		}

		ksort( $data_args );
		$cache_key = 'production_grid_data_' . md5( wp_json_encode( $data_args ) );

		$pmc_cache = new \PMC_Cache( $cache_key );

		// Expires in 10 minutes.
		$cache_data = $pmc_cache->expires_in( 600 )
								->updates_with( array( $this, 'get_production_grid' ), array( $data_args ) )
								->get();

		if ( ! empty( $cache_data ) && is_array( $cache_data ) && ! is_wp_error( $cache_data ) ) {
			return $cache_data;
		}

		return array();
	}

	/**
	 * Generate response by fetching production Chart from API.
	 *
	 * @param array $args
	 *
	 * @return boolean|string
	 */
	public function get_production_grid( $args ) {

		$result = array(
			'header_text' => esc_html__( 'Error','pmc-variety' ),
			'grid' => '',
		);

		$data = array(
			'0' => array(
				'title' => esc_html__( 'Error','pmc-variety' ),
			),
		);

		$page_num        = ( ! empty( $args['page_num'] ) && is_numeric( $args['page_num'] ) ) ? intval( $args['page_num'] ) : '';
		$page_size       = ( ! empty( $args['page_size'] ) && is_numeric( $args['page_size'] ) ) ? intval( $args['page_size'] ) : '';
		$sort_column     = ( ! empty( $args['sort_column'] ) ) ? sanitize_text_field( $args['sort_column'] ) : '';
		$sort_direction  = ( ! empty( $args['sort_direction'] ) ) ? sanitize_text_field( $args['sort_direction'] ) : '';
		$type            = ( ! empty( $args['type'] ) ) ? sanitize_text_field( $args['type'] ) : '';
		$genre           = ( ! empty( $args['genre'] ) ) ? sanitize_text_field( $args['genre'] ) : '';
		$location        = ( ! empty( $args['location'] ) ) ? rawurlencode( sanitize_text_field( $args['location'] ) ) : '';
		$status          = ( ! empty( $args['status'] ) ) ? sanitize_text_field( $args['status'] ) : '';

		$request_query = array(
			'tmpsec'         => 'ee19a11a89de27cd4d9ccd6a5ad4ca35',
			'type'           => $type,
			'page_size'      => $page_size,
			'page_num'       => $page_num,
			'genre'          => $genre,
			'status'         => $status,
			'location'       => $location,
			'sort_column'    => $sort_column,
			'sort_direction' => $sort_direction,
		);

		$request_url  = add_query_arg( $request_query, 'https://www.varietyinsight.com/grid_api/variety-production-grid-api2.php' );
		$request_url  = apply_filters( 'varietyinsight_vscore_production_grid_endpoint', $request_url );
		$raw_response = wpcom_vip_file_get_contents( $request_url );

		if ( empty( $raw_response ) || is_wp_error( $raw_response ) ) {

			return false;

		}

		$response = json_decode( $raw_response, true );

		if ( empty( $response['results'] ) ) {

			return false;

		}

		$data        = $response['results'];
		$header_text = $response['header_text'];

		// Setup the grid
		if ( ! empty( $response ) && ! empty( $response['total_rows'] ) && $response['total_rows'] > 0 ) {

			$data_rows = '';

			foreach ( $data as $entry ) {

				$item      = array();
				$logo_html = '';

				if ( ! empty( $entry['Logo'] ) ) {

					$logo_html = sprintf( '<img class="pg_logo_border" src="%s" /><br />', esc_url( '//www.varietyinsight.com/control/images/logo/company_logos/' . $entry['Logo'] ) );

				}

				$title_html = str_replace( 'ICONDIR', $this->_icon_dir_url, $entry['Title'] );

				$item['logo_html']        = $logo_html;
				$item['studio']           = $entry['Studio'];
				$item['status']           = $entry['Status'];
				$item['genre_final']      = str_replace( '&lt;br /&gt;', '', $entry['Genre'] );
				$item['dates_final']      = str_replace( '&lt;br /&gt;', '', $entry['Shoot_Dates'] );
				$item['location_final']   = str_replace( '&lt;br /&gt;', '', $entry['Location'] );
				$item['commitment_final'] = str_replace( '&lt;br /&gt;', '', $entry['Commitment'] );
				$item['title_html']       = strip_tags( $title_html, '<br><img>' );

				// kses'd below
				$data_rows .= PMC::render_template(
					sprintf(
						'%s/plugins/variety-production-grid/templates/production-grid-row.php',
						untrailingslashit( CHILD_THEME_PATH )
					),
					compact( 'item' )
				);

			}

			/**
			 * @TODO 2014-07-24 Corey Gilmore See https://wordpressvip.zendesk.com/requests/31545
			 * This is NOT ideal, and we will push a fix for it following light coverage.
			 * PMC: https://penskemediacorp.atlassian.net/browse/PPT-2919
			 *
			 */
			$result['grid'] .= wp_kses_post( $data_rows );

		} else {

			$result['grid'] = sprintf( '<tr valign="middle"><td colspan=7> %s</td></tr>', esc_html__( 'Your query returned no results.', 'pmc-variety' ) );

		}

		// Setup the header text
		$result['header_text']         = $header_text;
		$result['header_text_results'] = sprintf( '%1$s %2$s<br /><br />', esc_html( $response['total_rows'] ), esc_html__( 'Results Returned', 'pmc-variety' ) );

		// Setup the pagination text
		$result['pagination_html'] = '';
		$total_pages               = intval( $response['total_pages'] );
		$current_page              = intval( $response['current_page'] );

		if ( PMC::numeric_range( $total_pages, 0, 100 ) !== $total_pages ) {
			return false;
		}

		// Condense pagination if greater than 9 pages
		if ( $total_pages > 9 ) {

			if ( $current_page < 5 ) {

				// first 4 pages
				for ( $i = 1; $i < 10; $i++ ) {

					if ( $i !== $current_page ) {

						$result['pagination_html'] .= sprintf( ' <span id="pg-pagination-page" data-pagenum="%1$s">%1$s</span>', intval( $i ) );

					} else {

						$result['pagination_html'] .= sprintf( ' %s ', intval( $i ) );

					}
				}

				$result['pagination_html'] .= ' ...';

			} elseif ( $current_page >= ($total_pages - 5) ) {

				// pages (total pages - 5) through end
				for ( $i = $total_pages; $i > ($total_pages - 7); $i-- ) {

					if ( $i !== $current_page ) {

						$result['pagination_html'] = sprintf( ' <span id="pg-pagination-page" data-pagenum="%1$s">%1$s</span>', intval( $i ) ) . $result['pagination_html'];

					} else {

						$result['pagination_html'] = sprintf( ' %s ', intval( $i ) ) . $result['pagination_html'];

					}
				}

				$result['pagination_html'] = '... ' . $result['pagination_html'];

			} else {

				// page 5 through (total pages - 5)
				$i_beginning = intval( $current_page ) - 4;
				$i_end       = intval( $current_page ) + 4;

				$result['pagination_html'] .= '... ';

				for ( $x = 1; $x < 4; $x++ ) {

					$i = $x + $i_beginning;
					$result['pagination_html'] .= sprintf( ' <span id="pg-pagination-page" data-pagenum="%1$s">%1$s</span>', intval( $i ) );

				}

				$result['pagination_html'] .= sprintf( ' %s ', intval( $current_page ) );

				for ( $x = 0; $x < 3; $x++ ) {

					$i = $x + $i_end - 3;
					$result['pagination_html'] .= sprintf( ' <span id="pg-pagination-page" data-pagenum="%1$s">%1$s</span>', intval( $i ) );

				}

				$result['pagination_html'] .= ' &hellip;';

			}
		} else {

			for ( $i = 1; $i < $total_pages + 1; $i++ ) {

				if ( $i === $current_page ) {

					$result['pagination_html'] .= sprintf( ' %s ', intval( $i ) );

				} else {

					$result['pagination_html'] .= sprintf( ' <span id="pg-pagination-page" data-pagenum="%1$s">%1$s</span>' , intval( $i ) );

				}
			}
		}

		// Next page text
		if ( $total_pages > 1 && $current_page !== $total_pages ) {

			$next_page = intval( $current_page ) + 1;
			$result['pagination_html'] .= sprintf( ' <span class="pg-page-link"><span id="pg-pagination-page" data-pagenum="%1$s">%2$s &gt;&gt; </span></span>', intval( $next_page ), esc_html__( 'Next Page', 'pmc-variety' ) );

		}

		// Previous page text
		if ( '1' !== $current_page ) {

			$prev_page                 = intval( $current_page ) - 1;
			$result['pagination_html'] = sprintf( ' <span class="pg-page-link"><span id="pg-pagination-page" data-pagenum="%1$s"> &lt;&lt; %2$s</span></span>', intval( $prev_page ), esc_html__( 'Previous Page', 'pmc-variety' ) ) . $result['pagination_html'];

		} else {

			$result['pagination_html'] = esc_html__( 'Page: ', 'pmc-variety' ) . $result['pagination_html'];

		}

		$result['pagination_html'] = sprintf( '%1$s %2$s<br /><br />', intval( $response['total_rows'] ), esc_html__( 'Results Returned', 'pmc-variety' ) ) . $result['pagination_html'];

		return $result;

	}
}
