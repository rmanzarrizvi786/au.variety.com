<?php

/**
 * Class PMC_Save_Post_Delayed_Tasks
 *
 * Batch tasks delayed off save_post to speed up the actual save_post process.
 *
 * When a post is saved, flag the post as 'recently modified' with a meta key
 * which is added to the post: 'pmc-recently-modified-post'.
 *
 * Then, with a recurring cron event every 10 minutes; fetch and loop
 * through the changed posts (meta_key query), calling any/all callback functions for each post.
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Save_Post_Delayed_Tasks {

	use Singleton;

	public $post_meta_key              = 'pmc_recently_modified_post';
	public $allowed_post_types = array(
		'post',
		'page',
		'pmc-gallery',  // Used by the pmc-gallery plugins enabled on most sites
		'pmc_featured', // Used by the pmc-carousel plugin enabled on most sites
	);

	/**
	 * PMC Singleton instantiation method
	 *
	 * @internal called via inherited get_instance() method
	 * @return   null
	 */
	protected function __construct() {

		// Create our 10 minute cron schedule
		add_filter( 'cron_schedules', array( $this, 'create_cron_schedule' ), 10, 1 );

		// Flag posts which have been modified recently
		add_action( 'transition_post_status', array( $this, 'flag_recently_modified_post' ), 10, 3 );

		// Create a hook to be fired by our recurring cron event
		// This callback function will run the delayed save_post tasks
		add_action( 'pmc_do_delayed_save_post_tasks', array( $this, 'do_delayed_save_post_tasks' ), 10, 0 );

		// Create the cron event which will run every 10 minutes
		// This event will run the above 'pmc_do_delayed_save_post_tasks' action
		$this->create_cron_event();

		// Allow the list of allowed post types to be filtered
		$this->allowed_post_types = apply_filters( 'pmc_save_post_delayed_tasks_allowed_post_types', $this->allowed_post_types );

		// Axe any duplicates
		$this->allowed_post_types = array_unique( $this->allowed_post_types );
	}

	/**
	 * Create a 10 minute cron interval
	 *
	 * @internal                Called via cron_schedules filter
	 * @param  array $schedules Current cron schedules
	 * @return array            The modified $schedules array
	 */
	public function create_cron_schedule( $schedules ) {

		// Add an interval for one minute
		$schedules['pmc_ten_minutes'] = array(
			'interval' => 600,
			'display'  => esc_html__( 'Once every 10 minutes', 'pmc' ),
		);

		return $schedules;
	}

	/**
	 * Create a cron event which will execute every 10 minutes
	 *
	 * @internal Called directly in this class's _init() method
	 * @return   null
	 */
	public function create_cron_event(){

		// Create an event to execute every 10 minutes
		if ( ! wp_next_scheduled( 'pmc_do_delayed_save_post_tasks' ) ) {

			wp_schedule_event(
				time() + 600, # Start the event occurrence in 10 minutes
				'pmc_ten_minutes', # And run it every 10 minutes
				'pmc_do_delayed_save_post_tasks'
			);
		}
	}

	/**
	 * Flag recently changed posts with a post meta key
	 *
	 * This logic will run for most post transitions. The only
	 * times this won't run is for auto-drafts, revisions, and attachments.
	 *
	 * @internal                     Called via transition_post_status action
	 * @param    string  $new_status The post's new status
	 * @param    string  $old_status The post's previous status
	 * @param    WP_Post $post       The post object
	 * @return   null
	 */
	public function flag_recently_modified_post( $new_status, $old_status, $post ) {

		// Don't add post id's for auto-draft or revision or attachment status
		// Only proceed if the post is or was published (so we avoid drafts)
		if ( ! in_array( 'publish', array( $new_status, $old_status ) ) )
			return;

		// Only proceed if this post's post_type is allowed
		if ( ! in_array( $post->post_type, $this->allowed_post_types, true ) )
			return;

		// Flag this post as recently-modified
		update_post_meta( $post->ID, $this->post_meta_key, true );
	}

	/**
	 * Batch execution of save_post tasks
	 *
	 * Every 10 minutes this function is called via cron.
	 *
	 * This function fetches recently modified post's ids, loops through each
	 * and performs the supplied callback functions for each post.
	 *
	 * @internal Called via pmc_do_batch_term_counting action via cron
	 * @return   null
	 */
	public function do_delayed_save_post_tasks() {

		// Only proceed when cron has triggered this method
		if ( ! defined( 'DOING_CRON' ) && ! DOING_CRON ) {
			return;
		}

		// Query for posts which have the 'pmc_recently_modified_post' meta key
		$modified_post_ids = get_posts( array(
			'posts_per_page'         => 50,
			'cache_results'          => false,
			'update_post_term_cache' => false,
			'suppress_filters'       => true,
			'fields'                 => 'ids',
			'meta_key'               => $this->post_meta_key,
			'post_type'              => $this->allowed_post_types,
		) );

		// Capture tasks via the following filter
		$tasks = apply_filters( 'pmc_delayed_save_post_tasks', array() );

		// Bail if no posts have been changed recently
		// or if there are no tasks to be run
		if ( empty( $modified_post_ids ) || empty( $tasks ) ) {
			return;
		}

		// Loop through the recently-changed posts..
		foreach ( $modified_post_ids as $post_id ) {

			// Loop through each task which needs to be executed
			foreach ( $tasks as $task ) {

				// Bail if there is no callback or the callback is invalid
				if ( empty( $task['callback'] ) || ! is_callable( $task['callback'] ) ) {
					continue;
				}

				// Pass the post id as the last param to the callback
				$task['params'][] = intval( $post_id );

				// Call the task function/method
				call_user_func_array( $task['callback'], $task['params'] );
			}

			// Remove the post meta which flags the post as recently-modified
			delete_post_meta( intval( $post_id ), $this->post_meta_key );
		}
	}
}

// Create the singleton instance of our class
PMC_Save_Post_Delayed_Tasks::get_instance();

// EOF
