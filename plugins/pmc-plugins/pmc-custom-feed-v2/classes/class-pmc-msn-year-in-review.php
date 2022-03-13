<?php
/**
 * This class adds functionality to admin post option year in review.
 */

namespace PMC\Custom_Feed;

use PMC\Global_Functions\Traits\Singleton;
use \PMC\Post_Options\API as Post_Options_API;

class PMC_MSN_Year_In_Review {

	use Singleton;

	/**
	 * Class Constructor.
	 */
	protected function __construct() {

		add_action( 'init', [ $this, 'add_msn_year_in_review_post_option' ] );
		add_action( 'pmc_custom_feed_start', array( $this, 'action_pmc_custom_feed_start' ), 10, 2 );

		add_filter( 'pmc_custom_feed_options_toggles', [ $this, 'add_msn_feed_option' ] );
	}


	/**
	 * Registers post option.
	 *
	 * @return void
	 */
	public function add_msn_year_in_review_post_option() : void {

		Post_Options_API::get_instance()->register_global_options(
			[
				'pmc-msn-year-in-review' => [
					'label'       => 'MSN - Year In Review',
					'description' => 'Posts with this term will add Year In Review to post title in feed.',
				],
			]
		);

	}

	/**
	 * Hook callback to add feed option.
	 *
	 * @param array $feed_options
	 * @return array
	 */
	public function add_msn_feed_option( $feed_options = [] ) : array {

		$feed_options['msn-year-in-review-title'] = 'MSN - Year In Review';

		return $feed_options;

	}


	/**
	 * Add callback to hook when feed option selected
	 *
	 * @param bool $feed
	 * @param array $feed_options
	 */
	public function action_pmc_custom_feed_start( $feed = false, $feed_options = [] ) : void {

		if ( empty( $feed_options['msn-year-in-review-title'] ) ) {
			return;
		}

		add_filter( 'the_title_rss', [ $this, 'update_post_title' ] );
	}


	/**
	 * Updates post title before displaying in feed.
	 *
	 * @param string $title
	 * @return string
	 */
	public function update_post_title( $title = '' ) : string {

		if ( has_term( 'pmc-msn-year-in-review', '_post-options', get_the_ID() ) ) {
			$title = $title . ' - Year in Review';
		}

		return $title;

	}

}

PMC_MSN_Year_In_Review::get_instance();
