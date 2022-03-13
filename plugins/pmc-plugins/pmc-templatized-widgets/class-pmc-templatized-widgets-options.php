<?php
/**
 * Handles get/set of plugin options and WordPress options page
 * @since 2.0 2011-11-09 Gabriel Koen
 * @version 2.0 2011-11-09 Gabriel Koen
 * @todo Need to split this into logical objects: admin page, options, data api / model
 */
class PMC_Templatized_Widgets_Options
{

	/**
	 * String to use for the plugin name.  Used for generating class names, etc.
	 * @var string
	 */
	public static $plugin_id = 'pmc-templatized-widgets';

	/**
	 *
	 * @var string
	 */
	protected static $_subpage_base = '';

	/**
	 * String to use for the textdomain filename
	 * @var string
	 */
	public static $text_domain = 'pmc-templatized-widgets';


	/**
	 * Name of the option group for WordPress settings API
	 * @var string
	 */
	protected $_option_group = 'pmc-templatized-widgets-group';

	/**
	 * Name of the option for WordPress settings API
	 * @var string
	 */
	public static $option_name = 'pmc_templatized_widgets_options';

	/**
	 * Contains default optionss that get overridden in the constructor
	 * @var array
	 */
	public static $options_defaults = array(
	);

	/**
	 * Contains merged defaults + saved options
	 * @var array
	 */
	public static $options = array();

	/**
	 *
	 * @var array
	 */
	protected $_valid_tabs = array('configs', 'templates');

	/**
	 *
	 * @var array
	 */
	protected $_valid_actions = array('edit', 'new', 'trash');

	/**
	 * Hook into actions and filters here, along with any other global setup
	 * that needs to run when this plugin is invoked
	 *
	 *
	 * @since 2.0 2011-11-09 Gabriel Koen
	 * @version 2.0 2011-11-09 Gabriel Koen
	 */
	public function __construct() {
		load_plugin_textdomain( self::$text_domain, null, dirname( __FILE__ ) . '/languages/' );

		add_action( 'admin_menu', array(&$this,'admin_menu') );

		// NOTE: The class may be instantiated by init action with lower priority and may not fire any additional init event
		// that is added here to register the post type.  Therefore, we we need call register the post type directly at this
		// time to prevent other plugins errors with un-known post type when calling post save.
		// ie. when other plugins call current_user_can('edit_post',$post_id) without checking valid post type...
		$this->register_post_types();

		add_action( 'load-appearance_page_' . self::$plugin_id, array(&$this, 'save_template') );

		add_action( 'load-appearance_page_' . self::$plugin_id, array(&$this, 'save_configuration') );

		add_action( 'load-appearance_page_' . self::$plugin_id, array(&$this, 'trash_item') );

		// Only load on the widgets page
		add_action( 'admin_footer-widgets.php', array(&$this, 'load_widget_data') );

	}

	/**
	 * Load widget previews
	 *
	 * @since 1.2 2010-08-17 Gabriel Koen
	 * @version 1.3.3 2010-10-21 Gabriel Koen
	 * @version 2.0 2011-11-22 Gabriel Koen
	 */
	public function load_widget_data() {
		$templates = pmc_wt_get_templates();
		if ( $templates ) {
			foreach ( $templates as $template ) {
				$configurations = pmc_wt_get_configurations($template->ID);
				$configuration_options = array();

				if ( ! $configurations ) {
					$configuration_options[] = '|No configurations to show.';
				} else {
					if ( ! is_array($configurations) )
						$configurations = array($configurations);

					foreach ( $configurations as $configuration ) {
						$configuration_options[] = $configuration->ID . '|' . $configuration->post_title;
						?><div id="<?php echo esc_attr( 'pmc_wt_widget_configuration-'. $configuration->ID ); ?>" class="hidden"><textarea><?php
						$configuration_preview_html = $template->post_content;
						if ( ! empty( $configuration->post_content ) && is_array( $configuration->post_content ) ) {
							foreach ( $configuration->post_content as $key => $value ) {
								$configuration_preview_html = str_replace( '%%' . $key . '%%', $value, $configuration_preview_html );
							}
						}
						echo esc_textarea( $configuration_preview_html );
						?></textarea></div><?php
					}
				}
				?><div id="<?php echo esc_attr('pmc_wt_configuration_options-'. $template->ID ); ?>" class="hidden"><?php
				echo wp_kses_post( implode( '%%', $configuration_options ) );
				?></div><?php
			}
		}
		?>
		<script type="text/javascript" language="javascript">
			function pmc_wt_populate_configuration_list(template_select, field_id) {
				var template_id = template_select.value;

				jQuery("#" + field_id + "-pmc_wt_configuration").html("").append(jQuery("<option></option>").attr("value", "").text("Select a configuration..."));

				if ( template_id > 0 ) {
					var configuration_fields = jQuery("#pmc_wt_configuration_options-" + template_id).html().split("%%");

					jQuery(configuration_fields).each(function(){
						var configuration_data = this.split("|");

						jQuery("#" + field_id + "-pmc_wt_configuration").append(jQuery("<option></option>").attr("value", configuration_data[0]).text(configuration_data[1]));
					});

					jQuery("#" + field_id + "-pmc_wt_configuration").removeAttr("disabled");

					var selected_configuration = jQuery("#" + field_id + "-selected_configuration").val();

					jQuery("#" + field_id + "-pmc_wt_configuration option[value='" + selected_configuration + "']").attr("selected", true);
				}
			}

			function pmc_wt_preview_widget_configuration(configuration_select, field_id) {
				var configuration_id = configuration_select.value;

				jQuery("#" + field_id + "-preview-area").slideUp('slow', function() {
					if ( configuration_id > 0 ) {
						var widgetTitle = jQuery("#" + field_id + "-pmc_wt_configuration :selected").text();

						jQuery("#" + field_id + "-title").val(widgetTitle);

						var previewHtml = jQuery("#pmc_wt_widget_configuration-" + configuration_id + ' textarea' ).val();

						jQuery("#" + field_id + "-preview-area .widget-template-preview-area").html(previewHtml);

						jQuery("#" + field_id + "-preview-area").slideDown('slow');
					}
				});
			}
		</script>
		<?php
	}
	public function is_valid( $type, $name ) {
		switch ( $type ) {
			case 'action':
				$check_array = $this->_valid_actions;
				break;
			case 'tab':
				$check_array = $this->_valid_tabs;
				break;
		}
		return in_array( $name, $check_array );
	}

	public function clean_string( $string, $action = '' ) {
		$string = stripslashes($string);

		switch ( $action ) {
			case 'nohtml':
				// prevent someone from confusing our template
				$string = str_replace('%%', '%', $string);
				$string = strip_tags($string);
				$string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8', false);
				break;
			default:
				$extra_allowed_html = array(
					'strong' => array(
						'id' => array(),
						'class' => array(),
					),
					'time' => array(
						'id' => array(),
						'class' => array(),
						'datetime' => array(),
					),
				);
				global $allowedposttags;
				$allowed_html = array_merge( $allowedposttags, $extra_allowed_html );
				$string = wp_kses( $string, $allowed_html );
				unset( $allowed_html, $extra_allowed_html );
		}

		$string = trim($string);

		return $string;
	}

	public function verify_template_syntax( $html ) {
		// Find any token text that's not alphanumeric or a space and redirect with error
		$html = preg_replace_callback('/%%(.*?)%%/', 'PMC_Templatized_Widgets_Options::clean_token', $html);

		$html = force_balance_tags($html);

		return $html;
	}

	public static function clean_token( $matches ) {
		// Make sure we're dealing with the expected input from preg_replace_callback
		if ( is_array($matches) && 2 === count($matches) ) {
			// Strip out anything that's not alphanumeric, dash, underscore or space
			$matches = preg_replace('/([^a-zA-Z0-9-_ ])/', '', $matches[1]);
			$matches = '%%' . $matches . '%%';
		}

		return $matches;
	}

	public function trash_item() {
		if( !current_user_can( 'publish_posts' ) ){
			return;
		}
		if ( ! isset($_GET['action']) || 'trash' !== $_GET['action'] ) {
			return;
		}

		$action = ($this->is_valid( 'action', $_GET['action'])) ? $_GET['action'] : null;
		$tab = ($this->is_valid( 'tab', $_GET['tab'])) ? $_GET['tab'] : null;

		check_admin_referer( self::$plugin_id . '-trash_' . $tab );

		$id = (int)$_GET['item_id'];

		$done = wp_trash_post($id);

		if ( $done && ! is_wp_error($done) ) {
			// Success
			wp_safe_redirect( add_query_arg( 'message', 6, wp_get_referer() ) );
			exit;
		} else {
			// Failure
			wp_safe_redirect( add_query_arg( 'message', 7, wp_get_referer() ) );
			exit;
		}

	}

	public function save_template() {
		if( !current_user_can( 'publish_posts' ) ){
			return;
		}
		// Only run if POSTing template data
		if ( empty($_POST) || isset($_POST['tab']) && 'templates' !== $_POST['tab'] ) {
			return;
		}

		$action = ($this->is_valid( 'action', $_POST['action'])) ? $_POST['action'] : null;
		$tab = ($this->is_valid( 'tab', $_POST['tab'])) ? $_POST['tab'] : null;

		check_admin_referer(self::$plugin_id . '-' . $action . '_' . $tab);

		$data = array(
			'post_title' => $_POST['template_name'],
			'post_content' => $_POST['template_html'],
		);

		$data = $this->scrub_data($data, 'template');

		if ( empty($data['post_title']) && empty($data['post_content']) ) {
			// No data left after sanitization
			wp_safe_redirect( add_query_arg( 'message', 8, wp_get_referer() ) );
			exit;
		}

		switch ( $action ) {
			case 'new':
				$success_message = 1;
				$failure_message = 4;
				$post_id = $this->save($data, 'insert', 'pmc_wt_template_data' );
                // @todo Need to delete the widget cache
				break;

			case 'edit':
				$data['ID'] = (int)$_POST['item_id'];
				$success_message = 3;
				$failure_message = 5;
				$post_id = $this->save($data, 'update', 'pmc_wt_template_data' );
                // @todo Need to delete the widget cache
				break;
		}

		if ( $post_id && ! is_wp_error($post_id) ) {
			// Success
			wp_safe_redirect( add_query_arg( 'message', $success_message, wp_get_referer() ) );
			exit;
		} else {
			// Failure
			wp_safe_redirect( add_query_arg( 'message', $failure_message, wp_get_referer() ) );
			exit;
		}

	}

	public function save( $post_data, $save_type, $post_meta_key ) {

		switch ( $save_type ) {
			case 'insert':
				$post_data['post_status'] = 'publish';
				$post_data['post_date'] = current_time('mysql');
				$result = wp_insert_post($post_data);
                // @todo Need to delete the widget cache
				break;

			case 'update':
			default:
				$result = wp_update_post($post_data);
                // @todo Need to delete the widget cache

				// flush the templatized widget cache
				if ( 'pmc_wt_config_data' === $post_meta_key ) {
					$template_post = get_post( $post_data['post_parent'] );
					if ( !empty( $template_post ) ) {
						PMC_Templatized_Widgets::get_instance()->flush_cache( $template_post->post_name, $post_data['post_name'] );
					}
				} else {
					PMC_Templatized_Widgets::get_instance()->flush_cache( $post_data['post_name'] );
				}

				break;
		}

		if ( $result && ! is_wp_error( $result ) ){
			update_post_meta( $result, $post_meta_key, $post_data['post_content'] );
		}

		return $result;
	}

	public function scrub_data( $data, $data_type ) {
		if ( isset($data['post_title']) ) {
			$data['post_name'] = sanitize_title($data['post_title']);
		}

		$data['post_title'] = $this->clean_string($data['post_title'], 'nohtml');

		switch ( $data_type ) {
			case 'template':
				$data['post_type'] = 'pmc_widget_template';
				$data['post_content'] = $this->clean_string($data['post_content']);
				$data['post_content'] = $this->verify_template_syntax($data['post_content']);
				break;

			case 'config':
				$data['post_type'] = 'pmc_widget_data';
				if ( !empty($data['post_content']) && is_array($data['post_content']) ) {
					foreach ( $data['post_content'] as $key => $value ) {
						/** The array saves content in duplicate key/values. One pair of key values is cleaned up another raw. Most code uses uncleaned key. We saving cleaned values is uncleaned keys as well. To fix this permanently, we need to trim all the key value pair's before we save or show it. To do that we will need to clean all the templates as well, since replacement keys are uncleaned.
										Having uncleaned key values make unserialize function bark, hence no html will be shown in front end.
										**/
						$key_backup = $key;

						$key = $this->clean_string($key, 'nohtml');
						$value = $this->clean_string($value, 'nohtml');
						$data['post_content'][$key] = $value;
						$data['post_content'][$key_backup] = $value;
					}
					$data['post_content'] = serialize($data['post_content']);
				}
				break;
		}

		return $data;
	}

	public function save_configuration() {
		if( !current_user_can( 'publish_posts' ) ){
			return;
		}
		// Only run if POSTing configuration data
		if ( empty($_POST) || isset($_POST['tab']) && 'configs' !== $_POST['tab'] ) {
			return;
		}

		$action = ($this->is_valid( 'action', $_POST['action'])) ? $_POST['action'] : null;
		$tab = ($this->is_valid( 'tab', $_POST['tab'])) ? $_POST['tab'] : null;

		check_admin_referer(self::$plugin_id . '-' . $action . '_' . $tab);

		$data = array(
			'post_title' => $_POST['configuration_name'],
		);

		foreach ( $_POST as $key => $value ) {
			if ( '_input'=== substr($key, -6) ) {
				$key = str_replace('_input', '', $key);
				$key = str_replace('_', ' ', $key);
				$data['post_content'][$key] = $value;
			}
		}

		$data = $this->scrub_data($data, 'config');

		if ( empty($data['post_content']) && empty($data['post_title']) ) {
			// No data left after sanitization
			wp_safe_redirect( add_query_arg( 'message', 8, wp_get_referer() ) );
			exit;
		}

		$data['post_parent'] = (int) $_POST['template_id'];

		switch ( $action ) {
			case 'new':
				$success_message = 1;
				$failure_message = 4;
				$post_id = $this->save($data, 'insert', 'pmc_wt_config_data' );
				break;

			case 'edit':
				$data['ID'] = (int)$_POST['item_id'];
				$success_message = 3;
				$failure_message = 5;
				$post_id = $this->save($data, 'update', 'pmc_wt_config_data' );
				break;
		}

		if ( $post_id && ! is_wp_error($post_id) ) {
			// Success
			wp_safe_redirect( add_query_arg( 'message', $success_message, wp_get_referer() ) );

			if( isset( $_POST['template_id'] ) && !empty( $_POST['template_id'] ) ){
				$cache_key = 'pmc_wt_' . intval($_POST['template_id']) . '-' . $post_id;
				wp_cache_delete($cache_key, 'widget');
			}

			exit;
		} else {
			// Failure
			wp_safe_redirect( add_query_arg( 'message', $failure_message, wp_get_referer() ) );
			exit;
		}

	}

	public function register_post_types() {
		// for holding the templates
		register_post_type(
			'pmc_widget_template',
			array(
				'label'   => __('PMC Widget Templates', 'pmc-templatized-widgets' ),
				'public'  => false,
				'rewrite' => false,
			)
		);

		// for holding the data
		register_post_type(
			'pmc_widget_data',
			array(
				'label'   => __('PMC Widget Data', 'pmc-templatized-widgets' ),
				'public'  => false,
				'rewrite' => false,
			)
		);
	}

	/**
	 * Returns Singleton instance of this plugin
	 *
	 * @since 2.0 2011-11-09 Gabriel Koen
	 * @version 2.0 2011-11-09 Gabriel Koen
	 * @return PMC_Templatized_Widgets_Options
	 */
	public static function instance()
	{
		static $_instance = null;

		if ( is_null($_instance) ) {
			$class = __CLASS__;
			$_instance = new $class();
		}

		return $_instance;
	}

	/**
	 * Merge the saved options with the defaults
	 *
	 * @since 2.0 2011-11-09 Gabriel Koen
	 * @version 2.0 2011-11-09 Gabriel Koen
	 */
	public static function setup_options() {
		self::$options = array_merge(self::$options_defaults, get_option( self::$option_name, array() ));
	}

	/**
	 * Add the admin menu page
	 *
	 * @since 2.0 2011-11-09 Gabriel Koen
	 * @version 2.0 2011-11-09 Gabriel Koen
	 */
	public function admin_menu() {
		add_theme_page(__( 'Templatized Widgets', self::$text_domain ), __( 'Templatized Widgets', self::$text_domain ), 'edit_others_posts', self::$plugin_id, array(&$this, 'template_configuration_page') );
		self::$_subpage_base = admin_url( 'themes.php?page=' . self::$plugin_id );
	}

	public function get_tab_url( $tab_name = 'configs', $type = '' ) {
		return self::$_subpage_base . '&tab=' . $tab_name;
	}

	/**
	 * Templatized Widget Configuration page
	 *
	 * @since 1.0
	 * @version 1.3.6 2011-02-25 Prashant M
	 * @version 2.0 2011-11-09 Gabriel Koen
	 */
	public function template_configuration_page() {
		$tabs = array(
			'configs' => __( 'Configurations', 'pmc_templatized_widgets' ),
			'templates' => __( 'Templates', 'pmc_templatized_widgets' ),
		);

		// Set the current tab, if it exists in the $tabs array
		// Defaults to configs
		$tab = 'configs';
		if ( !empty($_GET['tab']) && isset($tabs[$_GET['tab']]) ) {
			$tab = $_GET['tab'];
		}

		switch ( $tab ) {
			case 'configs':
				$messages[1] = __( 'Configuration added.', 'pmc_templatized_widgets' );
				$messages[2] = __( 'Configuration deleted.', 'pmc_templatized_widgets' );
				$messages[3] = __( 'Configuration updated.', 'pmc_templatized_widgets' );
				$messages[4] = __( 'Configuration <strong>not</strong> added.', 'pmc_templatized_widgets' );
				$messages[5] = __( 'Configuration <strong>not</strong> updated.', 'pmc_templatized_widgets' );
				$messages[6] = __( 'Configuration trashed.', 'pmc_templatized_widgets' );
				$messages[7] = __( 'Configuration <strong>not</strong> trashed.', 'pmc_templatized_widgets' );
				break;

			case 'templates':
				$messages[1] = __( 'Template added.', 'pmc_templatized_widgets' );
				$messages[2] = __( 'Template deleted.', 'pmc_templatized_widgets' );
				$messages[3] = __( 'Template updated.', 'pmc_templatized_widgets' );
				$messages[4] = __( 'Template <strong>not</strong> added.', 'pmc_templatized_widgets' );
				$messages[5] = __( 'Template <strong>not</strong> updated.', 'pmc_templatized_widgets' );
				$messages[6] = __( 'Template trashed.', 'pmc_templatized_widgets' );
				$messages[7] = __( 'Template <strong>not</strong> trashed.', 'pmc_templatized_widgets' );
				break;
		}
		$messages[8] = __( 'All fields are required.', 'pmc_templatized_widgets' );
		$messages[9] = __( 'Template <strong>not</strong> saved. Tokens may only contain letters, numbers and spaces.', 'pmc_templatized_widgets' );
		remove_query_arg('message');

		// @todo Add a column with the number of times each configuration is used
		register_column_headers('settings_page_widget-settings', array('cb' => '<input type="checkbox" />', 'name' => 'Name', 'created' => 'Created', 'last_modified' => 'Last Modified'));
		?>
		<div class="wrap nosubsub">
			<?php screen_icon('themes'); ?>
			<h2><?php esc_html_e( 'Templatized Widgets', 'pmc_templatized_widgets' ); ?> <a href="<?php echo esc_url( $this->get_tab_url( $tab ) ); ?>" class="add-new-h2"><?php esc_html_e( 'Add New', 'pmc_templatized_widgets' ); ?></a></h2>

			<?php
			if ( isset($_GET['message']) && ( $msg = (int) $_GET['message'] ) ) {
				?>
				<div id="message" class="updated fade">
					<p><?php echo esc_html( $messages[ $msg ] ); ?></p>
				</div>
				<?php
			}
			?>
			<ul class="subsubsub">
				<?php
				$tabhtml = array();
				foreach ( $tabs as $stub => $title ) {
					$class = ( $stub === $tab ) ? ' class="current"' : '';
					$tabhtml[] = sprintf( '<li><a href="%1$s" %2$s>%3$s</a>',
						esc_url( $this->get_tab_url( $stub ) ),
						esc_html( $class ),
						esc_html( $title )
					);
				}
				echo wp_kses_post( implode( ' |</li>', $tabhtml ) . '</li>' );
				?>
			</ul>
			<br class="clear">
			<?php
			/* @todo Implement search
			  <form class="search-form topmargin" action="" method="get">
			  <p class="search-box">
			  <label class="screen-reader-text" for="widget-template-search-input">Search Templatized Widgets:</label>
			  <input type="text" id="widget-template-search-input" name="s" value="">
			  <input type="submit" value="Search Templates" class="button">
			  </p>
			  </form> */
			?>
			<p><?php _e('Templates define the look and feel of the widget, and configurations define the content.', 'pmc_templatized_widgets' ); ?></p>
			<div id="col-container">
				<div id="col-right">
					<div class="col-wrap">
						<?php $this->edit_table($tab); ?>
					</div>
				</div>
				<div id="col-left">
					<div class="col-wrap">
						<div class="form-wrap">
							<?php
							switch ($tab ) {
								case 'configs':
									$this->configuration_form();
									break;
								case 'templates':
									$this->templates_form();
									break;
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}


	/**
	 * Get total number of templates or configurations
	 *
	 * @param string $type Post type name
	 * @return int $total_posts
	 */
	public function get_total_posts( $type ) {
		global $wpdb;

		$total_posts = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(ID)
			FROM {$wpdb->posts}
			WHERE post_type=%s
				AND post_status='publish'", $type ) );

		return (int) $total_posts;
	}


	public function edit_table( $tab ) {
		?>
		<form id="posts-filter" action="" method="get">
			<div class="tablenav">
				<?php
				$pagenum = isset($_GET['pagenum']) ? absint($_GET['pagenum']) : 0;
				if (empty($pagenum))
					$pagenum = 1;

				$items_per_page = 20;

				switch ( $tab ) {
					case 'configs':
						$results = pmc_wt_get_configurations(0, array(), $pagenum, $items_per_page);
						$total_count = $this->get_total_posts('pmc_widget_data');
						break;

					case 'templates':
						$results = pmc_wt_get_templates(array(), $pagenum, $items_per_page);
						$total_count = $this->get_total_posts('pmc_widget_template');
						break;
				}

				$num_items = count($results);
				if ($num_items < 1)
					$num_items = 1;

				$page_links = paginate_links(array(
					'base' => add_query_arg('pagenum', '%#%'),
					'format' => '',
					'prev_text' => __( '&laquo;', 'pmc_templatized_widgets' ),
					'next_text' => __( '&raquo;', 'pmc_templatized_widgets' ),
					'total' => ceil($total_count / $items_per_page),
					'current' => $pagenum
				));

				if ( $page_links ) {
					?>
					<div class="tablenav-pages"><?php echo esc_html( $page_links ); ?></div>
					<?php
				}
				?>
				<br class="clear" />
			</div>

			<div class="clear"></div>

			<table class="widefat fixed" cellspacing="0">
				<thead>
					<tr>
						<?php print_column_headers('settings_page_widget-settings'); ?>
					</tr>
				</thead>

				<tfoot>
					<tr>
						<?php print_column_headers('settings_page_widget-settings', false); ?>
					</tr>
				</tfoot>

				<tbody id="the-list" class="list:tpl">
					<?php
					if ( ! $results ) {
						?>
						<tr>
							<td colspan="4">
								<?php esc_html_e( 'No items to show.', 'pmc_templatized_widgets' ); ?>
							</td>
						</tr>
						<?php
					} else {
						for ( $counter=0; $counter < $num_items; $counter++ ) {
							$alternate = ($counter % 2) ? '' : ' alternate';
							$item = $results[$counter];
							?>
							<tr id="<?php echo esc_attr( 'template-bulk-'. $item->ID ); ?>" class="<?php echo esc_attr( 'iedit'. $alternate ); ?>">
								<th scope="row" class="check-column">
									<input type="checkbox" name="trash[]" value="<?php echo esc_attr( $item->ID ); ?>">
								</th>
								<td class="name column-name">
									<a class="row-title" href="<?php echo esc_url( add_query_arg(array('edit' => $item->ID) ) ); ?>" title="<?php esc_html_e('Edit', 'pmc_templatized_widgets' ); ?> <?php echo esc_html( $item->post_title ); ?>"><?php echo esc_html( $item->post_title ); ?></a><br />
									<?php
									if ( $item->post_parent > 0 ) {
										$template = get_post( $item->post_parent );
										if ( $template ) {
											echo esc_html( sprintf( __( 'Template: %s', 'pmc_templatized_widgets' ), $template->post_title ) );
										}
									}

									$trash_url = $this->get_tab_url($tab);
									$trash_url = add_query_arg( 'item_id', $item->ID, $trash_url );
									$trash_url = add_query_arg( 'action', 'trash', $trash_url );
									$trash_url = wp_nonce_url( $trash_url, self::$plugin_id . '-trash_' . $tab );
									?>
									<div class="row-actions">
										<span class="edit"><a href="<?php echo esc_url( add_query_arg( 'edit', $item->ID, $this->get_tab_url( $tab ) ) ); ?>"><?php esc_html_e( 'Edit', 'pmc_templatized_widgets' ); ?></a> | </span><span class="trash"><a class="submitdelete" href="<?php echo esc_url( $trash_url ); ?>"><?php esc_html_e( 'Trash', 'pmc_templatized_widgets' ); ?></a></span>
									</div>
								</td>
								<td class="created column-created">
									<?php echo esc_html( $item->post_date ); ?>
								</td>
								<td class="last_modified column-last_modified">
									<?php echo esc_html( $item->post_modified ); ?>
								</td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>

			<div class="tablenav">
				<?php
				if ( $page_links ) {
					?>
					<div class="tablenav-pages"><?php echo esc_html( $page_links ); ?></div>
					<?php
				}
				?>
				<div class="alignleft actions">
					<?php /* @todo Implement bulk actions
					  <select name="action2">
					  <option value="" selected="selected"><?php _e('Bulk Actions', 'pmc_templatized_widgets' ); ?></option>
					  <option value="trash"><?php _e('Trash', 'pmc_templatized_widgets' ); ?></option>
					  </select>
					  <input type="submit" value="<?php esc_attr_e('Apply'); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
					  <?php wp_nonce_field('bulk-categories'); ?>
					  </div>
					 */ ?>
					<br class="clear" />
				</div>
			</div>
		</form>
		<?php
	}


	public function configuration_form() {
		$tab = 'configs';
		$template_id = isset($_POST['template_id']) ? (int)$_POST['template_id'] : null;
		$configuration_name = isset( $_POST['configuration_name'] ) ? $_POST['configuration_name'] : '';

		$date_created = time();
		$date_modified = time();

		if ( isset($_GET['edit']) ) {
			$action = 'edit';
			$page_title =  sprintf( __( 'You are editing &ldquo;%s&rdquo;', 'pmc_templatized_widgets' ), $configuration_name );
			$save_button_text = __( 'Save Changes', 'pmc_templatized_widgets' );
			$configuration_id = (int) $_GET['edit'];
			$configuration_result = pmc_wt_get_configurations(0, array('p' => $configuration_id));
			if ( isset($configuration_result[0]) ) {
				$template_id = $configuration_result[0]->post_parent;
				$configuration_name = $configuration_result[0]->post_title;
				$configuration_fields = $configuration_result[0]->post_content;
				$date_created = mysql2date('U', $configuration_result[0]->post_date);
				$date_modified = mysql2date('U', $configuration_result[0]->post_modified);
			}
		} else {
			$action = 'new';
			$page_title = __( 'New Configuration', 'pmc_templatized_widgets' );
			$save_button_text = __( 'Add Configuration', 'pmc_templatized_widgets' );
		}

		// If template name isn't empty, then display the "editing" text so that it's clear why the form has data in it.
		if ( !empty($configuration_name) ) {
			?>
			<h3><?php echo esc_html( sprintf( __( 'You are editing &ldquo;%s&rdquo;', 'pmc_templatized_widgets' ),  $configuration_name ) ); ?></h3>
			<?php
			if ( isset( $configuration_result[0] ) ) {
				/* translators: date format, see http://php.net/date */
				$date_format = __('F j Y, g:i:s a');
				?>
				<p class="description"><?php echo esc_html( sprintf( __( 'Created %s', 'pmc_templatized_widgets' ), date_i18n( $date_format, $date_created ) ) ); ?><br />
					<?php echo esc_html( sprintf( __( 'Last modified %s', 'pmc_templatized_widgets' ), date_i18n( $date_format, $date_modified ) ) ); ?>
				</p>
				<?php
			}
		} else {
			?>
			<h3><?php echo esc_html( $page_title ); ?></h3>
			<?php
		}
		?>
		<div id="ajax-response"></div>
		<form name="<?php echo esc_attr( $action . '_' . $tab ); ?>" id="<?php echo esc_attr( $action . '_' . $tab ); ?>" method="post" action="<?php echo esc_url( $this->get_tab_url( $tab ) ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>" />
			<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />

			<?php
			wp_nonce_field(self::$plugin_id . '-' . $action . '_' . $tab);

			if ( isset($_GET['edit']) ) {
				?>
				<input type="hidden" name="item_id" value="<?php echo esc_attr( $configuration_id ); ?>" />
				<?php
			}
			?>
			<select name="template_id" id="template_id" onchange="pmc_wt_populate_configuration_form(this);">
				<option value=""><?php esc_html_e( 'Please select...', 'pmc_templatized_widgets' ); ?></option>
				<?php
				$fields = array();
				$templates = pmc_wt_get_templates();
				foreach ( $templates as $template ) {
					?>
					<option value="<?php echo esc_attr( $template->ID ); ?>"<?php selected($template_id, $template->ID); ?>><?php echo esc_html( $template->post_title ); ?></option>
					<?php
					preg_match_all('/%%(.*?)%%/sm', $template->post_content, $matches);
					if ( isset($matches[1]) && !empty($matches[1]) ) {
						for ( $i=0; $i < count($matches[1]); $i++ ) {
							$fields[$template->ID][$matches[1][$i]] = $matches[1][$i];
						}
					}
				}
				?>
			</select>

			<?php
			// Store the configurations
			foreach ( $fields as $id => $data ) {
				?>
				<div id="<?php echo esc_attr( 'template-'. $id ); ?>" class="hidden"><?php echo esc_html( implode( '%%', $data ) ); ?></div>
				<?php
			}

			// Store the template HTML
			foreach ( $templates as $template ) {
				?>
				<div id="<?php echo esc_attr( 'preview-'. $template->ID ); ?>" class="hidden"><?php echo esc_html( $template->post_content ); ?></div>
				<?php
			}

			// Output the configurations for this template
			if ( isset($_GET['edit']) ) {
				$configuration_result = pmc_wt_get_configurations(0, array('p' => (int) $_GET['edit']));
				?>
				<div id="configuration-edit-values" class="hidden"><?php
					if ( ! empty( $configuration_result[0] ) ) {
						$tmp = array('configuration_name|' . $configuration_result[0]->post_title);
						if ( ! empty( $configuration_result[0]->post_content ) && is_array( $configuration_result[0]->post_content ) ) {
							foreach ( $configuration_result[0]->post_content as $key => $value ) {
								$tmp[] = sprintf( '%s|%s', $key, $value );
							}
						}
						echo wp_kses_post( implode( '%%', $tmp ) );
					}
				?></div>
				<?php
			}
			?>

			<div id="configuration-fields" style="display: none;"></div>

			<div class="clear"></div>

			<p class="submit" style="display: none;">
				<?php
				if ( isset($_GET['edit']) ) {
					?><a id="cancel-widget-template" class="cancel button-secondary alignleft" href="<?php echo esc_url( $this->get_tab_url( $tab ) ); ?>"><?php esc_html_e('Cancel', 'pmc_templatized_widgets' ); ?></a><?php
				}
				?>

				<a id="preview-widget-template" class="button-secondary action alignright"><?php esc_html_e('Preview', 'pmc_templatized_widgets' ); ?></a>

				<input type="submit" class="button-primary alignright" name="submit" id="save-widget-template" value="<?php echo esc_attr( $save_button_text ); ?>">
			</p>
			<script type="text/javascript" language="javascript">
				function pmc_wt_populate_configuration_form(configuration_select) {
					var configuration_defaults = '<div class="form-field form-required"><label for="configuration_name"><?php esc_html_e( 'Configuration Name', 'pmc_templatized_widgets' ); ?></label><input type="text" name="configuration_name" id="configuration_name" value="' + jQuery("#template_id :selected").text() + ' <?php echo esc_attr( date( "Y-m-d" ) ); ?>" /><p>This is a name for your reference, it will not be displayed to the public.</p></div>';

					var template_id = configuration_select.value;

					// Fade out the current configuration-fields and then clear the HTML (for a smooth transition).
					jQuery("#<?php echo $action . '_' . $tab; ?> p.submit").fadeOut('fast', function() {
						jQuery("#configuration-fields").slideUp('slow', function() {
							jQuery("#configuration-fields").html(configuration_defaults);

							jQuery("#preview-area").slideUp('fast', function() {
								jQuery("#preview-area .widget-template-preview-area").html("");
							});

							var config_values;

							if (template_id != '' && jQuery("#template-" + template_id).length != 0) {
								config_values = jQuery("#template-" + template_id).html().split('%%');
							}

							jQuery(config_values).each(function(){
								cleanName = this.replace(/\s/g, '_');

								jQuery("#configuration-fields").append("<div class=\"form-field form-required\"><label for=\"" + cleanName + "_input\">" + this + "</label><textarea name=\"" + cleanName + "_input\" id=\"" + cleanName + "_input\" rows=\"3\" cols=\"40\"></textarea></div>");
							});

							if (template_id != '') {
								jQuery("#configuration-fields").slideDown('slow', function() {
									jQuery("#<?php echo $action . '_' . $tab; ?> p.submit").fadeIn();
								});
							}
						});

					});
				}

				jQuery(document).ready(function() {
				<?php if (isset($_GET['edit'])) { ?>
					jQuery("#template_id").attr("disabled", true);
					// Remove disabled attribute on submit so that the template_id is POSTed along with the rest of the form data
					jQuery("#<?php echo $action . '_' . $tab; ?>").submit(function() {
						jQuery("#template_id").attr("disabled", false);
					});
					pmc_wt_populate_configuration_form(document.getElementById("template_id"));

					var editValues = jQuery("#configuration-edit-values").html().split("%%");

					jQuery(editValues).each(function(){
						var widgetValue = this.split("|");
						if ( 'configuration_name' == widgetValue[0] ) {
							jQuery("#configuration_name").val(widgetValue[1]);
						} else {
							var cleanName = widgetValue[0].replace(/\s/g, '_') + "_input";
							jQuery("#" + cleanName).val(widgetValue[1]);
						}
					});
				<?php } ?>
				<?php // @todo Preview in a thickbox popup	?>
					jQuery("#preview-widget-configuration").click(function() {
						var template_id = jQuery("#template_id").val();

						var previewHtml = jQuery("#preview-" + template_id).html();

						jQuery("#configuration-fields label").each(function(){
							var configuration_tag = jQuery(this).attr('for').replace("_input", "").replace(/_/g, " ");

							var regExPattern = new RegExp("%%" + configuration_tag + "%%", "g");
							var regExReplace = jQuery("#" + jQuery(this).attr('for')).val();

							previewHtml = previewHtml.replace(regExPattern, regExReplace);
						});

						jQuery("#preview-area .widget-template-preview-area").html(previewHtml);

						jQuery("#preview-area").slideDown('slow');
					});
				});
			</script>
		</form>
		<?php
		$this->widget_preview();
	}


	public function templates_form() {
		$tab = 'templates';
		$template_name = isset( $_POST['template_name'] ) ? $_POST['template_name'] : '';
		$template_html = isset( $_POST['template_html'] ) ? $_POST['template_html'] : '';
		$date_created = time();
		$date_modified = time();

		if ( isset($_GET['edit']) ) {
			$action = 'edit';
			$page_title =  sprintf( __( 'You are editing &ldquo;%s&rdquo;', 'pmc_templatized_widgets' ), $template_name );
			$save_button_text = __( 'Save Changes', 'pmc_templatized_widgets' );
			$template_id = (int) $_GET['edit'];
			$template_result = pmc_wt_get_templates(array('p' => $template_id));
			if ( isset($template_result[0]) ) {
				$template_name = $template_result[0]->post_title;
				$template_html = $template_result[0]->post_content;
				$date_created = mysql2date('U', $template_result[0]->post_date);
				$date_modified = mysql2date('U', $template_result[0]->post_modified);
			}
		} else {
			$action = 'new';
			$page_title = __( 'New Template', 'pmc_templatized_widgets' );
			$save_button_text = __( 'Add Template', 'pmc_templatized_widgets' );
		}

		// If template name isn't empty, then display the "editing" text so that it's clear why the form has data in it.
		if ( ! empty( $template_name ) ) {
			?>
			<h3><?php echo esc_html( sprintf( __( 'You are editing &ldquo;%s&rdquo;', 'pmc_templatized_widgets' ), $template_name ) ); ?></h3>
			<?php
			if ( isset($template_result[0]) ) {
				/* translators: date format, see http://php.net/date */
				$date_format = __('F j Y, g:i:s a');
				?>
				<p class="description">
					<?php echo esc_html( sprintf( __( 'Created %s', 'pmc_templatized_widgets' ), date_i18n( $date_format, $date_created ) ) ); ?><br />
				<?php echo esc_html( sprintf( __( 'Last modified %s', 'pmc_templatized_widgets' ), date_i18n( $date_format, $date_modified ) ) ); ?></p>
				<?php
			}
		} else {
			?>
			<h3><?php echo esc_html( $page_title ); ?></h3>
			<?php
		}
		?>
		<div id="ajax-response"></div>
		<form name="<?php echo esc_attr( $action . '_' . $tab ); ?>" id="<?php echo esc_attr( $action . '_' . $tab ); ?>" method="post" action="<?php echo esc_url( $this->get_tab_url( $tab ) ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>" />
			<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />

			<?php
			wp_nonce_field(self::$plugin_id . '-' . $action . '_' . $tab);

			if ( isset($_GET['edit']) ) {
				?>
				<input type="hidden" name="item_id" value="<?php echo esc_attr( $template_id ); ?>" />
				<?php
			}
			?>
			<div class="form-field form-required">
				<label for="template_name"><?php _e('Template Name', 'pmc_templatized_widgets' ); ?></label>
				<input type="text" name="template_name" id="template_name" value="<?php echo esc_attr( $template_name ); ?>" />
				<p><?php _e('This is a name for your reference, it will not be displayed to the public.', 'pmc_templatized_widgets' ); ?></p>
			</div>
			<div class="form-field form-required">
				<label for="template_html"><?php _e('Template HTML', 'pmc_templatized_widgets' ); ?></label>
				<textarea name="template_html" id="template_html" rows="15" cols="40"><?php echo esc_textarea( $template_html ); ?></textarea>
				<p><?php esc_html_e( 'Templates are standard HTML with one exception: You place <abbr title="Example: %%Token Name%%">tokens</abbr> in your template.  Then you create configurations that replace the tokens with your content.', 'pmc_templatized_widgets' ); ?></p>
				<p><?php esc_html_e( 'Example:', 'pmc_templatized_widgets' ); ?><pre>&lt;a href=&quot;%%Link Url%%&quot;&gt;%%Link Text%%&lt;/a&gt;</pre></p>
				<p><?php esc_html_e( 'It is recommended that you edit your template HTML in an HTML editor and then paste it here.', 'pmc_templatized_widgets' ); ?></p>
				<p><?php esc_html_e( 'If you change a <abbr title="Example: %%Token Name%%">token name</abbr>, any existing configurations that use this template will need to be updated.', 'pmc_templatized_widgets' ); ?></p>
			</div>
			<div id="configuration-fields"></div>
			<div class="clear"></div>
			<p class="submit">
				<?php
				if ( isset($_GET['edit']) ) {
					?><a id="cancel-widget-template" class="cancel button-secondary alignleft" href="<?php echo esc_url( $this->get_tab_url( $tab ) ); ?>"><?php esc_html_e( 'Cancel', 'pmc_templatized_widgets' ); ?></a><?php
				}
				?>

				<a id="preview-widget-template" class="button-secondary action alignright"><?php esc_html_e( 'Preview', 'pmc_templatized_widgets' ); ?></a>

				<input type="submit" class="button-primary alignright" name="submit" id="save-widget-template" value="<?php echo esc_attr( $save_button_text ); ?>">
			</p>
		</form>
		<?php
		$this->widget_preview();
	}

	public function widget_preview() {
		?>
		<script type="text/javascript" language="javascript">
			jQuery(document).ready(function() {
			<?php // @todo Preview in a thickbox popup	?>
				jQuery("#preview-widget-template").click(function() {
					var template_id = jQuery("#template_id").val();

					var previewHtml = jQuery("#preview-" + template_id).html();

					jQuery("#configuration-fields label").each(function(){
						var configuration_tag = jQuery(this).attr('for').replace("_input", "").replace(/_/g, " ");

						var regExPattern = new RegExp("%%" + configuration_tag + "%%", "g");
						var regExReplace = jQuery("#" + jQuery(this).attr('for')).val();

						previewHtml = previewHtml.replace(regExPattern, regExReplace);
					});

					jQuery("#preview-area .widget-template-preview-area").html(previewHtml);

					jQuery("#preview-area").slideDown('slow');
				});
			});
		</script>
		<div id="preview-area" class="preview-area-wrapper" style="display: none;">
			<h3><?php _e('Configuration Preview', 'pmc_templatized_widgets' ); ?></h3>
			<?php
			// See if a rail template exists for this site and use it, otherwise use the default.
			$rail_template = '/' . PMC_SITE_NAME . '-rail.phtml';
			$rail_template_path = dirname(__FILE__) . '/preview-templates';
			if ( file_exists($rail_template_path . '/' . $rail_template) ) {
				include $rail_template_path . $rail_template;
			} else {
				include $rail_template_path . '/default-rail.phtml';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Plugin option getter
	 *
	 * @since 2.0 2011-11-09 Gabriel Koen
	 * @version 2.0 2011-11-09 Gabriel Koen
	 */
	public static function get_option($option_key = '') {
		if ( empty(self::$options) ) {
			self::setup_options();
		}

		if ( isset(self::$options[$option_key]) ) {
			return self::$options[$option_key];
		}

		return null;
	}

	/**
	 * Plugin option setter
	 *
	 * @since 2.0 2011-11-09 Gabriel Koen
	 * @version 2.0 2011-11-09 Gabriel Koen
	 */
	public static function set_option($option_key, $option_value = '') {
		self::$options[$option_key] = $option_value;
	}


}

// EOF
