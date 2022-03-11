/**
 * Part of PMC-Celeb Profile module for Hollywoodlife
 *
 * @since 2012-01-27 Amit Gupta
 * @version 2012-02-23 Amit Gupta
 */
jQuery(document).ready(function() {
	/**
	 * Make our seed values integers or our mojo will fail later
	 */
	if ( typeof ( CelebProfile ) === 'undefined' ) {
		return;
	}
	CelebProfile.offset = parseInt( CelebProfile.offset );

	var arr_celeb_stories = new Array(); //array to cache stories for flipping quickly to previously loaded pages

	/**
	 * Loading animation, hooked into jQ's AJAX events
	 */
	jQuery("#spinner").bind("ajaxSend", function() {
		jQuery(this).show();
	}).bind("ajaxStop", function() {
		jQuery(this).hide();
	}).bind("ajaxError", function() {
		jQuery(this).hide();
	});

	/**
	 * one point contact for the hash pattern for easy maintenance
	 */
	var celeb_get_pattern = function() {
		return "#!/" + CelebProfile.ajax_token + "/";
	};

	/**
	 * handy function to check whether the hash is our desired type matching pattern
	 * returned by celeb_get_pattern() or not
	 */
	var celeb_is_hash = function( hash ) {
		if ( hash == undefined ) {
			return false;
		}
		hash = hash.toLowerCase();
		if ( hash.indexOf( celeb_get_pattern() ) !== -1 ) {
			return true;
		} else {
			return false;
		}
	};

	/**
	 * function which takes in hash & calculates the number by which the stories must be offset
	 */
	var celeb_get_offset = function( hash ) {
		if ( hash == undefined ) {
			hash = "";
		}

		if ( jQuery.trim( hash ) == "" ) {
			return 0;
		}

		hash = hash.toLowerCase();
		var offset = 0;
		if ( celeb_is_hash( hash ) == true ) {
			offset = parseInt( hash.replace( celeb_get_pattern(), "" ).replace( "/", "" ) );
			offset--;
			if ( isNaN(offset) || ( offset < 0 ) ) {
				offset = 0;
			}

			return parseInt( offset );
		}

		return false;
	};

	/**
	 * function to do the final cleanup, like setting the offset value in global var,
	 * updating the href in Load More button etc
	 */
	var celeb_mopup_vars = function( offset ) {
		CelebProfile.offset = offset + 1; //update global offset value with new offset
		//update the hash in Load More button for next call
		jQuery('#celeb_page_latest_loadmore a').attr('href', ( celeb_get_pattern() + ( offset + 2 ) ) );
	};

	/**
	 * function to save the data into cache array
	 */
	var celeb_save_cache = function( offset, data ) {
		offset = parseInt( offset );
		if ( offset < 0 || jQuery.trim( data ) == "" || arr_celeb_stories[offset] ) {
			return false;
		}

		arr_celeb_stories[offset] = data;

		return true;
	};
	celeb_save_cache( 0, jQuery('#celeb_page_latest_stories').html() ); //set zero index for cache

	/**
	 * function to fetch the data from cache for the page number (offset+1) & display it on page.
	 * return FALSE if page cache not available
	 */
	var celeb_show_cache = function( offset ) {
		offset = parseInt( offset );
		offset = ( offset < 0 ) ? 0 : offset;
		if ( ! arr_celeb_stories[offset] ) {
			return false;
		}

		jQuery('#celeb_page_latest_stories').html( arr_celeb_stories[offset] ); //replace the existing stories with stories from cache
		jQuery('html, body').animate({
			scrollTop: ( jQuery("#celeb_page_latest").offset().top - 20 )
			}, 1500); //scroll to top of story section

		celeb_mopup_vars( offset ); //call janitor to mop up & set vars & links in order

		return true;
	};

	/**
	 * called back by jQuery.post() when data is returned from server, this function handles display of
	 * stories
	 */
	var celeb_display_stories = function( data, offset ) {
		var latest_stories_more = data.celebLatestMore; //data received from server
		if ( latest_stories_more == "no-go" ) {
			//no more data on server, so lets hide the button
			jQuery('#celeb_page_latest_loadmore a').hide();
		} else {
			celeb_save_cache( offset, latest_stories_more );
			celeb_show_cache( offset );
		}
	};

	/**
	 * function which makes the AJAX call, sends the data & queues up the callback function on data receipt
	 */
	var celeb_fetch_stories = function( cp_offset ) {
		jQuery.get(
			CelebProfile.ajaxurl,
			{
				action: 'celeb-profile-load-more',
				offset: cp_offset,
				term_id: jQuery('#celeb_page_latest_loadmore a').attr("class")
			},
			function( data ) {
				celeb_display_stories( data, cp_offset );
			},
			"json"
		);
	};

	/**
	 * The Magic Show
	 * controller function which is called on hashchange event & which triggers other stuff
	 * cascadingly
	 */
	var celeb_hashchange_handler = function () {
		var offset_new = celeb_get_offset( window.location.hash );

		if ( ( CelebProfile.offset - 1 ) !== offset_new && offset_new !== false ) {
			if ( ! celeb_show_cache( offset_new ) ) {
				celeb_fetch_stories( offset_new ); //time to fetch more stories from server
			}
		}

	};

	/**
	 * bind handler function to the hashchange event
	 */
	jQuery(window).bind('hashchange', celeb_hashchange_handler);

	/**
	 * trigger the hashchange event on page load to handle bookmarked pages
	 */
	jQuery(window).trigger("hashchange");
});
