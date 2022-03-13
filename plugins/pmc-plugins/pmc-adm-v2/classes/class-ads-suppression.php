<?php
/**
 * Ads suppression to allow editor tag articles to exclude ads
 *
 * @package pmc-adm-v2
 *
 * @version 2021-10-14 Hau Vong REV-94
 */

namespace PMC\Adm;

use PMC\Global_Functions\Traits\Singleton;
use PMC_TimeMachine;
use WP_Term_Query;
use PMC_Cache;

/**
 * Ads Suppression
 */
class Ads_Suppression {
	use Singleton;

	/**
	 * The taxonomy being used for ads suppression
	 */
	const TAXONOMY = 'pmc_ads_suppression';

	/**
	 * The taxonomy meta key to store the schedules
	 */
	const SCHEDULE_KEY = 'pmc_schedules';

	/**
	 * The taxonomy meta key to store how the ads should behave
	 */
	const APPLY_TO_KEY = 'pmc_apply_to';

	/**
	 * The taxonomy meta key to store the tags to target
	 */
	const TARGET_TAGS_KEY = 'pmc_target_tags';

	/**
	 * The taxonomy meta key to store the linked information to the ads suppression
	 */
	const LINK_TERM_KEY = 'pmc_ads_suppression_link_term';

	/**
	 * Cache duration to check for ads suppression status
	 */
	const CACHE_DURATION = 900; // 15 minutes

	/**
	 * Default post types to support for registered taxonomy
	 * @var string[]
	 */
	protected $_post_types = [ 'post', 'page' ];

	/**
	 * Constructor for the class
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup all required web hooks
	 */
	protected function _setup_hooks() : void {
		add_action( 'init', [ $this, 'action_init' ] );
		add_filter( 'pmc_ads_suppression', [ $this, 'filter_pmc_ads_suppression' ], 10, 2 );
		add_filter( 'pmc-adm-fetch-ads', array( $this, 'filter_fetch_ads_suppression' ) );
	}

	/**
	 * The init action hook callback
	 */
	public function action_init() : void {
		// Allow override of the supported post types
		$this->_post_types = apply_filters( 'pmc_ads_suppression_post_types', $this->_post_types );

		// This must execute after post types filter above
		$this->register_ads_suppression_taxonomy();
	}

	/**
	 * Intercept and suppress all ads if suppression schedule is active on current article
	 *
	 * @param array $ad_posts
	 * @return array
	 */
	public function filter_fetch_ads_suppression( $ad_posts = [] ) {
		if ( $this->has_ads_suppression( 'all' ) ) {
			return [];
		}
		return $ad_posts;
	}

	/**
	 * Register the taxonomy
	 */
	public function register_ads_suppression_taxonomy() : void {
		$args = [
			'description'        => 'Ads Suppression',
			'hierarchical'       => false,
			'labels'             => [
				'name'               => __( 'Ads Suppression', 'pmc-adm' ),
				'singular_name'      => __( 'Ads Suppression', 'pmc-adm' ),
				'add_new_item'       => __( 'Add New Ads Suppression', 'pmc-adm' ),
				'edit_item'          => __( 'Edit Ads Suppression', 'pmc-adm' ),
				'new_item'           => __( 'New Ads Suppression', 'pmc-adm' ),
				'view_item'          => __( 'View Ads Suppression', 'pmc-adm' ),
				'search_items'       => __( 'Search Ads Suppression', 'pmc-adm' ),
				'not_found'          => __( 'No Ads Suppression found.', 'pmc-adm' ),
				'not_found_in_trash' => __( 'No Ads Suppression found in Trash.', 'pmc-adm' ),
				'all_items'          => __( 'Ads Suppression', 'pmc-adm' ),
			],
			'publicly_queryable' => false,
			'query_var'          => false,
			'rewrite'            => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'show_in_nav_menus'  => false,
			'show_in_rest'       => false,
		];

		register_taxonomy( self::TAXONOMY, $this->_post_types, $args );
	}

	/**
	 * Helper function to check if a given term has the ads schedule and is active
	 * @param $term_id
	 * @return bool
	 */
	public function term_has_ads_suppression( $term_id, $check_apply_to = null ) {
		$schedules = get_term_meta( $term_id, self::SCHEDULE_KEY, true );
		if ( $this->has_active_schedule( (array) $schedules ) ) {
			if ( empty( $check_apply_to ) ) {
				return true;
			}

			$apply_to = get_term_meta( $term_id, self::APPLY_TO_KEY, true );
			if ( empty( $apply_to ) ) {
				return true;
			}

			foreach ( (array) $check_apply_to as $key ) {
				if ( in_array( $key, (array) $apply_to, true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param bool $status
	 * @param string|array $check_apply_to
	 * @return bool
	 */
	public function filter_pmc_ads_suppression( $status, $check_apply_to = null ) {
		return $this->has_ads_suppression( $check_apply_to );
	}

	/**
	 * Function use for PMC Cache, do not call directly
	 * Return true if current queried post contains the ads suppression taxonomy with active schedules
	 *
	 * @param int $post_id          The post id.
	 * @param array $check_apply_to List of ads suppression type to check
	 * @return bool
	 */
	public function has_ads_suppression_uncache( $post_id, $check_apply_to ) : bool {

		$terms = get_the_terms( $post_id, self::TAXONOMY );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( (array) $terms as $term ) {
				if ( $this->term_has_ads_suppression( $term->term_id, $check_apply_to ) ) {
					return true;
				}
			}
		}

		$terms = get_the_terms( $post_id, 'post_tag' );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( (array) $terms as $term ) {
				$link_ids = get_term_meta( $term->term_id, self::LINK_TERM_KEY, false );
				if ( ! empty( $link_ids ) && is_array( $link_ids ) ) {
					foreach ( $link_ids as $id ) {
						if ( $this->term_has_ads_suppression( $id, $check_apply_to ) ) {
							return true;
						}
					}
				}
			}
		}

		return  false;
	}
	/**
	 * Return true if current queried post contains the ads suppression taxonomy with active schedules
	 *
	 * @param string $check_apply_to
	 * @return bool
	 */
	public function has_ads_suppression( $check_apply_to = null ) : bool {

		if ( is_singular() ) {
			$queried_object_id = get_queried_object_id();

			// queried object id should never be empty if is_singular is true, just double checking
			if ( ! empty( $queried_object_id ) ) {
				if ( is_array( $check_apply_to ) ) {
					sort( $check_apply_to );
				}
				$cache = new PMC_Cache( wp_json_encode( [ $queried_object_id, $check_apply_to ] ) );

				return $cache->expires_in( self::CACHE_DURATION )
					->updates_with( [ $this, 'has_ads_suppression_uncache' ], [ $queried_object_id, $check_apply_to ] )
					->get();
			}
		}

		return false;
	}

	/**
	 * Helper function to get the start | end date of the schedule
	 *
	 * @param array $schedule  The schedule array: [ 'start' => 'yyyy-mm-dd hh:mm', 'end' => 'yyyy-mm-dd hh:mm' ]
	 * @param string $key      The key to get the rime from: start | end
	 * @param string $timezone
	 * @return string | null
	 */
	private function _get_schedule_time( array $schedule, string $key, string $timezone ) {
		if ( ! empty( $schedule[ $key ] ) ) {
			$format = PMC_TimeMachine::create( $timezone )->from_time( 'Y-m-d H:i', $schedule[ $key ] )->format_as( 'U' );
			return ! empty( $format ) ? $format : null;
		}
		return '';
	}

	/**
	 * Return true if the schedule entry is active
	 *
	 * @param array $schedule The schedule array: [ 'start' => 'yyyy-mm-dd hh:mm', 'end' => 'yyyy-mm-dd hh:mm' ]
	 * @return bool
	 */
	public function is_active_schedule( array $schedule ) : bool {

		if ( ! empty( $schedule['start'] ) ) {
			if ( ! empty( $schedule['timezone'] ) ) {
				$timezone = $schedule['timezone'];
			} else {
				$timezone = PMC_TimeMachine::get_site_timezone();
			}

			$now        = PMC_TimeMachine::create( $timezone )->format_as( 'U' );
			$start_time = $this->_get_schedule_time( $schedule, 'start', $timezone );
			$end_time   = $this->_get_schedule_time( $schedule, 'end', $timezone );

			if ( isset( $start_time ) && isset( $end_time )
				&& $start_time <= $now
				&& ( empty( $end_time ) || $now < $end_time ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check the schedules to see if the start & end date are within current date/time range
	 *
	 * @param array $schedules The schedule in the format of [ [ 'start' => 'yyyy-mm-dd hh:mm', 'end' => 'yyyy-mm-dd hh:mm' ], [...], ... ]
	 * @return bool
	 */
	public function has_active_schedule( array $schedules ) : bool {

		if ( empty( $schedules ) ) {
			return false;
		}

		$schedules = $this->validate_schedules( $schedules );

		foreach ( $schedules as $schedule ) {
			if ( empty( $schedule ) || ! is_array( $schedule ) || empty( $schedule['start'] ) ) {
				continue;
			}

			if ( $this->is_active_schedule( $schedule ) ) {
				return true;
			}

		}

		return false;
	}

	/**
	 * Delete the schedule entry from the taxonomy and un-associated from all articles
	 *
	 * @param int $term_id
	 * @return array|bool|int|\WP_Error|\WP_Term|null
	 */
	public function delete( int $term_id ) {
		if ( ! empty( $term_id ) ) {
			$this->unlink_target_tags(
				$term_id,
				$this->_to_tag_ids( (array) get_term_meta( $term_id, self::TARGET_TAGS_KEY, true ) )
			);

			return wp_delete_term( $term_id, self::TAXONOMY );
		}
		return false;
	}

	/**
	 * Get the schedule entry from the given $term_id
	 *
	 * @param int $term_id
	 * @return array
	 */
	public function get( int $term_id ) : array {
		$data = [];
		if ( ! empty( $term_id ) ) {
			$term = get_term( $term_id, self::TAXONOMY );
			if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
				$data = [
					'id'          => $term->term_id,
					'count'       => $term->count,
					'name'        => $term->name,
					'description' => $term->description,
					'schedules'   => get_term_meta( $term->term_id, self::SCHEDULE_KEY, true ),
					'apply_to'    => get_term_meta( $term->term_id, self::APPLY_TO_KEY, true ),
					'target_tags' => $this->_to_tag_names( (array) get_term_meta( $term->term_id, self::TARGET_TAGS_KEY, true ) ),
				];
			}
		}
		return $data;
	}

	/**
	 * Translate array of tag ids into tag names
	 *
	 * @param array $tags
	 * @return array
	 */
	private function _to_tag_names( array $tags ) : array {
		$tag_names = [];
		foreach ( $tags as $tag ) {
			if ( empty( $tag ) ) {
				continue;
			}
			if ( is_numeric( $tag ) ) {
				$term = get_term( $tag, 'post_tag' );
				if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
					$tag_names[] = $term->name;
				}
			} else {
				$tag_names[] = $tag;
			}
		}
		$tag_names = array_unique( (array) $tag_names );
		sort( $tag_names );
		return $tag_names;
	}

	/**
	 * Translate array of tag names/sluges into tag ids
	 *
	 * @param array $tags
	 * @return array
	 */
	private function _to_tag_ids( array $tags ) : array {
		$tag_ids = [];
		foreach ( $tags as $tag ) {
			if ( empty( $tag ) ) {
				continue;
			}
			if ( is_numeric( $tag ) ) {
				$tag_ids[] = $tag;
			} else {
				$terms   = $this->_get_terms( $tag, 'post_tag', false );
				$tag_ids = array_merge( $tag_ids, wp_list_pluck( $terms, 'term_id' ) );
			}
		}
		$tag_ids = array_unique( (array) $tag_ids );
		sort( $tag_ids );
		return $tag_ids;
	}

	/**
	 * Save the data entry
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function save( array $data ) : int {
		$args = [];

		foreach ( [ 'name', 'description' ] as $key ) {
			if ( ! empty( $data[ $key ] ) ) {
				$args[ $key ] = $data[ $key ];
			}
		}

		if ( ! empty( $data['id'] ) ) {
			$result = wp_update_term( $data['id'], self::TAXONOMY, $args );
		} elseif ( ! empty( $args['name'] ) ) {
			$result = wp_insert_term( $args['name'], self::TAXONOMY, $args );
		}

		if ( empty( $result ) || is_wp_error( $result ) ) {
			return false;
		}

		$term_id = intval( $result['term_id'] );

		if ( ! empty( $data['schedules'] ) ) {
			$schedules = $this->validate_schedules( $data['schedules'] );
			$result    = update_term_meta( $term_id, self::SCHEDULE_KEY, $schedules );
		}

		if ( ! empty( $data['apply_to'] ) && ! is_wp_error( $result ) ) {
			$apply_to = (array) ( $data['apply_to'] );
			$result   = update_term_meta( $term_id, self::APPLY_TO_KEY, $apply_to );
		}

		if ( ! empty( $data['target_tags'] ) && ! is_wp_error( $result ) ) {
			$target_tags = (array) ( $data['target_tags'] );
			$result      = $this->update_target_tags( $term_id, $target_tags );
		}

		return ( ! empty( $result ) && ! is_wp_error( $result ) ) ? $term_id : 0;
	}

	/**
	 * @param int $term_id         Term ID.
	 * @param array $target_tags   Array of tags
	 * @return bool|int|\WP_Error
	 */
	public function update_target_tags( int $term_id, array $target_tags ) {

		$target_tag_ids = $this->_to_tag_ids( (array) $target_tags );

		$old_tag_ids = $this->_to_tag_ids( (array) get_term_meta( $term_id, self::TARGET_TAGS_KEY, true ) );

		// Unlink target tags that have been removed
		$unlink_tag_ids = array_diff( (array) $old_tag_ids, $target_tag_ids );
		$this->unlink_target_tags( $term_id, $unlink_tag_ids );

		// Link new tags that have been added
		$link_tag_ids = array_diff( $target_tag_ids, (array) $old_tag_ids );
		$this->link_target_tags( $term_id, $link_tag_ids );

		$result = update_term_meta( $term_id, self::TARGET_TAGS_KEY, $target_tag_ids );
		return ! is_wp_error( $result );
	}

	/**
	 * Helper function to look up post tag by name/slug, support duplicates lookup
	 *
	 * @param string $tag               The tag to lookup
	 * @param boolean $create_if_exists if true, create new tag if tag not found
	 * @return array
	 */
	private function _get_terms( $tag, $taxonomy, $create_if_exists = false ) : array {

		$defaults = [
			'get'                    => 'all',
			'number'                 => 1,
			'taxonomy'               => $taxonomy,
			'update_term_meta_cache' => false,
			'orderby'                => 'none',
			'suppress_filter'        => true,
		];

		$args  = array_merge( $defaults, [ 'name' => $tag ] );
		$terms = get_terms( $args );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			$args  = array_merge( $defaults, [ 'slug' => $tag ] );
			$terms = get_terms( $args );
		}

		if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
			// Complicated to trigger this code due to $terms always return empty array if failed
			$terms = []; // @codeCoverageIgnore
		}

		if ( empty( $terms ) && $create_if_exists ) {
			$result = wp_insert_term( $tag, $taxonomy );
			if ( is_array( $result ) ) {
				$terms[] = get_term( $result['term_id'] );
			}
		}

		return $terms;
	}

	/**
	 * Unlink the target tags from ads suppression
	 *
	 * @param int $term_id The ads suppression term id
	 * @param array $tags  The array of post tags to unlink
	 */
	public function unlink_target_tags( $term_id, array $tags ) {
		foreach ( $tags as $tag ) {
			if ( is_numeric( $tag ) ) {
				delete_term_meta( $tag, self::LINK_TERM_KEY, $term_id );
			} else {
				$terms = $this->_get_terms( $tag, 'post_tag', false );
				if ( ! empty( $terms ) ) {
					foreach ( $terms as $term ) {
						delete_term_meta( $term->term_id, self::LINK_TERM_KEY, $term_id );
					}
				}
			}
		}
	}

	/**
	 * Link the target tags to ads suppression
	 *
	 * @param int $term_id The ads suppression term id
	 * @param array $tags  The array of post tags to link
	 * @retun array
	 */
	public function link_target_tags( $term_id, array $tags ) : array {
		$found_tags = [];
		foreach ( $tags as $tag ) {
			$link_ids = [];
			if ( is_numeric( $tag ) ) {
				$link_ids[] = $tag;
			} else {
				$terms    = $this->_get_terms( $tag, 'post_tag', false );
				$link_ids = array_merge( $link_ids, wp_list_pluck( $terms, 'term_id' ) );
			}
			foreach ( $link_ids as $id ) {
				$links = get_term_meta( $id, self::LINK_TERM_KEY, false );
				if ( empty( $links ) ) {
					$links = [];
				}
				if ( ! in_array( $id, (array) $links, false ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.FoundNonStrictFalse
					add_term_meta( $id, self::LINK_TERM_KEY, $term_id );
				}
				$found_tags[] = $id;
			}
		}

		return $found_tags;
	}

	/**
	 * Make sure the schedules array is in multi schedule format
	 *
	 * @param array $schedules
	 * @return array|array[]
	 */
	public function validate_schedules( array $schedules ) : array {
		if ( isset( $schedules['start'] ) ) {
			$schedules = [ $schedules ];
		}
		return $schedules;
	}

	/**
	 * Query the ads suppression entries
	 *
	 * @param array $args
	 * @return array
	 */
	public function query( array $args ) : array {

		$term_query = new WP_Term_Query();

		$args = wp_parse_args(
			$args,
			[
				'page'      => 1,
				'page_size' => 25,
			] 
		);

		$query_args = [
			'taxonomy'        => self::TAXONOMY,
			'hide_empty'      => false,
			'suppress_filter' => true,
		];

		if ( ! empty( $args['search'] ) ) {
			$query_args['name__like'] = $args['search'];
		}

		$count = $term_query->query( wp_parse_args( $query_args, [ 'fields' => 'count' ] ) );

		$query_args['number'] = $args['page_size'];
		$query_args['offset'] = ( $args['page'] - 1 ) * $args['page_size'];

		$items = [];

		$terms = $term_query->query( $query_args );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$items[] = $this->get( $term->term_id );
			}
		}

		return [
			'total' => (int) $count,
			'items' => $items,
		];
	}

}
