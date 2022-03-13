<?php
namespace PMC\Editorial;

use PMC\Global_Functions\Traits\Singleton;

class Plugin {
	use Singleton;

	protected function __construct() {
		// We want wp to run out init function a bit late to allow theme to register the post type
		// This is to avoid conflict with taxonomy registered at theme level until code is consolidate and clean up.
		add_action( 'init', [ $this, 'action_init' ], 15 );
	}

	public function action_init() {
		if ( ! taxonomy_exists( Taxonomy::NAME ) ) {
			Taxonomy::get_instance();
		}
	}

}
