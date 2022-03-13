<?php
namespace PMC\Sponsored_Posts;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Post_Options\API;

/**
 * Class Utility
 */
class Utility {

	use Singleton;

	const CACHE_KEY  = 'pmc_sponsored_posts';
	const EVENT_HOOK = 'pmc_sponsored_posts_cleanup';

	protected $_default_sponsor;
	protected $_rotator_enabled;

	/**
	 * Utility constructor.
	 */
	protected function __construct() {
		$this->_default_sponsor = __( 'Sponsored', 'pmc-sponsored-posts' );
		$this->_setup_hooks();
		$this->_schedule_event();
	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() : void {
		add_action( 'pmc_sponsored_posts_placement', [ $this, 'display_active_post' ], 10, 2 );
		add_action( self::EVENT_HOOK, [ $this, 'maybe_clean_up_old_posts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_filter( 'body_class', [ $this, 'rotator_body_class' ] );
		add_action( 'init', [ $this, 'init_rotator' ] );
	}

	/**
	 * Initialise $_rotator_enabled value
	 */
	public function init_rotator() {
		$this->_rotator_enabled = apply_filters(
			'pmc_sponsored_posts_enable_rotator',
			( 1 < count( $this->get_active_posts() ) )
		);
	}

	/**
	 * Scheduled event that rotates active sponsored post every night at midnight.
	 *
	 * @return void
	 */
	protected function _schedule_event() : void {
		if ( ! wp_next_scheduled( self::EVENT_HOOK ) ) {
			wp_schedule_event( $this->_get_time(), 'daily', self::EVENT_HOOK );
		}
	}

	/**
	 * Get midnight local time.
	 *
	 * @return string
	 */
	protected function _get_time() : string {
		$tm                = \PMC_TimeMachine::create( wp_timezone_string() );
		$time_now          = $tm->format_as( 'H-i' );
		$time_now          = explode( '-', $time_now );
		$hour              = $time_now[0];
		$minute            = $time_now[1];
		$hours_to_future   = intval( 24 - (int) $hour );
		$minutes_to_future = intval( 60 - (int) $minute );
		$hours_to_future   = ( 0 < $minutes_to_future ) ? ( $hours_to_future - 1 ) : $hours_to_future;

		return $tm->go_forth(
			sprintf(
				'%d hours %d minutes',
				$hours_to_future,
				$minutes_to_future
			)
		)
		->format_as( 'U' );
	}

	/**
	 * Get configurations for sponsored posts placements.
	 *
	 * @return array
	 */
	protected function _get_config() : array {
		$config = apply_filters( 'pmc_sponsored_posts_config', [] );

		if ( ! is_array( $config ) ) {
			return [];
		}

		return $config;
	}

	/**
	 * Add body class for post rotator if enabled.
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function rotator_body_class( array $classes ) : array {
		if ( ! empty( $this->_rotator_enabled ) ) {
			$classes[] = 'pmc-sponsored-posts-rotator';
		}

		return $classes;
	}

	/**
	 * Display template with sponsored post if one exists.
	 *
	 * @param string $context
	 * @param string $sponsored_text
	 *
	 * @return void
	 */
	public function display_active_post( string $context = '', string $sponsored_text = '' ) : void {
		// Backend will use the first sponsored post in the array.
		$sponsored_post = $this->get_active_post();
		$config         = $this->_get_config();

		if (
			empty( $sponsored_text )
			&& ! empty( $config[ $context ]['sponsored_text'] )
		) {
			$sponsored_text = $config[ $context ]['sponsored_text'];
		}

		if ( ! empty( $sponsored_post ) ) {
			$template = $this->render_active_post( $sponsored_post, $context, $sponsored_text );

			if ( ! empty( $template ) ) {
				$open_tag  = ( ! empty( $context ) ) ? '<div data-pmc-sponsored-posts="' . esc_attr( $context ) . '">' : '';
				$close_tag = ( ! empty( $context ) ) ? '</div>' : '';

				echo wp_kses_post( $open_tag . $template . $close_tag );
			}
		}
	}

	/**
	 * Render template with sponsored post if one exits.
	 *
	 * @param array  $sponsored_post
	 * @param string $context
	 * @param string $sponsored_text
	 *
	 * @return string
	 */
	public function render_active_post( array $sponsored_post, string $context = '', string $sponsored_text = '' ) : string {
		$config   = $this->_get_config();
		$template = '';

		if ( ! empty( $config[ $context ]['template'] ) ) {
			$template = $config[ $context ]['template'];
		}

		// Override any template with this filter (especially if no context is used).
		$template = apply_filters( 'pmc_sponsored_posts_template', $template, $context );

		if (
			empty( $sponsored_text )
			|| false === strpos( $sponsored_text, '%s' )
		) {
			/* translators: %s: Sponsor Name */
			$sponsored_text = __( 'Sponsored By %s', 'pmc-sponsored-posts' );
		}

		if (
			! empty( $sponsored_post['sponsor'] )
			&& $this->_default_sponsor !== $sponsored_post['sponsor']
		) {
			$sponsored_post['sponsor'] = sprintf( $sponsored_text, $sponsored_post['sponsor'] );
		}

		if (
			! empty( $template )
			&& ! empty( $sponsored_post )
		) {
			return \PMC::render_template( $template, $sponsored_post );
		}

		return '';
	}

	/**
	 * Get the slug of post option used to filter sponsored posts.
	 *
	 * @return array
	 */
	public function get_post_option() : array {
		$default = [
			'name' => __( 'Sponsored Content', 'pmc-sponsored-posts' ),
			'slug' => 'sponsored-content',
		];

		$post_option = (array) apply_filters( 'pmc_sponsored_posts_post_option', $default );

		if ( empty( $post_option['slug'] ) || empty( $post_option['name'] ) ) {
			return $default;
		}

		return $post_option;
	}

	/**
	 * Helper to see if post has the sponsored post option.
	 *
	 * @param \WP_Post|null $post
	 *
	 * @return bool
	 */
	public function is_sponsored_post( ?\WP_Post $post = null ) : bool {
		if ( empty( $post ) ) {
			$post = get_post();
		}

		if ( ! empty( $post ) ) {
			$option = $this->get_post_option();

			return (bool) API::get_instance()->post( $post )->has_option( $option['slug'] );
		}

		return false;
	}

	/**
	 * Get the currently active sponsored posts.
	 *
	 * @return array of active posts or empty array if there are no active posts.
	 */
	public function get_active_posts() : array {
		$cache               = new \PMC_Cache( self::CACHE_KEY );
		$sponsored_post_data = $cache->expires_in( 5 * MINUTE_IN_SECONDS )
			->updates_with( [ $this, 'get_active_posts_uncached' ] )
			->get();
		$active_posts        = [];

		if ( empty( $sponsored_post_data ) || ! is_array( $sponsored_post_data ) ) {
			return $active_posts;
		}

		foreach ( (array) $sponsored_post_data as $sponsored_post ) {
			if (
				$this->active_dates_contain_today(
					(string) $sponsored_post['start_date'],
					(string) $sponsored_post['end_date']
				)
			) {
				$post_data = $sponsored_post['post_data'];

				if ( ! is_array( $post_data ) ) {
					continue;
				}

				foreach ( $post_data as $data ) {
					// Get first and only post ID in array.
					$post_id = current( array_slice( $data['sponsored_post'], 0, 1 ) );
					$post    = null;

					if ( 0 < intval( $post_id ) ) {
						$post = get_post( $post_id );
					}

					$sponsor      = $data['sponsored_by'];
					$sponsor_logo = ( ! empty( $data['sponsor_logo'] ) ) ? $data['sponsor_logo'] : '';

					if ( empty( $sponsor ) ) {
						$sponsor = $this->_default_sponsor;
					}

					if ( is_a( $post, '\WP_Post' ) ) {
						$active_posts[] = [
							'post'         => $post,
							'sponsor'      => $sponsor,
							'sponsor_logo' => $sponsor_logo,
						];
					}
				}
			}
		}

		return $active_posts;
	}

	/**
	 * Get the first active post.
	 *
	 * @return array
	 */
	public function get_active_post() : array {
		$posts = $this->get_active_posts();

		return ( ! empty( $posts ) ? $posts[0] : [] );
	}

	/**
	 * Uncached function to get the active uncached sponsored post.
	 *
	 * @return array
	 */
	public function get_active_posts_uncached() : array {
		$option              = get_option( 'global_curation' );
		$sponsored_post_data = [];

		if (
			! empty( $option )
			&& is_array( $option )
			&& is_array( $option['tab_pmc_sponsored_posts'] )
			&& is_array( $option['tab_pmc_sponsored_posts']['pmc_sponsored_posts'] )
		) {
			$sponsored_post_data = $option['tab_pmc_sponsored_posts']['pmc_sponsored_posts'];
		}

		return $sponsored_post_data;
	}

	/**
	 * Get the sponsor of the current active sponsored post.
	 *
	 * @param string $start_date
	 * @param string $end_date
	 *
	 * @return bool returns whether the current date falls between a start date and an end date.
	 */
	public function active_dates_contain_today( string $start_date, string $end_date ) : bool {
		if ( empty( $start_date ) ) {
			return false;
		}

		if ( empty( $end_date ) ) {
			$end_date = $start_date;
		} elseif ( $start_date > $end_date ) {
			return false;
		}

		if (
			current_time( 'Ymd', false ) >= wp_date( 'Ymd', $start_date )
			&& current_time( 'Ymd', false ) <= wp_date( 'Ymd', $end_date )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check if active dates has a date in the future.
	 *
	 * @param string $start_date
	 * @param string $end_date
	 *
	 * @return bool returns whether the active dates have dates in future.
	 */
	public function active_dates_contain_future_dates( string $start_date, string $end_date ) : bool {
		if ( empty( $start_date ) ) {
			return false;
		}

		if ( empty( $end_date ) ) {
			$end_date = $start_date;
		}

		if ( $start_date > $end_date ) {
			return false;
		}

		if ( current_time( 'Ymd', false ) <= wp_date( 'Ymd', $end_date ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Loops through all potential sponsored post and deletes them if they have
	 * no upcoming active dates.
	 *
	 * @return void
	 */
	public function maybe_clean_up_old_posts() : void {
		$cache    = new \PMC_Cache( self::CACHE_KEY );
		$curation = get_option( 'global_curation' );

		if (
			empty( $curation['tab_pmc_sponsored_posts'] )
			|| ! is_array( $curation['tab_pmc_sponsored_posts'] )
		) {
			return;
		}

		$sponsored_post_data = $curation['tab_pmc_sponsored_posts']['pmc_sponsored_posts'];

		foreach ( $sponsored_post_data as $key => $sponsored_post ) {
			$dates_in_future = $this->active_dates_contain_future_dates(
				(string) $sponsored_post['start_date'],
				(string) $sponsored_post['end_date']
			);

			// If there are no future dates where this post is scheduled, remove it from the zone
			if ( empty( $dates_in_future ) ) {
				unset( $sponsored_post_data[ $key ] );
			}
		}

		$sponsored_post_data = array_values( $sponsored_post_data );

		$curation['tab_pmc_sponsored_posts']['pmc_sponsored_posts'] = $sponsored_post_data;

		update_option( 'global_curation', $curation );

		$cache->invalidate();
	}

	/**
	 * Enqueue JavaScript and CSS file for rotator feature.
	 *
	 * @return void
	 */
	public function enqueue_scripts() : void {
		$active_posts = $this->get_active_posts();
		$localize     = [];
		$configs      = apply_filters( 'pmc_sponsored_posts_config', [] );

		if (
			empty( $active_posts )
			|| empty( $configs )
			|| 1 >= count( $active_posts )
			|| empty( $this->_rotator_enabled )
		) {
			return;
		}

		$asset = require_once dirname( __DIR__ ) . '/assets/build/index.asset.php';

		foreach ( $active_posts as $key => $post ) {
			$localize[ $key ] = [];

			foreach ( $configs as $context => $config ) {
				$sponsored_text = ! empty( $config['sponsored_text'] ) ? $config['sponsored_text'] : '';

				$localize[ $key ][ $context ] = $this->render_active_post( $post, $context, $sponsored_text );
			}
		}

		wp_enqueue_style(
			'pmc-sponsored-posts-style',
			sprintf( '%s/assets/build/style-index.css', PMC_SPONSORED_POSTS_URL ),
			[],
			$asset['version']
		);

		wp_enqueue_script(
			'pmc-sponsored-posts-script',
			sprintf( '%s/assets/build/index.js', PMC_SPONSORED_POSTS_URL ),
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_localize_script(
			'pmc-sponsored-posts-script',
			'pmcSponsoredPosts',
			[ 'activePosts' => $localize ]
		);
	}

}
