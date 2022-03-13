<?php
/**
 * Stylesheet utilities.
 *
 * @package pmc-plugins
 */

namespace PMC\Global_Functions;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Styles.
 */
class Styles {
	use Singleton;

	/**
	 * Action tag used to output inline CSS.
	 */
	public const INLINE_CSS_HOOK = 'pmc_styles_do_inline';

	/**
	 * Queued stylesheets to inline.
	 *
	 * @var array
	 */
	protected $_inline_queue = [];

	/**
	 * Styles constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register our actions.
	 */
	protected function _setup_hooks(): void {
		/**
		 * We often enqueue inline CSS in the same callback we use for scripts
		 * and styles, which is also hooked to `wp_enqueue_scripts`. Priority
		 * differs across themes, hence the lateness.
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'do_inline_action' ], 50 );

		// Output all queued CSS before default priority to limit outside interference.
		add_action( static::INLINE_CSS_HOOK, [ $this, 'process_inline_queue' ], 9 );
	}

	/**
	 * Fire the action for rendering inline CSS.
	 */
	public function do_inline_action(): void {
		do_action( static::INLINE_CSS_HOOK );
	}

	/**
	 * Queue a stylesheet for inline rendering.
	 *
	 * @param string $slug Stylesheet slug within build directory (sans extension).
	 * @param string $path Directory path.
	 */
	public function inline( string $slug, string $path ): void {
		$this->_inline_queue[] = compact( 'slug', 'path' );
	}

	/**
	 * Checks for bom characters in generated CSS and replaces with charset string.
	 */
	public function format_inline_css( $content ) {
		$charset_bom_mapping = [
			'UTF-8'    => "\xEF\xBB\xBF",
			'UTF-16BE' => "\xFE\xFF",
			'UTF-16LE' => "\xFF\xFE",
		];

		foreach ( $charset_bom_mapping as $charset => $bom ) {
			if ( 0 === strpos( $content, $bom ) ) {
				$content = sprintf( '@charset(%s);%s', $charset, substr( $content, strlen( $bom ) ) );
				break;
			}
		}

		return $content;
	}

	/**
	 * Render queued stylesheets.
	 * 
	 * Using wp_print_styles to render stylesheets because
	 * PMC::render_template renders BOM  characters when CSS
	 * is passed as a template. Using the first method also
	 * prevents echoing raw data or applying the escape function
	 * against CSS content.
	 */
	public function process_inline_queue(): void {
		foreach ( $this->_inline_queue as $key => $item ) {
			$path = sprintf(
				'%1$s/%2$s.css',
				rtrim( $item['path'], '/' ),
				$item['slug']
			);

			//phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown
			$css_content = $this->format_inline_css( file_get_contents( $path ) );

			wp_register_style( $item['slug'], false );
			
			wp_add_inline_style( $item['slug'], $css_content );
			
			wp_print_styles( $item['slug'] );

			unset( $this->_inline_queue[ $key ] );
		}
	}
}
