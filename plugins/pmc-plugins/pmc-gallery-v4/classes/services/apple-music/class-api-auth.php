<?php
/**
 * Service class to handle Apple Music API auth tokens
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2021-02-24
 */

namespace PMC\Gallery\Services\Apple_Music;

use \PMC;
use \PMC_Cache;
use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Gallery\Lists_Settings;
use \Jose\Component\Core\AlgorithmManager;
use \Jose\Component\KeyManagement\JWKFactory;
use \Jose\Component\Signature\Algorithm\ES256;
use \Jose\Component\Signature\JWSBuilder;
use \Jose\Component\Signature\Serializer\CompactSerializer;
use \Exception;

class API_Auth {

	use Singleton;

	public const CACHE_KEY          = 'pmc-gallery-apple-music-api-auth-token';
	public const CACHE_LIFE         = MONTH_IN_SECONDS;    // 30 days
	public const CACHE_LIFE_ON_FAIL = 900;    // 15 minutes

	/**
	 * @var int Number of days for which the token should be valid
	 */
	public const MAX_EXP_DAYS = 45;

	/**
	 * @var string Type of token
	 */
	protected const _TOKEN_TYPE = 'JWT';

	/**
	 * @var string Encryption algorithm used to generate the token
	 */
	protected const _TOKEN_ALG = 'ES256';

	/**
	 * @var array App details used by music player on front end.
	 */
	protected $_app = [];

	/**
	 * @var array Credentials used to generate token
	 */
	protected $_credentials = [];

	/**
	 * @var array An array of admin screen IDs on which error is to be displayed, if any
	 */
	protected $_admin_screens_for_error = [
		'dashboard',
		'edit-pmc_list',
		'pmc_list',
	];

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore Ignore coverage for class constructor because any methods called in here have their own tests
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to set up listeners to WP hooks
	 */
	protected function _setup_hooks() : void {

		/*
		 * Actions
		 */
		add_action( 'admin_notices', [ $this, 'maybe_display_error' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 12 );    // delayed to allow JS to be registered

		/*
		 * Filters
		 */
		// Priority 20 to make sure it stays disabled if auth token cannot be generated
		add_filter( 'pmc_gallery_v4_lists_enable_list_item_apple_music_player', [ $this, 'get_player_state' ], 20 );

	}

	/**
	 * Method to check if Apple Music Player is enabled or not
	 *
	 * @return bool
	 */
	protected function _is_player_enabled() : bool {

		return (
			true === apply_filters( 'pmc_gallery_v4_lists_enable_list_item_apple_music_player', false )
		);

	}

	/**
	 * Method to check if app array is valid with all data points existing.
	 *
	 * @param array $app
	 *
	 * @return bool
	 */
	protected function _is_app_info_valid( $app ) : bool {

		if (
			is_array( $app )
			&& ! empty( $app['name'] ) && ! empty( $app['build'] )
			&& is_string( $app['name'] ) && is_string( $app['build'] )
		) {
			return true;
		}

		return false;

	}

	/**
	 * Method to check if credentials array is valid with all data points existing.
	 * This does not check for actual validity of credentials with Apple Music and
	 * only checks for data integrity.
	 *
	 * @param array $credentials
	 *
	 * @return bool
	 */
	protected function _are_credentials_valid( $credentials ) : bool {

		if (
			is_array( $credentials )
			&& ! empty( $credentials['team_id'] ) && ! empty( $credentials['key_id'] ) && ! empty( $credentials['key_file'] )
			&& PMC::is_file_path_valid( $credentials['key_file'] )
		) {
			return true;
		}

		return false;

	}

	/**
	 * Method to get auth credentials
	 *
	 * @return array
	 */
	protected function _get_credentials() : array {

		if ( empty( $this->_credentials ) ) {

			$credentials        = apply_filters( 'pmc_gallery_v4_apple_music_credentials', [] );
			$this->_credentials = ( $this->_are_credentials_valid( $credentials ) ) ? $credentials : $this->_credentials;

		}

		return $this->_credentials;

	}

	/**
	 * Method to get the app info for the player on front-end
	 *
	 * @return array
	 */
	protected function _get_app_info() : array {

		if ( empty( $this->_app ) ) {

			// These are default details which should be overridden by site theme via filter.
			$default_app = [
				'name'  => 'PMC Gallery Apple Music Player',
				'build' => '1.0',
			];

			$app        = apply_filters( 'pmc_gallery_v4_apple_music_app_info', $default_app );
			$this->_app = ( $this->_is_app_info_valid( $app ) ) ? $app : $default_app;

		}

		return $this->_app;

	}

	/**
	 * Method to get cached auth token
	 *
	 * @return string
	 *
	 * @throws \ErrorException
	 */
	public function get_token() : string {

		$cache_version = apply_filters( 'pmc_gallery_v4_apple_music_credentials_version', 1 );
		$cache_key     = sprintf( '%s-v%d', self::CACHE_KEY, $cache_version );

		$cache = new PMC_Cache( $cache_key );
		$token = $cache->expires_in( self::CACHE_LIFE )
						->on_failure_expiry_in( self::CACHE_LIFE_ON_FAIL )
						->updates_with( [ $this, 'get_uncached_token' ] )
						->get();

		return $token;

	}

	/**
	 * Method to generate auth token
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore This method has coverage but this flag is needed because for some reason
	 *                     PHPUnit is not ignoring the catch statement even when this flag has been
	 *                     added there.
	 */
	public function get_uncached_token() : string {

		$token = '';

		$credentials = $this->_get_credentials();

		if ( empty( $credentials ) ) {
			return $token;
		}

		$time = time();

		$payload = [
			'iat' => $time,
			'exp' => $time + ( self::MAX_EXP_DAYS * DAY_IN_SECONDS ),
			'iss' => $credentials['team_id'],
		];

		$headers = [
			'alg' => self::_TOKEN_ALG,
			'typ' => self::_TOKEN_TYPE,
			'kid' => $credentials['key_id'],
		];

		try {

			$algorithm_manager = new AlgorithmManager(
				[
					new ES256(),
				]
			);

			$jws_builder   = new JWSBuilder( $algorithm_manager );
			$signature_key = JWKFactory::createFromKeyFile( $credentials['key_file'] );

			$jws = $jws_builder->create()
								->withPayload( wp_json_encode( $payload ) )
								->addSignature( $signature_key, $headers )
								->build();

			$serializer = new CompactSerializer();

			$token = $serializer->serialize( $jws );

		} catch ( Exception $e ) {    // phpcs:ignore

			//do nothing

		}

		return $token;

	}

	/**
	 * Method to display an error in wp-admin if auth token cannot be generated
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function maybe_display_error() : void {

		$current_screen = get_current_screen();

		if ( empty( $current_screen ) || ! in_array( $current_screen->id, (array) $this->_admin_screens_for_error, true ) ) {
			return;
		}

		if ( ! $this->_is_player_enabled() || ! empty( $this->get_token() ) ) {
			return;
		}

		PMC::render_template(
			sprintf( '%s/template-parts/admin/notices/apple-music-token-error.php', untrailingslashit( PMC_GALLERY_PLUGIN_DIR ) ),
			[],
			true
		);

	}

	/**
	 * Method to push out auth token to front end
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function enqueue_assets() : bool {

		if ( ! is_singular( Lists_Settings::LIST_POST_TYPE ) || ! $this->_is_player_enabled() ) {
			return false;
		}

		$token = $this->get_token();

		if ( empty( $token ) ) {
			return false;
		}

		return wp_localize_script(
			'pmc-lists-front',
			'pmcgalleryamapi',
			[
				'tkn'     => $token,
				'appinfo' => $this->_get_app_info(),
			]
		);

	}

	/**
	 * Method to disable Apple Music Player if there is no token available
	 *
	 * @param bool $is_enabled
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function get_player_state( bool $is_enabled ) : bool {

		if ( empty( $this->get_token() ) ) {
			$is_enabled = false;
		}

		return $is_enabled;

	}

}    // end class

//EOF
