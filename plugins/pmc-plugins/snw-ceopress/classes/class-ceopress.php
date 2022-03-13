<?php
/**
 * CEOPress
 *
 * @package snw-ceopress
 */

namespace SNW\CEO_Press;

use \PMC\Global_Functions\Traits\Singleton;
use \SNW\Traits\CEO_Press\SNW_Posts;

class CEOPress {

	use Singleton;
	use SNW_Posts;

	private $_print_statuses;
	private $_ceo_print_statuses;
	private $_print_sections;
	private $_ceo_print_sections;
	private $_print_issues;
	private $_ceo_print_issues;

	const USER_CAPABILITY = 'manage_options';

	/**
	 * Construct Method.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * To setup actions/filters.
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		add_action( 'wp_loaded', [ $this, 'after_wp_load' ] );

	}

	/**
	 * Fires after WP load.
	 * Set up print statuses, sections, issues and syns terms.
	 */
	public function after_wp_load() {

		// Check to see if we should make a new post.
		$page       = \PMC::filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		$create_new = \PMC::filter_input( INPUT_GET, 'create_new', FILTER_SANITIZE_STRING );
		$nonce      = \PMC::filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );

		if ( 'ceo-feed' === $page && ! empty( $create_new ) && current_user_can( CEOPress::USER_CAPABILITY ) ) {
			$this->init_new_post( $nonce );
		}

		if ( 'ceo-feed' === $page ) {
			$this->set_print_statuses();
			$this->set_print_sections();
			$this->set_print_issues();

			$this->sync_terms();
		}

	}

	/**
	 * set_print_statuses
	 * gets print-status terms so we have access to them in other places
	 *
	 * @return void
	 */
	public function set_print_statuses() {

		$http_response = snw_get_remote( 'workflow/', 'GET' );

		if ( empty( $http_response ) || is_wp_error( $http_response ) || ( is_array( $http_response ) && key_exists( 'error', $http_response ) ) ) {
			return;
		}

		$terms = get_terms( [
			'taxonomy'   => 'print-status',
			'hide_empty' => false,
		] );

		$this->_print_statuses = [];

		if ( is_array( $terms ) ) {
			$this->_print_statuses = $terms;
		}

		$this->_ceo_print_statuses = $http_response;

		$only_print = [];
		foreach ( $this->_ceo_print_statuses->items as $t_item ) {
			if ( '1' === $t_item->is_print ) {
				$only_print[ (string) ( $t_item->id ) ] = $t_item;
			}
		}

		$this->_ceo_print_statuses->items = $only_print;

	}

	/**
	 * To get print status and ceo print-status.
	 *
	 * @return array
	 */
	public function get_print_statuses() {

		return [
			'wp'  => $this->_print_statuses,
			'ceo' => $this->_ceo_print_statuses,
		];

	}

	/**
	 * set_print_sections
	 * gets print-section terms so we have access to them in other places
	 */
	public function set_print_sections() {

		$http_response = snw_get_remote( 'workflow-section/', 'GET' );

		if ( empty( $http_response ) || is_wp_error( $http_response ) || ( is_array( $http_response ) && key_exists( 'error', $http_response ) ) ) {
			return;
		}

		$terms = get_terms( [
			'taxonomy'   => 'print-section',
			'hide_empty' => false,
		] );

		$this->_print_sections = [];

		if ( is_array( $terms ) ) {
			$this->_print_sections = $terms;
		}

		$this->_ceo_print_sections = $http_response;

		$only_print = [];
		foreach ( $this->_ceo_print_sections->items as $t_item ) {
			if ( '1' === $t_item->is_print ) {
				$only_print[ (string) ( $t_item->id ) ] = $t_item;
			}
		}

		$this->_ceo_print_sections->items = $only_print;

	}

	/**
	 * To get print section and ceo print section.
	 *
	 * @return array
	 */
	public function get_print_sections() {

		return [
			'wp'  => $this->_print_sections,
			'ceo' => $this->_ceo_print_sections,
		];

	}

	/**
	 * set_print_issues
	 * gets print-issue so we have access to them in other places
	 *
	 * @return void
	 */
	public function set_print_issues() {

		$http_response = snw_get_remote( 'issue/', 'GET' );

		if ( empty( $http_response ) || is_wp_error( $http_response ) || ( is_array( $http_response ) && key_exists( 'error', $http_response ) ) ) {
			return;
		}

		$this->_ceo_print_issues = $http_response;

		$active = [];
		foreach ( $this->_ceo_print_issues->items as $t_item ) {
			if ( '1' === $t_item->status ) {
				$active[ (string) ( $t_item->id ) ] = $t_item;
			}
		}

		$this->_ceo_print_issues->items = $active;

	}

	/**
	 * To get print issues and ceo print issues.
	 *
	 * @return array
	 */
	public function get_print_issues() {

		return [
			'wp'  => $this->_print_issues,
			'ceo' => $this->_ceo_print_issues,
		];

	}

	/**
	 * handle_article
	 * parse ceo article, create or update WordPress article
	 *
	 * @return int Post ID
	 */
	public function handle_article( $content, $action = false ) {

		$update = false;

		$query = [
			'posts_per_page'   => 1,
			'post_type'        => 'post',
			'post_status'      => [ 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit' ],
			'suppress_filters' => false,
			'meta_query'       => [
				[
					'key'     => sprintf( 'uuid_%s', $content->uuid ),
					'compare' => 'EXISTS',
				],
			],
		]; // WPCS: slow query ok.

		$result = $this->get_cached_posts( $query );

		if ( ! is_wp_error( $result ) && ! empty( $result[0]->ID ) ) {
			$update = true;
		}

		$local = new \DateTimeZone( 'UTC' );

		if ( ! empty( get_option( 'timezone_string' ) ) ) {
			$local = new \DateTimeZone( get_option( 'timezone_string' ) );
		} elseif ( ! empty( get_option( 'gmt_offset' ) ) ) {
			$local = new \DateTimeZone( get_option( 'gmt_offset' ) );
		}

		$dt = date_create( 'now' );
		$dt->setTimezone( $local );

		$created = $dt->format( 'Y-m-d H:i:s' );

		$wf_status_id  = $content->export->workflow_id;
		$wf_section_id = $content->export->workflow_section_id;

		// get workflow statuses from ceo
		$workflow_statuses = $this->_ceo_print_statuses;
		$workflow_status   = ( $workflow_statuses->items[ $wf_status_id ] ) ? $workflow_statuses->items[ $wf_status_id ] : '';

		// compare to the statuses we gathered on init
		foreach ( $this->_print_statuses as $t_item ) {
			if ( $t_item->slug === $workflow_status->slug ) {
				$print_status = $t_item;
				break;
			}
		}

		// get workflow sections from ceo
		$workflow_sections = $this->_ceo_print_sections;
		$workflow_section  = ( $workflow_sections->items[ $wf_section_id ] ) ? $workflow_sections->items[ $wf_section_id ] : '';

		// compare to the sections we gathered on init

		foreach ( $this->_print_sections as $t_item ) {
			if ( $t_item->slug === $workflow_section->slug ) {
				$print_section = $t_item;
				break;
			}
		}

		$uuid_key = sprintf( 'uuid_%s', $content->uuid );

		$post_array = [
			'post_content' => $content->content,
			'post_title'   => $content->title,
			'post_date'    => $created,
			'meta_input'   => [
				'pmc_print_name'     => ( isset( $content->meta->{'print-name'}->value ) ) ? $content->meta->{'print-name'}->value : '',
				'pmc_print_headline' => $content->title,
				'pmc_print_dek'      => ( isset( $content->meta->subhead->value ) ) ? $content->meta->subhead->value : '',
				'pmc_print_content'  => $content->content,
				'pmc_print_status'   => ( isset( $print_status ) ) ? $print_status->term_id : null,
				'pmc_print_section'  => ( isset( $print_section ) ) ? $print_section->term_id : null,
				'is_ceo'             => true,
				'uuid'               => $content->uuid,
				$uuid_key            => $content->uuid,
				'assignment_id'      => $content->assignment_id,
				'workflow_id'        => $content->workflow_id,
				'weight'             => $content->weight,
				'version'            => $content->export->version,
				'ceo_user'           => $content->export->user->name,
			],
		];

		// Set comment data
		$current_user = wp_get_current_user();
		$gmt          = 0;
		$time         = current_time( 'mysql', $gmt );

		if ( isset( $content->meta->{'print-notes'}->value ) ) {
			$comment = [
				'comment_author'       => esc_sql( $current_user->display_name ),
				'comment_author_email' => esc_sql( $current_user->user_email ),
				'comment_author_url'   => esc_sql( $current_user->user_url ),
				'comment_content'      => $content->meta->{'print-notes'}->value,
				'comment_type'         => 'editorial-comment',
				'comment_parent'       => 0,
				'user_id'              => intval( $current_user->ID ),
				'comment_date'         => $time,
				'comment_date_gmt'     => $time,
				// Set to -1?
				'comment_approved'     => 'editorial-comment',
			];
		}

		if ( true === $update ) {

			$result                      = $result[0];
			$post_array['ID']            = $result->ID;
			$post_array['post_modified'] = $created;

			// update the post
			wp_update_post( $post_array );

			// add post id to comment array
			$comment['comment_post_ID'] = $post_array['ID'];
			// insert comment
			wp_insert_comment( $comment );

			return $post_array['ID'];

		}

		// create new post
		$result = wp_insert_post( $post_array );
		// add post id to comment array
		$comment['comment_post_ID'] = $result;
		// insert comment and get the comments id
		wp_insert_comment( $comment );

		return $result;

	}

	/**
	 * verify nonce and check if new post needs to be created based in CEO import feed
	 * Create a new WordPress post and redirect to the posts edit view.
	 *
	 * @param $nonce string Nonce to verify action before creating new post
	 */
	public function init_new_post( $nonce ) {

		$uuid = \PMC::filter_input( INPUT_GET, 'create_new', FILTER_SANITIZE_STRING );

		// check if new post needs to be created
		if ( empty( $uuid ) || ! snw_is_uuid( $uuid ) ) {
			return;
		}

		//check the nonce
		if ( ! wp_verify_nonce( $nonce, sprintf( 'ceopress-import-%s', $uuid ) ) ) {
			return;
		}

		$output = snw_get_remote( 'content/', 'GET', $uuid );

		if ( empty( $output ) || is_wp_error( $output ) || ( is_array( $output ) && key_exists( 'error', $output ) ) ) {
			return;
		}

		$output = $output[0];

		$output->srn;

		// this should always fire, pmc is generating all content
		// from their print plugins
		if ( property_exists( $output, 'export' ) ) {
			// update from print
			snw_get_remote( 'export/push/', 'PUT', $uuid );
		}

		// get new version
		$output = snw_get_remote( 'content/', 'GET', $uuid );

		if ( empty( $output ) || is_wp_error( $output ) || ( is_array( $output ) && key_exists( 'error', $output ) ) ) {
			return;
		}

		$output = $output[0];

		// create new post
		$post_id = $this->handle_article( $output );

		// redirect to the posts edit view
		$post_edit_url = add_query_arg(
			[
				'post'   => $post_id,
				'action' => 'edit',
			],
			admin_url( 'post.php' )
		);

		snw_redirect( $post_edit_url );

	}

	/**
	 * Sync up WordPress terms with CEO feed terms ('print-status', 'print-section')
	 * Creates new WordPress terms in not available.
	 */
	public function sync_terms() {

		$status_slugs  = [];
		$section_slugs = [];
		$issue_slugs   = [];

		if ( ! empty( $this->_print_statuses ) && is_array( $this->_print_statuses ) ) {
			foreach ( $this->_print_statuses as $value ) {
				$status_slugs[] = $value->slug;
			}
		}

		if ( ! empty( $this->_print_sections ) && is_array( $this->_print_sections ) ) {
			foreach ( $this->_print_sections as $value ) {
				$section_slugs[] = $value->slug;
			}
		}

		if ( ! empty( $this->_print_issues ) && is_array( $this->_print_issues ) ) {
			foreach ( $this->_print_issues as $value ) {
				$issue_slugs[] = $value->post_name;
			}
		}

		$terms_to_create = [];
		foreach ( $this->_ceo_print_statuses->items as $item ) {
			if ( ! in_array( $item->slug, (array) $status_slugs, true ) ) {
				$terms_to_create[] = $item;
			}
		}

		foreach ( $terms_to_create as $value ) {
			$term     = $value->name;
			$taxonomy = 'print-status';

			$args = [
				'slug' => $value->slug,
			];

			wp_insert_term( $term, $taxonomy, $args );
		}

		$terms_to_create = [];
		foreach ( $this->_ceo_print_sections->items as $item ) {
			if ( ! in_array( $item->slug, (array) $section_slugs, true ) ) {
				$terms_to_create[] = $item;
			}
		}

		foreach ( $terms_to_create as $value ) {
			$term     = $value->name;
			$taxonomy = 'print-section';

			$args = [
				'slug' => $value->slug,
			];

			wp_insert_term( $term, $taxonomy, $args );
		}

	}

}

//EOF
