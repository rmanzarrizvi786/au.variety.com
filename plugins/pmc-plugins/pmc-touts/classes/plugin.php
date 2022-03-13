<?php
namespace PMC\Touts;

use PMC\Global_Functions\Traits\Singleton;

class Plugin {
	use Singleton;

	protected function __construct() {
		// We want wp to run out init function a bit late to allow theme to register the post type
		// in case, theme has its own touts plugin code added that have not been consolidate.
		add_action( 'init', array( $this, 'action_init' ), 15 );
	}

	public function action_init() {
		// Only activate Tout's related features  if tout post type has not been registered.
		// This condition can be remove once tout has been consolidate and removed from theme
		if ( ! post_type_exists( Tout::POST_TYPE_NAME ) ) {
			Tout::get_instance();
			Admin::get_instance();
			Post_UI::get_instance();
		}
	}

}
