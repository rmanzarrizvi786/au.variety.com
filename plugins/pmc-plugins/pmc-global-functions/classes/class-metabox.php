<?php
/**
 * Class to allow easy/sensible addition of metabox
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */

namespace PMC\Global_Functions;

use \ErrorException;
use \PMC;


class Metabox {

	/**
	 * @var string Screen ID where metabox is to be added
	 */
	protected $_screen = '';

	/**
	 * @var array Metabox properties
	 */
	protected $_metabox = [
		'id'            => '',                // never set a default here
		'title'         => 'Custom Metabox',
		'context'       => 'normal',
		'priority'      => 'default',
		'class'         => '',
		'callback'      => [],
		'callback_args' => [],
	];

	/**
	 * Metabox constructor
	 *
	 * @param string $id ID of the metabox
	 *
	 * @throws \ErrorException
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct( string $id ) {

		if ( empty( $id ) ) {
			throw new ErrorException( 'Metabox ID is required and must be a string' );
		}

		$this->_metabox['id'] = sanitize_title_with_dashes( $id );

	}

	/**
	 * Factory method to create new object
	 *
	 * @param string $id
	 *
	 * @return \PMC\Global_Functions\Metabox
	 *
	 * @throws \ErrorException
	 */
	public static function create( string $id ) : Metabox {

		$class = __CLASS__;

		$obj = new $class( $id );

		return $obj;

	}

	/**
	 * Method to check if all is in order before setting up or rendering the metabox
	 *
	 * @return bool Returns TRUE if all is ok else FALSE
	 */
	protected function _is_pre_flight_ok() : bool {

		if (
			empty( $this->_metabox['id'] ) || ! is_string( $this->_metabox['id'] )
			|| empty( $this->_metabox['callback'] ) || ! is_callable( $this->_metabox['callback'] )
			|| empty( $this->_screen )
		) {
			return false;
		}

		return true;

	}

	/**
	 * Set the title of the metabox
	 *
	 * @param string $title
	 *
	 * @return \PMC\Global_Functions\Metabox
	 *
	 * @throws \ErrorException
	 */
	public function having_title( string $title ) : Metabox {

		if ( empty( $title ) ) {
			throw new ErrorException( 'Metabox title needs to be defined' );
		}

		$this->_metabox['title'] = $title;

		return $this;

	}

	/**
	 * Set the screen where metabox is to be added
	 *
	 * @param string $screen_id
	 *
	 * @return \PMC\Global_Functions\Metabox
	 *
	 * @throws \ErrorException
	 */
	public function on_screen( string $screen_id ) : Metabox {

		if ( empty( $screen_id ) ) {
			throw new ErrorException( 'Screen ID where metabox is to be rendered, is required' );
		}

		$this->_screen = trim( $screen_id );

		return $this;

	}

	/**
	 * Set context of the metabox
	 *
	 * @param string $context
	 *
	 * @return \PMC\Global_Functions\Metabox
	 *
	 * @throws \ErrorException
	 */
	public function in_context( string $context ) : Metabox {

		if ( empty( $context ) ) {
			throw new ErrorException( 'Metabox context must be defined' );
		}

		$this->_metabox['context'] = trim( $context );

		return $this;

	}

	/**
	 * Set priority of the metabox
	 *
	 * @param string $priority
	 *
	 * @return \PMC\Global_Functions\Metabox
	 *
	 * @throws \ErrorException
	 */
	public function of_priority( string $priority ) : Metabox {

		if ( empty( $priority ) ) {
			throw new ErrorException( 'Metabox priority must be defined' );
		}

		$this->_metabox['priority'] = trim( $priority );

		return $this;

	}

	/**
	 * Set CSS class for the metabox. Multiple classes can be set by passing them in a string with class names separated by single space.
	 *
	 * @param string $class
	 *
	 * @return \PMC\Global_Functions\Metabox
	 */
	public function with_css_class( string $class = '' ) : Metabox {

		$this->_metabox['class'] = $class;

		return $this;

	}

	/**
	 * Set callback which renders the metabox contents
	 *
	 * @param callable $callback
	 * @param array    $args
	 *
	 * @return \PMC\Global_Functions\Metabox
	 */
	public function render_via( callable $callback, array $args = [] ) : Metabox {

		$this->_metabox['callback']      = $callback;
		$this->_metabox['callback_args'] = $args;

		return $this;

	}

	/**
	 * Add the metabox and set it up for render. No more methods can be called on the class object after this method is called.
	 *
	 * @return void
	 */
	public function add() {

		if ( ! $this->_is_pre_flight_ok() ) {
			return;
		}

		add_meta_box(
			$this->_metabox['id'],
			$this->_metabox['title'],
			[ $this, 'render' ],
			$this->_screen,
			$this->_metabox['context'],
			$this->_metabox['priority']
		);

	}

	/**
	 * Method to render metabox container in which metabox contents are rendered by the specified callback
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function render() {

		if ( ! $this->_is_pre_flight_ok() ) {
			return;
		}

		PMC::render_template(
			sprintf( '%s/templates/metabox.php', untrailingslashit( PMC_GLOBAL_FUNCTIONS_PATH ) ),
			[
				'obj'           => $this,
				'metabox'       => $this->_metabox,
				'callback'      => $this->_metabox['callback'],
				'callback_args' => $this->_metabox['callback_args'],
			],
			true
		);

	}

}    // end class



//EOF
