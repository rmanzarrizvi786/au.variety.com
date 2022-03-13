<?php
/*
	Plugin Name: PMC Templatized Widgets
	Plugin URI: http://www.pmc.com/
	Description: Create widget templates that can be re-used in multiple sidebars.
	Version: 2.0.1
	Author: PMC
	Author URI: http://www.pmc.com/
	License: PMC Proprietary.  All rights reserved.

	Known issues:
 * In the configuration preview in Firefox, tokens with spaces inside hrefs are not properly replaced with their preview values because the tokens are url encoded
 * If you archive a template that has active configurations, you will not be able to archive the configurations

	Changelog:

	@date 2012-07-25
	@author Gabriel Koen
	@version 2.0.1
	* Fixed bug where only 5 templates were being shown in the dropdown.  Setting the default to show up to 40 templates, rather than setting to -1 (unlimited) just to try and keep it somewhat sane.

	@date 2011-03-24
	@author Prashant M
	@version 1.3.7
 * The capability required to access settings page changed to 'edit_others_posts'

	@date 2011-02-25
	@author Prashant M
	@version 1.3.6
 * Added new capability 'pmc_widget_templates' for settings access for administrator and editor roles
 * Added javascript check in add configuration to see if configuration fields exist

	@date 2011-02-10
	@author Gabriel Koen
	@version 1.3.5
 * Figures out which rail template to use from the server name, uses the default if it doesn't exist.

	@date 2011-01-04
	@author Gabriel Koen
	@version 1.3.4
 * Added template for default rail.	 Needs styling, just copied from BGR.

	@date 2010-10-21
	@author Gabriel Koen
	@version 1.3.3
 * Added activation if/else statement to insert different default templates based on the site using it.
 * Added widget caching.

	@date 2010-08-19
	@author Gabriel Koen
	@version 1.3.2
 * Changed name to "PMC Templatized Widgets" to keep with internal lingo and match the menu names.

	@date 2010-08-19
	@author Gabriel Koen
	@version 1.3.1
 * Added stripslashes to $_POST text processing, removed magic_quotes check

	@date 2010-08-18
	@author Gabriel Koen
	@version 1.3
 * Added Template editor
 * Changed a few page names
 * Changed select dropdown for templates/configurations in the actual widget to a linked list

	@date 2010-08-17
	@author Gabriel Koen
	@version 1.2
 * Externalizing widget preview css and javascript
 * Optimizing widget page so that widget previews are only loaded once
 * Moved trim($value) calls in pmc_wt_process_configuration_page() to the end of the chain so that any whitespace left over from strip_tags() etc gets trimmed

	@date 2010-08-13
	@author Gabriel Koen
	@version 1.1.1
 * Stripping tags and escaping special characters on form input
 * Fixing warning message when trying to loop through an array that doesn't exist

	@date 2010-08-09
	@author Gabriel Koen
	@version 1.1
 * Externalized widget template and configuration tables
 * Split configuration data and template data, configurations are now saved as key-value pairs that get injected into whatever the current template is
 * Changed name to "PMC Module Templates" to indicate the new broader scope of the plugin and to keep with the PMC plugin naming conventions
 * Changed the settings page to take advantage of WordPress's existing UI components, looks more polished and provides a better user experience and requires less work than trying to create a new UI from scratch
 * Updated the widget preview to look like HollywoodLife
 * Modified function and option names to follow standardizes naming conventions
 * Removed a bunch of functions that were no longer needed due to separating the saved configurations form the templates

	To do:
	@todo Change implode/explode on %% into functions so that the markers can be easily changed
	@todo In the template editor, need to ensure images come from the CDN
	@todo Display how many times a template or configuration is being used
	@todo Don't allow archiving templates or configurations that are in use
	@todo In the template editor, javascript button to add a token so that whoever's editing it doesn't have to remember the format
	@todo Code validator/diff in template editor
	@todo Create styling for default rail, find better way to select curent rail template for preview
	@todo Needs better activation hook for creating default templates, if/else for each LOB sucks
 */

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

include_once __DIR__ . '/class-mmc-widget-template.php';
add_action( 'widgets_init', 'pmc_wt_register_widgets' );

include_once __DIR__ . '/class-pmc-templatized-widgets-options.php';
add_action( 'init', 'pmc_templatized_widgets_options_loader', 10 );

include_once __DIR__ . '/class-pmc-templatized-widgets.php';
add_action( 'init', 'pmc_templatized_widgets_loader', 11 );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'pmc-hooks' );
} );


/**
 * For loading PMC_Templatized_Widgets via WordPress action
 *
 * @since 2.0 2011-11-09 Gabriel Koen
 * @version 2.0 2011-11-09 Gabriel Koen
 */
function pmc_templatized_widgets_loader() {
	PMC_Templatized_Widgets::get_instance();
}

/**
 * For loading PMC_Templatized_Widgets_Options via WordPress action
 *
 * @since 2.0 2011-11-09 Gabriel Koen
 * @version 2.0 2011-11-09 Gabriel Koen
 */
function pmc_templatized_widgets_options_loader() {
	PMC_Templatized_Widgets_Options::instance();
}

/**
 * Register the widget(s)
 *
 * @since 1.0
 * @version 1.1 2010-08-09 Gabriel Koen
 */
function pmc_wt_register_widgets() {
	register_widget( 'MMC_Widget_Template' );
}

/**
 * Get templates
 *
 * @since 1.1 2010-08-09 Gabriel Koen
 * @version 1.3 2010-08-18 Gabriel Koen
 * @version 2.0 2011-11-15 Gabriel Koen
 * @version 2.0.1 2012-07-25 Gabriel Koen
 */
function pmc_wt_get_templates( $conditions = array(), $page_number = 0, $per_page = 60 ) {
	$args = array(
		'numberposts' => $per_page,
		'orderby' => 'name',
		'order' => 'ASC',
		'post_type' => 'pmc_widget_template',
	);

	if ( !empty($conditions) && is_array($conditions) ) {
		$args = array_merge($args, $conditions);
	}

	if ( $page_number > 1 && $per_page > 0 ) {
		$args['offset'] = (($page_number - 1) * $per_page);
	}

	$posts = get_posts($args);

	if ( $posts ) {
		$count = count($posts);
		for ( $i=0; $i<$count; $i++ ) {

			$pmc_wt_template_data = get_post_meta( $posts[$i]->ID, 'pmc_wt_template_data', true );

			if( !empty( $pmc_wt_template_data ) ){
				$posts[$i]->post_content = $pmc_wt_template_data;
			}
		}
	}

	return $posts;
}

/**
 * Get template configurations
 *
 * @since 1.1 2010-08-09 Gabriel Koen
 * @version 1.1 2010-08-09 Gabriel Koen
 * @version 2.0 2011-11-15 Gabriel Koen
 */
function pmc_wt_get_configurations( $template_id=0, $conditions=array(), $page_number=0, $per_page=-1 ) {

	$args = array(
		'numberposts' => $per_page,
		'orderby' => 'post_date',
		'order' => 'DESC',
		'post_type' => 'pmc_widget_data',
	);


	if ( !empty($conditions) && is_array($conditions) ) {
		$args = array_merge($args, $conditions);
	}

	if ( $template_id > 0 ) {
		$args['post_parent'] = (int)$template_id;
	}

	if ( $page_number > 1 && $per_page > 0 ) {
		$args['offset'] = (($page_number - 1) * $per_page);
	}

	$posts = get_posts($args);

	if ( $posts ) {
		$count = count($posts);
		for ( $i=0; $i<$count; $i++ ) {

			$pmc_wt_config_data = get_post_meta( $posts[$i]->ID, 'pmc_wt_config_data', true );

			if( !empty( $pmc_wt_config_data ) ){
				$posts[$i]->post_content = unserialize( $pmc_wt_config_data );
			}else{
				$posts[$i]->post_content = unserialize( $posts[$i]->post_content );
			}
		}
	}

	return $posts;
}

/*
 * Adds a template. this method has been created to allow developers to create
 * templates programatically. For clarity, templates created with this method should be prefixed with an underscore
 * if it is not the method will add it.
 * @return false|int|obj Will return false or WP_Error object on failure, post ID on success
 * @version 2.0 2011-11-15 Gabriel Koen
 */

function pmc_wt_add_template($template_name, $template_html) {
	//test the template name if it isn't prefixed with an underscore then prefix it.
	if (strpos($template_name, "_") != 0)
		$template_name = "_" . $template_name;

	$data = array(
		'post_title' => $template_name,
		'post_name' => $template_name,
		'post_content' => $template_html
	);

	$widget_template = PMC_Templatized_Widgets_Options::instance();

	$data = $widget_template->scrub_data($data, 'template');

	$post_id = $widget_template->save($data, 'insert', 'pmc_wt_template_data' );

	return $post_id;
}

/**
 *
 * @param type $template_name
 * @param type $template_id
 * @param type $template_html
 * @return false|int|obj Will return false or WP_Error object on failure, post ID on success
 * @version 2.0 2011-11-15 Gabriel Koen
 */
function pmc_wt_edit_template($template_name, $template_id, $template_html)
{
	//test the template name if it isn't prefixed with an underscore then prefix it.
	if (strpos($template_name, "_") != 0)
		$template_name = "_" . $template_name;

	$data = array(
		'ID' => $template_id,
		'post_name' => $template_name,
		'post_content' => $template_html,
		'post_title' => $template_name
	);

	$widget_template = PMC_Templatized_Widgets_Options::instance();

	$data = $widget_template->scrub_data($data, 'template');

	$post_id = $widget_template->save($data, 'update', 'pmc_wt_template_data' );

	return $post_id;
}

/**
 *
 * @global type $wpdb
 * @param type $configuration
 * @return false|int|obj Will return false or WP_Error object on failure, post ID on success
 * Adds a configuration that is associated with a template. the template ID is stored in the input array with Key "configuration"
 * @version 2.0 2011-11-15 Gabriel Koen
 */
function pmc_wt_add_configuration($configuration=array())
{
	$data = array(
		'post_title' => $configuration['configuration_name'],
		'post_name' => $configuration['configuration_name'],
		'post_parent' => (int)$configuration['configuration'],
	);

	foreach ( $configuration as $key => $value ) {
		if ( '_input'=== substr($key, -6) ) {
			$key = str_replace('_input', '', $key);
			$key = str_replace('_', ' ', $key);
			$data['post_content'][$key] = $value;
		}
	}

	$widget_template = PMC_Templatized_Widgets_Options::instance();

	$data = $widget_template->scrub_data($data, 'config');

	$post_id = $widget_template->save($data, 'insert', 'pmc_wt_config_data' );

	return $post_id;
}

/**
 *
 * @param type $configuration_id
 * @param type $configuration
 * @return false|int|obj Will return false or WP_Error object on failure, post ID on success
 * @version 2.0 2011-11-15 Gabriel Koen
 */
function pmc_wt_edit_configuration($configuration_id, $configuration=array())
{
	$data = array(
		'ID' => $configuration_id,
		'post_name' => $configuration['configuration_name'],
		'post_title' => $configuration['configuration_name'],
		'post_parent' => (int)$configuration['configuration'],
	);

	foreach ( $configuration as $key => $value ) {
		if ( '_input'=== substr($key, -6) ) {
			$key = str_replace('_input', '', $key);
			$key = str_replace('_', ' ', $key);
			$data['post_content'][$key] = $value;
		}
	}

	$widget_template = PMC_Templatized_Widgets_Options::instance();

	$data = $widget_template->scrub_data($data, 'config');

	$post_id = $widget_template->save($data, 'update', 'pmc_wt_config_data' );

	return $post_id;

}

add_filter( 'pmc_ga_event_tracking','pmc_templatized_widgets_tracking' );

function pmc_templatized_widgets_tracking( $events ){
	$pmc_templatized_widget_ga_tracking = PMC_Cheezcap::get_instance()->get_option( 'pmc_templatized_widget_ga_tracking' );

	if ( isset( $pmc_templatized_widget_ga_tracking ) && $pmc_templatized_widget_ga_tracking == 'yes' ) {
		return array_merge([
			// Header Elements
			[
				'selector' => '.pmc-templatized-widget a',
				'category' => 'templatized-widget',
				'label' => '',
			]
		], $events);
	}

	return $events;
}

add_action( 'wp_enqueue_scripts',  'enqueue_event_tracking'  );
function enqueue_event_tracking(){
	$pmc_templatized_widget_ga_tracking = PMC_Cheezcap::get_instance()->get_option( 'pmc_templatized_widget_ga_tracking' );

	if ( isset( $pmc_templatized_widget_ga_tracking ) && $pmc_templatized_widget_ga_tracking == 'yes' ) {
		wp_enqueue_script('templatized-widget-event-track', plugins_url('js/ga-tracking.js', __FILE__), array('jquery'));
	}

}

add_filter( 'pmc_global_cheezcap_options','templatized_cheezcap_groups' );
function templatized_cheezcap_groups( $cheezcap_options ){
	$cheezcap_options[] = new CheezCapDropdownOption(
		'Enable templatized widget GA tracking',
		'Enable templatized widget GA tracking',
		'pmc_templatized_widget_ga_tracking',
		array( 'yes', 'no' ),
		1, // 1sts option => yes
		array( 'Yes', 'No' )
	);
	return $cheezcap_options;
	
}

// EOF
