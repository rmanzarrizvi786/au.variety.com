<?php
/**
 * Class to handle wp-admin side of the plugin
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since 2015-06-29
 */

namespace PMC\Disable_Live_Chat;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;


class Admin {

	use Singleton;

	const OPTION_KEY = 'pmc_disable_wpcom_live_chat';

	protected function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_action( 'init', array( $this, 'disable_live_chat' ) );
		add_action( 'personal_options', array( $this, 'add_profile_option' ) );
		add_action( 'personal_options_update', array( $this, 'save_profile_option' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_profile_option' ) );
	}

	public function disable_live_chat() {
		if ( ! is_admin() || ! function_exists( 'wpcom_vip_remove_livechat' ) ) {
			return;
		}

		if ( ! $usermeta = get_user_meta( get_current_user_id(), self::OPTION_KEY ) ) {
			return;
		}

		if ( count($usermeta) == 1 ) {
			$disable_chat = intval( reset( $usermeta ) );
		} else {
			$disable_chat = intval( $usermeta );
		}

		if ( $disable_chat === 1 ) {
			//wpcom_vip_remove_livechat();
		}
	}

	public function add_profile_option( $user ) {
		$disable_chat = intval( get_user_attribute( get_current_user_id(), self::OPTION_KEY ) );

		echo PMC::render_template( PMC_DISABLE_LIVE_CHAT_ROOT . '/templates/user-profile.php', array(
			'option_key' => self::OPTION_KEY,
			'disable_chat' => $disable_chat,
		) );
	}

	public function save_profile_option( $user_id ) {
		check_admin_referer( 'update-user_' . intval( $user_id ) );

		if ( ! current_user_can( 'edit_user', intval( $user_id ) ) ) {
			wp_die( 'You do not have permission to edit this user.' );
		}

		$disable_chat = ( empty( $_POST[ self::OPTION_KEY ] ) ) ? 0 : intval( $_POST[ self::OPTION_KEY ] );

		update_user_attribute( $user_id, self::OPTION_KEY, $disable_chat );
	}

}	//end of class


//EOF
