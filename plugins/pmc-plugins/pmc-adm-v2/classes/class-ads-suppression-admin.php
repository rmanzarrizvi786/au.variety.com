<?php
/**
 * Ads suppression admin ui to allow editor create and manage tag with scheduling
 *
 * @package pmc-adm-v2
 *
 * @version 2021-10-14 Hau Vong REV-94
 */

namespace PMC\Adm;

use PMC\Global_Functions\Nonce;
use PMC\Global_Functions\Traits\Singleton;
use PMC_TimeMachine;
use PMC;

const POST_TYPE = 'test';

/**
 * Ads Suppression Admin
 */
class Ads_Suppression_Admin {
	use Singleton;

	public $error;
	public $form_url;
	public $id;
	public $nonce;

	public $page = 'pmc_ads_suppression';

	/**
	 * Constructor
	 */
	protected function __construct() {
		$this->_init_vars();
		$this->_setup_hooks();
	}

	/**
	 * Initialize related variables
	 *
	 * @return void
	 */
	protected function _init_vars() : void {
		$id = \PMC::filter_input( INPUT_POST, 'id' );
		if ( empty( $id ) ) {
			$id = \PMC::filter_input( INPUT_GET, 'id' );
		}

		$this->search = \PMC::filter_input( INPUT_POST, 'search' );
		if ( empty( $this->search ) ) {
			$this->search = \PMC::filter_input( INPUT_GET, 'search' );
		}

		$this->form_url = remove_query_arg( [ 'action', '_pmc_nonce', 'id' ] );
		$this->nonce    = Nonce::get_instance();
		$this->id       = (int) $id;
	}

	/**
	 * Setup all related wp hooks
	 * @return void
	 */
	protected function _setup_hooks() : void {
		add_action( 'admin_menu', [ $this, 'action_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'action_admin_enqueue_scripts' ] );
	}

	public function action_admin_enqueue_scripts() {
		if ( preg_match( '/pmc_ads_suppression/', get_current_screen()->id ) ) {
			wp_enqueue_script( 'datetimepicker', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js', [ 'jquery' ], '2.5.20' );
			wp_enqueue_style( 'datetimepicker', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css', [], '2.5.20' );
		}
	}

	/**
	 * Action to add admin menu
	 *
	 * @return void
	 */
	public function action_admin_menu() : void {
		require_once __DIR__ . '/class-ads-suppression-table.php';

		$hook = add_submenu_page(
			'tools.php',
			'PMC Ads Suppression Scheduling',
			'PMC Ads Suppression',
			'pmc_manage_ads_cap',
			'pmc_ads_suppression',
			[ $this, 'plugin_settings_page' ]
		);

		add_action( "load-$hook", [ $this, 'load_screen_option' ] );
	}

	/**
	 * Load the screen option and process wp admin action
	 *
	 * @return void
	 */
	public function load_screen_option() : void {

		$args = [
			'label'   => 'Show items per page',
			'default' => 25,
			'option'  => 'pmc_ads_suppression_per_page',
		];

		add_screen_option( 'per_page', $args );

		$this->ads_suppression_table_obj = new Ads_Suppression_Table( $this->nonce, $this->search );

		switch ( strtolower( $this->ads_suppression_table_obj->current_action() ) ) {
			case 'cancel':
				wp_safe_redirect( $this->form_url );
				// We cannot cover code exit statement
				exit; // @codeCoverageIgnore
			case 'delete':
				if ( $this->nonce->verify() ) {
					Ads_Suppression::get_instance()->delete( $this->id );
				};

				wp_safe_redirect( $this->form_url );
				// We cannot cover code exit statement
				exit; // @codeCoverageIgnore

			case 'save':
				$start = PMC::filter_input( INPUT_POST, 'start', FILTER_SANITIZE_STRING );
				$end   = PMC::filter_input( INPUT_POST, 'end', FILTER_SANITIZE_STRING );

				if ( ! empty( $start ) ) {
					$start = gmdate( 'Y-m-d H:i', strtotime( $start ) );
				}
				if ( ! empty( $end ) ) {
					$end = gmdate( 'Y-m-d H:i', strtotime( $end ) );
				}

				$target_tags = [];
				$tokens      = explode( ',', PMC::filter_input( INPUT_POST, 'target_tags' ) );

				foreach ( $tokens as $tag ) {
					$tag = trim( $tag );
					if ( ! empty( $tag ) ) {
						$target_tags[] = $tag;
					}
				}

				$data = [
					'id'          => PMC::filter_input( INPUT_POST, 'id' ),
					'name'        => PMC::filter_input( INPUT_POST, 'name', FILTER_SANITIZE_STRING ),
					'description' => PMC::filter_input( INPUT_POST, 'description', FILTER_SANITIZE_STRING ),
					'apply_to'    => [
						PMC::filter_input( INPUT_POST, 'apply_to', FILTER_SANITIZE_STRING ),
					],
					'schedules'   => [
						[
							'start' => $start,
							'end'   => $end,
						],
					],
					'target_tags' => $target_tags,
				];

				$redirect_url = $this->form_url;
				if ( Ads_Suppression::get_instance()->save( $data ) ) {
					wp_safe_redirect( $redirect_url );
					// We cannot cover code exit statement
					exit; // @codeCoverageIgnore
				} else {
					$this->error = 'Error saving';
				}

				break;
		}

	}

	/**
	 * Render the admin screen settings
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function plugin_settings_page() : void {
		PMC::render_template(
			PMC_ADM_DIR . '/templates/admin/ads-suppression-admin.php',
			[
				'instance' => $this,
				'search'   => $this->search,
				'page'     => $this->page,
			],
			true 
		);
	}

	/**
	 * Render the edit form
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function render_edit_form() : void {
		$data = wp_parse_args(
			Ads_Suppression::get_instance()->get( $this->id ),
			[
				'name'        => '',
				'description' => '',
				'schedules'   => [],
				'apply_to'    => [ 'all' ],
				'target_tags' => [],
			],
		);

		$schedule = wp_parse_args(
			reset( $data['schedules'] ),
			[
				'start' => '',
				'end'   => '',
			]
		);

		$timezone = PMC_TimeMachine::get_site_timezone();
		if ( empty( $data['id'] ) ) {
			$schedule['start'] = PMC_TimeMachine::create()->format_as( 'Y-m-d' );
		}

		PMC::render_template(
			PMC_ADM_DIR . '/templates/admin/ads-suppression-admin-edit-form.php',
			[
				'instance' => $this,
				'data'     => $data,
				'timezone' => $timezone,
				'schedule' => $schedule,
			],
			true 
		);
	}

}
