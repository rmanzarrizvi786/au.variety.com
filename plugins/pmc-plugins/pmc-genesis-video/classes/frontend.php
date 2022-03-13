<?php
namespace PMC\Genesis_Video;

use \PMC;
use PMC\Global_Functions\Traits\Singleton;
use \PMC_Inject_Content;

class Frontend {

	use Singleton;
	/**
	 * Initialize class
	 *
	 * @since 2016-05-26
	 *
	 * @version 2016-05-26 Archana Mandhare PMCVIP-1636
	 *
	 *
	 */
	protected function __construct() {
		PMC_Inject_Content::get_instance()->register_post_type( array( 'post' ) );
		$this->_setup_hooks();
	}

	/**
	 * Setup all the hooks that are needed
	 *
	 * @since 2016-05-26
	 *
	 * @version 2016-05-26 Archana Mandhare PMCVIP-1636
	 *
	 * @codeCoverageIgnore
	 */
	private function _setup_hooks() {

		add_action( 'pmc_tags_head', array( $this, 'action_genesis_script_in_head' ) );
		add_filter( 'pmc_inject_content_paragraphs', array( $this, 'filter_pmc_inject_content_paragraphs' ), 11, 3 );
		add_filter( 'the_content', array( $this, 'filter_the_content' ) );

	}

	/**
	 * Add the script tag to the head section of HTML
	 *
	 * @since 2016-05-26
	 *
	 * @version 2016-05-26 Archana Mandhare PMCVIP-1636
	 *
	 *
	 */
	public function action_genesis_script_in_head() {


		// Genesis is disable over https
		// see https://penskemediacorp.atlassian.net/browse/PMCVIP-1636
		if ( PMC::is_https() ) {
			return;
		}

		if ( ! is_single() ) {
			return;
		}

		echo '<script async src="//adg.bzgint.com/pub/adg/data.js"></script>' . PHP_EOL;

	}

	/**
	 * Filter to inject content into the article after the first paragraph and after the 3rd paragraph
	 *
	 * @since 2016-05-26
	 *
	 * @version 2016-05-26 Archana Mandhare PMCVIP-1636
	 *
	 * @return array
	 *
	 */
	public function filter_pmc_inject_content_paragraphs( $paragraphs ) {
		global $post;

		// Genesis is disable over https
		// see https://penskemediacorp.atlassian.net/browse/PMCVIP-1636
		if ( PMC::is_https() ) {
			return $paragraphs;
		}

		if ( ! is_single( $post->ID ) ) {
			return $paragraphs;
		}

		// We want to only inject these tags when they are enabled.
		$pmc_genesis_ad_position_one = cheezcap_get_option( "pmc_genesis_ad_position_one", false );
		$pmc_genesis_ad_position_two = cheezcap_get_option( "pmc_genesis_ad_position_two", false );


		if( $pmc_genesis_ad_position_one == 1 ){
			$paragraphs[2][] = '<span id="adg_main"></span>';
		}
		if( $pmc_genesis_ad_position_two == 1 ){
			$paragraphs[3][] = '<span id="adg_main_middle"></span>';
		}


		return $paragraphs;
	}

	/**
	 * Add div placement at the end of the article content.
	 * @since 2016-05-26
	 *
	 * @version 2016-05-26 Archana Mandhare PMCVIP-1636
	 *
	 * @return string
	 */
	public function filter_the_content( $content ) {

		$content = $content . '<div id="adg_main_bottom"></div>';

		return $content;

	}

}
