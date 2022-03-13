/**
 * PMC JWPlayer
 * npm install uglify-js -g
 * uglifyjs pmc-jwplayer.js -cm -o pmc-jwplayer.min.js
 */

/*
 * This script is written to emulate jwplayer object to intercept and override the options
 * 1. Adjust width & height specified where mobile version of browser does not display properly
 *    due to width & height are defined with desktop size.
 * 2. Replace [rand] field with random number for advertising's tag
 * 3. Add support for multiple jwplayer with different pid and default config
 */

pmc_jwplayer = function ( id, player_id ) {

	if (
		document.cookie.indexOf( 'scroll0=' ) > -1 &&
		'object' === typeof pmc_partner_scroll &&
		'string' === typeof pmc_partner_scroll.player_id
	) {
		player_id = pmc_partner_scroll.player_id;
	}

	if ( 'default' === player_id ) {
		player_id = window.pmc_jwplayer_options ? window.pmc_jwplayer_options.pid : undefined;
	}

	if ( !(this instanceof pmc_jwplayer) ) {
		return new pmc_jwplayer( id, player_id  );
	}

	const _self         = this;

	this.id             = id;
	this.options        = {};
	this.player_id      = undefined;

	this.set_player_id = function ( player_id ) {
		if ( undefined !== player_id ) {
			this.player_id = player_id;
		}
		return _self;
	};

	this.adjust_device_screen_size = function() {
		try {
			// determine device screen size and width base on device orientation
			if (typeof window.orientation == 'undefined' || window.orientation == 0 || window.orientation == 180 ) {
				this.width = window.screen.width;
			}
			else {
				this.width = window.screen.height;
			}
			// Some device such as Android have a default viewport width set to 320, while its physical screen width is 480.
			// We need to adjust the width accordingly to current viewport width.
			if ( typeof window.devicePixelRatio != 'undefined' && window.devicePixelRatio > 1.0 ) {
				this.width = this.width / window.devicePixelRatio;
				if ( this.width < 300 ) {
					this.width = 300;	// set minimal width to 300
				}
			}
			if (typeof _self.options['width'] != 'undefined') {
				// we need to override and ajust video width
				if ( this.width < _self.options['width'] ) {
					// get the scaling ratio to ajust video height if needed
					this.ratio = this.width / _self.options['width'];
					if ( typeof _self.options['height'] != 'undefined' ) {
						// adjusting video height according to scale ratio
						_self.options['height'] = Math.floor( _self.options['height'] * this.ratio );
					}
					_self.options['width'] = this.width;
				}
			}
		} catch (e) {
			console.log(e);
		}
		return _self;
	};

	this.adjust_advertising_tag = function() {
		try {
			if (_self.options && _self.options.advertising && _self.options.advertising.tag) {
				const rand = Math.random() * 1000000000000000,
					tag_url = _self.options['advertising']['tag'].replace('[rand]', rand);
				_self.options.advertising.tag = tag_url;
			}
		} catch (e) {
			console.log(e);
		}
		return _self;
	};

	this.adjust_floating = function( floating ) {
		// If we have direct sold ad, we need to disable the player's floating feature
		if ( pmc_jwplayer.has_direct_sold_ad ) {
			floating = false;
		}

		if ( window.pmc_jwplayer_options.disable_floating ) {
			floating = false;
		}

		if ( typeof floating === 'boolean' ) {
			if ( typeof _self.options.floating === 'undefined' || _self.options.floating !== floating ) {
				_self.options.floating = floating;
				const instance = _self.instance();
				if ( instance ) {
					instance.setFloating(floating);
				}
			}
		}
	};

	this.instance = function () {
		const player = pmc_jwplayer.player( this.player_id, this.default_player_id );
		if ( 'undefined' === typeof player ) {
			return undefined;
		}
		pmc_jwplayer.instance_ids[this.id] = this.id;
		return player(this.id);
	};

	/*
	 * Get the player's position on the page
	 */
	this.get_position = function() {
		let position = false;

		try {
			const instance = _self.instance();

			if ( instance ) {
				const config = instance.getConfig();
				if ( 'string' === typeof config.pmc_position ) {
					position = config.pmc_position;
				} else if ( 'string' === typeof config.vloc ) {
					position = config.vloc;
				}

				// If we detect direct sold ad and vloc is floating, we want to reset it to auto
				if ( 'floating' === position && pmc_jwplayer.has_direct_sold_ad ) {
					position = 'auto';
				}

				if ( 'auto' === position || false === position ) {
					// Auto calculate position
					const element = instance.getContainer(),
						bodyRect  = document.body.getBoundingClientRect(),
						elemRect  = element.getBoundingClientRect(),
						offset    = elemRect.top - bodyRect.top,
						ih        = Math.max( document.documentElement.clientHeight, window.innerHeight || 0 ),
						viewport  = offset / ih;
					if ( 1.5 >= viewport ) {
						position = 'top';
					} else if ( 1.5 < viewport && 5 > viewport ) {
						position = 'mid';
					} else {
						position = 'bottom';
					}
				}
			}
		} catch (e) {
			console.log(e);
		}

		return position;
	};

	function _do_monetize() {

		// Including the initialize code to enqueue and monetize the player as early as possible.
		window.blogherads = window.blogherads || {};
		window.blogherads.adq = window.blogherads.adq || [];

		const instance = _self.instance();

		// Player instance is not ready; player might be loading in asynchronous mode
		if ( ! instance ) {
			return false;
		}

		// Let's Atlas manage the add/remove the player from monetize list
		window.blogherads.adq.push( function () {
			const options = {
					subAdUnitPath: 'instream',
					targeting: {}
				},
				position = _self.get_position();

			if ( position ) {
				options.targeting = {
					vloc: position
				};
			}
			window.blogherads.monetizeJWPlayer(instance,options);

			// debug
			const config = instance.getConfig();
			console.log( 'monetizeJWPlayer', {
				'vid': config.id,
				'pid': config.pid,
				'options': options
			} );

		} );

		return true;
	} // private function _do_monetize

	this.monetize = function() {
		try {
			if (window.pmc_jwplayer_options && window.pmc_jwplayer_options.ads_suppression) {
				return _self;
			}

			// @TODO: To remove other retry code and throw error once all jwplayer is call via pmc_jwplayer().setup()
			// Current code is needed for transition until we fixed all video related player at theme level
			// If function was called within _do_setup, we should be good. No need to retry
			if ( ! _do_monetize() && 'function' === typeof jQuery && 'complete' !== document.readyState ) {
				// If we can't monetize the player, The JWPlayer ID associated to the script has not fully loaded
				// Let's retry when document is ready
				jQuery(document).ready(function(){
					if ( ! _do_monetize() ) {
						// Still can't monetize? Let's retry on window load
						// If JWPlayer is not ready by then, there isn't much we can do
						jQuery(window).on('load', function() {
							_do_monetize();
						} );
					}
				});
			}
		} catch (e) {
			console.log(e);
		}
		return _self;
	};

	function _do_overlay_catapult() {

		const instance = _self.instance();

		// Player instance is not ready or catapult not set; player might be loading in asynchronous mode
		if ( ! instance || ! pmc_jwplayer_options.cgid ) {
			return false;
		}

		_self.catapult_initialized = false;

		instance.on('ready', function() {

			if (typeof CXBootstrapper === "undefined") {
				return false;
			}

			const cxBootstrapper = new CXBootstrapper(pmc_jwplayer_options.cgid);
			const config = instance.getConfig();

			cxBootstrapper.initCX({
				videoDescriptors: [{
					product:'overlay',
					videoElementId: config.id,
					playerType:'jwplayer'
				}]
			});

			_self.catapult_initialized = true;

			// debug
			console.log( 'catapult initialized', {
				'vid': config.id
			} );

		});

		return true;
	} // private function _do_overlay_catapult

	this.overlay_catapult = function() {
		try {
			if (
				window.pmc_jwplayer_options
				&& window.pmc_jwplayer_options.cgid
				&& ! window.pmc_jwplayer_options.ads_suppression
			) {
				// We can only load catapultx script only when jwplayer instance is instantiated
				if (!_do_overlay_catapult() && 'function' === typeof jQuery && 'complete' !== document.readyState) {
					jQuery(document).ready(function () {
						if (!_do_overlay_catapult()) {
							jQuery(window).on('load', function () {
								_do_overlay_catapult();
							});
						}
					});
				}
			}
		} catch (e) {
			console.log(e);
		}
		return _self;
	};

	function _do_comscore_tracking() {
		const instance = _self.instance();

		// Player instance is not ready or catapult not set; player might be loading in asynchronous mode
		if ( ! instance || ! pmc_jwplayer_options.comscore ) {
			return false;
		}

		instance.on('ready', function() {
			pmc_jwplayer.comscore.tracking.push(instance);
		});

		return true;
	} // private function _do_comscore_tracking

	this.comscore_tracking = function() {
		try {
			if ( pmc_jwplayer_options.comscore ) {
				if (!_do_comscore_tracking() && 'function' === typeof jQuery && 'complete' !== document.readyState) {
					jQuery(document).ready(function () {
						if (!_do_comscore_tracking()) {
							jQuery(window).on('load', function () {
								_do_comscore_tracking();
							});
						}
					});
				}
			}
		} catch (e) {
			console.log(e);
		}
		return _self;
	};

	/**
	 * This is to enable Closed captioning by default for JWPlayer.
	 *
	 * @since 2020-03-13 ROP-2049
	 * @since 2020-11-19 ROP-2240 - consolidate & standardize jw player related code
	 */
	this.apply_caption = function() {
		try {
			if ( 'undefined' !== typeof pmc_video_player_ads && '1' === pmc_video_player_ads.is_jwplayer_cc_enabled ) {
				const instance = _self.instance();
				if ( instance ) {
					instance.setCurrentCaptions(1);
					instance.setCaptions();
				}
			}
		} catch (e) {
			console.log(e);
		}
		return _self;
	};

	function _do_setup() {
		const instance = _self.instance();
		if ( ! instance ) {
			return false;
		}

		// adjust jw player options before setup
		_self.adjust_device_screen_size()
			.adjust_advertising_tag()
			.adjust_floating();

		// note: if instance is valid, then window.jwplayer is available
		const saved_defaults = window.jwplayer.defaults,
			info = pmc_jwplayer.player_info( _self.player_id, _self.default_player_id );

		// IMPORTANT: Need to temporarily set the defaults to the corresponding player before setup is call
		if ( info && info.defaults !== saved_defaults ) {
			window.jwDefaults = window.jwplayer.defaults = info.defaults;
		}

		// setup the jw player, this call should only be called once per video
		instance.setup(_self.options);

		// IMPORTANT: need to restore saved defaults to prevent override existing default player
		if ( info && info.defaults !== saved_defaults ) {
			window.jwDefaults = window.jwplayer.defaults = saved_defaults;
		}

		// integrate with jw player after setup
		_self.apply_caption()
			.monetize()
			.overlay_catapult()
			.comscore_tracking();

		return true;
	}

	// Wrapper for function jwplayer().setup
	this.setup = function ( options ) {

		if ( undefined === options.playlist && _self.playlist ) {
			options.playlist = _self.playlist;
		}

		_self.options = options;

		if ( ! _do_setup() && 'function' === typeof jQuery ) {
			// workaround where jwplayer is not ready or loaded yet.
			jQuery(document).ready(function(){
				_do_setup();
			});
		}

		return _self;
	};

	// Wrapper for function jwplayer().remove
	this.remove = function () {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.remove) {
			instance.remove();
		}
	}

	// Wrapper for function jwplayer().on
	this.on = function( event, callback ) {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.on) {
			instance.on( event, callback );
		}
	};

	// Wrapper for function jwplayer().play
	this.play = function() {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.play) {
			instance.play();
		}
	};

	// Wrapper for function jwplayer().pause
	this.pause = function() {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.pause) {
			instance.pause();
		}
	};

	// Wrapper for function jwplayer().stop
	this.stop = function() {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.stop) {
			instance.stop();
		}
	};

	// Wrapper for function jwplayer().getState
	this.getState = function() {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.getState) {
			return instance.getState();
		}
		return 'error';
	};

	// Wrapper for function jwplayer().getVolume
	this.getVolume = function() {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.getVolume) {
			return instance.getVolume();
		}
		return 0;
	};

	// Wrapper for function jwplayer().setVolume
	this.setVolume = function( volume ) {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.setVolume) {
			instance.setVolume( volume );
		}
	};

	// Wrapper for function jwplayer().getMute
	this.getMute = function() {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.getMute) {
			return instance.getMute( state );
		}
		return 0;
	};

	// Wrapper for function jwplayer().setMute
	this.setMute = function( state ) {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.setMute) {
			instance.setMute( state );
		}
	};

	// Wrapper for function jwplayer().getPlaylist
	this.getPlaylist = function () {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.getPlaylist) {
			return instance.getPlaylist();
		}
		return [];
	}

	// Wrapper for function jwplayer().getPlaylistIndex
	this.getPlaylistIndex = function () {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.getPlaylistIndex) {
			return instance.getPlaylistIndex();
		}
		return 0;
	}

	// Wrapper for function jwplayer().getPlugin
	this.getPlugin = function ( plugin ) {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.getPlugin) {
			return instance.getPlugin( plugin );
		}
	}

	// Wrapper for function jwplayer().getContainer
	this.getContainer = function () {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.getContainer) {
			return instance.getContainer();
		}
	}

	// Wrapper for function jwplayer().playlistItem
	this.playlistItem = function ( index ) {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.playlistItem) {
			return instance.playlistItem( index );
		}
	}

	// Wrapper for function jwplayer().load
	this.load = function ( playlist ) {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.load) {
			return instance.load( playlist );
		}
	}

	// Wrapper for function jwplayer().next
	this.next = function () {
		const instance = _self.instance();
		if (instance && 'function' === typeof instance.next) {
			return instance.next();
		}
	}

	if ( 'function' === typeof jQuery ) {
		const playerObject = jQuery('#' + this.id );
		if ( playerObject.length ) {
			this.playlist  = playerObject.data( 'jsonfeed' );
			if ( 'undefined' === typeof player_id || ! player_id ) {
				player_id = playerObject.data( 'player' );
			}
			if ( 'undefined' === typeof this.playlist || ! this.playlist ) {
				const videoHash = playerObject.data( 'videoid' );
				this.playlist = 'http://content.jwplatform.com/feeds/' + videoHash + '.json';
			}
		}
	}

	this.default_player_id = pmc_jwplayer.add();

	if ( undefined === player_id || ! player_id ) {

		// if player_id not specified, try to extract from element id
		// @see https://github.com/jwplayer/wordpress-plugin/blob/8f74634057c30439d262fdc566bfee15a27f3b5d/jw-player/include/shortcode.php#L205
		const tokens = /jwplayer_[^_]+_([^_]+)_div/.exec(this.id)
		if ( tokens && 2 === tokens.length ) {
			player_id = tokens[1];
		} else {
			player_id = this.default_player_id;
		}

	}

	this.set_player_id( player_id );

	return _self;

};

Object.assign( pmc_jwplayer, {
	has_direct_sold_ad: false,
	host: 'https://content.jwplatform.com',

	players_info: {}, // Track the jw player object storing the jwplayer default setup
	instance_ids: {}, // Track the jw player instance ids needed for direct sold trigger events

	player_info: function( pid, default_pid ) {
		if ( 'undefined' !== typeof pid && this.players_info[pid] ) {
			return pmc_jwplayer.players_info[pid];
		}
		else if ( 'undefined' !== typeof default_pid && this.players_info[default_pid] ) {
			return pmc_jwplayer.players_info[default_pid];
		}
		else if (window.jwplayer) {
			return {
				'jwplayer': window.jwplayer,
				'defaults': window.jwplayer.defaults
			}
		}
		return undefined;
	},

	player: function( pid, default_pid ) {
		const info = this.player_info( pid, default_pid );
		if ( info ) {
			return info.jwplayer;
		}
		return window.jwplayer;
	},

	add: function() {
		if ( 'undefined' === typeof window.jwplayer ) {
			return;
		}

		if (window.pmc_jwplayer_options && window.pmc_jwplayer_options.ads_suppression) {
			delete window.jwplayer.defaults.advertising;
		}

		this.players_info[ window.jwplayer.defaults.pid ] = {
			'jwplayer': window.jwplayer,
			'defaults': window.jwplayer.defaults
		};
		return window.jwplayer.defaults.pid;
	},

	load: function( pid, callback, host ) {
		if ( pmc_jwplayer.players[pid] ) {
			if ( 'function' === typeof callback ) {
				callback( pid );
			}
			return;
		}

		if ( ! host ) {
			host = this.host;
		}

		const script   = document.createElement('script');

		script.type    = 'text/javascript';
		script.charset = 'utf-8';
		script.async   = true;
		script.timeout = 45000;
		script.src     = host + '/libraries/' + pid + '.js';
		script.onload  = function() {
			pid = pmc_jwplayer.add();
			if( 'function' === typeof callback ) {
				callback( pid );
			}
		};

		const head = document.getElementsByTagName('head')[0] || document.documentElement;

		head.insertBefore(script, head.firstChild);

	},

	get_player_ids: function() {
		return Object.keys( this.players_info );
	},

	handle_direct_sold_ad_event: function() {
		pmc_jwplayer.has_direct_sold_ad = true;
		Object.keys( pmc_jwplayer.instance_ids ).forEach( function(id) {
			pmc_jwplayer(id).adjust_floating( false );
		} );
	}

} );

// comscore related modules
Object.assign(pmc_jwplayer, {

	comscore: {

		tracking: {

			queued_instances: [],

			onload: function () {
				pmc_jwplayer.comscore.tracking.process_queues();
			},

			push: function (instance) {
				pmc_jwplayer.comscore.tracking.queued_instances.push(instance);
				pmc_jwplayer.comscore.tracking.process_queues();
			},

			process_queues: function () {
				// Assign to variable and reset the object property to avoid race condition
				const queues = pmc_jwplayer.comscore.tracking.queued_instances;

				pmc_jwplayer.comscore.tracking.queued_instances = [];

				if (typeof window.ns_ !== "undefined" && queues.length) {

					queues.forEach(function (instance) {
						window.ns_.StreamingAnalytics.JWPlayer(instance, pmc_jwplayer_options.comscore);
						const config = instance.getConfig();

						console.log('comscore tracking initialized', {
							'vid': config.id
						});
					});

				}
			}

		} // tracking

	} // comscore

});

// pmc.hooks should have been loaded. Just double checking to avoid fatal
if ( 'undefined' !== typeof pmc && 'undefined' !== typeof pmc.hooks ) {
	pmc.hooks.add_action( 'pmc_adm_dfp_direct_sold', pmc_jwplayer.handle_direct_sold_ad_event );
}

// in case boomerang js code didn't register the pmc.hooks, we should do our own direct sold ad detection
window.addEventListener( 'message', function(event) {
	if ( 'string' === typeof event.data ) {
		const message_pattern = 'pmcadm:dfp:isdirect=true';
		if ( event.data.indexOf(message_pattern) === 0 ) {
			pmc_jwplayer.handle_direct_sold_ad_event();
		}
	}
}, false);

// deprecated for backward compatible, do not use pmcjwplayer
pmcjwplayer = pmc_jwplayer;
