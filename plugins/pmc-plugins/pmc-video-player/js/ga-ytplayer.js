/**
 * Handle Youtube Player Events.
 *
 * To Generate a Minified Version:
 * npm install uglify -g
 * cd pmc-video-player/js/
 * uglify -s ga-ytplayer.js -o ga-ytplayer.min.js
 */
jQuery(document).ready( function($) {

    var pmc_ga_ytplayer =  {

        /**
         * Range to compare for seconds event.
         */
        seconds_range: {
            '_5': 5,
            '_10': 10,
            '_30': 30,
            '_60': 60,
            '_90': 90,
            '_120': 120,
            '_300': 300,
            '_600': 600
        },

        /**
         * Range to compare for percentage event.
         */
        percent_range: {
            '_5': 5,
            '_10': 10,
            '_25': 25,
            '_50': 50,
            '_75': 75,
            '_95': 95
        },

        /**
         * List of Players object.
         */
        players: {},

        /**
         * Localized event tracking settings list.
         */
        event_list: {},

        /**
         * Player Prefix.
         */
        player_prefix: 'yt_',

        /**
         * List of target selector for youtube iframe.
         */
        target_selector: [ '.youtube-player', '.c-player__thumb > iframe', '.c-pvm-player__thumb > iframe' ],

        init: function() {

            if ( window.pmc_video_player_event_tracking ) {
                // Make it accesible globally, to handle youtube player onload request.
                window.onYouTubeIframeAPIReady = this.onYouTubeIframeAPIReady.bind(this);

                this.event_list = window.pmc_video_player_event_tracking;
            }

        },

        /**
         * Function will get called when youtube_iframe_api loads on a global scope.
         *
         * @param {array} $iframe list of youtube iframe.
         *
         * @returns {void}
         */
        onYouTubeIframeAPIReady: function() {

            var _self = this; // scope of this in $.each will be changed.

            $.each( this.target_selector, function( index1, selector ) { // loop through all the selectors.

                var targets = $( selector );

                if ( 0 !== targets.length ) {
                    $.each( targets, function( index2, iframe ) { // loop through all the selected iframes.
                        if ( ! $( iframe ).attr( 'src' ) ) {

                            // If don't have src means player is not autoplaying.
                            $( iframe ).on( 'load', function() {
                                $( iframe ).data( 'autoplay', false );
                                _self.init_player( iframe );
                            });
                        } else {

                            // If have src means player is ready and going to autoplay.
                            $( iframe ).ready( function() {
                                $( iframe ).data( 'autoplay', true );
                                _self.init_player( iframe );
                            });
                        }
                    });
                }
            } );
            
        },

        /**
         * Function to initialize youtube player object.
         *
         * @param {int}    index target index
         * @param {object} value target object
         *
         * @returns {void}
         */
        init_player: function( iframe ) {

            var player = '';
            var YT = window.YT || YT;

            if ( $( iframe )[0].id && ( 'object' === typeof YT ) ) {
                player = YT.get( $( iframe )[0].id ); // If the player is already generated then only id will be there.
            }

            if ( ! player && ( 'object' === typeof YT ) ) {
                new YT.Player( iframe, {
                    events: {
                        'onReady': this.on_player_ready.bind( this ),
                        'onStateChange': this.on_player_state_change.bind( this ),
                        'onError': this.on_player_error.bind( this )
                    }
                });
            } else { // If player instance already generated then add Event Listener only.
                player.addEventListener( 'onReady', this.on_player_ready.bind( this ) );
                player.addEventListener( 'onStateChange', this.on_player_state_change.bind( this ) );
                player.addEventListener( 'onError', this.on_player_error.bind( this ) );
            }
        },

        /**
         * Called on Player Ready Event.
         *
         * @param {object} event Dom event object.
         *
         * @returns {void}
         */
        on_player_ready: function( event ) {

            var _self = this; // scope of this will be changed in event trigger.

            this.reset_player( event.target );

            $( event.target.getIframe() ).on( 'yt-player:stop', function() {
                _self.on_player_stop( event.target );
            } );
    
            $( event.target.getIframe() ).on( 'yt-player:play', function() {
                event.target.playVideo();
            } );   

        },

        /**
         * Called on player state change.
         *
         * @param {object} event Dom event object.
         *
         * @returns {void}
         */
        on_player_state_change: function( event ) {

            var video_id = this.get_player_video_id( event.target );

            if ( event.data || 0 === event.data ) {

                if ( video_id ) {
                    this.players[ video_id ].status = event.data;
                }

                switch ( event.data ) {

                // Ended.
                case 0:
                    this.on_player_video_ended( event );
                    break;

                // Playing.
                case 1:
                    this.start_player_interval( event );
                    break;

                // Paused.
                case 2:
                    this.on_player_paused( event );
                    break;

                // Buffering.
                case 3:
                    this.players[ video_id ].last_status = event.data; // To check player is buffering and won't send play event after buffering complete.
                    break;

                default:
                    break;
                }
            }
        },


        /**
         * Called on player error occurs.
         *
         * @param {object} event Dom event object.
         *
         * @returns {void}
         */
        on_player_error: function( event ) {

            var concatenated_label = this.get_concatenated_label( event.target );

            // send error event;
            if ( '1' === this.event_list.basic.error && concatenated_label ) {
                this.send_ga_event(
                    event.target,
                    'error',
                    this.player_prefix + Math.floor( event.target.getCurrentTime() ) + '_' + concatenated_label,
                    false
                );
            }
        },

        /**
         * Called on player stopped.
         *
         * @param {object} video Video player instance.
         */
        on_player_stop: function( video ) {
            var video_id = this.get_player_video_id( video );

            video.stopVideo(); // Stop video.
            this.reset_player( video ); // Reset player configs.

            this.players[ video_id ].last_status = 2;

        },

        /**
         * Called on Player status is paused.
         *
         * @param {object} event Dom event object.
         *
         * @returns {void}
         */
        on_player_paused: function( event ) {

            var concatenated_label = this.get_concatenated_label( event.target );
            var video_id = this.get_player_video_id( event.target );
            var current_time = 0;
            var player_time_played = 0;
            var time_diff = 0;

            if ( ! video_id ) {
                return;
            }

            current_time = event.target.getCurrentTime();
            player_time_played = this.players[ video_id ].time_played;
            time_diff = current_time - player_time_played;

            clearInterval( this.players[ video_id ].int_id );

            /**
             * Send paused event.
             * Added time diff check so won't send pause event if we fast f/w or b/w because f/w and b/w will fire pause event before buffering and play.
             * When player pause event fired player status will be 2.
             * When pause event is enabled to sent then its value will be 1 in string otherwise 0 string.
             * Checking time diff of less than 6 because we are checking player status of video progress every 5 seconds and when we click pause by user interaction then it should be approx less than 6.
             * Pause won't fire when f/w or b/w because diff should be more than 6 sec.
             */
            if ( concatenated_label && 2 === this.players[ video_id ].status && '1' === this.event_list.advanced.pause && ( 6 >= Math.abs( time_diff ) ) ) {
                this.send_ga_event(
                    event.target,
                    'press-pause',
                    this.player_prefix + Math.floor( event.target.getCurrentTime() ) + '_' + concatenated_label,
                    false
                );
            }

            this.players[ video_id ].last_status = 2; // To keep track of last status.
            this.players[ video_id ].time_played = current_time;
        },


        /**
         * Called on player video gets completely loaded.
         *
         * @param {object} event Dom event object.
         *
         * @returns {void}
         */
        on_player_video_ended: function( event ) {

            var concatenated_label = this.get_concatenated_label( event.target );
            var video_id = this.get_player_video_id( event.target );

            if ( ! video_id ) {
                return;
            }

            clearInterval( this.players[ video_id ].int_id );

            // Video Ended event.
            if ( concatenated_label && '1' === this.event_list.basic._100_percent_played ) {
                this.send_ga_event(
                    event.target,
                    '100-percent-played',
                    this.player_prefix + concatenated_label,
                    false
                );
            }

            this.players[ video_id ].last_status = 0;
        },


        /**
         * To start interval to check player stats.
         *
         * @param {object} event Dom event object.
         *
         * @returns {void}
         */
        start_player_interval: function( event ) {

            var _self = this; // scope of this will be changed in setInterval.

            var video_id = this.get_player_video_id( event.target );
            var concatenated_label = this.get_concatenated_label( event.target );
            var current_time = event.target.getCurrentTime();

            if ( ! concatenated_label ) {
                return;
            }

            this.players[ video_id ].int_id = setInterval( function()	{
                _self.extract_player_data( event );
            }, 5000 );

            if ( this.players[ video_id ].is_autoplay && ( ! this.players[ video_id ].is_autoplay_sent ) && '1' === this.event_list.basic.autoplay ) {
                this.send_ga_event(
                    event.target,
                    'autoplay',
                    this.player_prefix + concatenated_label,
                    true
                );
                this.players[ video_id ].is_autoplay_sent = true;
            }

            /**
             * Check for ( last status is pause ) or ( is_autoplay not enable and last status is unstarted ) initially.
             * When player status is pause then it's value would be 2.
             * When player is unstarted the it's value would be -1.
             */
            if ( ( ! this.players[ video_id ].is_autoplay && -1 === this.players[ video_id ].last_status ) || 2 === this.players[ video_id ].last_status ) {

                /**
                 * When player status is play then it's value will be 1.
                 * When play event is enabled to sent then its value will be 1 in string otherwise 0 string.
                 */
                if ( 1 === this.players[ video_id ].status && '1' === this.event_list.basic.play ) {
                    this.send_ga_event(
                        event.target,
                        'press-play',
                        this.player_prefix + Math.floor( event.target.getCurrentTime() ) + '_' + concatenated_label,
                        false
                    );
                }
            }

            this.players[ video_id ].last_status = 1;
            this.players[ video_id ].time_played = current_time;
        },

        /**
         * Function calls all various functions to fetch listen player events.
         *
         * @param {object} event Dom event object.
         *
         * @returns {void}
         */
        extract_player_data: function( event ) {

            this.seconds_played( event );
            this.percent_played( event );
            this.volume_changed( event );
            this.is_mute( event );

        },


        /**
         * To check all seconds played events.
         *
         * @param {object} event Dom event object.
         *
         * @returns {void}
         */
        seconds_played: function( event ) {

            var _self = this; // scope of this will be changed in $.each.
            var current_time = event.target.getCurrentTime();
            var concatenated_label = this.get_concatenated_label( event.target );
            var video_id = this.get_player_video_id( event.target );

            if ( ! concatenated_label ) {
                return;
            }

            var player_time_played = this.players[ video_id ].time_played;
            var time_diff = current_time - player_time_played;

            if ( 10 <= time_diff ) {

                // jump forward event.
                if ( '1' === this.event_list.advanced.jump_forward ) {
                    this.send_ga_event(
                        event.target,
                        'jump-forward',
                        this.player_prefix + 'from' + Math.floor( player_time_played ) + 'to' + Math.floor( current_time ) + '_' + concatenated_label,
                        false
                    );
                }
            } else if ( -10 >= time_diff ) {

                // jump backward event.
                if ( '1' === this.event_list.advanced.jump_backward ) {
                    this.send_ga_event(
                        event.target,
                        'jump-backward',
                        this.player_prefix + 'from' + Math.floor( player_time_played ) + 'to' + Math.floor( current_time ) + '_' + concatenated_label,
                        false
                    );
                }
            }

            var player_range = this.players[ video_id ].seconds_range;

            $.each( player_range, function( index, value ) {
                if ( current_time > value ) {

                    // Content Consumed Event.
                    if ( '_30' === index && '1' === _self.event_list.basic.content_consumed && false === _self.players[ video_id ].is_content_consumed_sent ) {
                        _self.send_ga_event(
                            event.target,
                            'content-consumed',
                            _self.player_prefix + concatenated_label,
                            false
                        );

                        _self.players[ video_id ].is_content_consumed_sent = true;

                    }

                    if ( '1' === _self.event_list.advanced.seconds_played[ index ] ) {
                        _self.send_ga_event(
                            event.target,
                            index.slice( 1 ) + '-seconds-played',
                            _self.player_prefix + concatenated_label,
                            false
                        );

                        delete _self.players[ video_id ].seconds_range[ index ]; // removed item to prevent repetitiveness.
                    }
                }
            } );

            this.players[ video_id ].time_played = current_time;
        },


        /**
         * To check all percentage played events.
         *
         * @param {object} event Dom event object.
         *
         * @returns {void}
         */
        percent_played: function( event ) {

            var _self = this; // scope of this will be changed in $.each.
            var current_time = event.target.getCurrentTime();
            var concatenated_label = this.get_concatenated_label( event.target );
            var video_id = this.get_player_video_id( event.target );

            if ( ! concatenated_label ) {
                return;
            }

            var percent_range = this.players[ video_id ].percent_range;
            var current_percentage = ( current_time * 100 ) / this.players[ video_id ].duration;

            $.each( percent_range, function( index, value ) {
                if ( current_percentage > value ) {
                    if ( '1' === _self.event_list.advanced.percent_played[ index ] ) {
                        _self.send_ga_event(
                            event.target,
                            index.slice( 1 ) + '-percent-played',
                            _self.player_prefix + concatenated_label,
                            false
                        );
                        delete _self.players[ video_id ].percent_range[ index ]; // removed item to prevent repetitiveness.
                    }
                }
            } );

        },


        /**
         * To check volume up/down events.
         *
         * @param {object} event Dom event object.
         *
         * @returns {void}
         */
        volume_changed: function( event ) {

            var current_volume = this.get_player_volume( event.target );
            var concatenated_label = this.get_concatenated_label( event.target );
            var video_id = this.get_player_video_id( event.target );

            if ( ! concatenated_label ) {
                return;
            }

            var volume_diff = current_volume - this.players[ video_id ].volume;

            if ( 0 < volume_diff ) {
                if ( '1' === this.event_list.advanced.volume_up ) {

                    // fire volume up event.
                    this.send_ga_event(
                        event.target,
                        'press-volume-up',
                        this.player_prefix + Math.floor( event.target.getCurrentTime() ) + '_' + concatenated_label,
                        false
                    );
                }
            } else if ( 0 > volume_diff ) {
                if ( '1' === this.event_list.advanced.volume_down ) {

                    // fire volume down event.
                    this.send_ga_event(
                        event.target,
                        'press-volume-down',
                        this.player_prefix + Math.floor( event.target.getCurrentTime() ) + '_' + concatenated_label,
                        false
                    );
                }
            }

            this.players[ video_id ].volume = current_volume;

        },


        /**
         * To check player mute/unmute events.
         *
         * @param {object} event Dom event object.
         *
         * @returns {void}
         */
        is_mute: function( event ) {

            var is_player_mute = this.is_player_mute( event.target );
            var concatenated_label = this.get_concatenated_label( event.target );
            var video_id = this.get_player_video_id( event.target );

            if ( is_player_mute !== this.players[ video_id ].is_mute ) {
                if ( is_player_mute && ( '1' === this.event_list.advanced.mute ) ) {

                    // mute event.
                    this.send_ga_event(
                        event.target,
                        'press-mute',
                        this.player_prefix + Math.floor( event.target.getCurrentTime() ) + '_' + concatenated_label,
                        false
                    );
                } else if ( ( ! is_player_mute ) && ( '1' === this.event_list.advanced.unmute ) ) {

                    // unmute event.
                    this.send_ga_event(
                        event.target,
                        'press-unmute',
                        this.player_prefix + Math.floor( event.target.getCurrentTime() ) + '_' + concatenated_label,
                        false
                    );
                }

                this.players[ video_id ].is_mute = is_player_mute;
            }
        },


        /**
         * To get current Player Video id.
         *
         * @param {object} video player object.
         *
         * @returns {string/boolean}
         */
        get_player_video_id: function( video ) {

            var video_data = video.getVideoData();

            if ( video_data.video_id ) {
                return video_data.video_id;
            }

            return false;

        },

        /**
         * To get current Player Video Name.
         *
         * @param {object} video player object.
         *
         * @returns {string/boolean}
         */
        get_player_video_name: function( video ) {

            var video_data = video.getVideoData();
            var video_name = video_data.title;
            video_name = video_name.replace( /[\W_]+/g, '-' );

            if ( video_name ) {
                return video_name.toLowerCase();
            }

            return false;

        },

        /**
         * To get concatenated label.
         *
         * @param {object} _this player object.
         *
         * @returns {string/boolean}
         */
        get_concatenated_label: function( video ) {

            var video_name = this.get_player_video_name( video );
            var video_id   = this.get_player_video_id( video );

            if ( ! ( video_name && video_id ) ) {
                return false;
            }

            return video_id + '_' + video_name;

        },


        /**
         * To get player video duration.
         *
         * @param {object} video player object.
         *
         * @returns {int}
         */
        get_video_duration: function( video ) {
            return video.getDuration();
        },


        /**
         * To get player Current volume.
         *
         * @param {object} video player object.
         *
         * @returns {int}
         */
        get_player_volume: function( video ) {
            return video.getVolume();
        },


        /**
         * To check player Mute status.
         *
         * @param {object} video player object.
         *
         * @returns {boolean}
         */
        is_player_mute: function( video ) {
            return video.isMuted();
        },

        /**
         * To send GA events.
         *
         * @param {string}  event_action event action name.
         * @param {string}  event_label event lable.
         * @param {boolean} non_interaction is the event non interaction.
         */
        send_ga_event: function( target, event_action, event_label, non_interaction ) {
    
            if ( window.pmc && window.pmc.event_tracking ) {
                window.pmc.event_tracking( target, event_action, 'video', event_label, false, false, non_interaction );
            }
        },

        /**
         * To Set/Reset player status.
         *
         * @param {object} video player object.
         *
         * @returns {boolean}
         */
        reset_player: function( video ) {
            var video_id = this.get_player_video_id( video );
            var iframe = $( video.getIframe() );

            if ( video_id ) {
                this.players[ video_id ] = {
                    'status': -1,
                    'last_status': -1,
                    'seconds_range': Object.assign( {}, this.seconds_range ), /* eslint-disable-line space-in-parens */ // only assign value of object not it's reference.
                    'percent_range': Object.assign( {}, this.percent_range ), /* eslint-disable-line space-in-parens */
                    'int_id': 0,
                    'duration': this.get_video_duration( video ),
                    'volume': this.get_player_volume( video ),
                    'is_mute': this.is_player_mute( video ),
                    'time_played': 0,
                    'is_autoplay': $( iframe ).data( 'autoplay' ),
                    'is_autoplay_sent': false,
                    'is_content_consumed_sent': false
                };
            }
        }
    
    }

    pmc_ga_ytplayer.init();

} );
