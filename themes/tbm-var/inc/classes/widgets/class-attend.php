<?php

/**
 * Event promotion for Variety Business Leadership.
 *
 * @author Ashwini Joshi
 *
 */

namespace Variety\Inc\Widgets;

use PMC\Core\Inc\Widgets\Global_Curateable;

class Attend extends Global_Curateable
{

	/**
	 * Register widget with WordPress.
	 */
	public function __construct()
	{
		parent::__construct(
			'variety_business_events',
			esc_html__('Variety - Business Event', 'pmc-variety'),
			['description' => esc_html__('Event description and confirmed speakers for the event.', 'pmc-variety')]
		);
	}

	/**
	 * Get the FM fields for this widget.
	 *
	 * @todo Consider setting defaults to global data
	 *
	 * @return array
	 */
	public static function get_fields()
	{
		return [
			'variety_business_events' => new \Fieldmanager_Group(
				[
					'children' => [
						'event_name'        => new \Fieldmanager_TextField(esc_html__('Event Name', 'pmc-variety')),
						'event_description' => new \Fieldmanager_TextArea(
							[
								'label'      => esc_html__('Event Description', 'pmc-variety'),
								'attributes' => [
									'rows'  => 3,
									'style' => 'width:400px;max-width:100%',
								],
							]
						),
						'event_link'        => new \Fieldmanager_Link(esc_html__('Link', 'pmc-variety')),
						'event_speakers'    => new \Fieldmanager_Group(
							[
								'label'          => esc_html__('Event Speaker', 'pmc-variety'),
								'label_macro'    => ['%s', 'name'],
								'limit'          => 3,
								'extra_elements' => 0,
								'minimum_count'  => 0,
								'add_more_label' => esc_html__('Add a Speaker', 'pmc-variety'),
								'sortable'       => true,
								'collapsed'      => true,
								'children'       => [
									'name'    => new \Fieldmanager_TextField(esc_html__('Name', 'pmc-variety')),
									'company' => new \Fieldmanager_TextField(esc_html__('Company', 'pmc-variety')),
									'photo'   => new \Fieldmanager_Media(
										[
											'label'        => esc_html__('Photo', 'pmc-variety'),
											'button_label' => esc_html__('Select Photo', 'pmc-variety'),
										]
									),
								],
							]
						),
					],
				]
			),
		];
	}
}
