<?php
/**
 * Widget class for PMC Content Exchange plugin
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @ticket CDWE-195
 * @since 2017-02-24
 */

namespace PMC\Content_Exchange;

use \WP_Widget;
use \PMC;


class Widget extends WP_Widget {

	/**
	 * @var string Unique Widget ID
	 */
	const ID = 'pmc-content-exchange-widget';

	/**
	 * Maintain flag of if script is loaded or not.
	 *
	 * @var bool
	 */
	protected static $_is_script_enqueued = false;

	/**
	 * @var int Module ID for the widget
	 */
	protected $_module_id = 0;

	/**
	 * Outbrain publication slug and its corresponding ID.
	 * where key id slug and value is ID.
	 *
	 * @var array An array containing publication slug and its corresponding ID
	 *
	 * @TODO: Need to check if it's feasible to localize this.
	 */
	protected $_publications = array(
		'HollywoodLife_1' => 'HollywoodLife',
		'FootWearNews'    => 'FootWear News',
		'WWD'             => 'WWD',
		'BGR_2'           => 'BGR',
		'Variety'         => 'Variety',
		'TVLine'          => 'TV Line',
		'Deadline'        => 'Deadline',
		'VarietyLatino'   => 'Variety Latino',
		'IndieWire_1'     => 'IndieWire',
		'Spy.com'         => 'Spy',
		'GoldDerby'       => 'Gold Derby',
		'RobbReport_1'    => 'Robb Report',
		'RollingStone_1'  => 'RollingStone',
		'SourcingJournal' => 'Sourcing Journal',
	);

	/**
	 * Class constructor
	 */
	public function __construct() {

		parent::__construct(

			self::ID,						//Base ID
			__( 'PMC Content Exchange', 'pmc-content-exchange' ),			//Name
			array(							//args

				'classname'   => self::ID,
				'description' => __( 'PMC Content Exchange widget', 'pmc-content-exchange'),

			)

		);

	}

	/**
	 * Method to setup listeners to WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		add_action( 'wp_footer', array( $this, 'enqueue_scripts' ) );

	}

	/**
	 * Method which outputs widget configuration form
	 *
	 * @param array $instance Previously saved values from database
	 * @return void
	 */
	public function form( $instance = array() ) {

		$module_id = 0;

		if ( ! empty( $instance['module_id'] ) && ! empty( $this->_publications[ $instance['module_id'] ] ) ) {
			$module_id = $instance['module_id'];
		}

		$show_dummy_image = ( ! empty( $instance['show_dummy_image'] ) ) ? intval( $instance['show_dummy_image'] ) : 0;

		PMC::render_template(

			sprintf( '%s/templates/widget/config.php', PMC_CONTENT_EXCHANGE_ROOT ),
			array(

				'widget'           => $this,
				'module_id'        => $module_id,
				'publications'     => $this->_publications,
				'show_dummy_image' => $show_dummy_image,

			),
			true

		);

	}

	/**
	 * Enqueue outbrain script.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		if ( true === static::$_is_script_enqueued ) {
			return;
		}

		wp_enqueue_script( 'pmc_outbrain_partner_js', '//widgets.outbrain.com/outbrain.js', array(), false, true );

		// Set flag so that script won't enqueue multiple times.
		static::$_is_script_enqueued = true;

	}

	/**
	 * Method called to sanitize widget values before they are saved
	 *
	 * @param array $new_instance Values just sent to be saved
	 * @param array $old_instance Previously saved values from database
	 * @return array
	 */
	public function update( $new_instance = array(), $old_instance = array() ) {

		$instance = array(
			'module_id' => 0,
		);

		if ( ! empty( $new_instance['module_id'] ) && ! empty( $this->_publications[ $new_instance['module_id'] ] ) ) {
			$instance['module_id'] = sanitize_text_field( $new_instance['module_id'] );
		}

		$instance['show_dummy_image'] = ( isset( $new_instance['show_dummy_image'] ) ) ? 1 : 0;

		return $instance;

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @global Object $wp
	 *
	 * @param array $args     Widget arguments
	 * @param array $instance Saved values from database
	 *
	 * @return void
	 */
	public function widget( $args = array(), $instance = array() ) {

		global $wp;

		if ( empty( $instance['module_id'] ) || empty( $this->_publications[ $instance['module_id'] ] ) ) {
			return;
		}

		$this->_setup_hooks();

		$this->_module_id = $instance['module_id'];

		$show_dummy_image = 0;
		if ( ! PMC::is_production() && ! empty( $instance['show_dummy_image'] ) ) {
			$show_dummy_image = intval( $instance['show_dummy_image'] );
		}

		$widget_id = 'HOP';

		if ( is_single() ) {
			$widget_id = 'SB_1';
		}

		$widget_id = apply_filters( 'pmc_content_exchange_outbrain_widget_id', $widget_id );

		PMC::render_template( sprintf( '%s/templates/widget/front-end.php', PMC_CONTENT_EXCHANGE_ROOT ),
			array(
				'widget'           => $this,
				'module_id'        => $this->_module_id,
				'data_src'         => home_url( $wp->request ),
				'widget_id'        => $widget_id,
				'show_dummy_image' => $show_dummy_image,
			),
			true
		);

	}

}	//end of class


//EOF
