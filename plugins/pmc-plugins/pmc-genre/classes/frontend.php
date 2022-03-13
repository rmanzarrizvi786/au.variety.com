<?php
/*
 * This class handles the front-end part of PMC Genre plugin
 *
 * @author Amit Gupta <agupta@pmc.com>
 */

namespace PMC\Genre;


class Frontend extends Base {

	/**
	 * @var PMC\Genre\Taxonomy Object of the PMC\Genre\Taxonomy class
	 */
	protected $_taxonomy;


	protected function __construct() {
		$this->_taxonomy = Taxonomy::get_instance();

		$this->_setup_hooks();
	}

	protected function _setup_hooks() {

		/*
		 * Filters
		 */
		add_filter( 'pmc_page_meta', array( $this, 'add_to_page_meta' ) );
		add_filter( 'pmc_krux_allowed_data_attributes', array( $this, 'add_to_krux' ) );
		add_filter( 'pmc_robots_txt', array( $this, 'remove_from_robots_txt' ), 10, 2 );

	}

	/**
	 * Whitelist the genre tag from PMC_Page_Meta for Krux.
	 *
	 * @since 2015-07-03 Corey Gilmore PPT-5136
	 *
	 * @version 2015-07-03 Corey Gilmore Initial version - PPT-5136
	 *
	 * @param array $allowed_data_attributes List of key names from PMC_Page_Meta.
	 * @return array List of key names from PMC_Page_Meta that Krux can extract.
	 */
	public function add_to_krux( $allowed_data_attributes = array() ) {
		$allowed_data_attributes[] = self::NAME;

		return $allowed_data_attributes;
	}

	/**
	 * Called by 'pmc_page_meta' filter, this function adds genres to
	 * the page meta array which is passed to krux on article pages.
	 *
	 * @return array
	 */
	public function add_to_page_meta( $meta = array() ) {
		if ( ! is_array( $meta ) || ! is_singular() ) {
			/*
			 * Something is wrong, don't mess about,
			 * return meta as is and bail out
			 */
			return $meta;
		}

		$current_post_genres = $this->_taxonomy->get_post_terms( get_post() );

		if ( ! empty( $current_post_genres ) && is_array( $current_post_genres ) ) {
			$current_post_genre_names = array_map(
				array( 'PMC', 'untexturize' ),
				array_values(
					wp_list_pluck( $current_post_genres, 'name' )
				)
			);

			if ( ! empty( $current_post_genre_names ) ) {
				$meta[ self::NAME ] = $current_post_genre_names;
			}

			unset( $current_post_genre_names );
		}

		unset( $current_post_genres );

		return $meta;
	}

	/**
	 * Called by 'pmc_robots_txt' filter, this method
	 * disallows genre archive etc URLs from being crawled by search
	 * bots by excluding them in robots.txt
	 *
	 * @param string $output robots.txt rules as multi-line string
	 * @param boolean $is_public Boolean flag to determine whether site is public or not
	 * @return string robots.txt rules as multi-line string
	 */
	public function remove_from_robots_txt( $output, $is_public ) {

		if ( ! $is_public ) {
			return $output;
		}

		$output .= sprintf( 'Disallow: /%s/', self::NAME ) . PHP_EOL;
		$output .= sprintf( 'Disallow: /%s/*', self::NAME ) . PHP_EOL;

		return $output;

	}

}	//end of class


//EOF
