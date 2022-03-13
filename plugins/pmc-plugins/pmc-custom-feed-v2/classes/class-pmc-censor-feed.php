<?php
/**
 * This class adds necessory hooks for censoring feed content.
 */
namespace PMC\Custom_Feed;

use PMC\Global_Functions\Traits\Singleton;

class Censor_Feed {

	use Singleton;

	protected function __construct() {

		add_action( 'pmc_custom_feed_start', array( $this, 'action_pmc_custom_feed_start' ), 10, 3 );
	}

	public function action_pmc_custom_feed_start( $feed, $feed_options, $template ) {

		// Hook the functions only if censor-curse-words custom feed option is selected.
		if ( ! empty( $feed_options['censor-curse-words'] ) && true === $feed_options['censor-curse-words'] ) {

			add_filter( 'pmc_custom_feed_content', array( $this, 'censor_curse_words' ), 10, 1 );
			add_filter( 'the_content', array( $this, 'censor_curse_words' ), 10, 1 );
			add_filter( 'pmc_custom_feed_censor_curse_words', array( $this, 'censor_curse_words' ), 10, 1 );
			add_filter( 'get_the_excerpt', array( $this, 'censor_curse_words' ), 10, 1 );
			add_filter( 'the_title', array( $this, 'censor_curse_words' ), 10, 1 );

		}
	}

	/**
	 * @param $content
	 *
	 * @return string
	 */
	public function censor_curse_words( $content ) {

		if ( ! empty( $content ) ) {
			$content = \PMC\Custom_Feed\Censor::get_instance()->censor_curse_words( $content );
		}

		return $content;
	}
}

Censor_Feed::get_instance();
