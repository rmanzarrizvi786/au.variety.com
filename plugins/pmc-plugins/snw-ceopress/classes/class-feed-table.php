<?php
/**
 * Create a new table class that will extend the WP_List_Table
 */

namespace SNW\CEO_Press;
use \SNW\Traits\CEO_Press\SNW_Posts;

class Feed_Table extends \WP_List_Table {

	use SNW_Posts;

	/**
	 * Prepare the items for the table to process
	 *
	 * @return void
	 */
	public function prepare_items() {
		$s = \PMC::filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );
		$k = \PMC::filter_input( INPUT_GET, 'k', FILTER_SANITIZE_STRING );

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$data = $this->_table_data();
		usort( $data, [ &$this, '_sort_data' ] );

		if ( ! empty( $s ) ) {

			$new_array = array_filter( $data, function( $obj ) use ( $s ) {

				if ( isset( $obj->title ) && stripos( $obj->title, $s ) ) {
					return true;
				}

				if ( isset( $obj->slug ) && stripos( $obj->slug, $s ) ) {
					return true;
				}

				return false;

			} );

			$data = $new_array;

		}

		if ( ! empty( $k ) ) {

			$new_array = array_filter( $data, function( $obj ) use ( $k ) {

				$filter = \PMC::filter_input( INPUT_GET, 'v', FILTER_SANITIZE_STRING );

				if ( isset( $obj->$k ) && $obj->$k === $filter ) {
					return true;
				}

				return false;

			} );

			$data = $new_array;

		}

		$per_page     = 12;
		$current_page = $this->get_pagenum();
		$total_items  = count( $data );

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
		] );

		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->_column_headers = [ $columns, $hidden, $sortable ];
		$this->items           = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return array
	 */
	public function get_columns() {

		return [
			'in_sync'        => 'WP',
			'title'          => 'Title',
			'print_status'   => 'Status',
			'print_section'  => 'Section',
			'print_issue'    => 'Issue',
			'modified_at'    => 'Modified',
			'print_version'  => 'Print Version',
			'import_version' => 'Import Version',
		];

	}

	/**
	 * Define which columns are hidden
	 *
	 * @return array
	 */
	public function get_hidden_columns() {
		return [];
	}

	/**
	 * Define the sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {

		return [
			'in_sync'       => [ 'in_sync', false ],
			'title'         => [ 'title', false ],
			'print_status'  => [ 'print_status', false ],
			'print_section' => [ 'print_section', false ],
			'print_issue'   => [ 'print_issue', false ],
			'modified_at'   => [ 'modified_at', false ],
		];

	}

	/**
	 * Get the table data
	 *
	 * @return array
	 */
	private function _table_data() {

		// snw_ceopress has stored statuses and sections
		$snw_ceopress = CEOPress::get_instance();

		$workflow_statuses = $snw_ceopress->get_print_statuses()['ceo'];
		$workflow_sections = $snw_ceopress->get_print_sections()['ceo'];
		$workflow_issues   = $snw_ceopress->get_print_issues()['ceo'];

		// Generate feed.
		$feed = snw_get_remote( 'content/', 'GET' );

		if ( empty( $feed ) || is_wp_error( $feed ) || ( is_array( $feed ) && key_exists( 'error', $feed ) ) ) {
			return [];
		}

		foreach ( $feed->items as $key => $value ) {

			if ( 'article' !== $value->type ) {
				unset( $feed->items[ $key ] );
				continue;
			}

			$query = [
				'posts_per_page'   => 1,
				'post_type'        => 'post',
				'post_status'      => [ 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit' ],
				'suppress_filters' => false,
				'meta_query'       => [
					[
						'key'     => sprintf( 'uuid_%s', $value->uuid ),
						'compare' => 'EXISTS',
					],
				],
			]; // WPCS: slow query ok.

			$result = $this->get_cached_posts( $query );

			if ( isset( $result[0]->ID ) ) {
				$result = $result[0];
			}

			$wf_status_id  = $value->export->workflow_id;
			$wf_section_id = $value->export->workflow_section_id;
			$wf_issue_id   = $value->export->issue_id;

			if ( $workflow_statuses->items[ $wf_status_id ] ) {
				$value->print_status      = $workflow_statuses->items[ $wf_status_id ]->name;
				$value->print_status_slug = $workflow_statuses->items[ $wf_status_id ]->slug;
			}

			if ( $workflow_sections->items[ $wf_section_id ] ) {
				$value->print_section      = $workflow_sections->items[ $wf_section_id ]->name;
				$value->print_section_slug = $workflow_sections->items[ $wf_section_id ]->slug;
			}

			if ( $workflow_issues->items[ $wf_issue_id ] ) {
				$value->print_issue      = $workflow_issues->items[ $wf_issue_id ]->label;
				$value->print_issue_slug = sanitize_title( $workflow_issues->items[ $wf_issue_id ]->decorated_label );
			}

			$meta             = get_post_meta( $result->ID );
			$imported_version = null;

			if ( isset( $meta['version'][0] ) ) {
				$imported_version = $meta['version'][0];
			}

			$in_sync = ( $imported_version === $value->export->version );

			$export_modified_at = $value->export->modified_at;

			$value = (array) $value;

			// We need to set some data for display and to make filtering easier.
			$value['wp_id']          = ( isset( $result->ID ) ) ? $result->ID : null;
			$value['in_sync']        = $in_sync;
			$value['import_version'] = $imported_version;
			$value['modified_at']    = $export_modified_at;

			$value = (object) $value;

			$feed->items[ $key ] = $value;

		}

		return $feed->items;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  array $item        Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {

			case 'title':
			case 'slug':
			case 'print_status':
			case 'print_section':
			case 'print_issue':
				return ( ! empty( $item->{$column_name} ) ) ? $item->{$column_name} : '';

			case 'in_sync':
				return ( $item->in_sync ) ? '<span class="dashicons dashicons-yes"></span>' : '';

			case 'modified_at':
				$time = strtotime( $item->modified_at );
				return date( 'Y/m/d', $time );

			case 'print_version':
				return $item->export->version;

			case 'import_version':
				return ( $item->import_version ) ? $item->import_version : '-';

			default:
				return '';

		}

	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @param  string $which
	 *
	 * @return void
	 */
	function display_tablenav( $which ) {

		\PMC::render_template(
			sprintf( '%s/templates/template-tablenav.php', SNW_CEO_DIR ),
			[
				'table' => $this,
				'which' => $which,
			],
			true
		);

	}

	/**
	 * Extra Filters to be displayed between bulk actions and pagination
	 * Overrides the extra_tablenav of \WP_List_Table
	 *
	 * @param string $which
	 */
	function extra_tablenav( $which ) {

		if ( empty( $which ) ) {
			return;
		}

		// snw_ceopress has stored statuses and sections
		$snw_ceopress = CEOPress::get_instance();

		if ( 'top' === $which ) {
			// we need the statuses and sections to create select options
			$print_statuses = $snw_ceopress->get_print_statuses()['ceo'];
			$print_sections = $snw_ceopress->get_print_sections()['ceo'];
			$print_issues   = $snw_ceopress->get_print_issues()['ceo'];

			// get the slug if there is one
			$slug = \PMC::filter_input( INPUT_GET, 'v', FILTER_SANITIZE_STRING );

			// get the search string if there is one
			$search = \PMC::filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );

			\PMC::render_template(
				sprintf( '%s/templates/template-filters.php', SNW_CEO_DIR ),
				[
					'print_statuses' => $print_statuses,
					'print_sections' => $print_sections,
					'print_issues'   => $print_issues,
					'slug'           => $slug,
					'search'         => $search,
				],
				true
			);
		}

	}

	/**
	 * To get Column Title
	 *
	 * @param  object $item
	 *
	 * @return string
	 */
	function column_title( $item ) {

		$title = ( $item->title ) ? sprintf( '<span class="titletext">%s</span><br>%s', esc_html( $item->title ), esc_html( $item->slug ) ) : esc_html( $item->slug );

		$nonce = false;
		if ( current_user_can( CEOPress::USER_CAPABILITY ) ) {
			$nonce = wp_create_nonce( sprintf( 'ceopress-import-%s', $item->uuid ) );
		}

		$import_url = esc_url( add_query_arg(
			[
				'page'       => 'ceo-feed',
				'create_new' => $item->uuid,
				'_wpnonce'   => $nonce,
			],
			admin_url( 'tools.php' )
		) );

		$edit_url = esc_url( add_query_arg(
			[
				'post'   => $item->wp_id,
				'action' => 'edit',
			],
			admin_url( 'post.php' )
		) );

		$actions = [

			'a' => [
				'edit' => sprintf( '<a href="%s">%s</a>', $edit_url, esc_html__( 'Edit', 'snw-ceopress' ) ),
			],

			'b' => [
				'edit'   => sprintf( '<a href="%s">%s</a>', $edit_url, esc_html__( 'Edit', 'snw-ceopress' ) ),
				'import' => sprintf( '<a href="%s">%s</a>', $import_url, esc_html__( 'Import', 'snw-ceopress' ) ),
			],

			'c' => [
				'import' => sprintf( '<a href="%s">%s</a>', $import_url, esc_html__( 'Import', 'snw-ceopress' ) ),
			],

		];

		//Return the title contents
		if ( $item->in_sync ) {

			return sprintf('%1$s %2$s',
				/*$1%s*/ $title,
				/*$2%s*/ $this->row_actions( $actions['a'] )
			);

		} elseif ( isset( $item->import_version ) ) {

			return sprintf('%1$s %2$s',
				/*$1%s*/ $title,
				/*$2%s*/ $this->row_actions( $actions['b'] )
			);

		}

		return sprintf('%1$s %2$s',
			/*$1%s*/ $title,
			/*$2%s*/ $this->row_actions( $actions['c'] )
		);

	}

	/**
	 * To render No items text
	 */
	public function no_items() {

		printf(
			/* translators: %s - Theme settings anchor tag */
			wp_kses_post( __( 'No items found. Please go to %s and setup the CEO API connection.', 'snw-ceopress' ) ),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( admin_url( 'admin.php?page=theme#cap_ceo-api-settings' ) ),
				esc_html__( 'Theme Settings', 'snw-ceopress' )
			)
		);

	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @return Mixed
	 */
	private function _sort_data( $a, $b ) {

		// If orderby is set, use this as the sort column
		$orderby = \PMC::filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );
		$orderby = ( ! empty( $orderby ) ) ? $orderby : 'modified_at';

		// If order is set use this as the order
		$order = \PMC::filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING );
		$order = ( ! empty( $order ) ) ? $order : 'desc';

		$result = strcmp( $a->$orderby, $b->$orderby );

		if ( 'asc' === $order ) {
			return $result;
		}

		return -$result;

	}

}


//EOF
