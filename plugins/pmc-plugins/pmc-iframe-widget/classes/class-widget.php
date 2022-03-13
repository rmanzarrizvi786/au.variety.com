<?php
/**
 * PMC Iframe widget for sidebar.
 *
 * @package pmc-iframe-widget
 */

namespace PMC\Iframe_Widget;

/**
 * Class Widget
 */
class Widget extends \FM_Widget {

	/**
	 * List of whitelisting domains which are allowed to enter in iframe url field.
	 *
	 * @var array
	 */
	private $_whitelist_domains = [ 'datawrapper.dwcdn.net' ];

	/**
	 * PMC_Iframe_Widget constructor.
	 */
	public function __construct() {

		parent::__construct(
			'pmc-iframe-widget',
			esc_html__( 'PMC Iframe Widget', 'pmc-iframe-widget' ),
			array(
				'description' => esc_html__( 'Display iframe widget for sidebar', 'pmc-iframe-widget' ),
			)
		);
	}

	/**
	 * Define the fields that should appear in the widget.
	 *
	 * @return array Fieldmanager fields.
	 *
	 * @throws \FM_Developer_Exception Exception.
	 */
	protected function fieldmanager_children(): array {

		return [
			'url'    => new \Fieldmanager_TextField( __( 'URL', 'pmc-iframe-widget' ) ),
			'width'  => new \Fieldmanager_TextField( __( 'Width', 'pmc-iframe-widget' ) ),
			'height' => new \Fieldmanager_TextField( __( 'Height', 'pmc-iframe-widget' ) ),
		];
	}

	/**
	 * WP_widget form.
	 *
	 * @param array $instance Args.
	 *
	 * @return string|void
	 *
	 * @throws \Exception Throws exception.
	 */
	public function form( $instance ) {

		if ( ! empty( $this->_whitelist_domains ) ) {

			\PMC::render_template(
				sprintf( '%s/templates/domains.php', untrailingslashit( dirname( __DIR__ ) ) ),
				[
					'whitelist_domains' => $this->_whitelist_domains,
				],
				true
			);
		}

		parent::form( $instance );
	}

	/**
	 * Updating form
	 *
	 * @param array $new_instance New arguments.
	 * @param array $old_instance Old arguments.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = parent::update( $new_instance, $old_instance );

		// Used esc_url_raw() to insert valid url in db.
		$url = ( isset( $instance['url'] ) ) ? esc_url_raw( $instance['url'] ) : '';

		if ( ! empty( $url ) && wpcom_vip_is_valid_domain( $url, $this->_whitelist_domains ) ) {

			$instance['url']    = $url;
			$instance['width']  = ( is_numeric( intval( $instance['width'] ) ) ) ? $instance['width'] : '';
			$instance['height'] = ( is_numeric( intval( $instance['height'] ) ) ) ? $instance['height'] : '';

		} else {

			$instance = [];
		}

		return $instance;
	}

	/**
	 * Visual presentation of widget code.
	 *
	 * @param array $args     Widget details.
	 * @param array $instance Widget's user elements.
	 */
	public function widget( $args, $instance ) {

		if ( ! empty( $instance['url'] ) ) {

			$width  = ( ! empty( $instance['width'] ) ) ? $instance['width'] : '100%';
			$height = ( ! empty( $instance['height'] ) ) ? $instance['height'] : 300;

			printf(
				'<iframe allow="autoplay" width="%s" height="%d" src="%s" frameborder="0"></iframe>',
				esc_attr( $width ),
				esc_attr( $height ),
				esc_url( $instance['url'] )
			);
		}

	}

}
