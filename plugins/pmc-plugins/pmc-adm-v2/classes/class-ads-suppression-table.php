<?php
namespace PMC\Adm;

use PMC\Global_Functions\Nonce;
use WP_List_Table;

class Ads_Suppression_Table extends WP_List_Table {

	public $nonce;
	public $search;

	/**
	 * Constructor
	 *
	 * @param Nonce $nonce
	 * @param string $search
	 */
	function __construct( Nonce $nonce, $search = null ) {
		parent::__construct(
			[
				'singular' => __( 'Ads Suppression Schedule', 'pmc-adm' ),
				'plural'   => __( 'Ads Suppression Schedules', 'pmc-adm' ),
				'ajax'     => false,
			] 
		);
		$this->nonce  = $nonce;
		$this->search = $search;
	}

	/**
	 * Return a list of columns for display
	 *
	 * @return array
	 */
	public function get_columns() : array {
		return [
			'name'  => __( 'Name', 'pmc-adm' ),
			'start' => __( 'Start', 'pmc-adm' ),
			'end'   => __( 'End', 'pmc-adm' ),
			'count' => __( 'Article Count', 'pmc-adm' ),
		];
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @return void
	 */
	public function prepare_items() : void {

		$this->_column_headers = $this->get_column_info();
		$per_page              = $this->get_items_per_page( 'pmc_ads_suppression_per_page', 25 );

		$result = Ads_Suppression::get_instance()->query(
			[
				'page'      => $this->get_pagenum(),
				'page_size' => $per_page,
				'search'    => $this->search,
			]
		);

		$this->set_pagination_args(
			[
				'total_items' => $result['total'],
				'per_page'    => $per_page,
			]
		);

		$this->items = [];
		foreach ( $result['items']  as $data ) {
			$schedule = [];
			if ( ! empty( $data['schedules'] ) ) {
				$schedule = reset( $data['schedules'] );
			}

			$schedule = wp_parse_args(
				$schedule,
				[
					'start' => 'Never',
					'end'   => 'Never',
				] 
			);

			$this->items[] = [
				'id'    => $data['id'],
				'name'  => $data['name'],
				'start' => ! empty( $schedule['start'] ) ? $schedule['start'] : 'Never',
				'end'   => ! empty( $schedule['end'] ) ? $schedule['end'] : 'Never',
				'count' => $data['count'],
			];
		};
	}

	/**
	 * Return the column default to display
	 *
	 * @param object|array $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ] ?? '';
	}

	/**
	 * Return the column's label text
	 *
	 * @param $item
	 * @return string
	 */
	function column_name( $item ) : string {

		$url = $this->nonce->get_url(
			add_query_arg(
				[
					'page' => \PMC::filter_input( INPUT_GET, 'page' ),
					'id'   => $item['id'],
				] 
			) 
		);

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
			'edit'   => sprintf( '<a href="%s">Edit</a>', esc_url( add_query_arg( [ 'action' => 'edit' ], $url ) ) ),
			'delete' => sprintf( '<a onclick="return confirm(\'This operation will remove entry (%s) from all articles and cannot be undo. Do you still wish to continue?\');" href="%s">Delete</a>', esc_attr( $item['name'] ), esc_url( add_query_arg( [ 'action' => 'delete' ], $url ) ) ),
		];

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Render the table grid
	 *
	 * @return void
	 */
	public function render() : void {
		$this->prepare_items();
		$this->display();
	}

}
