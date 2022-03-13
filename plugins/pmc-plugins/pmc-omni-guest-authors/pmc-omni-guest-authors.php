<?php

/*
 * https://jira.pmcdev.io/browse/OMNI-49
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Omni_Guest_Authors {

	use Singleton;

	protected function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'action_add_metaboxes' ), 10, 2 );
		add_filter( 'coauthors_guest_author_fields', array( $this, 'add_coauthors_guest_author_fields' ), 10, 2 );
	}

	public function action_add_metaboxes( $post_type, $post ) {
		if ( 'guest-author' === $post_type ) {
			add_meta_box( 'coauthors-manage-guest-author-pmc-classification', __( 'Author Classification', 'co-authors-plus' ), array( $this, 'metabox_manage_guest_author_pmc_classification' ), 'guest-author', 'normal', 'low' );
		}
	}

	/**
	 * Function to add our custom info for authors.
	 *
	 * @param $fields_to_return
	 * @param $groups
	 *
	 * @return array
	 */
	public function add_coauthors_guest_author_fields( $fields_to_return, $groups ) {

		$employment_status = array(
			'unknown'    => __( 'Unknown' ),
			'employee'   => __( 'Employee' ),
			'contractor' => __( 'Contractor' ),
			'other'      => __( 'Other' ),
		);

		$employment_location = array(
			'unknown' => __( 'Unknown' ),
			'ny'      => __( 'NY (US/East)' ),
			'la'      => __( 'LA (US/West)' ),
			'intl'    => __( 'International' ),
		);

		$new_fields = array(

			array(
				'key'   => '_pmc_employment_status',
				'label' => __( 'Status' ),
				'group' => 'pmc-classification',
				'input' => 'dropdown',
				'list'  => $employment_status,
			),

			array(
				'key'   => '_pmc_employment_location',
				'label' => __( 'Location' ),
				'group' => 'pmc-classification',
				'input' => 'dropdown',
				'list'  => $employment_location,
			),

		);

		foreach ( $new_fields as $single_field ) {
			if ( in_array( $single_field['group'], $groups, true ) || 'all' === $groups[0] && 'hidden' !== $single_field['group']  ) {
				$fields_to_return[] = $single_field;
			}
		}

		return $fields_to_return;
	}

	/**
	 * Metabox to edit the bio and other biographical details of the Guest Author
	 *
	 */
	public function metabox_manage_guest_author_pmc_classification() {
		$this->render_metabox( 'pmc-classification' );
	}

	/**
	 * Render metabox for the given group of the Guest Author
	 *
	 */
	function render_metabox( $group ) {
		global $post, $coauthors_plus;

		$fields = $coauthors_plus->guest_authors->get_guest_author_fields( $group );
		echo '<table class="form-table"><tbody>';
		foreach ( $fields as $field ) {
			$pm_key = $coauthors_plus->guest_authors->get_post_meta_key( $field['key'] );
			$value  = get_post_meta( $post->ID, $pm_key, true );
			echo '<tr><th>';
			echo '<label for="' . esc_attr( $pm_key ) . '">' . esc_html( $field['label'] ) . '</label>';
			echo '</th><td>';

			if ( ! isset( $field['input'] ) ) {
				if ( 'about' === $group ) {
					$field['input'] = "textarea";
				} else {
					$field['input'] = "text";
				}
			}
			$field['input'] = apply_filters( 'coauthors_name_field_type_' . $pm_key, $field['input'] );
			switch ( $field['input'] ) {
				case "checkbox":
					echo '<input type="checkbox" name="' . esc_attr( $pm_key ) . '"' . checked( '1', $value, false ) . ' value="1"/>';
					break;
				case 'textarea':
					echo '<textarea style="width:300px;margin-bottom:6px;" name="' . esc_attr( $pm_key ) . '">' . esc_textarea( $value ) . '</textarea>';
					break;
				case 'dropdown':
					echo '<select name="' . esc_attr( $pm_key ) . '" >';
					foreach ( $field['list'] as $key => $desc ) {
						echo '<option ' . selected( $key, $value, false ) . 'value="' . esc_attr( $key ) . '">' . esc_html( $desc ) . '</option>';
					}
					echo '</select>';
					break;
				default:
					echo '<input type="' . esc_attr( $field['input'] ) . '" name="' . esc_attr( $pm_key ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
					break;
			}

			echo '</td></tr>';
		}
		echo '</tbody></table>';
	}

}

PMC_Omni_Guest_Authors::get_instance();
