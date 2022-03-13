<?php

/**
 * class PMC_Editorial_Reports which handles all report generation for the PMC Editorial Reports plugin
 *
 * @author Amit Gupta
 * @since 2013-06-13
 *
 * @version 2013-06-13
 * @version 2013-06-14
 * @version 2013-06-17
 * @version 2013-06-21
 * @version 2013-06-24
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Editorial_Reports {

	use Singleton;

	const max_post_fetch = 100;	//max posts to fetch in one go

	protected $_post_types = array( 'post', 'pmc-gallery' );

	/**
	 * Get reporting post types
	 *
	 * @codeCoverageIgnore
	 *
	 * @since 2013-07-12 Taylor Lovett
	 * @version 2013-07-12 Taylor Lovett
	 */
	public function get_post_types() {
		return $this->_post_types;
	}

	/**
	 * This function accepts a string and returns its word count after stripping
	 * off HTML and WordPress shortcodes from it.
	 *
	 * Ignoring code coverage as test cases are missing.
	 * @codeCoverageIgnore
	 *
	 * @since 2013-06-07
	 * @version 2013-06-07
	 */
	public function get_word_count( $content ) {
		if( empty( $content ) || !is_string( $content ) ) {
			return 0;
		}

		$content = strip_tags( strip_shortcodes( $content ) );	//scrub content

		/*
		 * doing our own counting instead of str_word_count() because it counts
		 * a word like fri3nd as 2 words -> fri & nd
		 */
		$arr_content = array_filter( array_map( 'trim', explode( ' ', $content ) ) );	//remove items with just spaces

		return count( $arr_content );
	}

	/**
	 * Helper function to get the word count of a post, this also considers words from attachements.
	 *
	 * @param int|WP_Post $post WP Post object or Post ID.
	 *
	 * @return int|bool Number of words or false if something went wrong.
	 */
	public function get_post_word_count( $post ) {

		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		$word_count = $this->get_word_count( $post->post_content );

		// Add word count for posts attachments
		$args = array(
			'posts_per_page' => 200, // phpcs:ignore
			'post_parent'    => $post->ID,
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
		);

		$child_query = new WP_Query( $args );

		if ( $child_query->have_posts() ) {

			while ( $child_query->have_posts() ) {
				global $post;
				$child_query->the_post();

				$word_count += $this->get_word_count( $post->post_excerpt );
			}
		}

		wp_reset_postdata();

		return $word_count;
	}

	/**
	 * Helper function to count number of images in a post.
	 *
	 * @param int|WP_Post $post WordPress Post object or Post ID.
	 *
	 * @todo Count number of images in Lists if Stake Holders asks for it.
	 *
	 * @return int|bool Returns number of images or false if something went wrong.
	 */
	public function get_image_count( $post ) {

		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		$image_count = ( ! empty( get_post_thumbnail_id( $post ) ) ) ? 1 : 0;

		// If the post is gallery then count the gallery images.
		if ( 'pmc-gallery' === $post->post_type ) {

			$gallery_images = apply_filters( 'pmc_fetch_gallery', false, $post->ID );

			if ( is_array( $gallery_images ) ) {
				$image_count += count( $gallery_images );
			}
		}

		// Count number of images in post content.
		$post_content = do_shortcode( $post->post_content );

		if ( ! empty( $post_content ) ) {
			$image_count += substr_count( $post_content, '<img' );
		}

		return $image_count;

	}

	/**
	 * Helper function to get the terms along with primary term of a given taxonomy of a post.
	 *
	 * @param int|WP_Post $post WP Post object or Post ID.
	 *
	 * @return array Returns an array containing term names or empty array if something went wrong.
	 */
	public function get_post_taxonomy_categorization( $post, $taxonomy = 'category' ) {

		$post = get_post( $post );
		if ( ! $post ) {
			return [];
		}

		if ( empty( $taxonomy ) ) {
			return [];
		}

		$primary_term_id = 0;
		$term_names      = [];

		// Get the terms.
		$terms = get_the_terms( $post->ID, $taxonomy );

		if ( ! empty( $terms ) && is_array( $terms ) ) {

			// We want to identify the primary taxonomy
			if ( class_exists( 'PMC_Primary_Taxonomy' ) ) {
				$primary_term = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $post, $taxonomy );
				if ( ! is_wp_error( $primary_term ) && is_object( $primary_term ) && ! empty( $primary_term->term_id ) ) {
					$primary_term_id = $primary_term->term_id;
				}
			}

			foreach ( $terms as $term ) {
				if ( $term->term_id === $primary_term_id ) {
					$term_names[] = $term->name . ' ( Primary )';
				} else {
					$term_names[] = $term->name;
				}
			}

		}

		return $term_names;
	}

	/**
	 * This function accepts a post ID and returns display_name of all its
	 * authors/guest-authors in an array. This is Co Authors Plus compatible.
	 *
	 * Ignoring code coverage as test cases are missing.
	 * @codeCoverageIgnore
	 *
	 * @since 2013-06-14
	 * @version 2013-06-17
	 */
	protected function _get_post_authors( $post_id ) {
		$post_id = intval( $post_id );

		if( $post_id < 1 ) {
			return;
		}

		$authors = PMC::get_post_authors( $post_id, 'all', array( 'display_name' ) );

		if( empty( $authors ) ) {
			return;
		}

		return $authors;
	}

	/**
	 * Ignoring code coverage as test cases are missing.
	 * @codeCoverageIgnore
	 */
	public function get_hollywood_execs_report() {
		global $wpdb;

		$sql = array();

		$sql['select'] = "SELECT COUNT(posts.ID)";
		$sql['body'] = "FROM $wpdb->posts as posts WHERE posts.post_type = 'hollywood_exec' AND posts.post_status='publish' AND (posts.ID) NOT IN  ( SELECT posts.ID FROM $wpdb->posts as posts, $wpdb->terms as terms, $wpdb->term_taxonomy as term_taxonomy WHERE posts.post_type = 'hollywood_exec' AND posts.post_name = terms.slug AND terms.term_id = term_taxonomy.term_id AND term_taxonomy.count >= 2 AND posts.post_status = 'publish')";
		$sql['limit'] = '';

		$total_rows = $wpdb->get_var( implode( ' ', $sql ) );

		if ( $total_rows < 1 ) {
			return false;
		}

		$csv = "Post ID,Post Title,Post Name";
		$csv .= "\n";

		$chunk_size = 500;

		$total_chunks = ceil( $total_rows / $chunk_size );

		$sql['select'] = "SELECT posts.ID, posts.post_title, posts.post_name";

		for ( $i = 0; $i < $total_chunks; $i++ ) {
			$sql['limit'] = 'LIMIT ' . ( $i * $chunk_size ) . ',' . $chunk_size;
			$posts = $wpdb->get_results( implode( ' ', $sql ) );

			foreach ( $posts as $post ) {
				$csv .= '"' . (int) $post->ID. '",';
				$csv .= '"' . esc_attr( apply_filters( 'the_title', $post->post_title ) ) . '",';
				$csv .= '"' . esc_attr( $post->post_name ) . '",';
				$csv .= "\n";
			}
		}

		return $csv;
	}

	/**
	 * This function accepts the start & end time of a week for which to generate
	 * report and generates the numbers report containing number of posts written,
	 * words written etc.
	 *
	 * Ignoring code coverage as test cases are missing.
	 * @codeCoverageIgnore
	 *
	 * @since 2013-06-07
	 * @version 2013-06-07
	 * @version 2013-06-11
	 * @version 2013-06-13
	 * @version 2013-06-14
	 * @version 2013-06-17
	 * @version 2013-06-21
	 * @version 2013-06-24
	 */
	public function get_weekly_numbers_report( $start_date, $end_date ) {
		if( $start_date < 0 || $end_date < 0 || $start_date > $end_date ) {
			return;
		}

		//allow post types to be overridden on any site
		$post_types = apply_filters( 'pmc_editorial_reports_weekly_numbers_post_types', $this->_post_types );

		global $wpdb;

		$data = array();

		$all_posts = array();

		$has_print_issues = false;	//assume no print issues term

		if( taxonomy_exists( 'print-issues' ) ) {
			$print_issue_tt_ids = array();
			$print_issues = $wpdb->get_col( "SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'print-issues'" );

			if( ! empty( $print_issues ) ) {
				$print_issue_tt_ids = array_unique( array_map( 'intval', $print_issues ) );
			}

			if( ! empty( $print_issue_tt_ids ) ) {
				$has_print_issues = true;

				//lets get the print articles numbers here itself for the selected date range
				$sql_print_counts = array();
				$sql_print_counts['select'] = "SELECT ";
				$sql_print_counts['what'] = "WEEK(p.post_date) AS week, MONTHNAME(p.post_date) AS month, p.ID AS ID ";
				$sql_print_counts['from'] = "FROM {$wpdb->posts} AS p ";
				$sql_print_counts['from'] .= "JOIN {$wpdb->term_relationships} AS tr ON ( p.ID = tr.object_id ) ";
				$sql_print_counts['where'] = "WHERE p.post_date_gmt >= %s AND p.post_date_gmt <= %s AND p.post_status = 'publish' ";
				$sql_print_counts['where'] .= $wpdb->prepare( "AND p.post_type IN ( " . rtrim( str_repeat( "%s,", count( $post_types ) ), ',' ) . " ) ", $post_types );
				$sql_print_counts['where'] .= $wpdb->prepare( "AND tr.term_taxonomy_id IN ( " . rtrim( str_repeat( "%d,", count( $print_issue_tt_ids ) ), ',' ) . " ) ", $print_issue_tt_ids );
				$sql_print_counts['order'] = "ORDER BY p.post_date ASC ";
				$sql_print_counts['limit'] = "LIMIT %d, %d";

				$post_count_sql = $sql_print_counts['select'] . "COUNT(p.ID) AS post_count " . $sql_print_counts['from'] . $sql_print_counts['where'];

				$post_count = intval( $wpdb->get_var( $wpdb->prepare( $post_count_sql, date( 'Y-m-d H:i:s', $start_date ), date( 'Y-m-d H:i:s', $end_date ) ) ) );

				if( $post_count > 0 ) {
					$offset = 0;
					$limit = self::max_post_fetch;

					$print_counts_sql = $sql_print_counts['select'] . $sql_print_counts['what'] . $sql_print_counts['from'] . $sql_print_counts['where'] . $sql_print_counts['order'] . $sql_print_counts['limit'];

					while( $offset < $post_count ) {
						$posts = $wpdb->get_results( $wpdb->prepare( $print_counts_sql, date( 'Y-m-d H:i:s', $start_date ), date( 'Y-m-d H:i:s', $end_date ), $offset, $limit ) );

						$offset += $limit;	//increment offset for next run

						foreach( $posts as $post ) {
							$authors = $this->_get_post_authors( $post->ID );	//get all authors/guest-authors on this post

							if( empty( $authors ) ) {
								continue;	//no authors for this post, skip to next post
							}

							//this will run only once unless a post has more than one author
							//in which case it'll add data for each of the authors
							foreach( $authors as $author_id => $author ) {
								$data[ $author_id ]['name'] = $author['display_name'];
								$data[ $author_id ][ $post->week ]['month'] = $post->month;

								//lets make this as well, it'll be needed later
								if( ! isset( $data[ $author_id ][ $post->week ]['word_count'] ) ) {
									$data[ $author_id ][ $post->week ]['word_count'] = 0;
								}

								if( ! isset( $data[ $author_id ][ $post->week ]['posts_print'] ) ) {
									$data[ $author_id ][ $post->week ]['posts_print'] = array();
								}

								if( ! in_array( $post->ID, $data[ $author_id ][ $post->week ]['posts_print'] ) ) {
									$data[ $author_id ][ $post->week ]['posts_print'][] = $post->ID;
									$all_posts[] = $post->ID;
								}
							}

							unset( $authors );
						}

						unset( $posts );
					}	//end while

					unset( $print_counts_sql, $limit, $offset );
				}

				unset( $post_count, $post_count_sql, $sql_print_counts );
			}

			unset( $print_issues );
		}

		//time to get digital articles numbers for the selected week
		$sql_digital_counts = array();
		$sql_digital_counts['select'] = "SELECT ";
		$sql_digital_counts['what'] = "WEEK(p.post_date) AS week, MONTHNAME(p.post_date) AS month, p.ID AS ID ";
		$sql_digital_counts['from'] = "FROM {$wpdb->posts} AS p ";

		if( $has_print_issues === true ) {
			$sql_digital_counts['from'] .= "JOIN {$wpdb->term_relationships} AS tr ON ( p.ID = tr.object_id ) ";
		}

		$sql_digital_counts['where'] = "WHERE p.post_date_gmt >= %s AND p.post_date_gmt <= %s AND p.post_status = 'publish' ";
		$sql_digital_counts['where'] .= $wpdb->prepare( "AND p.post_type IN ( " . rtrim( str_repeat( "%s,", count( $post_types ) ), ',' ) . " ) ", $post_types );

		if( $has_print_issues === true ) {
			$sql_digital_counts['where'] .= $wpdb->prepare( "AND tr.term_taxonomy_id NOT IN ( " . rtrim( str_repeat( "%d,", count( $print_issue_tt_ids ) ), ',' ) . " ) ", $print_issue_tt_ids );
		}

		$sql_digital_counts['order'] = "ORDER BY p.post_date ASC ";
		$sql_digital_counts['limit'] = "LIMIT %d, %d";

		$post_count_sql = $sql_digital_counts['select'] . "COUNT(p.ID) AS post_count " . $sql_digital_counts['from'] . $sql_digital_counts['where'];

		$post_count = intval( $wpdb->get_var( $wpdb->prepare( $post_count_sql, date( 'Y-m-d H:i:s', $start_date ), date( 'Y-m-d H:i:s', $end_date ) ) ) );

		if( $post_count > 0 ) {
			$offset = 0;
			$limit = self::max_post_fetch;

			$digital_counts_sql = $sql_digital_counts['select'] . $sql_digital_counts['what'] . $sql_digital_counts['from'] . $sql_digital_counts['where'] . $sql_digital_counts['order'] . $sql_digital_counts['limit'];

			while( $offset < $post_count ) {
				$posts = $wpdb->get_results( $wpdb->prepare( $digital_counts_sql, date( 'Y-m-d H:i:s', $start_date ), date( 'Y-m-d H:i:s', $end_date ), $offset, $limit ) );

				$offset += $limit;	//increment offset for next run

				foreach( $posts as $post ) {
					$authors = $this->_get_post_authors( $post->ID );	//get all authors/guest-authors on this post

					if( empty( $authors ) ) {
						continue;	//no authors for this post, skip to next post
					}

					//this will run only once unless a post has more than one author
					//in which case it'll add data for each of the authors
					foreach( $authors as $author_id => $author ) {
						$data[ $author_id ]['name'] = $author['display_name'];
						$data[ $author_id ][ $post->week ]['month'] = $post->month;

						//lets make this as well, it'll be needed later
						if( ! isset( $data[ $author_id ][ $post->week ]['word_count'] ) ) {
							$data[ $author_id ][ $post->week ]['word_count'] = 0;
						}

						if( ! isset( $data[ $author_id ][ $post->week ]['posts_digital'] ) ) {
							$data[ $author_id ][ $post->week ]['posts_digital'] = array();
						}

						if( ! in_array( $post->ID, $data[ $author_id ][ $post->week ]['posts_digital'] ) ) {
							$data[ $author_id ][ $post->week ]['posts_digital'][] = $post->ID;
							$all_posts[] = $post->ID;
						}
					}

					unset( $authors );
				}

				unset( $posts );
			}	//end while

			unset( $digital_counts_sql, $limit, $offset );
		}

		unset( $post_count, $post_count_sql, $sql_digital_counts );

		$all_posts = array_filter( array_unique( $all_posts ) );

		//lets get word counts for all posts
		$sql_word_counts = "SELECT pm.post_id AS ID, WEEK(p.post_date) AS week, pm.meta_value AS word_count ";
		$sql_word_counts .= "FROM {$wpdb->posts} AS p ";
		$sql_word_counts .= "JOIN {$wpdb->postmeta} AS pm ON (p.ID = pm.post_id) ";
		$sql_word_counts .= "WHERE pm.meta_key = '_pmc_word_count' ";
		$sql_word_counts .= $wpdb->prepare( "AND pm.post_id IN ( " . rtrim( str_repeat( "%d,", count( $all_posts ) ), ',' ) . " ) ", $all_posts );

		$posts = $wpdb->get_results( $sql_word_counts );

		if( ! empty( $posts ) ) {
			foreach( $posts as $post ) {
				$word_count = intval( $post->word_count );

				foreach( $data as $author_id => $author_data ) {
					if( ! isset( $data[ $author_id ][ $post->week ]['word_count'] ) ) {
						$data[ $author_id ][ $post->week ]['word_count'] = 0;
					}

					if( in_array( $post->ID, $data[ $author_id ][ $post->week ]['posts_print'] ) || in_array( $post->ID, $data[ $author_id ][ $post->week ]['posts_digital'] ) ) {
						$data[ $author_id ][ $post->week ]['word_count'] += $word_count;
					}
				}

				unset( $word_count );
			}
		}

		unset( $posts, $sql_word_counts );

		//if we don't have any data then bail out
		if( empty( $data ) ) {
			return false;
		}

		$csv = "Author,Week,Print Posts,Digital Posts,Total Posts,Word Count";
		$csv .= "\n";

		//generate CSV for output
		foreach( $data as $author_id => $author ) {
			if( isset( $author['name'] ) ) {
				$name = $author['name'];
				unset( $author['name'] );
			} else {
				$name = '';
			}

			foreach( $author as $week => $author_data ) {
				$posts_print = ( ! isset( $author_data['posts_print'] ) ) ? 0 : count( $author_data['posts_print'] );
				$posts_digital = ( ! isset( $author_data['posts_digital'] ) ) ? 0 : count( $author_data['posts_digital'] );
				$total_posts = $posts_print + $posts_digital;
				$word_count = ( ! isset( $author_data['word_count'] ) ) ? 0 : $author_data['word_count'];

				$csv .= '"' . esc_attr( $name ) . '",';
				$csv .= '"' . intval( $week ) . '",';
				$csv .= '"' . intval( $posts_print ) . '",';
				$csv .= '"' . intval( $posts_digital ) . '",';
				$csv .= '"' . intval( $total_posts ) . '",';
				$csv .= '"' . intval( $word_count ) . '",';
				$csv .= "\n";

				unset( $word_count, $total_posts, $posts_digital, $posts_print );
			}

			unset( $name );
		}

		unset( $all_posts, $data );

		return $csv;
	}

//end of class
}


//EOF
