<?php
/**
 * Class contains PMC Stocks API related functions.
 *
 * @since 2016-07-18 - Mike Auteri - PPT-6906
 * @version 2016-07-18 - Mike Auteri - PPT-6906
 */
namespace PMC\Stocks;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

class Api {

	use Singleton;

	protected $_auth_details = null;
	protected $_google_oauth2_details = 'pmc-stocks-oauth2-configs';
	protected $_bigquery_url = 'https://www.googleapis.com/bigquery/v2/projects/';
	protected $_stocks_configs_defaults = array(
		'project_id' => 'pmc-analytical-data-mart',
		'database'      => '',
	);

	public $meta_key = 'pmc_stock_meta';

	public $cache_keys = array(
		'stock_index'   => 'pmc_stock_index_data_',
		'stock_summary' => 'pmc_stock_summary_data',
		'stock'         => 'pmc_stock_data',
		'group'         => 'pmc_stocks',
	);

	public $_the_configs = array();

	protected function __construct() {
		add_filter( 'pmc-google-oauth2', array( $this, 'google_oauth2_configs' ) );
		add_action( 'template_redirect', array( $this, 'ajax_graph_update' ) );
		add_action( 'init', array( $this, 'api_rewrite_rules' ) );

		$this->_stocks_configs();
	}

	/**
	 * Rewrite rules for JSON endpoint.
	 */
	public function api_rewrite_rules() {
		add_rewrite_tag( '%pmc_stocks_graph_range%', '([^&]+)' );
		add_rewrite_rule( 'pmc-stocks-graph/([^&]+)/?', 'index.php?pmc_stocks_graph_range=$matches[1]', 'top' );
	}

	/**
	 * Set configs for BigQuery.
	 */
	protected function _stocks_configs() {
		$this->_the_configs = apply_filters( 'pmc_stocks_configs', $this->_stocks_configs_defaults );

		if ( ! empty( $this->_the_configs['project_id'] ) ) {
			$this->_bigquery_url .= sanitize_text_field( $this->_the_configs['project_id'] ) . '/queries';
		}
	}

	/**
	 * Get configs for BigQuery.
	 *
	 * @return array|bool
	 */
	public function get_stocks_configs() {
		if ( ! empty ( $this->_the_configs ) && is_array( $this->_the_configs ) ) {
			return $this->_the_configs;
		}
		return false;
	}

	/**
	 * Hook into Google OAuth2 plugin to make connection.
	 *
	 * @param array $configs
	 * @see pmc-google-oauth2
	 * @return array
	 */
	public function google_oauth2_configs( $configs = array() ) {
		$configs[ $this->_google_oauth2_details ] = array(
			'description' => 'PMC Stocks to Google BigQuery',
			'scope' => 'bigquery',
		);

		return $configs;
	}

	/**
	 * Requests data from BigQuery.
	 *
	 * Example:
	 * $sql = "select * from wwd.stock_data where date > '2016-07-10'";
	 * $request = $this->bigquery_request( $sql );
	 *
	 * @param $sql
	 * @return array|bool
	 */
	public function bigquery_request( $sql ) {
		if ( empty( $sql ) || ! is_string( $sql ) ) {
			return false;
		}

		$google_auth2 = PMC\Google_OAuth2\OAuth2::get_instance();
		$auth_details = $google_auth2->get_current_google_token( $this->_google_oauth2_details );
		if ( ! is_array( $auth_details ) || empty( $auth_details['access_token'] ) ) {
			return false;
		}
		$data = json_encode( array( 'query' => $sql ) );

		if ( false === $data ) {
			return false;
		}

		$response = wp_remote_post( $this->_bigquery_url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $auth_details['access_token'],
				'Content-Type' => 'application/json; charset=UTF-8',
			),
			'body' => $data,
		) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		try {
			if ( 200 !== $response['response']['code'] ) {
				return false;
			}
			$data = json_decode( $response['body'] );
			if ( null === $data || false === $data ) {
				return false;
			}

			$rows = $data->rows;
			if ( empty( $rows ) || ! is_array( $rows ) ) {
				return false;
			}

			$fields = $data->schema->fields;
			if ( empty( $fields ) || ! is_array( $fields ) ) {
				return false;
			}

			$payload = array();

			foreach ( $rows as $row ) {
				$row = current( $row );
				$clean_row = array();

				foreach ( $row as $key => $value ) {
					$field = $fields[ $key ];
					if ( empty( $field ) ) {
						continue;
					}
					$field = sanitize_text_field( $field->name );
					$value = current( $value );
					$clean_row[ $field ] = sanitize_text_field( $value );
				}

				$payload[] = $clean_row;
			}

			return $payload;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Array of available timeframes.
	 *
	 * @return array
	 */
	public function get_timeframes() {
		// Records only go back to January 2012, so lets set a basic limit when `all` is selected.
		$all = intval( ( date( 'Y' ) - 2012 ) * 366 );

		return array(
			'1 month ago'  => 31,
			'3 months ago' => 93,
			'6 months ago' => 186,
			'1 year ago'   => 366,
			'all'          => $all,
		);
	}

	/**
	 * Endpoint to get individual stock details.
	 *
	 * @return array
	 */
	public function stock_data() {
		$cache_key = sanitize_key( $this->cache_keys['stock'] );
		$group = sanitize_key( $this->cache_keys['group'] );

		$payload = wp_cache_get( $cache_key, $group );

		if ( false === $payload ) {
			$args = array(
				'post_type'      => 'pmc-stock',
				'post_status'    => 'publish',
				'posts_per_page' => 500,
			);

			$query = new \WP_Query( $args );

			$payload = array();

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$payload[] = get_post_meta( get_the_ID(), $this->meta_key, true );
				}

				wp_reset_postdata();
			}

			wp_cache_set( $cache_key, $payload, $group, 0 ); // Cache is deleted in cron.
		}

		return $payload;
	}

	/**
	 * Endpoint for range of stocks.
	 *
	 * @param string $timeframe
	 *
	 * @return array|bool
	 */
	public function stock_index_data( $timeframe = '3 months ago' ) {

		$available_timeframes = $this->get_timeframes();

		if ( empty( $available_timeframes[ $timeframe ] ) ) {
			return false;
		}

		$key = $available_timeframes[ $timeframe ];

		$cache_key = sanitize_key( $this->cache_keys['stock_index'] . sanitize_title( $timeframe ) );
		$group = sanitize_key( $this->cache_keys['group'] );

		$payload = wp_cache_get( $cache_key, $group );

		if ( false === $payload ) {
			// Protect against -1...
			if ( intval( $key ) < 0 ) {
				return false;
			}

			$args = array(
				'post_type'      => 'pmc-stock-index',
				'post_status'    => 'publish',
				'posts_per_page' => intval( $key ),
				'order'          => 'ASC',
				'date_query'     => array(
					'column' => 'post_date',
					'after'  => sanitize_text_field( $timeframe ),
				),
			);

			$query = new \WP_Query( $args );

			$payload = array();

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$payload[] = get_post_meta( get_the_ID(), $this->meta_key, true );
				}

				wp_reset_postdata();
			}

			wp_cache_set( $cache_key, $payload, $group, 0 ); // Cache is deleted in cron.
		}

		return $payload;
	}

	/**
	 * Endpoint for stock summary.
	 *
	 * @return bool|mixed
	 */
	public function stock_summary_data() {
		$cache_key = sanitize_key( $this->cache_keys['stock_summary'] );
		$group = sanitize_key( $this->cache_keys['group'] );

		$payload = wp_cache_get( $cache_key, $group );

		if ( false === $payload ) {

			$args = array(
				'post_type'      => 'pmc-stock-summary',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
			);

			$query = new \WP_Query( $args );
			$payload = '';

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$payload = get_post_meta( get_the_ID(), $this->meta_key, true );
				}

				wp_reset_postdata();
			}

			wp_cache_set( $cache_key, $payload, $group, 0 ); // Cache is deleted in cron.
		}

		return $payload;
	}

	/**
	 * Get date of second to last most recent. If that does not exist, get date of most recent post of a post type.
	 *
	 * @param $post_type
	 *
	 * @return bool|string
	 */
	public function get_the_start_date( $post_type ) {
		if ( empty( $post_type ) ) {
			return false;
		}

		// First try to get the one before the last published.
		$args = array(
			'numberposts'      => 1,
			'offset'           => 1,
			'post_type'        => sanitize_key( $post_type ),
			'post_status'      => 'publish',
			'suppress_filters' => false,
		);

		$most_recent = wp_get_recent_posts( $args );

		// If it doesn't exist, try to get most recent.
		if ( ! count( $most_recent ) ) {
			$args = array(
				'numberposts'      => 1,
				'post_type'        => sanitize_key( $post_type ),
				'post_status'      => 'publish',
				'suppress_filters' => false,
			);

			$most_recent = wp_get_recent_posts( $args );
		}

		// If we get something, format it and return the date.
		if ( count( $most_recent ) ) {
			$most_recent = current( $most_recent );
			$date = \DateTime::createFromFormat( 'Y-m-d H:i:s', $most_recent['post_date'] );
			if ( false === $date ) {
				return false;
			}

			return $date->format( 'Y-m-d' );
		}

		return false;
	}

	/**
	 * Creates or gets a post of a certain stock type.
	 *
	 * @param string $name
	 * @param string $post_type
	 * @param array  $extra_args
	 *
	 * @return bool|int
	 */
	public function create_stock_type( $name = '', $post_type = '', $extra_args = array() ) {
		if ( empty( $name ) || empty( $post_type ) ) {
			return false;
		}

		$post = null;

		// Check if stock exists already. If it does, reference and update it. If not, we will create it.
		// Note: we need to use esc_html here to match correctly.
		$post = wpcom_vip_get_page_by_title( esc_html( $name ), OBJECT, $post_type );

		if ( empty( $post ) ) {
			$args = array(
				'post_title'    => sanitize_text_field( $name ),
				'post_type'     => sanitize_text_field( $post_type ),
				'post_status'   => 'publish',
			);

			if ( is_array( $extra_args ) ) {
				$args = array_merge( $extra_args, $args );
			}

			$post_id = wp_insert_post( $args );
			if ( is_wp_error( $post_id ) ) {
				return false;
			}
		} else {
			$post_id = $post->ID;
		}

		$post_id = intval( $post_id );

		if ( ! $post_id ) {
			return false;
		}

		return $post_id;
	}

	/**
	 * Ajax endpoint to update graph data.
	 *
	 * @return json
	 */
	public function ajax_graph_update() {

		global $wp_query;

		$value = sanitize_key( $wp_query->get( 'pmc_stocks_graph_range' ) );

		if ( ! $value ) {
			return;
		}

		$value = str_replace( '-', ' ', $value );

		if ( empty( $value ) ) {
			wp_send_json_error();
		}

		$payload = array(
			'data'    => $this->stock_index_data( $value ),
			'summary' => $this->stock_summary_data(),
		);

		if ( is_array( $payload['data'] ) && is_array( $payload['summary'] ) ) {
			wp_send_json_success( $payload );
		}

		wp_send_json_error();
	}

	/**
	 * Completely delete all stocks cache.
	 */
	public function delete_stocks_cache() {
		$group = sanitize_key( $this->cache_keys['group'] );
		$rtn = true;

		$cache_key = sanitize_key( $this->cache_keys['stock'] );
		if ( ! wp_cache_delete( $cache_key, $group ) ) {
			$rtn = false;
		}

		$cache_key = sanitize_key( $this->cache_keys['stock_summary'] );
		if ( ! wp_cache_delete( $cache_key, $group ) ) {
			$rtn = false;
		}

		$timeframes = $this->get_timeframes();
		foreach ( (array) $timeframes as $key => $timeframe ) {
			$cache_key = sanitize_key( $this->cache_keys['stock_index'] . sanitize_title( $key ) );
			if ( ! wp_cache_delete( $cache_key, $group ) ) {
				$rtn = false;
			}
		}
		return $rtn;
	}

}

// EOF
