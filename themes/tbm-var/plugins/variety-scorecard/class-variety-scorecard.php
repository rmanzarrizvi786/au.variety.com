<?php
/**
 * File contains class for Variety Scorecard.
 *
 * CDWE-477 -- Copied from pmc-variety-2014 theme.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-08-21
 */

use \PMC\Global_Functions\Traits\Singleton;

class Variety_Scorecard {

	use Singleton;

	const PAGE_SIZE        = 30;
	const CACHE_GROUP      = 'variety-scorecard';
	const CACHE_LIFE       = 900;    // 15 minutes
	const SERVES_STALED    = false;
	const ENABLE_SHORTCODE = true;

	/**
	 * Current page number.
	 *
	 * @var int
	 */
	public $current_page = 1;

	/**
	 * Page total.
	 *
	 * @var int
	 */
	public $page_total = 0;

	/**
	 * Next page number.
	 *
	 * @var int
	 */
	public $page_next = 0;

	/**
	 * Default values.
	 *
	 * @var array
	 */
	protected $_settings = array(
		'page'       => 1,
		'page_size'  => self::PAGE_SIZE,
		'genre_id'   => '',
		'status_id'  => '',
		'network_id' => '',
	);

	/**
	 * Class Initialization.
	 */
	protected function __construct() {

		// Implement ajax action.
		if ( is_admin() ) {
			add_action( 'wp_ajax_get_scorecard', array( $this, 'do_ajax_action_get' ) );
			add_action( 'wp_ajax_nopriv_get_scorecard', array( $this, 'do_ajax_action_get' ) );
		}

		// Fire up related settings.
		Variety_Scorecard_Settings::get_instance();
		add_action( 'wp_enqueue_scripts', array( $this, 'do_enqueue_scripts' ), 12 );

		// Register basic things.
		add_action( 'init', array( $this, 'do_action_init' ) );

	}

	/**
	 * Setup rewrite rules, shortcode and hooks.
	 */
	public function do_action_init() {

		// customize permalink for scorecard & paging.
		add_rewrite_rule( '^([^/]*-?scorecard)-?([0-9]*)', 'index.php?pagename=$matches[1]&pn=$matches[2]', 'top' );
		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );

		$this->register_sidebar();

		add_shortcode( 'variety_scorecard_table', array( $this, 'do_shortcode' ) );
		add_shortcode( 'variety_scorecard_setting', array( $this, 'do_shortcode_setting' ) );

	}

	/**
	 * Register sidebars.
	 */
	public function register_sidebar() {

		register_sidebar(
			array(
				'id'            => 'scorecard-top-col2',
				'name'          => __( 'Scorecard - Top right column', 'pmc-variety' ),
				'description'   => __( 'Column 2 Shows on Scorecard top right', 'pmc-variety' ),
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '',
				'after_title'   => '',
			)
		);

		register_sidebar(
			array(
				'id'            => 'scorecard-bottom-col1',
				'name'          => __( 'Scorecard - Bottom left column', 'pmc-variety' ),
				'description'   => __( 'Column 1 Shows below Scorecard bottom left/center before comments', 'pmc-variety' ),
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '',
				'after_title'   => '',
			)
		);

		register_sidebar(
			array(
				'id'            => 'scorecard-bottom-col2',
				'name'          => __( 'Scorecard - Bottom right column', 'pmc-variety' ),
				'description'   => __( 'Column 2 Shows below Scorecard bottom right', 'pmc-variety' ),
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '',
				'after_title'   => '',
			)
		);

	}

	/**
	 * Register query variables.
	 *
	 * @param array $vars List of variables.
	 *
	 * @return array List of variables.
	 */
	public function register_query_vars( $vars ) {
		$vars[] = 'pn';

		return $vars;
	}

	/**
	 * @codeCoverageIgnore
	 * Enqueue Scripts.
	 */
	public function do_enqueue_scripts() {

		$should_enqueue = false;

		if ( is_page_template( 'page-scorecard.php' ) ) {
			$should_enqueue = true;
		} elseif ( self::ENABLE_SHORTCODE ) {
			if ( is_front_page() || is_home() ) {
				return;
			}

			if ( is_singular() ) {
				$content = get_queried_object()->post_content;
				if ( ! has_shortcode( $content, 'variety_scorecard_setting' ) && ! has_shortcode( $content, 'variety_scorecard_table' ) ) {
					return;
				}
			}
			$should_enqueue = true;
		}

		if ( true === $should_enqueue ) {

			\PMC\Social_Share_Bar\Frontend::get_instance()->load_frontend_assets();

			wp_enqueue_style(
				'scorecard-style',
				VARIETY_THEME_URL . '/plugins/variety-scorecard/css/single.css'
			);

			wp_enqueue_script(
				'scorecard-scripts',
				VARIETY_THEME_URL . '/plugins/variety-scorecard/js/scripts.js',
				[ 'jquery' ],
				'1.03',
				false
			);
			pmc_js_libraries_enqueue_script( 'pmc-chosen' );

			wp_localize_script(
				'scorecard-scripts',
				'scorecard_script_admin',
				[
					'ajax_endpoint' => admin_url( 'admin-ajax.php' ),
				]
			);
		}
	}

	/**
	 * Ajax actions.
	 */
	public function do_ajax_action_get() {

		$data   = ( isset( $_GET['data'] ) ) ? sanitize_text_field( wp_unslash( $_GET['data'] ) ) : ''; // Input var ok.
		$format = ( isset( $_GET['format'] ) ) ? sanitize_text_field( wp_unslash( $_GET['format'] ) ) : 'json'; // Input var ok.
		$render = ( isset( $_GET['render'] ) ) ? sanitize_text_field( wp_unslash( $_GET['render'] ) ) : 'html'; // Input var ok.

		switch ( $data ) {
			case 'network':
				$selected = isset( $_GET['selected'] ) ? sanitize_text_field( wp_unslash( $_GET['selected'] ) ) : ''; // Input var ok.

				switch ( $format ) {
					case 'json':
						ob_clean();
						header( 'Content-Type: application/json' );
						echo wp_json_encode( $this->fetch_network() );
						wp_die();
						break;
					default:
						$select_box  = '<select name="network">';
						$select_box .= $this->get_network_select_option( $selected );
						$select_box .= '</select>';

						// No need to escape HTML here since it’s escaped in function.
						echo $select_box; // xss ok.
						wp_die();
				}
				break;

			default:
				$options = array(
					'page'            => ( isset( $_GET['page'] ) ) ? intval( $_GET['page'] ) : 0, // Input var ok.
					'page_size'       => ( isset( $_GET['page_size'] ) ) ? intval( $_GET['page_size'] ) : 0, // Input var ok.
					'network_id'      => ( isset( $_GET['network_id'] ) ) ? sanitize_text_field( wp_unslash( $_GET['network_id'] ) ) : '', // Input var ok.
					'network_type_id' => ( isset( $_GET['network_type_id'] ) ) ? sanitize_text_field( wp_unslash( $_GET['network_type_id'] ) ) : '', // Input var ok.
					'genre_id'        => ( isset( $_GET['genre_id'] ) ) ? sanitize_text_field( wp_unslash( $_GET['genre_id'] ) ) : '', // Input var ok.
					'status_id'       => ( isset( $_GET['status_id'] ) ) ? sanitize_text_field( wp_unslash( $_GET['status_id'] ) ) : '', // Input var ok.
					'sort'            => ( isset( $_GET['sort'] ) ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : '', // Input var ok.
				);

				switch ( $format ) {
					case 'html': // default return html.
						$output  = '<table class="scorecard">';
						$output .= $this->get_thead();
						$output .= $this->get_tbody( $options );
						$output .= '</table>';

						// No need to escape HTML here since it’s escaped in function.
						echo $output; // xss ok.
						wp_die();
						break;
					default:
						if ( 'html' === $render ) {
							$html                   = sprintf( '<table class="scorecard">%1$s %2$s</table>', $this->get_thead(), $this->get_tbody( $options ) );
							$result['html']         = $html;
							$result['page_total']   = $this->page_total;
							$result['page_next']    = $this->page_next;
							$result['current_page'] = $this->current_page;
							$result['pagination']   = $this->get_pagination(
								array(
									'base' => '#page-%#%',
								)
							);
						} else {
							$result = $this->fetch_records( $options );
						}

						ob_clean();
						header( 'Content-Type: application/json' );
						echo wp_json_encode( $result );
						wp_die();

				}
				break;

		}
		wp_die();
	} // end function do_ajax_action_get

	/**
	 * Add cron schedules.
	 *
	 * @param array $schedules List of schedule crons.
	 *
	 * @return array List of schedule crons.
	 */
	public function add_cron_schedules( $schedules ) {

		if ( empty( $schedules['variety_15_min'] ) ) {

			// add a 15 min schedule if not exists already.
			$schedules['variety_15_min'] = array(
				'interval' => 900,
				'display'  => __( 'Variety 15 Mins', 'pmc-variety' ),
			);

		}

		return $schedules;
	}

	/**
	 * Get scorecard modified time.
	 *
	 * The return value is use to check for outdated contents from cache.
	 *
	 * @return int ast scorecard modified time.
	 */
	public function get_modified_time() {
		return intval( $this->fetch_modified_time( false ) );
	}

	/**
	 * Helper function to check if network tag exist and generate the link.
	 *
	 * @param string $network_name  Name of network.
	 * @param string $network_img   Url of image.
	 * @param string $network_url   Url of network.
	 *
	 * @return string Html of image OR link.
	 */
	protected function _get_network_link( $network_name, $network_img, $network_url = '' ) {

		$term = get_term_by( 'name', $network_name, 'post_tag' );

		if ( ! empty( $term ) && $term->count > 0 ) {
			$network_url = get_term_link( $term, 'post_tag' );
		}

		if ( ! is_wp_error( $network_url ) ) {

			return sprintf(
				'<a href="%s"><img src="%s" title="%s" alt="%s" /></a>',
				esc_url( $network_url ),
				PMC::esc_url_ssl_friendly( $network_img ),
				esc_attr( $network_name ),
				esc_attr( $network_name )
			);

		} else {

			return sprintf(
				'<img src="%s" title="%s" alt="%s" />',
				PMC::esc_url_ssl_friendly( $network_img ),
				esc_attr( $network_name ),
				esc_attr( $network_name )
			);

		}

	}

	/**
	 * Get term html link by title.
	 *
	 * @param string $title Title of Tag.
	 *
	 * @return string Returns link tag if tag found otherwise return title only.
	 */
	protected function _get_title_link( $title ) {

		$term = get_term_by( 'name', $title, 'post_tag' );

		if ( ! empty( $term ) && $term->count > 0 ) {

			$title_url = get_term_link( $term, 'post_tag' );

			if ( ! is_wp_error( $title_url ) ) {

				return sprintf( '<a href="%s">%s</a>', esc_url( $title_url ), esc_html( $title ) );
			}
		}

		return esc_html( $title );
	}

	/**
	 * Helper function to piece together the html for table tbody section.
	 *
	 * @param array $records List of rows.
	 *
	 * @return string Return table rows.
	 */
	public function to_tbody( $records ) {
		$tabody  = '';
		$tabody .= sprintf( '<tbody>' );
		foreach ( $records as $record ) {
			$title   = $this->_get_title_link( $record['title'] );
			$studios = $record['studios'];
			$writers = $record['ep_writers_director'];
			$logline = stripslashes( $record['logline'] );
			$genre   = $record['genre'];
			$status  = $record['status'];
			$network = $this->_get_network_link( $record['network_name'], $record['network_img'] );

			/**
			 * @since 2017-09-01 Milind More CDWE-499
			 */
			$tabody .= \PMC::render_template(
				CHILD_THEME_PATH . '/plugins/variety-scorecard/templates/scorecard-table-body.php',
				array(
					'title'   => $title,
					'studios' => $studios,
					'writers' => $writers,
					'logline' => $logline,
					'genre'   => $genre,
					'status'  => $status,
					'network' => $network,
				)
			);

		}

		$tabody .= sprintf( '</tabody>' );

		return $tabody;

	}

	/**
	 * Manage html table tbody data section.
	 *
	 * @param array|null $args Arguments to fetch data.
	 *
	 * @return string Returns body section of html table.
	 */
	public function get_tbody( $args = null ) {
		$options = array(
			'page'            => 1,
			'page_size'       => self::PAGE_SIZE,
			'network_id'      => '',
			'network_type_id' => '',
		);

		if ( is_array( $args ) ) {
			$options = array_merge( $options, $args );
		}

		$options['page'] = intval( $options['page'] );

		if ( empty( $options['page'] ) ) {
			$options['page'] = 1;
		}

		$this->current_page = $options['page'];

		$result = $this->fetch_records( $options );

		if ( ! empty( $result ) && ! empty( $result['data'] ) ) {
			$this->page_total = intval( $result['page_total'] );
			$this->page_next  = intval( $result['page_next'] );
			$content          = $this->to_tbody( $result['data'] );

			return $content;
		}

		return sprintf(
			'<tr><td colspan="6"><div class="noresult-heading">%s</div><div class="noresult-text">%s</div></td></tr>',
			esc_html__( 'We\'re sorry, we couldn\'t find any results that matched your criteria.', 'pmc-variety' ),
			esc_html__( 'Please update your search criteria and try again.', 'pmc-variety' )
		);

	}

	/**
	 * Manage html table thead section.
	 *
	 * @return string Returns head portion of html table.
	 */
	public function get_thead() {

		/**
		* @since 2017-09-01 Milind More CDWE-499
		*/
		return \PMC::render_template( CHILD_THEME_PATH . '/plugins/variety-scorecard/templates/scorecard-table-head.php' );

	}

	/**
	 * Helper function to piece together the html for select option section.
	 *
	 * @param array  $networks  List of networks.
	 * @param string $selected  Key of selected network.
	 *
	 * @return string Returns options of network.
	 */
	protected function _get_network_select_option( $networks, $selected = '' ) {
		$label    = '';
		$name     = '';
		$value    = '';
		$bufs     = sprintf( '<option value="">%s</option>', esc_html__( 'ALL NETWORKS', 'pmc-variety' ) );
		$selected = strtolower( $selected );

		foreach ( $networks as $rec ) {

			if ( strtolower( $rec['network_type'] ) !== $name ) {

				if ( ! empty( $name ) ) {
					$bufs .= '</optgroup>';
				}

				$name = strtolower( $rec['network_type'] );

				switch ( $name ) {
					case 'cable':
						$label = __( 'All Cable Networks', 'pmc-variety' );
						$value = '2';
						break;

					case 'broadcast network':
						$label = __( 'All Broadcast Networks', 'pmc-variety' );
						$value = '1';
						break;

					default:
						$label = "{$name}";
						$value = $name;
						break;
				}

				$bufs .= sprintf( '<optgroup label="%s" value="%s">', esc_attr( $label ), esc_attr( $value ) );
			}
			$bufs .= sprintf(
				'<option %s value="%s">%s</option>',
				selected( strtolower( $rec['network_id'] ), $selected, false ),
				esc_attr( strtolower( $rec['network_id'] ) ),
				esc_html( $rec['network_name'] )
			);
		}

		if ( ! empty( $label ) ) {
			$bufs .= '</optgroup>';
		}

		return $bufs;

	} // end function _get_network_select_option

	/**
	 * Manage the html dropdown options for list of networks.
	 *
	 * @param string $selected Key of selected option.
	 *
	 * @return bool|string Returns the html dropdown options for list of networks, otherwise false.
	 */
	public function get_network_select_option( $selected = '' ) {

		$result = $this->fetch_network();

		if ( ! empty( $result ) ) {
			return $this->_get_network_select_option( $result['data'], $selected );
		}

		return false;
	}

	/**
	 * Manage markup of selected option of dropdown.
	 *
	 * @param array  $data               List of options.
	 * @param string $selected           Key of selected option.
	 * @param bool   $use_key            Check key is already used or not.
	 * @param string $first_value        Default option value.
	 * @param string $first_description  Default option text.
	 *
	 * @return string Returns html select box.
	 */
	protected function _get_select_option( $data, $selected, $use_key = false, $first_value = '', $first_description = '' ) {

		$bufs     = sprintf( '<option value="%s">%s</option>', esc_attr( $first_value ), esc_html( $first_description ) );
		$selected = strtolower( $selected );

		foreach ( $data as $key => $value ) {

			if ( ! $use_key ) {
				$key = $value;
			}
			$bufs .= sprintf(
				'<option %s value="%s">%s</option>',
				selected( strtolower( $key ), $selected, false ),
				esc_attr( strtolower( $key ) ),
				esc_html( $value )
			);
		}

		return $bufs;

	}

	/**
	 * Manage html dropdown options for list of genre.
	 *
	 * @param string $selected Key of selected option.
	 *
	 * @return bool|string Returns the html dropdown options, otherwise false.
	 */
	public function get_genre_select_option( $selected = '' ) {

		$result = $this->fetch_genre();

		if ( ! empty( $result ) ) {
			return $this->_get_select_option( $result['data'], $selected, true, '', 'ALL GENRES' );
		}

		return false;
	}

	/**
	 * Manage the html dropdown options for list of statuses.
	 *
	 * @param string $selected Key of selected option.
	 *
	 * @return bool|string Returns the html dropdown options, otherwise false.
	 */
	public function get_status_select_option( $selected = '' ) {

		$result = $this->fetch_status();

		if ( ! empty( $result ) ) {
			return $this->_get_select_option( $result['data'], $selected, true, '', 'ALL STATUS' );
		}

		return false;
	}

	/**
	 * Get pagination Links.
	 *
	 * @param array|bool $args List of arguments, default boolean false.
	 *
	 * @return array|string|void Returns pagination link, if no link found returns false.
	 */
	public function get_pagination( $args = false ) {

		$options = array(
			'base'    => add_query_arg( 'page', '%#%', rtrim( get_permalink(), '/' ) ),
			'total'   => $this->page_total,
			'current' => $this->current_page,
			'echo'    => false,
		);

		if ( is_array( $args ) ) {
			$options = array_merge( $options, $args );
		}

		return paginate_links( $options );

	}

	/**
	 * Execute shortcode.
	 *
	 * @param array $args List of arguments.
	 *
	 * @return string Returns html of scorecard.
	 */
	public function do_shortcode( $args ) {

		$options = array();

		if ( isset( $args['page_size'] ) ) {
			$options['page_size'] = intval( trim( $args['page_size'], '"' ) );
		}

		if ( isset( $args['pagination_base'] ) ) {
			$options['pagination_base'] = trim( $args['pagination_base'], '"' );
		} else {
			$options['pagination_base'] = '#page=%#%';
		}

		if ( isset( $args['network_id'] ) ) {
			$options['network_id'] = trim( $args['network_id'], '"' );
		}

		return $this->render_scorecard_html( $options, false );

	}

	/**
	 * Apply shortcode settings.
	 *
	 * @param array $args List of arguments.
	 */
	public function do_shortcode_setting( $args ) {

		if ( is_array( $args ) ) {
			$this->_settings = array_merge( $this->_settings, $args );
		}
	}

	/**
	 * Manage to render scorecard table html elements.
	 *
	 * @param array|bool $args List of arguments, default value is false.
	 * @param bool       $echo False will return html table, Default true will print table content.
	 */
	public function render_scorecard_html( $args = false, $echo = true ) {

		$options = array(
			'pagingation-base' => rtrim( get_permalink(), '/' ) . '-%#%#page=%#%',
		);

		if ( is_array( $args ) ) {
			$options = array_merge( $options, $args );
		}

		$options = array_merge( $this->_settings, $options );

		$allowed_tags = array(
			'select',
			'optgroup' => array(
				'label' => array(),
				'value' => array(),
			),
			'option'   => array(
				'value' => array(),
			),
		);

		$table = PMC::render_template(
			CHILD_THEME_PATH . '/plugins/templates/scorecard-table.php',
			array(
				'options'      => $options,
				'allowed_tags' => $allowed_tags,
			)
		);

		if ( false === $echo ) {

			return $table;

		} else {

			// No need to escape HTML here.
			echo $table; // xss ok.
		}

	} // end function render_scorecard_html

	/**
	 * From below caching related functions are available.
	 */

	/**
	 * Check cache, if found, return, if staled, invalidate cache and fetch records from api.
	 *
	 * @param array|bool $args List of arguments, default value is false.
	 *
	 * @return bool|mixed Returns records.
	 */
	public function fetch_records( $args = false ) {

		$options = array(
			'page'            => 1,
			'page_size'       => self::PAGE_SIZE,
			'network_id'      => '',
			'genre_id'        => '',
			'network_type_id' => '',
			'status_id'       => '',
		);

		if ( is_array( $args ) ) {
			$options = array_merge( $options, $args );
		}

		$result = Variety_Scorecard_API::get_instance()->get_records( $options );

		if ( empty( $result ) || ! is_array( $result ) || is_wp_error( $result ) ) {
			return array();
		}

		return $result;

	} // end function fetch_records

	/**
	 * Check cache, if found, return, if staled, invalidate cache and fetch networks from api.
	 *
	 * @return bool|mixed Returns network otherwise false.
	 */
	public function fetch_network() {

		$result = Variety_Scorecard_API::get_instance()->get_networks();

		if ( empty( $result ) || ! is_array( $result ) || is_wp_error( $result ) ) {
			return array();
		}

		return $result;

	} // end function fetch_network

	/**
	 * Check cache, if found, return, if staled, invalidate cache and fetch genre from api.
	 *
	 * @return bool|mixed Returns network otherwise false.
	 */
	public function fetch_genre() {

		$result = Variety_Scorecard_API::get_instance()->get_genre();

		if ( empty( $result ) || ! is_array( $result ) || is_wp_error( $result ) ) {
			return array();
		}

		return $result;

	}

	/**
	 * Check cache, if found, return, if staled, invalidate cache and fetch status from api.
	 *
	 * @return bool|mixed Returns network otherwise false.
	 */
	public function fetch_status() {

		$result = Variety_Scorecard_API::get_instance()->get_status();

		if ( empty( $result ) || ! is_array( $result ) || is_wp_error( $result ) ) {
			return array();
		}

		return $result;
	}

	/**
	 * Get the last modified time if found in cache or get from api.
	 *
	 * @param bool $force If it is true, it will get update from api.
	 *
	 * @return int Modified time.
	 */
	public function fetch_modified_time( $force = false ) {

		$cache = new \PMC_Cache( 'modified_time', self::CACHE_GROUP );

		$modified_time = $cache->expires_in( self::CACHE_LIFE )
								->updates_with( array( Variety_Scorecard_API::get_instance(), 'get_modified_time' ) )
								->get();

		if ( empty( $modified_time ) || $force ) {

			$modified_time = Variety_Scorecard_API::get_instance()->get_modified_time();
		}

		return intval( $modified_time );
	}

} // class
