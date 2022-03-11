<?php

if ( class_exists( 'Fieldmanager_Datasource' ) ) {

	/**
	 * Custom Fieldmanager Datasource for Co-Authors Plus.
	 */
	class Variety_Datasource_CAP extends Fieldmanager_Datasource {

		/**
		 * Force ajax.
		 *
		 * @var boolean
		 */
		public $use_ajax = true;

		/**
		 * Capability required to refer to a user via this datasource.
		 *
		 * @var string
		 */
		public $capability = 'list_users';

		/**
		 * Should we set the default user, as CAP does? Defaults to false.
		 *
		 * @var boolean
		 */
		public $set_default = false;

		/**
		 * Get a user by stored value.
		 *
		 * @param int $value post_id
		 *
		 * @return string post title
		 */
		public function get_value( $value ) {

			global $coauthors_plus;
			$author = $coauthors_plus->get_coauthor_by( 'user_nicename', $value );

			return $author ? $author->display_name : '';
		}

		/**
		 * Get users which match this datasource, optionally filtered by
		 * a search fragment, e.g. for Autocomplete.
		 *
		 * @param string $fragment
		 *
		 * @return array post_id => post_title for display or AJAX
		 */
		public function get_items( $fragment = null ) {

			global $coauthors_plus;

			$ignore = [];
			if ( ! empty( $_POST['existing_authors'] ) ) { // phpcs:ignore
				$ignore = sanitize_text_field( wp_unslash( $_POST['existing_authors'] ) ); // phpcs:ignore
				$ignore = explode( ',', $ignore );
			}

			$authors = $coauthors_plus->search_authors( $fragment, $ignore );
			$ret     = [];

			foreach ( $authors as $author ) {
				$ret[ $author->user_nicename ] = $author->display_name;
			}

			return $ret;
		}

		/**
		 * Load up the current author if no others are specified.
		 *
		 * @param Fieldmanager_Field $field The field using this datasource.
		 * @param array              $values
		 *
		 * @return array $values loaded up, if applicable.
		 */
		public function preload_alter_values( Fieldmanager_Field $field, $values ) {

			if ( $this->set_default && empty( $values ) ) {
				global $post, $coauthors_plus, $current_screen;
				$values = [];

				if ( empty( $post->ID ) || ( empty( $post->post_author ) && ! $coauthors_plus->force_guest_authors ) || ( 'post' === $current_screen->base && 'add' === $current_screen->action ) ) {
					$default_user = apply_filters( 'coauthors_default_author', wp_get_current_user() );
					// If guest authors is enabled, try to find a guest author attached to this user ID
					if ( $coauthors_plus->is_guest_authors_enabled() ) {
						$coauthor = $coauthors_plus->guest_authors->get_guest_author_by(
							'linked_account',
							$default_user->user_login
						);
						if ( $coauthor ) {
							$values[] = $coauthor->user_nicename;
						}
					}

					// If the above block was skipped, or if it failed to find a guest author, use the current
					// logged in user, so long as force_guest_authors is false. If force_guest_authors = true, we are
					// OK with having an empty authoring box.
					if ( ! $coauthors_plus->force_guest_authors && empty( $values ) ) {
						if ( is_array( $default_user ) ) {
							$values = array_map(
								function ( $a ) {

									return $a->user_nicename; // phpcs:ignore
								},
								(array) $default_user
							);
						} else {
							$values[] = $default_user->user_nicename;
						}
					}
				} else {
					$users = get_coauthors();
					foreach ( (array) $users as $user ) {
						if ( ! empty( $user->user_nicename ) ) {
							$values[] = $user->user_nicename;
						}
					}
				}
			}

			return $values;
		}

		/**
		 * Save authors as coauthors.
		 *
		 * @param Fieldmanager_Field $field          The field using this datasource.
		 * @param mixed              $values         The values to be saved.
		 * @param mixed              $current_values The previous values.
		 *
		 * @return string The modified $values.
		 */
		public function presave_alter_values( Fieldmanager_Field $field, $values, $current_values ) {

			global $coauthors_plus;
			$coauthors = (array) $values;

			$post      = get_post( $field->data_id );
			$coauthors = array_filter( $coauthors );

			if ( ! empty( $coauthors ) && $post && $coauthors_plus->current_user_can_set_authors( $post ) ) {
				$coauthors_plus->add_coauthors( $post->ID, $coauthors );
			}

			return parent::presave_alter_values( $field, $values, $current_values );
		}

		/**
		 * Get view link for a user. Currently disabled.
		 *
		 * @param int $value
		 *
		 * @return string
		 */
		public function get_view_link( $value ) {

			return '';
		}

		/**
		 * Get edit link for a user. Currently disabled.
		 *
		 * @param int $value
		 *
		 * @return string
		 */
		public function get_edit_link( $value ) {

			return '';
		}
	}

}
