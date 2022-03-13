/**
 * The Gallery JS
 * Handles 2 kinds of Galleries
 *  Stand Alone
 *  Enbedded
 *
 * the gallery html loads the images which currently needs to be shown
 * For eg . for a 15 image gallery if 3 thumbs are shown at a time
 * the gallery will load 1 full image and 3 thumbs
 *
 * The next possible needed image is loaded by the JS
 * For eg. Continuing the above Example
 * 2 thumbs to the right
 * 1 thum to the left and
 * 2 full Images
 * are loaded in the background
 *
 * As the gallery moves the same pattern is continued
 * Any Image the user can possibly click on is loaded in the background
 *
 * relevant events are fired as needed like
 * image load (pmc-gallery-image-load)
 * rotate ad (pmc-gallery-rotate-ad)
 * interstital rotate (pmc-gallery-rotate-interstitial)
 * interstitial start (pmc-gallery-interstitial-start)
 * interstitial stop (pmc-gallery-interstitial-stop)
 *
 * This handles interstitial rotating the interstitial settings are in the pmc gallery settings class
 *
 * One unique complexity was movement from one gallery to another
 * and the way we used to handle it was to preload imaged from next gallery
 * So when the next gallery loaded the images were already cached with the browser
 * @todo the way we should approach this is On Gallery edge read Gallery Info along
 * with image Info and change using state and AJAX
 *
 * @todo - this file can be boken into atleast 2 files
 * @todo - use state
 *
 * @package PMC Gallery Plugin
 * @since 1/1/2013 Vicky Biswas
 */
jQuery( document ).ready( function() {
	// @todo It looks like these are used in the pmc_gallery object, but they're here because of scoping issues. Since the pmc_gallery object was moved out of this document ready call, this might cause a bug. It's also a problem that these have such generic names outside the namespaced object.
	var show = 0, start = 0, target = null, pos = 0;

	// Has gallery?
	if ( parseInt( jQuery( '.gallery-image div.gallery-multi' ).length, 10 ) < 1 ) {
		return;
	}

	// Bind an event to window.onhashchange that, when the hash changes, gets the
	// hash and adds the class "selected" to any matching nav link.
	jQuery(window).hashchange( function(){
		if ( '#comments' == location.hash || '#article-comments' == location.hash ) {
			var pos = window.pmc_gallery.current_position;
			//handling edge case where pos stays 0 of 1st load
			if ( pos < 1 ) pos = 1;
			var image = jQuery('.gallery-image > div > div ').eq(pos).find('img');
			jQuery('html,body').animate({scrollTop: jQuery(location.hash).offset().top},'slow');
			window.location.hash = '!' + pos + "/" + image.attr('data-slug') + "/";
			return false;
		} else {
			var imageid = window.pmc_gallery.parsehash();
			if ( imageid > pmc_gallery_jsdata.gallery_count || imageid < 1 ) {
				imageid = 1;
			}

			if( window.pmc_gallery.get_auto_start() === 0){
				window.pmc_gallery.jump(imageid-1+pmc_gallery_jsdata.gallery_start);
			}
		}
	});


	// Check for back Link and integrate
	// this is an absurd approach
	// but this approach was used because it was being used in the old HL galleries and
	// I had to make things backward compatible
	// One of the requirements was all will work as is without url change.

	var picsbackregex = new RegExp( "ref=(.*)pos=" );
	var picsbackurl	= picsbackregex.exec( location.hash );
	if ( picsbackurl !== null && /.+:\/\//.exec(picsbackurl) === null ) {
		jQuery( '.gallery-back' ).attr( 'href', picsbackurl[1] ).css( 'display', 'block' );
	}

	window.pmc_gallery.init( pmc_gallery_jsdata );

	// arrow keys -> <-
	jQuery( document ).on( "keydown", function( e ) {
		switch( e.which ) {
			case 37: // left arrow
				window.pmc_gallery.prev();
				break;

			case 39: // right arrow
				window.pmc_gallery.next();
				break;

			default:
				return; // exit this handler for other keys
		}
		e.preventDefault();
	} );

	// nav clicks
	jQuery( '.gallery-navigation-previous' ).on( "click", function() {
		window.pmc_gallery.prev();
	} );
	jQuery( '.gallery-navigation-next' ).on( "click", function() {
		window.pmc_gallery.next();
	} );
	jQuery( '.gallery-interstitial .skip-ad' ).on( "click", function() {
		window.pmc_gallery.next();
	} );

	//thumb clicks
	jQuery( '.gallery-thumbs > div.gallery-multi > div' ).on( "click", function() {
		window.pmc_gallery.move( jQuery( this ).parent().children().index( this ) );

	} );

} );

//Mappings to our functions
window.pmc_gallery = {
	// pattern only looks for a hash starting with a numeral. This is on purpose so that hashes like #!2 or #!2/ will work just like #!2/image-name/
	pattern: new RegExp(/^\d+/),
	hash: window.location.hash ? ( window.location.hash.indexOf("!") === 1 ? window.location.hash.substr(2) : window.location.hash.substr(1) ) : '',
	current_position: 0,
	total_images: 0,
	swipe: null,
	click_counter: 0,
	interstitial_timer_id: null,
	settings: null,
	init: function( settings ) {
		var pos;
		this.settings = settings;
		//get current position
		pos = ( this.parsehash() - 1 );
		if ( pos < 0 ) {
			pos = 0;
		}

		if ( this.settings.gallery_first > 0 ) {
			pos = this.settings.gallery_first;
		}

		//Creating Difference for first Load
		this.current_position = -1;
		this.move( ( pos + this.settings.gallery_start ) );

		//start swipe from current if not IE
		if ( BrowserDetect.browser !== "Explorer" ) {
			this.swipe = new Swipe(
				jQuery( '.gallery-image' )[0],
				{
					startSlide: this.current_position,
					auto: 1000 * this.get_auto_start(),
					continuous: this.settings.continuous_cycle,
					slide_callback: function( index ) {
						window.pmc_gallery.move( index, 'swipe-callback' );
					}
				}
			);
		}

	},
	is_playing: function() {
		return this.swipe.delay > 0 ;
	},
	is_ending: function() {
		return this.swipe.index + 1 == this.swipe.length;
	},
	move: function(pos, source){
		var track_view = false, position, total, image, start, stop, newHash;

		// prevent multiple call if we're at current position
		if ( this.current_position == pos) {
			return;
		}

		// need to hide interstitial ad before we swipe to next image
		if( jQuery('.gallery-interstitial').length && jQuery('.gallery-interstitial').is(":visible") ) {
			this.stop_interstitial();
		}
		if ( pos + 1 == this.current_position && ! this.settings.continuous_cycle ) {
			this.swipe.stop();
		}

		if ( 'swipe-callback' != source ) {
			if ( this.swipe && this.swipe.getPos() != pos ) {
				this.swipe.slide( pos );
			}
		}

		if ( "Explorer" === BrowserDetect.browser ) {
			jQuery( '.gallery-image > div.gallery-multi > div ' ).hide();
			jQuery( '.gallery-image > div.gallery-multi > div ' ).eq( pos ).show();
		}

		if ( "Safari" === BrowserDetect.browser ) {
			jQuery( '.gallery-image > div.gallery-multi > div' ).css( 'display','inline-block' );
		}

		position = ( ( pos + 1 ) - this.settings.gallery_start );

		// hash change cause next gallery to reload...
		if ( pos < this.settings.gallery_start || position > this.settings.gallery_count ) {
			div = jQuery( '.gallery-image > div.gallery-multi > div ' ).eq( pos );
			if ( div.attr('data-url').indexOf('#!') > 0 ) {
				window.location = div.attr('data-url');
			} else {
				window.location = div.attr( 'data-url' ) + '#!' + div.attr( 'data-pos' ) + '/' + div.attr( 'data-slug' ) + '/';
			}
		} else {
			// IMPORTANT: need to fire all gallery event first before trigger any external events.

			total = jQuery( '.gallery-thumbs div.gallery-multi div' ).length;

			//swipe hack - hide not visible main image
			//so that the height stays at auto
			//drawback - the images go to white n then to the next image instead of continuous swipe
			if ( this.settings.variable_height ) {
				jQuery( '.gallery-image > div > div ' ).slice( 0, pos ).each( function() {
					jQuery( this ).find( 'img' ).hide();
				} );
				jQuery( '.gallery-image > div > div ' ).slice( pos, ( pos + 1 ) ).each( function() {
					jQuery( this ).find( 'img' ).show();
				} );
				jQuery( '.gallery-image > div > div ' ).slice( ( pos + 1 ) ).each( function() {
					jQuery( this ).find( 'img' ).hide();
				} );
			}

			jQuery('.gallery-image > div.gallery-multi > div').removeClass('image-transition image-visible image-current').eq(pos).addClass('image-visible image-current');

			//show proper text
			for ( var cssclass in this.settings.multiparts ) {
				jQuery( '.' + this.settings.multiparts[cssclass] + ' > div > div ' ).hide();
				jQuery( '.' + this.settings.multiparts[cssclass] + ' > div > div ' ).eq( pos ).show();
			}

			//show proper thumbs
			for ( var cssclass in this.settings.imageparts ) {
				if ( jQuery( '.' + cssclass ).attr( 'data-style' ) !== 'all' ) {
					start = pos;
					stop = ( pos + this.settings.imageparts[cssclass].show );
					jQuery( '.'+cssclass+' > div > div ' ).slice( 0, start ).hide();
					jQuery( '.'+cssclass+' > div > div ' ).slice( start, stop ).show();
					jQuery( '.'+cssclass+' > div > div ' ).slice( stop, total ).hide();
					jQuery( '.'+cssclass+' > div > div > img' ).removeClass( 'current' );
					jQuery( '.' + cssclass + ' > div > div ' ).eq( start ).find( 'img' ).addClass( 'current' );
				}
			}

			image = jQuery( '.gallery-image > div > div ' ).eq( pos ).find( 'img' );
			jQuery( '.gallery-count .current' ).text( position );

			this.preload( pos );

			// update position, this must not set until preload has a chance to call
			this.current_position = pos;

			// may trigger external events from here on

			if ( this.click_counter > 0 ) {
				track_view = true;
				jQuery.event.trigger( {
					type: 'pmc-gallery-image-load',
					current: position,
					total: this.settings.gallery_count
				} );
			}
			this.click_counter++;
			if ( this.click_counter !== 1 || this.settings.start_with_interstitial === 1 ) {
				this.settings.interstitial_refresh_clicks = parseInt( this.settings.interstitial_refresh_clicks, 10 );

				this.can_rotate_ads = true;
				if ( this.settings.enable_interstitial && this.settings.interstitial_refresh_clicks > 0 && ( ( this.click_counter - 1 ) % this.settings.interstitial_refresh_clicks === 0 ) ) {
					this.start_interstitial();
				}

				if ( this.can_rotate_ads && ! isNaN( this.settings.ad_refresh_clicks ) && this.settings.ad_refresh_clicks > 0 && ( this.click_counter - 1 ) % this.settings.ad_refresh_clicks === 0 && this.click_counter !== 1 ) {
					if ( typeof pmc_adm_gpt !== "undefined" && jQuery.isFunction( pmc_adm_gpt.refresh_ads ) ) {
						pmc_adm_gpt.refresh_ads();
					}
					jQuery.event.trigger( {
						type: 'pmc-gallery-rotate-ad',
						current: position,
						click: this.click_counter,
						showon: this.settings.ad_refresh_clicks
					} );
				}
			}

			/* globals pmc_comscore */
			/* eslint no-magic-numbers: [ "error", { "ignore": [1] } ] */
			if ( ( 1 < position || '' !== window.location.hash ) && image.length ) {
				newHash = '!' + position + '/' + image.attr( 'data-slug' ) + '/';
				if ( '#' + newHash !== window.location.hash && 'function' === typeof pmc_comscore.pageview ) {
					pmc_comscore.pageview();
				}
				window.location.hash = newHash;
			}

			if ( track_view ) {
				if ( typeof global_urlhashchanged !== "undefined" && jQuery.isFunction( global_urlhashchanged ) ) {
					// if we're calling function we don't know, better wrap it around try catch statement
					try {
						global_urlhashchanged();
					} catch ( e ) {}

				}
			}

			// swipe 1.0 doesn't support this, need to remove when switch to swipe 2.0
			if( this.get_auto_start() > 0 && (pos + 1) >= this.settings.gallery_count ){
				if( typeof this.swipe !== 'undefined' && this.swipe !== null && ! this.settings.continuous_cycle ){
					this.swipe.stop();
				}
			}

			// This event is fire everytime image is moved into view position (visible) where this.current_position != pos
			jQuery.event.trigger( {
				type: 'pmc-gallery-image-rendered',
				current: position, // image x of y
				total: this.settings.gallery_count, // y
				node: jQuery( '.gallery-image > div.gallery-multi > div ' ).eq( pos ) // the current image node
			} );

		} // else
	},

	get_auto_start: function() {
		if ( typeof this.settings.auto_start_delay != 'undefined' && this.settings.auto_start_delay > 0 ) {
			return this.settings.auto_start_delay;
		}
		return 0;
	},

	get_interstitial_duration: function() {
		if ( typeof this.settings.interstitial_duration != 'undefined' && this.settings.interstitial_duration > 0 ) {
			return this.settings.interstitial_duration;
		}
		return this.get_auto_start();
	},

	start_interstitial: function() {
		this.can_rotate_ads = true;

		if ( ! this.settings.enable_interstitial ) {
			return false;
		}

		// if there is no interstitial ad rendered, skip
		if ( ! jQuery('.gallery-interstitial .admz').length ) {
			return false;
		}

		// we need to stop the swipe so we can resume later
		this._start_interstitial_swipe_delay = this.swipe.delay;
		this.swipe.stop();

		jQuery.event.trigger( {
			type: 'pmc-gallery-interstitial-start'
		} );

		//show ad layer n write ad code
		jQuery( '.gallery-interstitial' ).show().css('display','block');
		jQuery('body').addClass('gallery-interstitial-active');

		if ( this.interstitial_duration_countdown = this.get_interstitial_duration() ) {
			if ( typeof this.interstitial_timer_id  != 'undefined' && this.interstitial_timer_id ) {
				clearInterval(this.interstitial_timer_id);
				this.interstitial_timer_id = 0;
			}
			this.interstitial_timer_id = setInterval( this.show_interstitial_countdown, 1000 );
		}

		if ( typeof pmc_adm_gpt !== "undefined" && jQuery.isFunction( pmc_adm_gpt.rotate_ads ) ) {

			pmc_adm_gpt.rotate_ads('interrupt-ads-gallery');
			if ( typeof this.settings.interstital_hide_ads != 'undefined' && this.settings.interstital_hide_ads ) {
				pmc_adm_gpt.remove_ads();
				this.can_rotate_ads = false;
			}

		}

		jQuery.event.trigger( {
			type: 'pmc-gallery-rotate-interstitial',
			click: this.click_counter,
			showon: this.settings.interstitial_refresh_clicks
		} );

		return true;
	},

	stop_interstitial: function(){
		// to stop the counter
		clearInterval(this.interstitial_timer_id);
		jQuery('.gallery-interstitial > .countdown').text('');
		jQuery(".gallery-interstitial").hide().css('display','none');
		jQuery('body').removeClass('gallery-interstitial-active');
		this.interstitial_duration_countdown = 0;
		jQuery.event.trigger({
			type: 'pmc-gallery-interstitial-stop'
		});
		if (BrowserDetect.browser== "Safari") {
			jQuery('.gallery-image > div.gallery-multi > div').css('display','table-cell');
		}

		pmc_adm_gpt.remove_ads('interrupt-ads-gallery');

		if ( typeof this.settings.interstital_hide_ads != 'undefined' && this.settings.interstital_hide_ads ) {
			pmc_adm_gpt.rotate_ads();
		}

		if ( this._start_interstitial_swipe_delay && ! this.is_ending() && this.get_auto_start() > 0 ) {
			this.swipe.resume();
		}
	},

	// callback function
	show_interstitial_countdown: function(){
		// need to access the global variable, can't use this because this != window.pmc_gallery
		if ( window.pmc_gallery.interstitial_duration_countdown <= 0 ) {
			window.pmc_gallery.stop_interstitial();
			return;
		}

		jQuery('.gallery-interstitial > .countdown').text("Pictures will display in " + window.pmc_gallery.interstitial_duration_countdown + " seconds.");
		--window.pmc_gallery.interstitial_duration_countdown;
	},

	preload: function( pos ){
		var start, show;
		if ( this.current_position === pos ) {
			return;
		}

		if ( typeof this.settings.imageparts == 'undefined' || 0 == this.settings.imageparts.length ) {
			//In case no thumbs
			this.load_full( '', ( pos - 1 ) );
			this.load_full( '', pos );
			this.load_full( '', ( pos + 1 ) );
		} else {
			for ( cssclass in this.settings.imageparts ) {
				show = ( ( ( pos + 2 ) * this.settings.imageparts[cssclass].show ) - 1 );
				if ( show > jQuery( '.'+cssclass+' > div > div' ).length ){
					show = jQuery( '.'+cssclass+' > div > div' ).length;
				}
				//might have to put a condition here
				start = ( pos - 1 );
				for ( var i = 0; i < show; i++ ) {
					if ( ( i - pos ) < this.settings.imageparts[cssclass].show ) {
						this.load_full( cssclass, i );
					} else {
						this.load_thumb( cssclass, i );
					}
				}
			}
		}
	},
	load_thumb: function( cssclass, pos ) {
		var target,img;
		target = jQuery( '.' + cssclass + ' > div > div' ).eq( pos );
		if ( target.find( 'img' ).size() === 0 ){
			img = jQuery('<img/>',{
				'src': target.attr( 'data-src' ),
				'alt': target.attr( 'data-alt' ),
				'data-slug': target.attr( 'data-slug' )
			});
			target.append( img );
			target.append( target.attr( 'data-content' ) );
		}
	},
	load_full: function( cssclass, pos ) {
		var target,img;
		//accomodating the case of no thumbs
		if ( cssclass.length > 0 ) {
			this.load_thumb( cssclass, pos );
		}

		target = jQuery( '.gallery-image > div > div' ).eq( pos );
		if ( target.find( 'img' ).size() === 0 ){
			img = jQuery('<img/>',{
				'src': target.attr( 'data-src' ),
				'alt': target.attr( 'data-alt' ),
				'data-slug': target.attr( 'data-slug' )
			});

			if ( target.css( 'width' ) > '0px' ) {
				// restrict image to max-width to div holder width
				img.css( 'max-width', target.css( 'width' ) );
			}
			target.append( img );
		}
	},
	prev: function() {
		if( jQuery('.gallery-interstitial').length && jQuery('.gallery-interstitial').is(":visible") ){
			this.stop_interstitial();
		}else{
			//todo: needs edge for stopping left movement
			// add image-transition class to the image that is transition into view
			jQuery('.gallery-image > div.gallery-multi > div').eq(this.current_position - 1).addClass('image-transition');
			if ( "Explorer" === BrowserDetect.browser ) {
				width = jQuery( '.gallery-image' ).width();
				this.move( ( this.current_position - 1 ) );
			} else {
				this.swipe.prev();
			}
		}
	},
	next: function() {
		//if an interstitial is showing when the next or previous button is showing close the ad and don't move the photos
		if( jQuery('.gallery-interstitial').length && jQuery('.gallery-interstitial').is(":visible") ){
			this.stop_interstitial();
		}else{
			//todo: needs edge for stopping right movement
			// add image-transition class to the image that is transition into view
			jQuery('.gallery-image > div.gallery-multi > div').eq(this.current_position + 1).addClass('image-transition');
			if ( "Explorer" === BrowserDetect.browser ) {
				width = jQuery( '.gallery-image' ).width();
				this.move( ( this.current_position + 1 ) );
			} else {
				this.swipe.next();
			}
		}
	},
	jump: function( position ) {
		if (BrowserDetect.browser== "Explorer") {
			this.move(position);
		} else {
			this.swipe.slide(position);
		}
	},
	parsehash: function() {
		var image_id = 1;

		this.redirect_old_url(); //if current URL is old gallery URL then redirect it to new URL

		image_id = parseInt((window.location.hash).replace( /^#!/, '' ).split('/')[0]);
		if ( isNaN(image_id) ) {
			image_id = 1;
		}
		return image_id;

	},

	redirect_old_url: function() {
		var current_url = location.href;

		// Handling _escaped_fragment_
		var image = '';
		var position = 0;
		var temp_current_url_parts = current_url.split( "#" );
		var pos = 0;

		if ( current_url.indexOf( '_escaped_fragment_=' ) !== -1 ) {
			var current_url_parts = temp_current_url_parts[0].split( '?' );
			if ( current_url_parts.length >= 2 ) {
				var params= current_url_parts[1].split(/[&;]/g);
				// @todo Fix syntax
				for ( var i = params.length; i-->0; ) {
					if ( params[i].lastIndexOf( '_escaped_fragment_=', 0 ) !== -1 ) {
						params.splice( i, 1 );
					}
				}
				//pos = position in array
				pos = ( this.settings.gallery_first + this.settings.gallery_start );
				image = jQuery( '.gallery-image > div > div ' ).eq( pos ).find( 'img' );
				//position = position displayed
				position = ( ( pos + 1 ) - this.settings.gallery_start );
				// @todo Fix syntax
				window.location = current_url_parts[0] + '?' + params.join( '&' ) + '#' + '!' + position + '/' + image.attr( 'data-slug' ) + '/';
				return;
			}
		}

		//Handling Old formats of hashstring
		if( current_url.indexOf( "#" ) < 0 || current_url.indexOf( "#!" ) > 0 ) {
			//either current url has no hash or hashbang is correct in it, bail out
			return;
		}

		var current_url_parts = current_url.split( "#" );

		if( current_url_parts.length !== 2 ) {
			//not a url we should be concerned with
			return;
		}

		// @todo Ugh...
		url_format_hl = /^(\d+)\-(\d+)\-(.*)/.exec( current_url_parts[1] );
		if ( url_format_hl !== null ) {
			//1027327-8-08-Kristen-Stewart
			window.location = current_url_parts[0] + '#!' + url_format_hl[2] + '/' + url_format_hl[3];
		} else {
			//05/Jumpin-Baby
			url_format_hl = /^(\d+)\/(.*)/.exec( current_url_parts[1] );
			if ( url_format_hl !== null ) {
				current_url_parts[1] = '!' + current_url_parts[1];
				//redirect
				window.location = current_url_parts.join( "#" );
			}
		}
		return;
	}
};


//EOF
