<?php
/**
 * Widget Template
 *
 * @since 1.0
 * @version 1.3.3 2010-10-21 Gabriel Koen
 * @version 2.0 2011-11-22 Gabriel Koen
 */
class MMC_Widget_Template extends WP_Widget {

	function __construct() {
		/* Widget settings. */
		$widget_ops = array('classname' => 'pmc_wt_widget_template', 'description' => __( 'Shows a widget based on a widget template and configuration.', 'pmc_templatized_widgets' ));

		/* Widget control settings. */
		$control_ops = array( 'width' => 450, 'height' => 350, 'id_base' => 'pmc_wt_widget' );

		/* Create the widget. */
		parent::__construct('pmc_wt_widget', __( 'Template', 'pmc_templatized_widgets' ), $widget_ops, $control_ops);
	}

	function form( $instance ) {
		// Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'pmc_wt_configuration' => '', 'pmc_wt_template' => '' ) );

		$templates = pmc_wt_get_templates();
		if ( !$templates ) {
			?><p><?php _e( 'No templates defined.', 'pmc_templatized_widgets' ); ?></p><?php
		} else {
			?>
			<input class="hidden" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />

			<input class="hidden" id="<?php echo $this->get_field_id('selected_configuration'); ?>" name="<?php echo $this->get_field_name('selected_configuration'); ?>" type="text" value="<?php echo $instance['pmc_wt_configuration']; ?>" />

			<select name="<?php echo $this->get_field_name('pmc_wt_template'); ?>" id="<?php echo $this->get_field_id('pmc_wt_template'); ?>" onchange="pmc_wt_populate_configuration_list(this, 'widget-<?php echo $this->id_base; ?>-<?php echo $this->number; ?>');">
				<option value=""><?php _e( 'Select a template...', 'pmc_templatized_widgets' ); ?></option>
			<?php
				foreach ( $templates as $template ) {
				?>
					<option value="<?php echo $template->ID; ?>"<?php selected($instance['pmc_wt_template'], $template->ID); ?>><?php echo $template->post_title; ?></option>
					<?php
				}
				?>
			</select>

			<select name="<?php echo $this->get_field_name('pmc_wt_configuration'); ?>" id="<?php echo $this->get_field_id('pmc_wt_configuration'); ?>" onchange="pmc_wt_preview_widget_configuration(this, 'widget-<?php echo $this->id_base; ?>-<?php echo $this->number; ?>');">
				<option value=""><?php _e( 'Select a configuration...', 'pmc_templatized_widgets' ); ?></option>
			</select>
			<div id="<?php echo $this->get_field_id('preview-area'); ?>" class="preview-area-wrapper">
				<h3 onclick="<?php echo esc_attr( "pmc_wt_preview_widget_configuration(document.getElementById('" . $this->get_field_id('pmc_wt_configuration') . "'), 'widget-" . $this->id_base . $this->number . "');"); ?>" ><?php _e('Configuration Preview', 'pmc_templatized_widgets' ); ?></h3>
			<?php
			// See if a rail template exists for this site and use it, otherwise use the default.
			$server_name = explode('.', $_SERVER['SERVER_NAME']);
			$rail_template = '/' . $server_name[count($server_name) - 2] . '-rail.phtml';
			$rail_template_path = dirname(__FILE__) . '/preview-templates';
				if ( file_exists($rail_template_path . '/' . $rail_template) ) {
				include $rail_template_path . $rail_template;
			} else {
				include $rail_template_path . '/default-rail.phtml';
			}
			?>
			</div>
			<script type="text/javascript" language="javascript">
				jQuery(document).ready(function(){
					var configuration_id = jQuery("#widget-<?php echo $this->id_base; ?>-<?php echo $this->number; ?>-selected_configuration").val();

					if ( configuration_id > 0 ) {
						//asyncing list load
						setTimeout(function(){
							pmc_wt_populate_configuration_list(document.getElementById("<?php echo $this->get_field_id('pmc_wt_template'); ?>"), "widget-<?php echo $this->id_base; ?>-<?php echo $this->number; ?>");
						},500);
						jQuery("#<?php echo $this->get_field_id('pmc_wt_configuration'); ?>").removeAttr("disabled");

					} else {
						jQuery("#<?php echo $this->get_field_id('pmc_wt_configuration'); ?>").attr("disabled", true);

						jQuery("#widget-<?php echo $this->id_base; ?>-<?php echo $this->number; ?>-preview-area").hide();
					}
				});
			</script>
			<?php
		}
	}

	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;

		$instance['pmc_wt_template'] = sanitize_text_field( $new_instance['pmc_wt_template'] );

		$instance['pmc_wt_configuration'] = sanitize_text_field( $new_instance['pmc_wt_configuration'] );

		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		$this->flush_widget_cache( $instance );

		return $instance;
	}

	function widget( $args, $instance ) {
		$cache_key = 'pmc_wt_' . $instance['pmc_wt_template'] . '-' . $instance['pmc_wt_configuration'];

		$cache_data = wp_cache_get($cache_key, 'widget');

		if ( $cache_data ) {
			echo $cache_data;
			return;
		}

		$cache_data = '';
		extract( $args );

		if ( $instance['pmc_wt_configuration'] > 0 ) {
			$configuration = get_post($instance['pmc_wt_configuration']);
			$template = get_post($instance['pmc_wt_template']);
			if ( $configuration && $template ) {

				$pmc_wt_template_data = get_post_meta( $template->ID, 'pmc_wt_template_data', true  );
				$pmc_wt_config_data =  get_post_meta( $configuration->ID, 'pmc_wt_config_data', true  );
				if( !empty( $pmc_wt_template_data ) ){
					$template->post_content = $pmc_wt_template_data;
				}

				if( !empty( $pmc_wt_config_data ) ){
					$configuration->post_content = $pmc_wt_config_data;
				}

				$configuration->post_content = unserialize($configuration->post_content);

				if( is_array( $configuration->post_content ) ){
					foreach ( $configuration->post_content as $key => $value ) {
						$template->post_content = str_replace('%%' . $key . '%%', $value, $template->post_content);
					}
				}

				$cache_data .= $before_widget;
				$cache_data .= '<span class="pmc-templatized-widget" data-label="'.esc_attr( sanitize_title( $template->post_title ) ).'">';
				$template->post_content = PMC::html_ssl_friendly( $template->post_content );
				$template->post_content = apply_filters( 'widget_text', $template->post_content, $instance );
				$template->post_content = do_shortcode( $template->post_content );
				$cache_data .= $template->post_content;
				$cache_data .= '</span>';
				$cache_data .= $after_widget;
			}
		}

		echo $cache_data;

		wp_cache_set( $cache_key, $cache_data, 'widget', 21600 );	//expiry set to 6 hours

	}

	function flush_widget_cache( $instance ) {

		$cache_key = 'pmc_wt_' . $instance['pmc_wt_template'] . '-' . $instance['pmc_wt_configuration'];

		wp_cache_delete($cache_key, 'widget');
	}
}

// EOF
