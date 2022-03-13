<?php
/**
 * Shared methods for preloading scripts and styles registered with WP.
 *
 * @package pmc-preload
 */

namespace PMC\Preload\Traits;

use _WP_Dependency;

/**
 * Trait WP_Dependencies.
 */
trait WP_Dependencies {
	/**
	 * Dependency type. Must be a singular string representing an asset type
	 * that has a corresponding class extending \WP_Dependencies.
	 *
	 * @return string
	 */
	abstract protected function _type(): string;

	/**
	 * Retrieve this dependency type's object.
	 *
	 * @return \WP_Dependencies
	 */
	abstract protected function _dependency_manager(): \WP_Dependencies;

	/**
	 * Render a single preload tag for a registered dependency.
	 *
	 * @param string $handle Dependency handle.
	 */
	protected function _render_preload_tag_for_registered_dependency( string $handle ): void {
		$item = $this->_dependency_manager()->registered[ $handle ] ?? null;

		if ( ! $item instanceof _WP_Dependency ) {
			return;
		}

		$this->_render_preload_tag( $item );
	}

	/**
	 * Render preload tag for given dependency object.
	 *
	 * Does not require that the passed dependency represents a registered item,
	 * but if it specifies any dependencies for itself, those must be registered
	 * before this method is invoked or they won't be preloaded. This is relevant
	 * when preloading scripts rendered by the pmc-tags plugin, for instance.
	 *
	 * @param _WP_Dependency $dependency Dependency data.
	 * @param bool           $filter_src Whether to filter item source.
	 */
	protected function _render_preload_tag( _WP_Dependency $dependency, bool $filter_src = true ): void {
		// A mock _WP_Dependency can rely on registered items, but not on other mock objects.
		array_map( [ $this, '_render_preload_tag_for_registered_dependency' ], (array) $dependency->deps );

		if ( in_array( $dependency->handle, (array) $this->_done, true ) ) {
			return;
		}

		$this->_done[] = $dependency->handle;

		if ( empty( $dependency->src ) ) {
			return;
		}

		$src = $this->_build_source( $dependency, $filter_src );

		?>
		<link rel="preload" as="<?php echo esc_attr( $this->_type() ); ?>" href="<?php echo esc_url( $src ); ?>" />
		<?php
	}

	/**
	 * Build asset source according to \WP_Scripts::do_item() and \WP_Styles::_css_href().
	 *
	 * @param _WP_Dependency $dependency   Dependency to retrieve source for.
	 * @param bool           $filter_result Filter built source or return raw.
	 * @return string
	 */
	protected function _build_source( _WP_Dependency $dependency, bool $filter_result = true ): string {
		$src = $dependency->src;

		/**
		 * Borrowed from \WP_Scripts::do_item() and \WP_Styles::_css_href() to
		 * handle Core's relatively-registered dependencies.
		 */
		if (
			! preg_match( '|^(https?:)?//|', $src )
			&& ! (
				$this->_dependency_manager()->content_url
				&& 0 === strpos( $src, $this->_dependency_manager()->content_url )
			)
		) {
			$src = $this->_dependency_manager()->base_url . $src;
		}

		if ( null !== $dependency->ver ) {
			$src = add_query_arg(
				'ver',
				$dependency->ver ?: $this->_dependency_manager()->default_version,
				$src
			);
		}

		if ( ! $filter_result ) {
			return $src;
		}

		return apply_filters( $this->_type() . '_loader_src', $src, $dependency->handle );
	}
}
