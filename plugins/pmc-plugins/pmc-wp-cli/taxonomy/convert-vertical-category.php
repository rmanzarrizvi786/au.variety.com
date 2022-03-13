<?php namespace PMC\WPCLI\Taxonomy;
/**
 * This class is create to implement the cli command: pmc-taxonomy convert-vertical-category
 * @see PMC_WP_CLI_Taxonomy::convert_vertical_category
 *
 * The class was extended from \PMC_WP_CLI_Base too allow help functions usage within the class
 * and the class code is keep within the class to prevent code clustering the main wp cli pmc-taxonomy class PMC_WP_CLI_Taxonomy
 *
 * The script will take a csv with two columns: FROM,TO that contains mapping of vertical/category pairs
 * Search through all post types with the vertical/category pairs assigned and REPLACE the existing post taxonomy vertical & category with the new vertical/category paired.
 *
 * csv file example:
 * FROM,TO
 * oldvertical/oldcategory,oldvertical/newcategory : to only replace category.
 * oldvertical/oldcategory,newvertical/oldcategory : to only replace vertical.
 * oldvertical/oldcategory,newvertical/newcategory : replcae both, can be used without tag.
 * oldvertical/oldcategory/tag,newvertical/newcategory/tag : tag is for quering purpose.
 */

class ConvertVerticalCategory extends \PMC_WP_CLI_Base {

	private $_mappings        = [];
	private $_tax_query       = [];
	private $_last_post_id    = 0;
	private $_skipped         = 0;
	private $_processed       = 0;
	private $_total           = 0;
	private $_should_redirect = true;


	/**
	 * Main function to run
	 * @see PMC_WP_CLI_Taxonomy::convert_vertical_category
	 */
	public function run() {

		$this->_notify_start('Convert vertical/category start');

		$csv_file = $this->_assoc_args['csv-file'];

		if ( ! file_exists( $csv_file ) ) {
			$this->_error( sprintf( 'File not found %s', $csv_file ) );
		}

		if ( ! $this->parse_csv( $csv_file ) ) {
			$this->_error( sprintf( 'Error parsing file %s', $csv_file ) );
		}

		if ( isset( $this->_assoc_args[ 'post-type' ] ) ) {
			$post_types = explode( ',', $this->_assoc_args[ 'post-type' ] );
		}

		$post_status = 'publish';

		if ( ! empty( $this->_assoc_args['post-status'] ) ) {
			$post_status = explode( ',', $this->_assoc_args['post-status'] );
		}

		if ( ! empty( $this->_assoc_args['should-redirect'] ) && 'true' !== $this->_assoc_args['should-redirect'] ) {
			$this->_should_redirect = false;
		}

		if ( empty( $post_types ) ) {
			$post_types = [ 'post' ];
		} else {
			// let's validate post types before we continue
			$invalid_post_types = [];
			foreach( $post_types as $post_type) {
				if ( ! post_type_exists ( $post_type ) ) {
					$invalid_post_types[] = $post_type;
				}
			}
			if ( !empty( $invalid_post_types ) ) {
				$this->_error( sprintf( 'Invalid post type detected: %s', implode( ',', $invalid_post_types ) ) );
			}
		}

		$this->_write_log( sprintf( "Post type(s): %s", implode(', ', $post_types ) ) );

		// we need where filter to work around pagination that can be affected by code in loop affecting the query resultset for next page
		add_filter( 'posts_where', function( $where, $wp_query ) {
			global $wpdb;
			$where = $where . $wpdb->prepare(" AND {$wpdb->posts}.ID > %d", $this->_last_post_id );
			return $where;
		}, 10, 2 );

		/**
		 * Loop through all csv row and tax_query array contain list of from items,
		 * which is used to query posts to do operatio  of replacements.
		 */
		foreach ( $this->_tax_query as $params ) {
			$this->query_posts( $post_types, $post_status, $params );
		}

		$this->_notify_done( sprintf( 'Task finished: processed %d of %d posts, skipped: %d', $this->_processed, $this->_total, $this->_skipped ) );

	}

	/**
	 * Helper function to do the taxonomy replacement
	 *
	 * @param int   $post_id The post ID.
	 * @param int   $index   Index of array walk element.
	 * @param array $params  list of query params, here it will help to verify old mappings of vertical and category.
	 */
	public function replace_taxonomy( $post_id, $index, $params ) {

		$this->_total += 1;

		if ( ! empty( $params['tag'] ) ) {
			$new_vertical = $this->_mappings[ $params['vertical'] ][ $params['category'] ][ $params['tag'] ]['vertical'];
			$new_category = $this->_mappings[ $params['vertical'] ][ $params['category'] ][ $params['tag'] ]['category'];
		} else {
			$new_vertical = $this->_mappings[ $params['vertical'] ][ $params['category'] ]['vertical'];
			$new_category = $this->_mappings[ $params['vertical'] ][ $params['category'] ]['category'];
		}

		$old_path = wp_parse_url( get_permalink( $post_id ), PHP_URL_PATH );

		$this->_write_log( sprintf( '%d - %s', $post_id, get_the_title( $post_id ) ) );

		if ( ! $this->dry_run ) {
			$this->_write_log( sprintf( ' -> Replacing vertical: %s', $params['vertical'] ) );
			$this->_write_log( sprintf( '     with new vertical: %s', $new_vertical->slug ) );
			$this->_write_log( sprintf( ' -> Replacing category: %s', $params['category'] ) );
			$this->_write_log( sprintf( '     with new category: %s', $new_category->slug ) );

			if ( $this->_should_redirect ) {

				$this->_write_log( sprintf( ' -> Legacy redirect from: %s', $old_path ) );
				\WPCOM_Legacy_Redirector::insert_legacy_redirect( $old_path, $post_id );
			}

			wp_set_object_terms( $post_id, $new_vertical->term_id, 'vertical' );
			wp_set_object_terms( $post_id, $new_category->term_id, 'category' );

			$this->_processed += 1;

		} else {
			$this->_write_log( sprintf( ' -> Would replace vertical: %s', $params['vertical'] ) );
			$this->_write_log( sprintf( '         with new vertical: %s', $new_vertical->slug ) );
			$this->_write_log( sprintf( ' -> Would replace category: %s', $params['category'] ) );
			$this->_write_log( sprintf( '         with new category: %s', $new_category->slug ) );

			if ( $this->_should_redirect ) {
				$this->_write_log( sprintf( ' -> Would add legacy redirect: %s', $old_path ) );
			}
		}

	} // function replace_taxonomy

	/**
	 * This function parse the csv and prepare the mappings rules require for the script to run
	 *
	 * @param string $file The file to be parse.
	 *
	 * @return bool True if csv parse successfully, false otherwise.
	 */
	public function parse_csv( $file ) {
		$fp = fopen( $file, 'r' );
		if ( ! $fp ) {
			return false;
		}

		$headers = fgetcsv( $fp );
		if ( empty( $headers ) ) {
			fclose ( $fp );
			return false;
		}

		$headers = array_map( 'strtolower', $headers );
		if ( array_diff( ['to','from'], $headers ) != [] ) {
			fclose( $fp );
			return false;
		}

		while ( $row = fgetcsv( $fp ) ) {
			$row = array_combine( $headers, $row );
			$from = $row['from'];
			$to   = $row['to'];

			list( $vertical, $category, $tag ) = explode( '/', $from );

			list( $new_vertical, $new_category, $new_tag ) = explode( '/', $to );

			if ( "{$vertical}/{$category}/{$tag}" === "{$new_vertical}/{$new_category}/{$new_tag}" ) {
				// to == from, we're not going to make any change
				continue;
			}

			if ( ! empty( $tag ) ) {

				// create the old vertical/category to new vertical/category mapping with tag.
				// Here it may posible that csv row of old vertical/category are same,
				// and according to assigned tag the post needs to be migratted.
				$this->_mappings[ $vertical ][ $category ][ $tag ] = [
					'vertical' => $this->new_term( $new_vertical, 'vertical' ),
					'category' => $this->new_term( $new_category, 'category' ),
				];
			} else {

				// create the old vertical/category to new vertical/category mapping.
				$this->_mappings[ $vertical ][ $category ] = [
					'vertical' => $this->new_term( $new_vertical, 'vertical' ),
					'category' => $this->new_term( $new_category, 'category' ),
				];
			}

			$this->_tax_query[] = [
				'vertical' => $vertical,
				'category' => $category,
				'tag'      => $tag,
			];

		}

		fclose( $fp ); // phpcs:ignore

		return true;
	}

	/**
	 * Helper function to create new taxonomy term from slug if term doesn't exist
	 * All terms should have been created and verify before script run.
	 */
	public function new_term( $slug, $taxonomy ) {
		$term = term_exists( $slug, $taxonomy );

		if ( empty( $term ) ) {
			$name = ucwords( str_replace( '-',' ', $slug ) );
			$term = wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
			if ( is_wp_error( $term ) ) {
				$this->_error( sprintf('Error trying to add %s: %s', $taxonomy, $slug ) );
			}
		}
		return get_term( $term['term_id'], $taxonomy );
	}

	/**
	 * Generate Query args for WP_Query.
	 * And loop through queried post and call replace_taxonomy function to perform replacement operation.
	 *
	 * @param array $post_types list of post types.
	 * @param array $params     list of query params, eg. tax query parameteres.
	 *
	 * @return void
	 */
	public function query_posts( $post_types, $post_status, $params ) {

		$this->_last_post_id = 0;

		$args = array(
			'suppress_filters' => false,
			'post_type'        => $post_types,
			'post_status'      => $post_status,
			'posts_per_page'   => $this->batch_size,
			'fields'           => 'ids',
			'orderby'          => 'ID', // we need to order by ID for endless loop detection.
			'order'            => 'ASC',
			'tax_query'        => [ // phpcs:ignore -- slow query.
				'relation' => 'AND',
			], // tax_query.
		);

		if ( ! empty( $params['vertical'] ) ) {
			$args['tax_query'][] = [
				'taxonomy' => 'vertical',
				'terms'    => $params['vertical'],
				'field'    => 'slug',
				'operator' => 'IN',
			];
		}

		if ( ! empty( $params['category'] ) ) {
			$args['tax_query'][] = [
				'taxonomy' => 'category',
				'terms'    => $params['category'],
				'field'    => 'slug',
				'operator' => 'IN',
			];
		}

		if ( ! empty( $params['tag'] ) ) {
			$args['tax_query'][] = [
				'taxonomy' => 'post_tag',
				'terms'    => $params['tag'],
				'field'    => 'slug',
				'operator' => 'IN',
			];
		}

		// filter by start & end date.
		if ( isset( $this->_assoc_args['start-date'] ) ) {
			$start_dtime = strtotime( $this->_assoc_args['start-date'] );
			if ( isset( $this->_assoc_args['end-date'] ) ) {
				$end_dtime = strtotime( $this->_assoc_args['end-date'] );
			} else {
				$end_dtime = strtotime( '+1 day' );
			}
			$args['date_query'] = [
				'after'     => date( 'Y-m-d H:i:s', $start_dtime ),
				'before'    => date( 'Y-m-d H:i:s', $end_dtime ),
				'inclusive' => true,
			];

			$this->_write_log( sprintf( 'Date range: %s to %s', date( 'Y-m-d H:i:s', $start_dtime ), date( 'Y-m-d H:i:s', $end_dtime ) ) );
		}

		// do while loop.
		do {

			$query = new \WP_Query( $args );

			$count = count( $query->posts );

			if ( 0 === $count ) {
				break;
			}

			$this->_write_log( '==============================================' );
			$this->_write_log(
				sprintf(
					'%s %d of %d posts for Vertical: %s, Category: %s, Tag: %s',
					$this->dry_run ? 'Dry run' : 'Processing',
					$count,
					$query->found_posts,
					$params['vertical'],
					$params['category'],
					$params['tag']
				)
			);
			$this->_write_log( '==============================================' );

			// endless loop detection.  If last id is found in the result set, then we are in endless loop.
			if ( in_array( $this->_last_post_id, (array) $query->posts, true ) ) {
				$this->_error( 'Error: Endless loop detected' );
				break;
			}

			// grab the first id from the result array.
			$this->_last_post_id = end( $query->posts );

			// Run each returned post through the provided callback function.
			array_walk(
				$query->posts,
				array( $this, 'replace_taxonomy' ),
				$params
			);

			if ( 0 < $this->sleep && ! $this->dry_run ) {
				\WP_CLI::line( "Sleep for {$this->sleep} seconds..." );
				$this->stop_the_insanity();
				sleep( $this->sleep );
			}

			$this->stop_the_insanity();

		} while ( $count > 0 );
	}
}
