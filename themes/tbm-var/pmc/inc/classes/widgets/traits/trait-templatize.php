<?php
namespace PMC\Core\Inc\Widgets\Traits;

/**
 * Auto load partials for widgets.
 */
trait Templatize {

	/**
	 * Output the widget on the frontend. This will load the template part
	 * `template-parts/widgets/<group name, lowercased and dasherized>` by
	 * default, but this may be overridden on individual widgets.
	 *
	 * @param  array $args Widget args, from global data or overrides.
	 * @param  array $data Widget instance data.
	 */
	public function widget( $args, $data ) {
		echo \PMC::render_template( locate_template( '/template-parts/widgets/' . str_replace( '_', '-',
				$this->id_base ) . '.php' ), [ 'args' => $args, 'data' => $data ] );
	}
}
