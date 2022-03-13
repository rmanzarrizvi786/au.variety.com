<?php
/**
 * Base class for PMC Sticky Posts services
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @revision 2017-07-28 Hau
 *
 */

namespace PMC\Sticky_Posts\Service;

use \ErrorException;
use PMC\Global_Functions\Traits\Singleton;

abstract class Base {
	use Singleton;

	/*
	 * Plugin info vars
	 */
	const PLUGIN_ID    = 'pmc-sticky-posts';
	const PLUGIN_NAME  = 'PMC Sticky Posts';

	/**
	 * @var int Maximum number of posts that can be stickied on homepage
	 * We need to enforce the max post, should not allow child class to override this value
	 */
	private $_max_posts_allowed = 20;

	/**
	 * @var array Configuration for sticky post implementation on homepage
	 */
	protected $_config = array(

		/*
		 * Maximum number of sticky posts allowed on homepage
		 */
		'max_count'  => 1,

		/*
		 * Offsets after which each sticky post is shown in river.
		 * Eg. to show a sticky post on 3rd position, offset would
		 * be 2 (because it would show after 2 posts).
		 *
		 * This array can contain multiple offsets but the number of offsets must
		 * match the value of 'max_count' set above.
		 * Eg. Two offsets should not be defined below if 'max_count' above is set to 1
		 * because if there is a mis-match then the service would drop extra offsets automatically
		 * which are of lower position in the river. If number of offsets is less than 'max_count'
		 * defined above then sticky posts would be inserted only in the specified offsets (one per offset)
		 */
		'positions'  => array( 0 ),

		/*
		 * Post types from which sticky posts are displayed in river
		 */
		'post_type' => array( 'post' ),

	);

	/**
	 * Class constructor
	 */
	protected function __construct() {
		// We should not relying on child class to call this function.
		$this->_realign_max_posts_allowed();
	}


	/**
	 * Method to finalize service config. After this method is called,
	 * service config should not be changed.  Child class may override the config as needed.
	 * This function implement the bare minimum data validation that all sub class must enforce.
	 *
	 * @return void
	 */
	public function commit_config() {

		if ( $this->_config['max_count'] === count( $this->_config['positions'] ) ) {
			return;
		}

		/*
		 * Lets validate/sanitize max sticky posts to show on a page and
		 * number of positions defined for sticky posts.
		 */
		if ( $this->_config['max_count'] < count( $this->_config['positions'] ) ) {

			/*
			 * Max count is less than positions defined, lets drop
			 * extra positions
			 */
			$this->set_positions( array_slice( $this->_config['positions'], 0, $this->_config['max_count'] ) );

		} elseif ( $this->_config['max_count'] > count( $this->_config['positions'] ) ) {

			/*
			 * Max count is more than positions defined, lets reduce max count
			 * to same number as number of positions defined
			 */
			$this->set_max_count( count( $this->_config['positions'] ) );

		}

	}

	/**
	 * Utility method called on class init to align value of max sticky posts allowed on
	 * a page using the 'posts_per_page' option of the current site
	 *
	 * @return void
	 */
	private function _realign_max_posts_allowed( ) {

		$posts_per_page = intval( get_option( 'posts_per_page' ) );

		if ( $posts_per_page > 0 && $posts_per_page < $this->_max_posts_allowed ) {
			$this->_max_posts_allowed = $posts_per_page;
		}

	}

	/**
	 * Utility method to sanitize max position number of a sticky post by not allowing
	 * position number greater than maximum posts allowed on a page. This method
	 * is typically called using array_map() on an array of position numbers.
	 *
	 * Example - if current site can have only 10 posts in river on a single page
	 * then having a sticky post position of greater than 10 is useless.
	 *
	 * @param integer $position Position in river for a sticky post
	 * @return integer Sanitized position number in river for sticky post
	 *
	 * @note we need to enforce max post allow, using final function to prevent child class override
	 */
	final public function sanitize_max_position_number( $position ) {
		$max_position  = intval( $this->_max_posts_allowed ) - 1;

		return min( $position, $max_position );
	}

	/**
	 * Service config method which allows setting up how many sticky posts at max
	 * should be displayed in river.
	 *
	 * @param integer $count
	 * @return PMC\Sticky_Posts\Service\Base Returns object of the called class to facilitate method chaining when setting up service config
	 *
	 * @note we need to enforce max post allow, using final function to prevent child class override
	 */
	final public function set_max_count( $count ) {

		if ( empty( $count ) || ! is_numeric( $count ) || intval( $count ) < 1 || intval( $count ) > $this->_max_posts_allowed ) {
			throw new ErrorException( sprintf( '%s::%s() expects an integer parameter between 0 and %d', get_called_class(), __FUNCTION__, $this->_max_posts_allowed ) );
		}

		$this->_config['max_count'] = intval( $count );

		return $this;

	}

	/**
	 * Service config method which allows setting up positions in river
	 * where sticky posts should be displayed
	 *
	 * @param array $positions
	 * @return PMC\Sticky_Posts\Service\Base Returns object of the called class to facilitate method chaining when setting up service config
	 */
	public function set_positions( array $positions ) {

		$sanitized_positions = array_unique( array_map( array( $this, 'sanitize_max_position_number' ), array_map( 'absint', $positions ) ) );

		if ( empty( $sanitized_positions ) || count( $sanitized_positions ) < 1 ) {
			throw new ErrorException( sprintf( '%s::%s() expects an array containing integers greater than zero which denote positions where sticky posts are to be set', get_called_class(), __FUNCTION__ ) );
		}

		sort( $sanitized_positions );

		$this->_config['positions'] = $sanitized_positions;

		return $this;

	}

	/**
	 * Service config method which allows setting up a single position in river
	 * where a sticky post should be displayed
	 *
	 * @param integer $position
	 * @return PMC\Sticky_Posts\Service\Base Returns object of the called class to facilitate method chaining when setting up service config
	 */
	public function set_position( $position ) {

		if ( empty( $position ) || ! is_numeric( $position ) || intval( $position ) < 1 ) {
			throw new ErrorException( sprintf( '%s::%s() expects an integer greater than zero which denotes position where sticky post is to be set', get_called_class(), __FUNCTION__ ) );
		}

		$this->set_positions( array( $position ) );

		return $this;

	}

	/**
	 * Service config method which allows setting up post types which should be
	 * included when sticky posts are displayed in river
	 *
	 * @param array $post_types
	 * @return PMC\Sticky_Posts\Service\Base Returns object of the called class to facilitate method chaining when setting up service config
	 */
	public function set_post_types( array $post_types ) {

		$post_types = array_filter( array_unique( array_map( 'trim', $post_types ) ) );

		if ( empty( $post_types ) ) {
			throw new ErrorException( sprintf( '%s::%s() expects an array containing post types from which sticky posts are to be fetched', get_called_class(), __FUNCTION__ ) );
		}

		$this->_config['post_type'] = $post_types;

		return $this;

	}

}	//end class


//EOF