<?php
/**
 * Class for rendering Whizzco widget
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-03-25
 */


namespace PMC\Vendor_Widgets\Whizzco;


use \PMC\Vendor_Widgets\Base;
use \PMC;


class UI extends Base {

	const ID = 'pmc-vw-whizzco';

	/**
	 * @var int
	 */
	protected $_key = 0;

	/**
	 * @var int
	 */
	protected $_widget_id = 0;

	/**
	 * @var int
	 */
	protected $_website_id = 0;

	/**
	 * Method to setup values for config vars for the widget
	 *
	 * @return bool Returns TRUE if vars are successfully set else FALSE
	 */
	protected function _setup_vars() : bool {

		$key        = apply_filters( 'pmc_vendor_widgets_whizzco_key', 0 );
		$widget_id  = apply_filters( 'pmc_vendor_widgets_whizzco_widget_id', 0 );
		$website_id = apply_filters( 'pmc_vendor_widgets_whizzco_website_id', 0 );

		if (
			intval( $key ) > 0
			&& intval( $widget_id ) > 0
			&& intval( $website_id ) > 0
		) {

			$this->_key        = $key;
			$this->_widget_id  = $widget_id;
			$this->_website_id = $website_id;

			return true;

		}

		return false;

	}

	/**
	 * Method to check if all is ok before widget is rendered
	 *
	 * @return bool
	 */
	protected function _is_all_ok() : bool {

		if (
			intval( $this->_key ) <= 0
			|| intval( $this->_widget_id ) <= 0
			|| intval( $this->_website_id ) <= 0
		) {
			return false;
		}

		return true;

	}

	/**
	 * Method called on 'wp_footer' hook to load up assets on current page
	 *
	 * @return void
	 */
	public function enqueue_stuff() : void {

		if ( ! $this->_should_load_assets() ) {
			return;
		}

		wp_enqueue_script(
			sprintf( '%s-widget', self::ID ),
			'https://cdn.whizzco.com/scripts/widget/widget_rt.js',
			[],
			false,
			true
		);

	}

	/**
	 * Method to setup override values for config vars for the widget
	 *
	 * @param int $key
	 * @param int $widget_id
	 * @param int $website_id
	 *
	 * @return bool Returns TRUE if vars are successfully set else FALSE
	 */
	public function set_var_overrides( int $key, int $widget_id, int $website_id ) : bool {

		if (
			intval( $key ) > 0
			&& intval( $widget_id ) > 0
			&& intval( $website_id ) > 0
		) {

			$this->_key        = $key;
			$this->_widget_id  = $widget_id;
			$this->_website_id = $website_id;

			return true;

		}

		return false;

	}

	/**
	 * Method to render widget
	 *
	 * @param bool $are_vars_overridden Set this to TRUE if var values have been overridden via set_var_overrides()
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function render_widget( bool $are_vars_overridden = false ) : void {

		if ( false === $are_vars_overridden ) {
			$this->_setup_vars();
		}

		if ( ! $this->_is_all_ok() ) {
			return;
		}

		$this->mark_assets_for_loading();

		PMC::render_template(
			sprintf( '%s/templates/whizzco/widget.php', PMC_VENDOR_WIDGETS_ROOT ),
			[
				'key'        => $this->_key,
				'widget_id'  => $this->_widget_id,
				'website_id' => $this->_website_id,
			],
			true
		);

	}

}    //end class


//EOF
