<?php
class PMC_Transient_Update_Server {
	public function __construct() {
		// @change Corey Gilmore 2013-03-22 This init needs to occur very late, after all other inits have occurred
		//   otherwise we risk having taxonomies and custom post types that may not have been registered.
		//   See: http://vip-support.automattic.com/requests/15714
		add_action( 'init', array( $this, 'init' ), 9999 );
	}

	public function init() {
		if ( isset( $_POST['_pmc_update'] ) ) {

			$position = strpos($_POST['_pmc_update'],'pmc_lock_');

			if( $position !==false){

				if( isset( $_POST['key'] ) && strlen( $_POST['key'] ) == 32 ){
					$update = get_transient( 'pmc_up__' . $_POST['key'] );
				}

				if ( isset( $update[0] ) && $update[0] == $_POST['_pmc_update'] ) {
					if( class_exists( 'PMC_Transient' ) ){

						$lock = (isset($update[0])) ? $update[0] : null;
						$key = (isset($update[1])) ? $update[1] : null;
						$seconds = (isset($update[2])) ? $update[2] : 300;
						$callback = (isset($update[3])) ? $update[3] : '';
						$params = (isset($update[4])) ? $update[4] : array();

						$transient = new PMC_Transient( $key, false );
						$transient->expires_in( $seconds )
						->updates_with($callback, (array) $params )
						->set_lock( $lock )
						->fetch_and_cache();
					}
				}

				exit();
			}
		}
	}
}
//EOF