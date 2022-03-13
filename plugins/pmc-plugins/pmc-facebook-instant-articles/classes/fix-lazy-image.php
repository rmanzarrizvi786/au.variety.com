<?php
namespace PMC\Facebook_Instant_Articles;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Fix lazy image loading for FBIA contents
 */
class Fix_Lazy_Image {
	use Singleton;

	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup all required actions & filters
	 * @return void
	 */
	protected function _setup_hooks() : void {
		// We need to use priority < 10 in order to add a filter to disable lazy image loading plugin
		add_action( 'wp', [ $this, 'action_wp' ], 9 );
		add_filter( 'the_content', [ $this, 'filter_the_content' ], 9999 );
		add_filter( 'pmc_render_template_variables', [ $this, 'filter_pmc_render_template_variables' ], 10, 2 );
	}

	/**
	 * Callback function for wp action to disable lazy image loading as needed
	 *
	 * @return void
	 */
	public function action_wp() : void {
		if ( ! empty( \PMC::filter_input( INPUT_GET, 'ia_markup' ) )
			|| ! empty( \PMC::filter_input( INPUT_GET, 'amp_markup' ) ) ) {
			add_filter( 'lazyload_is_enabled', '__return_false', 9999 );
			remove_action( 'wp_head', [ 'LazyLoad_Images', 'setup_filters' ], 9999 );
		}
	}

	/**
	 * Callback function to undo lazy image loading
	 *
	 * @param $content
	 * @return string
	 */
	public function filter_the_content( $content ) : string {
		if ( Plugin::get_instance()->is_rendering_content() ) {
			$content = preg_replace_callback( '#<img [^>]*?data-lazy-src\s*=\s*"([^"]+)"[^>]*?>#si', array( $this, 'undo_lazy_image' ), $content );
		}

		return $content;
	}

	/**
	 * Callback function for preg replace to undo lazy image src
	 *
	 * @param array $matches
	 * @return string
	 */
	public function undo_lazy_image( $matches ) : string {
		$content = $matches[0];
		$img_src = $matches[1];
		return preg_replace( '#\s+src\s*=\s*"[^"]+"#si', sprintf( ' src="%s"', $img_src ), $content );
	}

	/**
	 * Callback function to override larva's component c-lazy-image as needed to undo lazy image loading
	 *
	 * @param array $variables
	 * @param string $path
	 * @return array
	 */
	public function filter_pmc_render_template_variables( array $variables, string $path ) : array {
		if ( 'c-lazy-image.php' === basename( $path ) && Plugin::get_instance()->is_rendering_content() ) {
			$variables['c_lazy_image_placeholder_url'] = $variables['c_lazy_image_src_url'];
			$variables['c_lazy_image_src_url']         = '';
		}

		return $variables;
	}

}
