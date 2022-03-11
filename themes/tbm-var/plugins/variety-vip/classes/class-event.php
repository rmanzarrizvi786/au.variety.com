<?php
/**
 * Event
 *
 * Responsible for event based functionality.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Variety_VIP;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Event
 */
class Event {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() {

		add_filter( 'pmc_core_global_curation_modules', [ $this, 'global_curation_modules' ] );

	}

	/**
	 * Fetch the fields for Global Curation.
	 *
	 * @param array $modules Existing modules.
	 *
	 * @return array Modules.
	 */
	public function global_curation_modules( $modules ) {

		return array_merge(
			[
				'variety_vip_upcoming_event' => [
					'label'    => esc_html__( 'Variety VIP Upcoming Event', 'pmc-variety' ),
					'children' => [
						'vip_event' => new \Fieldmanager_Group(
							[
								'children' => [
									'event_header'      => new \Fieldmanager_TextField(
										[
											'label' => esc_html__( 'Event Header', 'pmc-variety' ),
											'default_value' => __( 'Upcoming Events', 'pmc-variety' ),
										]
									),
									'event_image'       => new \Fieldmanager_Media(
										[
											'name'         => 'event_image',
											'button_label' => esc_html__( 'Set Event Image ', 'pmc-variety' ),
											'modal_title'  => esc_html__( 'Event Image ', 'pmc-variety' ),
											'mime_type'    => 'image',
										]
									),
									'event_name'        => new \Fieldmanager_TextField( esc_html__( 'Event Name', 'pmc-variety' ) ),
									'event_description' => new \Fieldmanager_TextArea(
										[
											'label'      => esc_html__( 'Event Description', 'pmc-variety' ),
											'attributes' => [
												'rows'  => 3,
												'style' => 'width:400px;max-width:100%',
											],
										]
									),
									'event_link'        => new \Fieldmanager_Link( esc_html__( 'Event Link', 'pmc-variety' ) ),
								],
							]
						),
					],
				],
			],
			$modules
		);

	}

}

// EOF.
