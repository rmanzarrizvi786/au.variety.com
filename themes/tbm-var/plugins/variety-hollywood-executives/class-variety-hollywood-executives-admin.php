<?php
/**
 * Variety Hollywood Executive Profile Admin class which is sub-class of Variety Hollywood Executive Profile class and deals with admin section of the
 * module
 *
 * @since 2012-01-16 Amit Gupta
 * @version 2012-06-24 Amit Gupta
 * @version 2013-07-16 Adaeze Esiobu
 * @version 2018-05-17 Jignesh Nakrani READS-906 - Disable and remove old Hollywood Exec importer
 */

class Variety_Hollywood_Executives_Profile_Admin extends Variety_Hollywood_Executives_Profile {

	/**
	 * Constructor
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		parent::$a__metaname = array(
			'firstname' => 'firstname',
			'lastname' => 'lastname',
			'dob' => 'dob',
			'dob_d' => 'dob_date',
			'dob_m' => 'dob_month',
			'dob_y' => 'dob_year',
			'nicknames' => 'nicknames',
			'gender' => 'gender',
			'replace_tag' => 'replace_tag',
			'height' => 'height',
			'height_f' => 'height_f',
			'height_i' => 'height_i',
			'hometown' => 'hometown',
			'state' => 'state',
			'country' => 'country',
			'twitter' => 'twitter',
			'quotes' => 'quotes',
			'right_col' => 'right_col',
			'relation_name' => 'relation_name',
			'relation_slug' => 'relation_slug',
			'relation_profile' => 'relation_profile',
		);

		//add metabox with meta info UI on exec profile add/edit page in admin
		add_action( 'admin_init', array( $this, 'add_metabox' ) );

		//handle stuff when exec profile is saved
		add_action( 'save_post', array( $this, 'save_profile' ) );

		//exec profile listing actions
		add_action( 'manage_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );
		add_filter( 'manage_edit-hollywood_exec_columns', array( $this, 'edit_columns' ) );

		add_filter( 'post_row_actions', array( $this, 'filter_hollywood_exec_row_actions' ), 10, 2 );

		add_action( 'restrict_manage_posts', array( $this, 'action_update_exec_profile_filter' ) );

	}

	/**
	 * Adds metabox for meta info UI for exec profile
	 */
	public function add_metabox() {
		add_meta_box( 'exec_meta', 'Exec Information', array( $this, 'metabox_meta_ui' ), parent::$a__options['post_type'], 'normal', 'default' );
	}

	/**
	 * adds UI for collecting meta info for exec profile
	 */
	public function metabox_meta_ui() {

		$custom = get_post_custom();

		if ( isset( $custom[ parent::$a__metaname['dob'] ][0]) && ! empty( $custom[ parent::$a__metaname['dob'] ][0] ) ) {
			$arr_dob = explode( '-', $custom[ parent::$a__metaname['dob'] ][0] );
		} else {
			$arr_dob = array( 'January', 1, 1900 );
		}

		$firstname = ( isset( $custom[ parent::$a__metaname['firstname'] ][0] ) && ! empty( $custom[ parent::$a__metaname['firstname'] ][0] ) ) ? $custom[ parent::$a__metaname['firstname'] ][0] : '';
		$lastname = ( isset( $custom[ parent::$a__metaname['lastname'] ][0] ) && ! empty( $custom[ parent::$a__metaname['lastname'] ][0] ) ) ? $custom[ parent::$a__metaname['lastname'] ][0] : '';
		$gender = ( isset($custom[parent::$a__metaname['gender']][0]) && ! empty($custom[parent::$a__metaname['gender']][0]) ) ? $custom[parent::$a__metaname['gender']][0] : '';
		$nicknames = ( isset($custom[parent::$a__metaname['nicknames']][0]) && ! empty($custom[parent::$a__metaname['nicknames']][0]) ) ? $custom[parent::$a__metaname['nicknames']][0] : '';
		$replace_tag = ( isset($custom[parent::$a__metaname['replace_tag']][0]) && ! empty($custom[parent::$a__metaname['replace_tag']][0]) ) ? $custom[parent::$a__metaname['replace_tag']][0] : '';
		$height = ( isset($custom[parent::$a__metaname['height']][0]) && ! empty($custom[parent::$a__metaname['height']][0]) ) ? explode('-', $custom[parent::$a__metaname['height']][0]) : array();
			$height_f = ( isset($height[0]) && ! empty($height[0]) ) ? $height[0] : '';
			$height_i = ( isset($height[1]) && ! empty($height[1]) ) ? $height[1] : '';
		$hometown = ( isset($custom[parent::$a__metaname['hometown']][0]) && ! empty($custom[parent::$a__metaname['hometown']][0]) ) ? $custom[parent::$a__metaname['hometown']][0] : '';
		$state = ( isset($custom[parent::$a__metaname['state']][0]) && ! empty($custom[parent::$a__metaname['state']][0]) ) ? $custom[parent::$a__metaname['state']][0] : '';
		$country = ( isset($custom[parent::$a__metaname['country']][0]) && ! empty($custom[parent::$a__metaname['country']][0]) ) ? $custom[parent::$a__metaname['country']][0] : 'United States';
		$twitter = ( isset($custom[parent::$a__metaname['twitter']][0]) && ! empty($custom[parent::$a__metaname['twitter']][0]) ) ? $custom[parent::$a__metaname['twitter']][0] : '';
		$quotes = ( isset($custom[parent::$a__metaname['quotes']][0]) && ! empty($custom[parent::$a__metaname['quotes']][0]) ) ? implode("\n\n", unserialize($custom[parent::$a__metaname['quotes']][0])) : '';
		$right_col = ( isset($custom[parent::$a__metaname['right_col']][0]) && ! empty($custom[parent::$a__metaname['right_col']][0]) ) ? $custom[parent::$a__metaname['right_col']][0] : '';

		$relation_name = ( isset($custom[parent::$a__metaname['relation_name']][0]) && ! empty($custom[parent::$a__metaname['relation_name']][0]) ) ? $custom[parent::$a__metaname['relation_name']][0] : '';
		$relation_slug = ( isset($custom[parent::$a__metaname['relation_slug']][0]) && ! empty($custom[parent::$a__metaname['relation_slug']][0]) ) ? $custom[parent::$a__metaname['relation_slug']][0] : '';

		$dob_month = $this->get_month_options( $arr_dob[0], 'hide', 'full' );
		$dob_date  = $this->get_number_options( 1, 31, $arr_dob[1], 'hide', 'show' );
		$dob_year  = $this->get_number_options( 1900, date( 'Y' ), $arr_dob[2], 'hide', 'hide' );
		$height_f  = $this->get_number_options( 1, 12, $height_f, 'show', 'hide' );
		$height_i  = $this->get_number_options( 0, 11, $height_i, 'show', 'hide' );
		$country   = $this->get_country_options( $country, 'hide' );
		$time = get_post_meta( get_the_ID(), Variety_Hollywood_Executives_API::UPDATED_TIMESTAMP_META_KEY, true );
		$parent_right_col = parent::$a__metaname['right_col'];
		$parent_relation_name = parent::$a__metaname['relation_name'];
		$parent_relation_slug = parent::$a__metaname['relation_slug'];

		/**
		 * @since 2017-09-01 Milind More CDWE-499
		 */
		\PMC::render_template(
			CHILD_THEME_PATH . '/plugins/variety-hollywood-executives/templates/hollywood-executives-metabox.php',
			array(
				'firstname'            => $firstname,
				'lastname'             => $lastname,
				'gender'               => $gender,
				'nicknames'            => $nicknames,
				'replace_tag'          => $replace_tag,
				'height'               => $height,
				'height_f'             => $height_f,
				'height_i'             => $height_i,
				'hometown'             => $hometown,
				'state'                => $state,
				'country'              => $country,
				'twitter'              => $twitter,
				'quotes'               => $quotes,
				'right_col'            => $right_col,
				'relation_name'        => $relation_name,
				'relation_slug'        => $relation_slug,
				'dob_month'            => $dob_month,
				'dob_date'             => $dob_date,
				'dob_year'             => $dob_year,
				'time'                 => $time,
				'parent_right_col'     => $parent_right_col,
				'parent_relation_name' => $parent_relation_name,
				'parent_relation_slug' => $parent_relation_slug,
			),
			true
		);

	}

	/**
	 * saves meta info of exec profile
	 *
	 * @param $post_id
	 */
	public function save_profile($post_id) {
		if( empty($post_id) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// verify if this came from our admin page or not
		if ( ! isset( $_POST['hollywood_exec_profile_admin_noncefield'] ) || ! wp_verify_nonce( $_POST['hollywood_exec_profile_admin_noncefield'], 'hollywood-exec-profile-admin' ) ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if( ! isset($_POST['hid_exec']) ) {
			return;
		}
		$right_col = ( isset($_POST[parent::$a__metaname['right_col']]) && ! empty($_POST[parent::$a__metaname['right_col']]) ) ? wp_filter_post_kses($_POST[parent::$a__metaname['right_col']]) : '';
		$firstname = ( isset($_POST[parent::$a__metaname['firstname']]) && ! empty($_POST[parent::$a__metaname['firstname']]) ) ? sanitize_text_field($_POST[parent::$a__metaname['firstname']]) : '';
		$lastname = ( isset($_POST[parent::$a__metaname['lastname']]) && ! empty($_POST[parent::$a__metaname['lastname']]) ) ? sanitize_text_field($_POST[parent::$a__metaname['lastname']]) : '';
		$gender = ( isset($_POST[parent::$a__metaname['gender']]) && ! empty($_POST[parent::$a__metaname['gender']]) ) ? sanitize_text_field($_POST[parent::$a__metaname['gender']]) : '';
		$nicknames = ( isset($_POST[parent::$a__metaname['nicknames']]) && ! empty($_POST[parent::$a__metaname['nicknames']]) ) ? sanitize_text_field($_POST[parent::$a__metaname['nicknames']]) : '';
		$replace_tag = ( isset($_POST[parent::$a__metaname['replace_tag']]) && ! empty($_POST[parent::$a__metaname['replace_tag']]) ) ? sanitize_key($_POST[parent::$a__metaname['replace_tag']]) : '';
		$dob = sanitize_text_field($_POST[parent::$a__metaname['dob_m']]) . "-" . intval($_POST[parent::$a__metaname['dob_d']]) . "-" . intval($_POST[parent::$a__metaname['dob_y']]);

		$height_f = intval($_POST[parent::$a__metaname['height_f']]);
		$height_i = intval($_POST[parent::$a__metaname['height_i']]);
		if($height_f>0) {
			$height = $height_f . '-' . $height_i;
		} else {
			$height = '';
		}

		$hometown = ( isset($_POST[parent::$a__metaname['hometown']]) && ! empty($_POST[parent::$a__metaname['hometown']]) ) ? sanitize_text_field($_POST[parent::$a__metaname['hometown']]) : '';
		$state = ( isset($_POST[parent::$a__metaname['state']]) && ! empty($_POST[parent::$a__metaname['state']]) ) ? sanitize_text_field($_POST[parent::$a__metaname['state']]) : '';
		$country = ( isset($_POST[parent::$a__metaname['country']]) && ! empty($_POST[parent::$a__metaname['country']]) ) ? sanitize_text_field($_POST[parent::$a__metaname['country']]) : '';
		$twitter = ( isset($_POST[parent::$a__metaname['twitter']]) && ! empty($_POST[parent::$a__metaname['twitter']]) ) ? sanitize_text_field($_POST[parent::$a__metaname['twitter']]) : '';
		$relation_name = ( isset($_POST[parent::$a__metaname['relation_name']]) && ! empty($_POST[parent::$a__metaname['relation_name']]) ) ? sanitize_text_field($_POST[parent::$a__metaname['relation_name']]) : '';
		$relation_slug = ( isset($_POST[parent::$a__metaname['relation_slug']]) && ! empty($_POST[parent::$a__metaname['relation_slug']]) ) ? sanitize_key($_POST[parent::$a__metaname['relation_slug']]) : '';
		$quotes = ( isset($_POST[parent::$a__metaname['quotes']]) && ! empty($_POST[parent::$a__metaname['quotes']]) ) ? explode("\n", $_POST[parent::$a__metaname['quotes']]) : array();
		$quotes_tmp = array();
		$text_tmp = '';
		if( ! empty($quotes) && is_array($quotes) ) {
			foreach( $quotes as $key => $value ) {
				$value = trim($value);
				if( empty($value) && ! empty($text_tmp) ) {
					$quotes_tmp[] = sanitize_text_field($text_tmp);
					$text_tmp = '';
				} else {
					$text_tmp .= $value . "\n";
				}
			}
			if( ! empty($text_tmp) ) {
				$quotes_tmp[] = sanitize_text_field( trim($text_tmp) );
			}
			unset($quotes);
			$quotes = $quotes_tmp;
		}
		unset($quotes_tmp, $text_tmp);

		//check relation's profile slug & save profile link
		$relation_slug_old = get_post_meta( $post_id, parent::$a__metaname['relation_slug'], true );
		$relation_profile_old = get_post_meta( $post_id, parent::$a__metaname['relation_profile'], true );
		$bln_save_profile_link = false; //no need to get relation's profile & save at present
		if( $relation_slug !== $relation_slug_old || empty($relation_profile_old) ) {
			$bln_save_profile_link = true;
		}
		$relation_profile = '';
		if( true === $bln_save_profile_link ) {
			$relation_profile = ( ! empty($relation_slug) ) ? $this->_get_profile_url($relation_slug) : '';
			$relation_profile = ( ! empty($relation_profile) ) ? $relation_profile : '';
		}

		update_post_meta($post_id, parent::$a__metaname['right_col'], $right_col);
		update_post_meta($post_id, parent::$a__metaname['firstname'], $firstname);
		update_post_meta($post_id, parent::$a__metaname['lastname'], $lastname);
		update_post_meta($post_id, parent::$a__metaname['gender'], $gender);
		update_post_meta($post_id, parent::$a__metaname['nicknames'], $nicknames);
		update_post_meta($post_id, parent::$a__metaname['replace_tag'], $replace_tag);
		update_post_meta($post_id, parent::$a__metaname['dob'], $dob);
		update_post_meta($post_id, parent::$a__metaname['height'], $height);
		update_post_meta($post_id, parent::$a__metaname['hometown'], $hometown);
		update_post_meta($post_id, parent::$a__metaname['state'], $state);
		update_post_meta($post_id, parent::$a__metaname['country'], $country);
		update_post_meta($post_id, parent::$a__metaname['twitter'], $twitter);
		update_post_meta($post_id, parent::$a__metaname['relation_name'], $relation_name);
		update_post_meta($post_id, parent::$a__metaname['relation_slug'], $relation_slug);
		update_post_meta($post_id, parent::$a__metaname['relation_profile'], $relation_profile);
		update_post_meta($post_id, parent::$a__metaname['quotes'], $quotes);
	}

    /**
     * displays values for non-default columns on profile listing page in admin
     * @param $column
     */
	public function custom_columns( $column, $post_id ) {
		switch($column) {
			case "dob":
				print( esc_html( get_post_meta( get_the_ID(), parent::$a__metaname['dob'], true ) ) );
				break;
			case "gender":
				print( esc_html( ucfirst( get_post_meta( get_the_ID(), parent::$a__metaname['gender'], true ) ) ) );
				break;
			case "hometown":
				$arr = array(
					get_post_meta( get_the_ID(), parent::$a__metaname['hometown'], true ),
					get_post_meta( get_the_ID(), parent::$a__metaname['state'], true ),
					get_post_meta( get_the_ID(), parent::$a__metaname['country'], true )
				);
				print( esc_html( $this->array_to_string($arr, ', ') ) );
				break;
			case 'modified':
				$time = get_post_meta( get_the_ID(), Variety_Hollywood_Executives_API::UPDATED_TIMESTAMP_META_KEY, true );
				if ( !empty( $time ) ) {
					print( esc_html( date( 'Y-m-d H:i:s', $time ) ) );
				}
				break;
			case 'vy500_year':
				$taxonomy    = 'vy500_year';
				$vy500_years = get_the_terms( $post_id, $taxonomy );
				if ( ! empty( $vy500_years ) && is_array( $vy500_years ) && ! is_wp_error( $vy500_years ) ) {
					foreach ( $vy500_years as $key => $year ) {
						$vy500_years[ $key ] = $year->name;
					}
					print( esc_html( implode( ' , ', $vy500_years ) ) );
				}
				break;

		}
	}

    /**
     * defines which columns to show on profile listing page in admin
     * @param $columns
     * @return array
     */
	public function edit_columns($columns) {
		$columns = array(
			"cb" => "<input type=\"checkbox\" />",
			"title" => "Celeb Name",
			"dob" => "Date of Birth",
			"gender" => "Gender",
			"hometown" => "Hometown",
			"modified" => "Modified",
			"date" => "Date",
			'vy500_year' => __( 'VY 500 Year', 'pmc-variety' ),
		);
		return $columns;
	}

	/**
	 * To add new vy500_year category filter.
	 *
	 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
	 */
	public function action_update_exec_profile_filter() {
		global $wp_query;

		if ( empty( $wp_query->query['post_type'] ) || 'hollywood_exec' !== $wp_query->query['post_type'] ) {
			return;
		}

		$vy500_taxonomy       = get_taxonomy( 'vy500_year' );
		$vy500_selected_value = ( ! empty( $wp_query->query['vy500_year'] ) ) ? $wp_query->query['vy500_year'] : '0';

		wp_dropdown_categories( array(
			'show_option_all' => sprintf( '%s %s', __( 'Show All', 'pmc-variety' ), $vy500_taxonomy->label ),
			'taxonomy'        => 'vy500_year',
			'name'            => 'vy500_year',
			'orderby'         => 'name',
			'selected'        => $vy500_selected_value,
			'value_field'     => 'slug',
		) );
	}

	/**
	 * To add new preview action button on hollywood_exec post type.
	 *
	 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
	 *
	 * @param array   $actions list of actions
	 * @param WP_POST $post    post object
	 *
	 * @return array $actions updated actions
	 */
	public function filter_hollywood_exec_row_actions( $actions, $post ) {

		if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) || empty( $actions ) || ! is_array( $actions ) || 'hollywood_exec' !== $post->post_type ) {
			return $actions;
		}

		$actions['vy500-preview'] = sprintf( '<a href="%s">%s</a>', get_preview_post_link( $post ), __( 'VY500 Preview', 'pmc-variety' ) );

		return $actions;
	}

}

//EOF
