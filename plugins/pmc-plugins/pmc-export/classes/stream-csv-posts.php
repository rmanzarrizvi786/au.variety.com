<?php
namespace PMC\Export;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Note: We cannot use PMC_Cache since we need to be able to retrieve cache without trigger cache rebuild
 * PMC_Cache::get will throw an error if there is no valid callback.
 *
 */

class Stream_Csv_Posts extends Stream_Csv {

	const ID             = 'csv-posts';
	const CACHE_DURATION = 900; // cache for 15 minutes
	const CACHE_GROUP    = __CLASS__;
	const PAGE_SIZE      = 100; // Number of records to return should not be greater than 100

	/**
	 * User selected post type, defaults to 'post.
	 *
	 * @var string
	 */
	private $_post_type = 'post';

	/**
	 * User selected year.
	 *
	 * @var string|bool
	 */
	private $_filter_year = false;

	/**
	 * User selected month.
	 *
	 * @var string|bool
	 */
	private $_filter_month = false;

	/**
	 * Total number of pages for the requested data.
	 *
	 * @var int|bool
	 */
	private $_pages = false;

	/**
	 * User selected reporting data fields.
	 *
	 * @var array
	 */
	private $_reporting_fields = [];

	protected function __construct() {

		parent::__construct();

		$this->_post_type = \PMC::filter_input( INPUT_POST, 'post_type' );

		$reporting_fields = \PMC::filter_input( INPUT_POST, 'reporting_fields_filter' );

		// Setup selected reporting fields, these will be included in the report.
		if ( ! empty( $reporting_fields ) ) {

			// Remove spaces from all elements and empty elements.
			$reporting_fields = array_filter(
				array_map(
					'trim',
					(array) explode( ',', $reporting_fields )
				)
			);

			$this->_reporting_fields = array_unique(
				array_intersect(
					$reporting_fields,
					Posts::get_instance()->get_supported_reporting_fields()
				)
			);

		} else {

			$this->_reporting_fields = Posts::get_instance()->get_supported_reporting_fields();
		}

		// extract the input from drop down value YYYYMM into year & month
		if ( preg_match( '/(\d{4})(\d{2})*/', \PMC::filter_input( INPUT_POST, 'date_filter' ), $matches ) ) {
			if ( 3 === count( $matches ) ) {
				$this->_filter_month = $matches[2];
			}
			$this->_filter_year = $matches[1];
		}

	}

	/**
	 * Generate the cache key base on input fields
	 * @param $prefix
	 * @return string
	 */
	public function cache_key( $prefix ) {
		return $prefix . '_' . $this->_post_type . '_' . $this->_filter_year . $this->_filter_month . implode( '-', $this->_reporting_fields );
	}

	/**
	 * Retrieve the total pages via get_rows
	 * @return int
	 */
	public function pages() : int {

		if ( empty( $this->_post_type ) || empty( $this->_filter_year ) ) {
			return 0;
		}

		if ( false !== $this->_pages && is_numeric( $this->_pages ) ) {
			return intval( $this->_pages );
		}

		$cache_key    = $this->cache_key( 'pages' );
		$this->_pages = wp_cache_get( $cache_key, static::CACHE_GROUP );

		if ( ! is_numeric( $this->_pages ) ) {
			$this->get_rows( 1 );
		}

		return intval( $this->_pages );
	}

	/**
	 * Define the first rows headers column name for the CSV file
	 * @return array
	 */
	public function get_headers() : array {
		return $this->_reporting_fields;
	}

	/**
	 * Return the array data rows for the request input $page
	 * @param int $page
	 * @return array
	 */
	public function get_rows( int $page ) : array {
		global $wpdb;

		if ( empty( $this->_post_type ) || empty( $this->_filter_year ) || 0 === $page || empty( $this->_reporting_fields ) ) {
			return [];
		}

		// Grab from cache if exists, otherwise query the db
		$cache_key_pages = $this->cache_key( 'pages' );
		$cache_key_rows  = $this->cache_key( 'rows-' . $page );
		$this->_pages    = wp_cache_get( $cache_key_pages, static::CACHE_GROUP );
		$rows            = wp_cache_get( $cache_key_rows, static::CACHE_GROUP );

		if ( ! is_numeric( $this->_pages ) || ! is_array( $rows ) ) {

			// Note: To minimize memory usage, we use direct SQL query to avoid retrieving post content text blob, etc...

			$sql_safe_part = $wpdb->prepare(
				"
				FROM $wpdb->posts
				WHERE post_status = 'publish'
					AND post_type = %s
					AND YEAR(post_date) = %d
				",
				$this->_post_type,
				$this->_filter_year
			);

			if ( ! empty( $this->_filter_month ) ) {
				$sql_safe_part = $sql_safe_part . $wpdb->prepare( ' AND MONTH(post_date) = %d ', $this->_filter_month );
			}

			if ( ! is_numeric( $this->_pages ) ) {
				$sql_safe = 'SELECT COUNT(*) ' . $sql_safe_part;
				$count      = intval( $wpdb->get_var( $sql_safe ) ); // phpcs:ignore
				if ( $count > 0 ) {
					$this->_pages = intval( $count / static::PAGE_SIZE );
					if ( $this->_pages * static::PAGE_SIZE < $count ) {
						$this->_pages = $this->_pages + 1;
					}
				} else {
					$this->_pages = 0;
					// We really don't want to cache empty results
					return [];
				}
				wp_cache_set( $cache_key_pages, $this->_pages, static::CACHE_GROUP, static::CACHE_DURATION ); // phpcs:ignore WordPressVIPMinimum.Performance.LowExpiryCacheTime.LowCacheTime
			}

			$offset = 0;

			if ( $page > 1 ) {
				$offset = ( $page - 1 ) * static::PAGE_SIZE;
			}

			$sql_safe_fields = 'ID';

			if ( $this->is_reporting_field_requested( 'Published Date' ) ) {

				$sql_safe_fields .= ',post_date';

			}

			if ( $this->is_reporting_field_requested( 'Title' ) ) {

				$sql_safe_fields .= ',post_title';

			}

			$sql_safe = " SELECT $sql_safe_fields " . $sql_safe_part
				. $wpdb->prepare( ' LIMIT %d, %d ', $offset, static::PAGE_SIZE );

			$posts = $wpdb->get_results( $sql_safe ); // phpcs:ignore

			if ( empty( $posts ) ) {
				// We really don't want to cache empty results
				return [];
			}

			$rows = [];

			foreach ( $posts as $post ) {

				$row = [];

				// These data must match the columns defined in get_headers()
				$row['Post ID'] = $post->ID;

				// Prepare row based on requested fields.

				if ( $this->is_reporting_field_requested( 'URL' ) ) {
					$row['URL'] = get_permalink( $post->ID );
				}

				if ( $this->is_reporting_field_requested( 'Title' ) ) {
					$row['Title'] = $post->post_title;
				}

				if ( $this->is_reporting_field_requested( 'Published Date' ) ) {
					$row['Published Date'] = $post->post_date;
				}

				if ( $this->is_reporting_field_requested( 'Author' ) ) {
					$row['Author'] = $this->get_authors( $post->ID );
				}

				if ( $this->is_reporting_field_requested( 'Category + Sub Category' ) || $this->is_reporting_field_requested( 'Vertical' ) ) {

					$categorization = $this->get_categorization( $post->ID );

					if ( $this->is_reporting_field_requested( 'Category + Sub Category' ) ) {
						$row['Category + Sub Category'] = join( ', ', (array) $categorization['category'] );
					}

					if ( $this->is_reporting_field_requested( 'Vertical' ) ) {
						$row['Vertical'] = join( ', ', (array) $categorization['vertical'] );
					}
				}

				if ( $this->is_reporting_field_requested( 'Word Count' ) ) {
					$word_count = get_post_meta( $post->ID, Posts::WORD_COUNT_META_SLUG, true );

					// If the word count is not set, we need to calculate it from function and set in meta.
					if ( empty( $word_count ) ) {
						$word_count = Helper::get_post_word_count( $post->ID );
						update_post_meta( $post->ID, Posts::WORD_COUNT_META_SLUG, (int) $word_count );
					}

					$row['Word Count'] = $word_count;
				}

				if ( $this->is_reporting_field_requested( 'Number of Image' ) ) {
					$image_count = get_post_meta( $post->ID, Posts::IMAGE_COUNT_META_SLUG, true );

					// If the image count is not set, we need to calculate it from helper function and set in meta.
					if ( empty( $image_count ) ) {
						$image_count = Helper::get_image_count( $post->ID );
						update_post_meta( $post->ID, Posts::IMAGE_COUNT_META_SLUG, (int) $image_count );
					}

					$row['Number of Image'] = $image_count;
				}

				if ( $this->is_reporting_field_requested( 'Attached Galleries' ) ) {

					// Get post linked gallery.
					$linked_gallery = get_post_meta( $post->ID, Posts::ATTACHED_GALLERY_META_SLUG, true );

					if ( ! empty( $linked_gallery ) ) {

						$linked_gallery = json_decode( $linked_gallery );
						$linked_gallery = ! empty( $linked_gallery->url ) ? $linked_gallery->url : '';

					} else {

						$linked_gallery = '';
					}

					$row['Attached Galleries'] = $linked_gallery;
				}

				if ( $this->is_reporting_field_requested( 'Tasks Completed' ) ) {
					$row['Tasks Completed'] = $this->get_tasks( $post->ID );
				}

				$rows[] = $row;
			}

			wp_cache_set( $cache_key_rows, $rows, static::CACHE_GROUP, static::CACHE_DURATION ); // phpcs:ignore WordPressVIPMinimum.Performance.LowExpiryCacheTime.LowCacheTime
		}

		return $rows;
	}

	/**
	 * Helper to determine if a field has been requested or not.
	 */
	private function is_reporting_field_requested( $field ) {
		return in_array( $field, (array) $this->_reporting_fields, true );
	}

	/**
	 * Return the category & sub-categories for a given post id.
	 * @param int $post_id
	 * @return array
	 */
	public function get_categorization( int $post_id ) : array {
		$categories = [];
		$verticals  = [];

		// Get post categorization.
		$categorization = json_decode( get_post_meta( $post_id, Posts::CATEGORIZATION_META_SLUG, true ) );

		// If no categorization data found in postmeta. Then get it from the taxonomy and set it to postmeta.
		if ( empty( $categorization ) ) {
			// Get taxonomy categorization.
			$categories = implode( ', ', Helper::get_post_taxonomy_categorization( $post_id, 'category' ) );
			$verticals  = implode( ', ', Helper::get_post_taxonomy_categorization( $post_id, 'vertical' ) );

			$categorization = wp_json_encode(
				[
					'category' => $categories,
					'vertical' => $verticals,
				]
			);

			// Save in postmeta.
			update_post_meta( $post_id, Posts::CATEGORIZATION_META_SLUG, $categorization );

			// Make json string to object.
			$categorization = json_decode( $categorization );
		}

		if ( ! empty( $categorization ) ) {
			$categories = ! empty( $categorization->category ) ? $categorization->category : '';
			$verticals  = ! empty( $categorization->vertical ) ? $categorization->vertical : '';
		}

		return [
			'category' => $categories,
			'vertical' => $verticals,
		];

	}

	/**
	 * Return a list of comas delimited Authors for a given post
	 * @param int $post_id
	 * @return string
	 */
	public function get_authors( int $post_id ) : string {
		$authors = [];
		foreach ( \get_coauthors( $post_id ) as $author ) {
			$authors[] = $author->display_name;
		}
		return join( ', ', $authors );
	}

	/**
	 * Return a list of comas delimited completed tasks for a given post
	 * @param int $post_id
	 * @return string
	 */
	public function get_tasks( int $post_id ) : string {

		$tasks = [ 'Checklist Not Enabled' ];

		if ( class_exists( '\Publishing_Checklist' ) ) {
			$tasks = [];
			$list  = \Publishing_Checklist::get_instance()->evaluate_checklist( $post_id );
			if ( ! empty( $list ) && is_array( $list ) ) {
				foreach ( $list['completed'] as $completed ) {
					$tasks[] = $list['tasks'][ $completed ]['label'];
				}
			}
		}

		return join( ', ', $tasks );
	}

}
