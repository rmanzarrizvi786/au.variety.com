<?php
/**
 * Assets
 *
 * Methods to handle asset loading, like inlining CSS. Initially
 * ported from pmc-core-v2.
 *
 * @package pmc-larva
 *
 * @since   2020-05-30
 */

namespace PMC\Larva;

use PMC\Global_Functions\Styles;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Assets
 *
 * @since 2019-08-26
 * @see   \PMC\Global_Functions\Traits\Singleton
 *
 */
class Assets {

	use Singleton;

	/**
	 * Render css inline in page source. Assets must be stored
	 * in 'build/css' following the $path.
	 *
	 * Should be called in the function hooked into the
	 * wp_enqueue_scripts action.
	 *
	 * @param string $css_slug  Stylesheet slug.
	 * @param string|null $path Optional. Theme root directory. Defaults to brand_directory.
	 *
	 * @throws \Exception
	 */
	public function inline_style( string $css_slug, ?string $path = null ) : void {
		if ( empty( $css_slug ) ) {
			return;
		}

		if ( empty( $path ) ) {
			$path = Config::get_instance()->get( 'brand_directory' );
		}

		Styles::get_instance()->inline(
			$css_slug,
			rtrim( $path, '/' ) . '/build/css/'
		);
	}

	/**
	 * Render tokens (CSS variables) inline in page source.
	 *
	 * Should be called in the function hooked into the
	 * wp_enqueue_scripts action.
	 *
	 * @param string $brand Slug of brand's tokens.
	 */
	public function inline_tokens( string $brand ): void {
		if ( empty( $brand ) ) {
			return;
		}

		Styles::get_instance()->inline(
			sprintf( '%1$s.custom-properties', $brand ),
			PMC_LARVA_PLUGIN_PATH . '/_core/build/tokens/'
		);
	}

	/**
	 * Register a Larva script for later enqueueing.
	 *
	 * @param string $handle         Script handle.
	 * @param string $relative_path  Path relative to `build/js` directory.
	 * @param string $directory_type Larva directory, either 'core' or 'brand'.
	 * @param array  $dependencies   Script dependencies.
	 * @param bool   $in_footer      Output script in footer.
	 * @return bool
	 */
	public function register_script(
		string $handle,
		string $relative_path,
		string $directory_type = 'core',
		array $dependencies = [],
		bool $in_footer = false
	): bool {
		$path = $this->_build_script_reference(
			$relative_path,
			$directory_type,
			'directory'
		);
		$url  = $this->_build_script_reference(
			$relative_path,
			$directory_type,
			'url'
		);

		return wp_register_script(
			$handle,
			$url,
			$dependencies,
			filemtime( $path ),
			$in_footer
		);
	}

	/**
	 * Build path or URL reference for a given script.
	 *
	 * @param string $relative_path  Path relative to `build/js` directory.
	 * @param string $directory_type Larva directory, either 'core' or 'brand'.
	 * @param string $reference_type Reference type, either 'directory' or 'url'.
	 * @return string
	 */
	protected function _build_script_reference(
		string $relative_path,
		string $directory_type,
		string $reference_type
	): string {
		return sprintf(
			'%1$s/build/js/%2$s',
			rtrim(
				Config::get_instance()->get(
					sprintf(
						'%1$s_%2$s',
						$directory_type,
						$reference_type
					)
				),
				'/'
			),
			$relative_path
		);
	}
}
