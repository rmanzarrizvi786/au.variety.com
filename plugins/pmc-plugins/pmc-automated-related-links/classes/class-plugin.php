<?php
/**
 * PMC Automated Related Links plugin
 * Based on Variety See Also Links plugin
 */

namespace PMC\Automated_Related_Links;

use PMC\Global_Functions\Traits\Singleton;
use PMC_LinkContent;

class Plugin {

	use Singleton;

	/**
	 * Plugin ID
	 *
	 * @var string Plugin ID.
	 */
	const PLUGIN_ID = 'pmc-automated-related-links';

	/**
	 * Post meta name
	 *
	 * @var string Post meta name.
	 */
	const POST_META_NAME = '_pmc_automated_related_links';

	/**
	 * Nonce key
	 *
	 * @var string
	 */
	const NONCE_KEY = 'pmc-automated-related-links_nonce';

	/**
	 * Option.
	 *
	 * @var array
	 */
	protected $_options = [];

	/**
	 * Field data.
	 *
	 * @var string
	 */
	protected $_field_data = '';

	/**
	 * Initialize plugin, hook into WordPress
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	protected function __construct() {
		add_action( 'load-post.php', array( $this, 'meta_box_setup' ) );
		add_action( 'load-post-new.php', array( $this, 'meta_box_setup' ) );

		add_action( 'wp', array( $this, 'on_wp_init' ) );
		add_action( 'admin_init', array( $this, 'on_wp_init' ) );
		//setup our enqueuing
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_stuff' ) );

		if ( ! empty( $GLOBALS['pagenow'] ) && class_exists( 'PMC_LinkContent', false )
			&& ( 'post.php' === $GLOBALS['pagenow'] || 'post-new.php' === $GLOBALS['pagenow'] )
		) {
			add_action( 'init', array( 'PMC_LinkContent', 'enqueue' ) );
		}

	}

	/**
	 * Plugin initialization stuff fired on 'init' hook
	 *
	 * @return void
	 */
	public function on_wp_init() {

		$this->_options = [
			'number_of_links'     => 3,
			'default_module_name' => esc_html__( 'Related', 'pmc-automated-related-links' ),
			'field_token'         => 'pmc_arl_',
		];

		// Allow option override on current site.
		$this->_options = wp_parse_args( apply_filters( 'pmc_automated_related_links_options', $this->_options ), $this->_options );
	}

	/**
	 * This function enqueues our scripts and styles in wp-admin
	 *
	 * @param string $hook Page name.
	 *
	 * @return void
	 */
	public function enqueue_stuff( $hook ) {
		if ( ! is_admin() || ( 'post.php' !== $hook && 'post-new.php' !== $hook ) ) {
			//either page is not in wp-admin or not post add/edit page, so bail out
			return;
		}

		//load script
		wp_enqueue_script(
			self::PLUGIN_ID,
			sprintf( '%s/js/post-add-edit-page.js', PMC_AUTOMATED_RELATED_LINKS_PLUGIN_URL ),
			array( 'jquery' ),
			PMC_AUTOMATED_RELATED_LINKS_JS_VERSION
		);

		wp_localize_script(
			self::PLUGIN_ID,
			'pmc_arl',
			[
				'field_token' => esc_js( $this->_options['field_token'] ),
			]
		);
	}

	/**
	 * Add actions to add meta boxes
	 *
	 * @return void
	 */
	public function meta_box_setup() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_data' ) );
	}

	/**
	 * Add meta box to posts
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box( 'pmc-automated-related-links', 'Related Content', [ $this, 'meta_box' ], 'post', 'normal', 'core' );
	}

	/**
	 * Save meta data for the post
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function save_meta_data( $post_id ) {

		$nonce = \PMC::filter_input( INPUT_POST, self::NONCE_KEY );

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			|| empty( $nonce )
			|| ! wp_verify_nonce( $nonce, basename( __FILE__ ) )
			|| ! current_user_can( 'edit_post', $post_id )
		) {
			//its either auto-save call, not our form or current user is not allowed here, bail out
			return;
		}

		$new_meta_value = array(
			'settings' => array(
				'module_name' => $this->_options['default_module_name'],
				'hide_box'    => $this->_options['default_hide_box'],
			),
			'data'     => array(),
		);

		$module_name = \PMC::filter_input( INPUT_POST, $this->_options['field_token'] . 'module_name' );
		$hide_box    = \PMC::filter_input( INPUT_POST, $this->_options['field_token'] . 'hide_box' );

		if ( ! empty( $module_name ) ) {
			$new_meta_value['settings']['module_name'] = sanitize_text_field( $module_name );
		}

		$new_meta_value['settings']['hide_box'] = ( 'yes' === $hide_box ) ? 1 : 0;

		$field_names = array(
			'title'     => 'custom_title_' . $this->_options['field_token'],
			'data'      => 'pmclinkcontent-post-value-' . $this->_options['field_token'],
			'automated' => 'pmc_arl_automated-' . $this->_options['field_token'],
		);

		if ( ! isset( $this->_options['number_of_links'] ) || intval( $this->_options['number_of_links'] ) < 1 ) {
			//number of links not set, bail out
			update_post_meta( $post_id, self::POST_META_NAME, $new_meta_value );

			return;
		}

		$data = array();

		//we loop only N times where N is the max number of links
		//set in $_options
		for ( $i = 0; $i < $this->_options['number_of_links']; $i++ ) {
			$data[ $i ] = array(
				'url'       => '',
				'id'        => '',
				'title'     => '',
				'automated' => true,
			);

			$field_automated = \PMC::filter_input( INPUT_POST, $field_names['automated'] . $i );
			$field_data      = \PMC::filter_input( INPUT_POST, $field_names['data'] . $i );

			if (
				( ! empty( $field_automated ) && strtolower( $field_automated ) === 'yes' )
				|| ( empty( $field_data ) )
			) {
				//either automated links are to be used or we didnt get required data
				//so default to automated links and skip to next iteration
				continue;
			}

			$post_data = json_decode( trim( $field_data ) );

			if ( empty( $post_data ) || empty( $post_data->url ) || empty( $post_data->id ) || empty( $post_data->title ) ) {
				continue;
			}

			$data[ $i ] = array(
				'url'       => esc_url_raw( $post_data->url ),
				'id'        => intval( $post_data->id ),
				'title'     => trim( sanitize_text_field( $post_data->title ) ),
				'automated' => false,
			);

			$field_title = \PMC::filter_input( INPUT_POST, $field_names['title'] . $i );
			$field_title = trim( sanitize_text_field( $field_title ) );

			// Custom Title gets 1st priority
			// SEO Title (if it exists) gets 2nd priority
			// Linked post's Title is the fallback
			if ( ! empty( $field_title ) ) {
				$data[ $i ]['title'] = $field_title;
			} elseif ( $data[ $i ]['id'] > 0 ) {
				$seo_title = get_post_meta( $data[ $i ]['id'], 'mt_seo_title', true );

				if ( ! empty( $seo_title ) ) {
					$data[ $i ]['title'] = trim( sanitize_text_field( $seo_title ) );
				}

				unset( $seo_title );
			}

			unset( $post_data );

		}    //end for

		$new_meta_value['data'] = $data;

		unset( $data, $field_names );

		update_post_meta( $post_id, self::POST_META_NAME, $new_meta_value );
	}

	/**
	 * Content of the meta box filled from the saved array
	 *
	 * @param object $post
	 */
	public function meta_box( $post ) {
		wp_nonce_field( basename( __FILE__ ), self::NONCE_KEY );

		$linked_data = get_post_meta( $post->ID, self::POST_META_NAME, true );

		if ( empty( $linked_data ) ) {
			$linked_data = array(
				'settings' => array(
					'module_name' => $this->_options['default_module_name'],
				),
				'data'     => array(),
			);
		}

		?>
		<p>
			<label>
				<input type="checkbox" class="pmclinkcontent-link-article checkbox"
					name="<?php echo esc_attr( $this->_options['field_token'] ); ?>hide_box<?php echo( ( empty( $id ) ) ? '' : esc_attr( '-' . $id ) ); ?>"
					<?php checked( $linked_data['settings']['hide_box'], true ); ?> value="yes">
				Hide Related Module From The Article
			</label>
		</p>
		<p><label>
				Module Name:
				<input type="text" id="<?php echo esc_attr( $this->_options['field_token'] ); ?>module_name"
				name="<?php echo esc_attr( $this->_options['field_token'] ); ?>module_name" size="50"
				placeholder="<?php echo esc_attr( $linked_data['settings']['module_name'] ); ?>"
				value="<?php echo esc_attr( $linked_data['settings']['module_name'] ); ?>"/>
			</label>
		</p>
		<?php

		add_action( 'pmc-linkcontent-insert_field', array( $this, 'add_related_articles_cb' ), 1 );

		for ( $i = 0; $i < $this->_options['number_of_links']; $i ++ ) {
			$data = ( ! empty( $linked_data['data'][ $i ] ) ) ? (array) $linked_data['data'][ $i ] : array();

			if ( ! isset( $data['automated'] ) ) {
				$data['automated'] = apply_filters( 'pmc_automated_related_links', true );
			}

			$data              = wp_json_encode( $data );
			$this->_field_data = $data;

			PMC_LinkContent::insert_field( $data, 'Article', $this->_options['field_token'] . $i );

			unset( $data );
		}

		remove_action( 'pmc-linkcontent-insert_field', array( $this, 'add_related_articles_cb' ), 1 );
	}

	/**
	 * This function, called on 'pmc-linkcontent-insert_field' action, adds the
	 * checkbox for selected automatic links option in our metabox UI
	 *
	 * @return void
	 */
	public function add_related_articles_cb( $id ) {
		$data = json_decode( $this->_field_data );

		//if key for automatic links not present then default to true
		$is_checked = ( isset( $data->automated ) ) ? $data->automated : true;
		?>
		<p>
			<label>
				<input type="checkbox" class="pmclinkcontent-link-article checkbox"
				name="<?php echo esc_attr( $this->_options['field_token'] ); ?>automated<?php echo( ( empty( $id ) ) ? '' : esc_attr( '-' . $id ) ); ?>" <?php checked( $is_checked, true ); ?>
				value="yes">
				Automatically Select Related Content
			</label>
		</p>
		<?php
	}

	/**
	 * This function returns the module name set for the post. If its not
	 * set then it uses the value in $default
	 *
	 * @param int    $post_id Post ID.
	 * @param string $default Module name.
	 *
	 * @return string
	 */
	public function get_module_name( $post_id = 0, $default = '' ) {
		$post_id = intval( $post_id );

		if ( $post_id < 1 ) {
			$post_id = get_the_ID();
		}

		if ( intval( $post_id ) < 1 ) {
			//no data to return, bail out
			return $default;
		}

		$linked_data = get_post_meta( $post_id, self::POST_META_NAME, true );

		if ( empty( $linked_data['settings']['module_name'] ) ) {
			//no data to return, bail out
			return $default;
		}

		return $linked_data['settings']['module_name'];
	}

	/**
	 * This function returns related links for a post in an array.
	 *
	 * @param int   $post_id         Post ID for which related links are to be returned
	 * @param int   $number_of_links Number of links to return
	 * @param int   $link_offset     Number of links to skip from beginning
	 * @param array $query_args      An array of arguments to be passed to the filler function, 'pmc_related_articles'.
	 *
	 * @return array
	 */
	public function get_related_links( $post_id = 0, $number_of_links = 3, $link_offset = 0, array $query_args = array() ) {

		if ( empty( $this->_options ) ) {
			$this->on_wp_init();
		}

		$post_id         = intval( $post_id );
		$number_of_links = intval( $number_of_links );
		$number_of_links = ( ! empty( $this->_options['number_of_links'] ) && 0 < intval( $this->_options['number_of_links'] ) ) ? intval( $this->_options['number_of_links'] ) : $number_of_links;
		$link_offset     = intval( $link_offset );

		if ( $post_id < 1 ) {
			$post_id = get_the_ID();
		}

		$related_links = array();

		if ( intval( $post_id ) < 1 ) {
			//no data to return, bail out
			return $related_links;
		}

		$linked_data = get_post_meta( $post_id, self::POST_META_NAME, true );

		$linked_data = apply_filters( 'pmc_automated_related_links_linked_data_override', $linked_data );

		if ( empty( $linked_data['data'] ) || ! is_array( $linked_data['data'] ) ) {
			//no data to return, bail out
			return $related_links;
		}

		$linked_data = array_slice( $linked_data['data'], $link_offset, $number_of_links );

		if ( empty( $linked_data ) ) {
			//no data to return, bail out
			return $related_links;
		}

		/**
		 * Filters automated related links post query arguments.
		 *
		 * @param array $query_args An array of query arguments.
		 */
		$query_args = apply_filters( 'pmc_automated_related_links_query_args', $query_args );

		$pmc_related_articles = \pmc_related_articles( $post_id, $query_args ); // Fetch related articles for the post.
		$pmc_related_articles = ( ! empty( $pmc_related_articles ) && is_array( $pmc_related_articles ) ) ? $pmc_related_articles : [];

		$linked_post_ids = array_column( $linked_data, 'id' );

		// remove duplicate related articles.
		foreach ( $pmc_related_articles as $key => $related_post ) {

			$common_post = array_search( $related_post->post_id, (array) $linked_post_ids, true );

			if ( false !== $common_post ) {
				unset( $pmc_related_articles[ $key ] );
			}
		}

		$pmc_related_articles = array_values( $pmc_related_articles );

		foreach ( $linked_data as $key => $data ) {
			if ( false === $data['automated'] ) {
				$related_links[ $key ] = array(
					'id'        => $data['id'],
					'title'     => $data['title'],
					'url'       => $data['url'],
					'automated' => false,
				);
			}

			if ( true === $data['automated'] && ! empty( $pmc_related_articles ) ) {
				$related_article = (array) array_shift( $pmc_related_articles );

				$related_links[ $key ] = array(
					'id'        => $related_article['post_id'],
					'title'     => $related_article['title'],
					'url'       => $related_article['link'],
					'automated' => true,
				);

				unset( $related_article );
			}
		}

		unset( $pmc_related_articles, $linked_data );

		return $related_links;
	}

}

//EOF
