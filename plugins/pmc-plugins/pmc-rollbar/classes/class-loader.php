<?php
namespace PMC\Rollbar;

use Rollbar\Rollbar;
use Rollbar\Payload\Level;
use PMC\Global_Functions\Traits\Singleton;

class Loader {

	use Singleton;

	/**
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		// Use wp_head to render js code for rollbar so that the code is loaded before anything else
		add_action( 'wp_head', array( $this, 'load_rollbar_js_code' ), 1 );
		add_action( 'init', array( $this, 'load_rollbar_php_code' ) );
	}

	private function _get_env() {

		// Default environment
		$environment = 'development';

		$server_name = sanitize_text_field( wp_unslash( \PMC::filter_input( INPUT_SERVER, 'SERVER_NAME' ) ) );

		if ( \PMC::is_production() ) {
			$environment = 'production';
		} elseif ( ! empty( $server_name ) && strpos( $server_name, 'pmcqa.com' ) !== false ) {
			$environment = 'staging';
		}

		return $environment;
	}

	public function load_rollbar_js_code() {

		$plugin = Plugin::get_instance();

		$rollbar_config = [
			'enabled'                    => $plugin->is_rollbar_enabled(),
			'logLevel'                   => $plugin->get_log_level(),
			'reportLevel'                => $plugin->get_log_level(),
			'accessToken'                => $plugin->get_access_token_js(),
			'captureUncaught'            => false,
			'captureUnhandledRejections' => false,
			'payload'                    => [
				'environment' => $this->_get_env(),
			],
		];

		?>
		<script>
			var _rollbarConfig = <?php echo wp_json_encode( $rollbar_config ); ?>
		</script>
		<?php

		// Load rollbar js script as recommended on https://docs.rollbar.com/docs/browser-js
		//
		// @todo It's probably better to find another way to output this js code to avoid phpcs warnings -
		// Found Mustache unescaped output notation: "}"
		\PMC::render_template( PMC_ROLLBAR_ROOT . '/templates/rollbar-js-script.php', [], true );
	}

	public function load_rollbar_php_code() {

		$plugin = Plugin::get_instance();

		switch ( $plugin->get_log_level() ) {
			case 'debug':
				$log_level = Level::DEBUG;
				break;
			case 'info':
				$log_level = Level::INFO;
				break;
			case 'warning':
				$log_level = Level::WARNING;
				break;
			case 'error':
				$log_level = Level::ERROR;
				break;
			case 'critical':
			default:
				$log_level = Level::CRITICAL;
				break;
		}

		$access_token = $plugin->get_access_token_php();

		// Don't initialize rollbar if feature flag is disabled
		// and access token isn't set in cheezcap
		if ( ! $plugin->is_rollbar_enabled() || empty( $access_token ) ||
			! is_string( $access_token ) || 32 !== strlen( $access_token ) ) {
			return;
		}

		Rollbar::init(
			[
				'access_token' => $access_token,
				'environment'  => $this->_get_env(),
			],
			false, // handle exceptions
			false, // handle errors
			false // handle fatal errors
		);

		Rollbar::configure(
			[
				'minimum_level' => $log_level,
				'scrub_fields'  => [ 'Cookie', 'jwt' ],
			]
		);
	}

}

