<?php
/**
 * Preload theme specific fonts.
 *
 * @package pmc-preload
 */

namespace PMC\Preload;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Fonts.
 */
class Fonts {
	use Traits\Queue;
	use Singleton;

	/**
	 * Add fonts to be preloaded.
	 *
	 * @param string $url Font source url.
	 * @param string $type Type.
	 */
	public static function add( string $url, string $type ): void {
		static::get_instance()->_add( $url, $type );
	}

	/**
	 * Add fonts to be preloaded.
	 *
	 * @param string $url Font source url.
	 * @param string $type Type.
	 */
	protected function _add( string $url, string $type ): void {
		if ( empty( $url ) || empty( $type ) ) {
			return;
		}

		$this->_queue[] = [ $url, $type ];
	}

	/**
	 * Process and preload the fonts.
	 *
	 * @param string $item Font source and type.
	 */
	protected function _process_item( array $item ): void {
		[ $url, $type ] = $item;

		?>
		<link rel="preload" href="<?php echo esc_url( $url ); ?>" as="font" type="<?php echo esc_attr( $type ); ?>" crossorigin="anonymous">
		<?php
	}

}
