<?php
/**
 * The PMC Excerpt class that handles functionality related to post excerpts.
 *
 * @author Kelin Chauhan <kelin.chauhan@rtcamp.com>
 */

namespace PMC\PMC_Excerpt;

use PMC\Global_Functions\Traits\Singleton;
use \CheezCapTextOption;
use \CheezCapDropdownOption;
use \PMC_Cheezcap;

class PMC_Excerpt {

	use Singleton;

	/**
	 * Constructor
	 *
	 * @codeCoverageIgnore.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to setup hooks.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore.
	 */
	protected function _setup_hooks() {

		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );

		// Add a global cheezcap option for limiting excerpt length.
		add_filter( 'pmc_global_cheezcap_options', array( $this, 'filter_pmc_global_cheezcap_options' ) );

	}

	/**
	 * Enqueues the main(.min).js file in admin side.
	 *
	 * @param string $hook Hook suffix for the current admin page.
	 *
	 * @codeCoverageIgnore.
	 */
	public function action_admin_enqueue_scripts( $hook ) {

		// Don't load the script unless its Add new post page or post edit page.
		if ( 'post-new.php' !== $hook && 'post.php' !== $hook ) {
			return;
		}

		$js_dir = apply_filters( 'pmc_js_folder', 'build', 'src' );

		wp_enqueue_script(
			'pmc-excerpt-main-js',
			sprintf( '%s/assets/%s/js/main.js', untrailingslashit( PMC_EXCERPT_URL ), $js_dir ),
			[ 'jquery' ]
		);

		$pmc_excerpt_config['pmc_excerpt_limit']   = PMC_Cheezcap::get_instance()->get_option( 'pmc_excerpt_limit' );
		$pmc_excerpt_config['pmc_excerpt_prevent'] = PMC_Cheezcap::get_instance()->get_option( 'pmc_excerpt_prevent_adding_chars' );

		wp_localize_script( 'pmc-excerpt-main-js', 'pmcExcerptConfig', $pmc_excerpt_config );

	}

	/**
	 * Adds Cheezcap options for post excerpt configuraion.
	 *
	 * @param string $hook Hook suffix for the current admin page.
	 */
	public function filter_pmc_global_cheezcap_options( $cheezcap_options = array() ) {

		$cheezcap_options[] = new CheezCapTextOption(
			__( 'PMC Excerpt: Post Excerpt Limit', 'pmc-excerpt' ),
			__( 'Used for limiting post excerpt length, Default is 450', 'pmc-excerpt' ),
			'pmc_excerpt_limit',
			'450',
			false,
			false
		);

		$cheezcap_options[] = new CheezCapDropdownOption(
			__( 'PMC Excerpt: Prevent Adding More Characters Than Excerpt Limit', 'pmc-excerpt' ),
			__( 'When enabled, will prevent users from inserting more characters than defined in Post Excerpt Limit option', 'pmc-excerpt' ),
			'pmc_excerpt_prevent_adding_chars',
			array( 'disable', 'enable' ),
			'disable',
			array( __( 'Disable', 'pmc-excerpt' ), __( 'Enable', 'pmc-excerpt' ) )
		);

		return $cheezcap_options;

	}

}
