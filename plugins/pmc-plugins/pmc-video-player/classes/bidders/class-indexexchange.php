<?php
/**
 * IndexExchnage Bidder config class.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since 2018-10-10 READS-1551
 */

namespace PMC\Video_Player\Bidders;

use PMC\Global_Functions\Traits\Singleton;

class IndexExchange {

	use Singleton;

	/**
	 * Bidder config variable.
	 *
	 * @var array
	 */
	protected $_config;

	/**
	 * IndexExchange constructor.
	 *
	 * @codeCoverageIgnore Code coverage is generally ignored for singleton constructors.
	 */
	protected function __construct() {
		/**
		 * Actions.
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );

		/**
		 * Filters.
		 */
		add_filter( 'pmc_video_player_bidders', [ $this, 'filter_bidder_config' ] );
	}

	/**
	 * Enqueue JS script to handle video IX bidder request.
	 */
	public function enqueue_scripts() {

		if ( ! empty( $this->_config ) && is_array( $this->_config ) && ! empty( $this->_config['config']['siteID'] ) ) {
			wp_enqueue_script( 'indexexchnage-js', 'https://js-sec.indexww.com/htv/htv-jwplayer.min.js', [], false, '', true );
		}
	}

	/**
	 * Add IndexExchnage bidder config.
	 *
	 * @param array $bidder_config Bidder configurations.
	 *
	 * @return array
	 */
	public function filter_bidder_config( $bidder_config = [] ) {

		$params = apply_filters(
			'pmc_video_player_indexechange_params',
			[
				'site_id' => '',
			] 
		);

		if ( empty( $params['site_id'] ) ) {
			return $bidder_config;
		}

		$this->_config = [
			'mvt'    => '',
			'bids'   => '',
			'config' => [
				'siteID'          => $params['site_id'],
				'videoCommonArgs' => [
					'protocols' => [ 2, 3, 5, 6 ],
					'mimes'     => [
						'video/mp4',
						'video/webm',
						'application/javascript',
					],
					'apiList'   => [ 1, 2 ],
				],
			],
		];

		$bidder_config['indexExchange'] = $this->_config;

		return $bidder_config;

	}

}
