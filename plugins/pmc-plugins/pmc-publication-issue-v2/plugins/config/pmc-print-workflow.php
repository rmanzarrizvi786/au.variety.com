<?php
/**
 * Config class for PMC Print Workflow plugin.
 * If PMC Print Workflow plugin is in use with this plugin then it will
 * automatically be configured for use with this plugin as per configuration
 * set in here.
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since 2015-04-07 Amit Gupta
 */

use \PMC\Publication_Issue_V2\Publication_Issue;
use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class PMC_Publication_Issue_PMC_Print_Workflow
 */
class PMC_Publication_Issue_PMC_Print_Workflow {

	use Singleton;

	protected function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		/*
		 * Filters
		 */
		add_filter( 'pmc_print_workflow_publication_taxonomy', array( $this, 'set_publication_taxonomy' ) );
		add_filter( 'pmc_print_workflow_issue_type', array( $this, 'set_issue_type' ) );
		add_filter( 'pmc_print_workflow_get_issues_args', array( $this, 'remove_issue_meta_query' ) );
	}

	/**
	 * Called by 'pmc_print_workflow_publication_taxonomy' filter, this function
	 * sets the taxonomy which is used to mark publication of issues. This should
	 * not be called directly.
	 *
	 * @param string $taxonomy
	 * @return string Taxonomy which is to be used to mark publication of issues
	 */
	public function set_publication_taxonomy( $taxonomy = '' ) {
		return Publication_Issue::PUB_TAXONOMY;
	}

	/**
	 * Called by 'pmc_print_workflow_issue_type' filter, this function
	 * sets the post type which is used for issues. This should
	 * not be called directly.
	 *
	 * @param string $post_type
	 * @return string Post type which is used for issues
	 */
	public function set_issue_type( $post_type = '' ) {
		return Publication_Issue::POST_TYPE;
	}

	/**
	 * Called by 'pmc_print_workflow_get_issues_args' filter, this function
	 * removes the meta key index from the query args used to fetch issue type
	 * posts by pmc-print-workflow plugin. This should not be called directly.
	 *
	 * @param array $args
	 * @return array Query args without the 'meta_key' index
	 */
	public function remove_issue_meta_query( $args ) {
		if ( ! is_array( $args ) || ! array_key_exists( 'meta_key', $args ) ) {
			return $args;
		}

		unset( $args['meta_key'] );

		return $args;
	}

}

/**
 * Class initialization
 */
PMC_Publication_Issue_PMC_Print_Workflow::get_instance();

//EOF
