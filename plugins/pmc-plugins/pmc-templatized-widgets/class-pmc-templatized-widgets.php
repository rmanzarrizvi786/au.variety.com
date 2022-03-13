<?php
/**
 * This is a stub for future use, need to split the massive PMC_Templatized_Widgets_Options class into a couple logical groups for dmin page, options, data api / model
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Templatized_Widgets {

	use Singleton;

	const CACHE_DURATION = 1800;  // 30 minutes

	// support template render without sidebar placement
	// ref: function MMC_Widget_Template::widget
	public function render( $template_name, $configuration_name = false, $echo = true ) {

		$cache_key = md5( $template_name . $configuration_name );
		$cache_data = wp_cache_get($cache_key, 'templatized_widget');

		if ( $cache_data ) {
			if ( true === $echo ) {
				echo $cache_data;
				return;
			}
			return $cache_data;
		}

		$templates = pmc_wt_get_templates( array( 'name' => $template_name ) , 0, 1 );

		if ( empty( $templates ) ) {
			return;
		}

		$template = $templates[0];

		$args = array();
		if ( !empty( $configuration_name ) ) {
			$args['name'] = $configuration_name;
		}
		$configurations = pmc_wt_get_configurations( $template->ID, $args, 0, 1 );

		if ( empty( $configurations ) ) {
			return;
		}

		$configuration = $configurations[0];

		foreach ( $configuration->post_content as $key => $value ) {
			$template->post_content = str_replace('%%' . $key . '%%', $value, $template->post_content);
		}

		$template->post_content = PMC::html_ssl_friendly( $template->post_content );

		$cache_data = $template->post_content;
		wp_cache_set( $cache_key, $cache_data, 'templatized_widget', self::CACHE_DURATION );

		if ( $cache_data ) {
			if ( true === $echo ) {
				echo $cache_data;
				return;
			}
			return $cache_data;
		}

	} // function

	public function flush_cache( $template_name, $configuration_name = false ) {

		$templates = pmc_wt_get_templates( array( 'name' => $template_name ) , 0, 1 );

		if ( empty( $templates ) ) {
			return;
		}

		$template = $templates[0];

		$args = array();
		if ( !empty( $configuration_name ) ) {
			$args['name'] = $configuration_name;
		}
		$configurations = pmc_wt_get_configurations( $template->ID, $args );

		if ( empty( $configurations ) ) {
			return;
		}

		foreach ( $configurations as $configuration ) {
			$cache_key = md5( $template->post_name . $configuration->post_name );
			wp_cache_delete( $cache_key, 'templatized_widget' );
		} // foreach

	} // function
}

// EOF
