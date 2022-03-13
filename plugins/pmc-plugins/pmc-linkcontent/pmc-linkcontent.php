<?php
/*
Plugin Name: PMC Link Post
Description: Provides Form Field to link a Post in WP.
Version: 1.0.0
Author: PMC
License: PMC Proprietary.  All rights reserved.
*/
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

use \PMC\Global_Functions\Traits\Singleton;

class PMC_LinkContent {

	use Singleton;

	// Posts to show in drop down
	const posts_per_page = 4;

	/**
	 * Initialization function called when object is instantiated
	 */
	protected function __construct() {
		add_action( 'admin_init', array( 'PMC_LinkContent', 'admin_ajax_init' ) );
	}

	// Add necessary AJAX actions
	public static function admin_ajax_init( ) {
		add_action( 'wp_ajax_pmclinkcontent_search_posts', array( 'PMC_LinkContent', 'ajax_search_posts' ) );
	}

	public static function enqueue() {
		// Enqueue Scripts and Styles
		add_action( 'admin_enqueue_scripts', array( 'PMC_LinkContent', 'admin_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( 'PMC_LinkContent', 'admin_enqueue_styles' ) );
	}

	public static function admin_enqueue_scripts() {
		wp_enqueue_script( 'json2' );
		wp_enqueue_script( 'jquery-ui-autocomplete', 'jquery' );
		wp_enqueue_script( 'pmc-linkcontent', plugins_url( 'js/pmc-linkcontent.js' , __FILE__ ), array('jquery-ui-autocomplete') );
	}

	public static function admin_enqueue_styles() {
		wp_enqueue_style( 'pmc-linkcontent', plugins_url( 'css/pmc-linkcontent.css' , __FILE__ ) );
	}

	public static function insert_field( $data, $type='Article', $id='' ) {
		// JSON string with quotes in title are causing JSON to be malformed.
		// Need to replace &quot; entity with \" to prevent something like "title":"My "quoted" title"
		$data_string = str_replace( '&quot;', '\"', $data );
		?>
		<div id="<?php echo esc_attr( $id ); ?>" class="pmclinkcontent-search-wrapper ">
			<span class="pmclinkcontent-post-result ">
				<?php
				$data = json_decode($data);
				if( is_object( $data ) && isset( $data->url ) && isset( $data->id ) && isset( $data->title ) ) { ?>
					Selected <?php echo esc_html( $type ); ?>:
					<a class='pmclinkcontent-post' href='<?php echo esc_url( $data->url ); ?>' data-id='<?php echo esc_attr( $data->id ); ?>' target='_blank' >
						<?php echo esc_html( $data->title ); ?>
					</a>
					<span class='pmclinkcontent-remove'>(remove)</span>
				<?php } ?>
			</span>
			<label class="pmclinkcontent-post-search-label" for="pmclinkcontent-post-search<?php echo ( '' == $id )?'':'-' . esc_attr( $id ); ?>"><?php echo esc_html( $type ) . ' Search';?></label>
			<input type="text" class="pmclinkcontent-post-search" name="pmclinkcontent-post-search<?php echo ( '' == $id )?'':'-' . esc_attr( $id ); ?>" placeholder="Search" />
			<input type="hidden" class="pmclinkcontent-post-value" name="pmclinkcontent-post-value<?php echo ( '' == $id )?'':'-' . esc_attr( $id ); ?>" value='<?php echo esc_attr( $data_string ); ?>' />
			<input type="hidden" class="pmclinkcontent-type" name="pmclinkcontent-type<?php echo ( '' == $id )?'':'-' . esc_attr( $id ); ?>" value='<?php echo esc_attr( $type ); ?>' />
			<p class="description"><?php echo 'Enter a term or phrase in the text box above to search for and add/change ' . esc_html( $type ) . '.'; ?></p>
			<div class="pmclinkcontent-include-old-container">
				<input type="checkbox" class="pmclinkcontent-include-old" name="pmclinkcontent-include-old<?php echo ( '' == $id )?'':'-' . esc_attr( $id ); ?>" id="pmclinkcontent-include-old<?php echo ( '' == $id )?'':'-' . esc_attr( $id ); ?>" value="1" />
				<label for="pmclinkcontent-include-old<?php echo ( '' == $id )?'':'-' . esc_attr( $id ); ?>">Include content older than 1 year (this may be slow)</label>
			</div>
			<?php
			if ( in_array( $type, array( 'Article', 'Section Front') ) ) {
				?>
				<p>Choose What you want to Link.
				<label><input type="radio" class="pmclinkcontent-link-article" name="pmc_type<?php echo (( '' == $id )?'':'-' . esc_attr( $id )); ?>" <?php checked( $type, 'Article' ); ?> value="Article">Article</label>
                <label><input type="radio" class="pmclinkcontent-link-sectionfront" name="pmc_type<?php echo (( '' == $id )?'':'-' . esc_attr( $id )); ?>" <?php checked( $type, 'Section Front' ); ?> value="Section Front">Section Front<br/><label></p>
			<?php } else {?>
				<p style="display:none" ><input type="radio" class="pmclinkcontent-link-article" name="pmc_type<?php echo (( '' == $id )?'':'-' . esc_attr( $id )); ?>" checked="checked" value="<?php echo esc_attr( $id ); ?>"></p>
			<?php }
			do_action( 'pmc-linkcontent-insert_field', $id );

			wp_nonce_field( 'pmc-linkcontent', 'pmc-linkcontent-nonce');
			?>
		</div>
	<?php }

	public static function ajax_search_posts() {
		$q = self::_get_request_var( 'term', '', 'stripslashes' );

		if( ! empty( $q ) ) {

			$t = self::_get_request_var( 'type', '', 'stripslashes' );

			if ($t=='sf') {
                $default_sf_types = self::get_sf_taxonomy_defaults();
				$sf_types = apply_filters( 'pmc-linkcontent-sf-types',  $default_sf_types );
				$limit = self::_get_request_var( 'limit', self::posts_per_page );
				if( $limit <= 0 )
					$limit = self::posts_per_page;
				$exclude = (array) self::_get_request_var( 'exclude', array(), 'absint' );

				$args = apply_filters( 'pmclinkcontent_sf_args', array(
					'search' => $q,
					'exclude' => $exclude,
					'number' => $limit,
					'order' => 'DESC',
					'orderby' => 'count'
				) );

				$terms = get_terms( $sf_types, $args );
				$stripped_posts = array();

				if ( count( $terms ) > 0 ) {

					foreach ( $terms as $term ) {
						$stripped_posts[] = array(
							'title'       => !empty( $term->name ) ? $term->name : __( '(no title)', 'pmclinkcontent' ),
							'post_id'     => $term->term_id,
							'date'        => '',
							'post_type'   => $term->taxonomy,
							'post_status' => '',
							'post_url'    => get_term_link( $term->slug, $term->taxonomy )
						);
					}
				}
			} else {
				if ($t!='a' ) {
					$filter_add = '_' . $t;
				} else {
					$filter_add = '';
				}

				$post_types = apply_filters( 'pmclinkcontent_post_types' . $filter_add, array('post','pmc-gallery','video') );
				$post_status = apply_filters( 'pmclinkcontent_post_status', array( 'publish', 'future' ) );
				$limit = self::_get_request_var( 'limit', self::posts_per_page );
				if( $limit <= 0 )
					$limit = self::posts_per_page;
				$exclude = (array)self::_get_request_var( 'exclude', array(), 'absint' );

				$args = apply_filters( 'pmclinkcontent_search_args' . $filter_add, array(
						 'posts_per_page'   => $limit,
						 'post_type'        => $post_types,
						 'post_status'      => $post_status,
						 'order'            => 'DESC',
						 'orderby'          => 'post_date',
						 'suppress_filters' => false,
					) );

				/*
				 * Add post_not_in only if there is exclude param.
				 */
				if ( !empty( $exclude ) ) {
					$args['post__not_in'] = $exclude;
				}

				add_filter( 'posts_where', array( self::get_instance(), 'filter_title_and_date' ) );

				$query = new WP_Query( $args );

				$stripped_posts = array();

				if ( $query->have_posts() ) {

					/**
					 *	PPT-3486
					 * 	Date: Oct 20, 2014
					 * 	Added post_content
					 */
					$post_excerpt_length = 200;
					apply_filters( 'pmc-linkcontent-excerpt-length', $post_excerpt_length );

					$date_format = get_option( 'date_format' );
					foreach ( $query->posts as $post ) {
						$stripped_posts[] = array(
							'title'       	=> !empty( $post->post_title ) ? $post->post_title : __( '(no title)', 'pmclinkcontent' ),
							'post_id'     	=> $post->ID,
							// note: do not use get_the_time here, unless calling setup_postdata or remove the_time filter from postrelease-vip plugin
							'date'        	=> get_post_time($date_format, false, $post, true),
							'post_type'  	=> $post->post_type,
							'post_status' 	=> $post->post_status,
							'post_url'    	=> get_permalink( $post->ID ),
							'post_excerpt'  => PMC::truncate( strip_shortcodes($post->post_content), $post_excerpt_length, '', true )
						);
					} // foreach

					unset( $date_format );
				} // if have posts

				remove_filter( 'posts_where', array( self::get_instance(), 'filter_title_and_date' ) );

			}

			if ( empty( $stripped_posts ) ) {
				$stripped_posts[] = array( 'post_id'     => 'fail',
										   'title'       => 'no results',
										   'date'        => '',
										   'post_status' => '',
										   'post_type'   => '',
											'post_excerpt' => '');
			}

			echo json_encode( $stripped_posts );
			exit;
		}
	}

	/**
	 * Search by title only. Also limit searches to last one year only, if the user did not specify to search old posts.
	 * @param $where
	 *
	 * @return string
	 */
	public static function filter_title_and_date( $where ) {

		global $wpdb;

		$main_where = $where;

		$q = self::_get_request_var( 'term', '', 'stripslashes' );

		$where .= $wpdb->prepare( " AND $wpdb->posts.post_title like '%s' ", '%' . $q . '%' );

		$includeold = self::_get_request_var( 'includeold', false );
		if ( ! $includeold || '1' != $includeold ) {
			$where .= $wpdb->prepare( " AND $wpdb->posts.post_date >= '%s' ", date( 'Y-m-d', strtotime( '-1 year' ) ) );
		}

		return apply_filters( 'pmclinkcontent_posts_where', $where, $main_where );
	}

	private static function _get_request_var( $var, $default = '', $sanitize_callback = '' ) {
		$object = $_REQUEST;
		if( is_object( $object ) )
			$value = ! empty( $object->$var ) ? $object->$var : $default;
		elseif( is_array( $object ) )
			$value = ! empty( $object[$var] ) ? $object[$var] : $default;
		else
			$value = $default;

		if( is_callable( $sanitize_callback ) ) {
			if( is_array( $value ) )
				$value = array_map( $sanitize_callback, $value );
			else
				$value = call_user_func( $sanitize_callback, $value );
		}

		return $value;
	}

    /**
     * returns an array of taxonomy defaults used by  ajax_search_posts
     * right now we know every LOB has categor and post_tag as taxonomy
     * some LOB's might not have editorial and vertical so we check first before including it in the list.
     * each LOB has the ability to override this entire list with the filter 'pmc-linkcontent-sf-types'
     * @static
     * @return array
     */
    public static function get_sf_taxonomy_defaults(){
        $taxonomy_defaults = array('category','post_tag');

        if( taxonomy_exists( 'editorial' ) ){
            $taxonomy_defaults[] = 'editorial';
        }

        if( taxonomy_exists( 'vertical' ) ){
            $taxonomy_defaults[] = 'vertical';
        }

        return $taxonomy_defaults;


    }
}

PMC_LinkContent::get_instance();

// EOF
