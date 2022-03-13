/*
 PMC's Sticky Rails Ads script

 Minify this file with:
 sudo npm install -g uglify
 uglify -s sticky-rail-ads.js -o sticky-rail-ads.min.js
 */

(function($) {

	//defaults
	var parent_selector        = ''; //Sidebar css selector
	var first_ad_selector      = ''; // Fisrt ad css selector
	var second_ad_selector     = ''; // Second ad css selector
	var nav_bar_selector       = '#masthead-sticky-wrapper'; // Sticky nav bar css selector if any
	var admin_bar_selector     = '#wpadminbar'; // Admin bar css selector when logged int
	var first_ad_limit         = 0; // First ad scroll limit
	var sticky_margin_top      = 20; //Sticky top position
	var second_ad_limit        = 0; //Second ad scroll limit
	var ad_container_width     = 320; // Ad container width
	var is_dynamic_content     = false;
	var get_second_ad_limit;

	if ( 'undefined' !== typeof pmc_sticky_rail_ads
		&& 'rail_selector' in pmc_sticky_rail_ads
		&& 'first_ad_selector' in pmc_sticky_rail_ads
		&& 'second_ad_selector' in pmc_sticky_rail_ads
		&& 'nav_bar_selector' in pmc_sticky_rail_ads
		&& 'admin_bar_selector' in pmc_sticky_rail_ads
		&& 'first_ad_limit' in pmc_sticky_rail_ads
		&& 'ad_container_width' in pmc_sticky_rail_ads
		&& 'is_dynamic_content' in pmc_sticky_rail_ads
	) {
		parent_selector        = pmc_sticky_rail_ads.rail_selector;
		first_ad_selector      = pmc_sticky_rail_ads.first_ad_selector;
		second_ad_selector     = pmc_sticky_rail_ads.second_ad_selector;
		nav_bar_selector       = pmc_sticky_rail_ads.nav_bar_selector;
		admin_bar_selector     = pmc_sticky_rail_ads.admin_bar_selector;
		first_ad_limit         = parseInt( pmc_sticky_rail_ads.first_ad_limit );
		ad_container_width     = parseInt( pmc_sticky_rail_ads.ad_container_width );
		is_dynamic_content     = pmc_sticky_rail_ads.is_dynamic_content;
	}

	var $parent_object          = $(parent_selector);
	var $first_ad_object        = $(first_ad_selector);
	var $second_ad_object       = $(second_ad_selector);
	var first_ad_limit_final    = 0;
	var ad_wrapper              = '<div id="sticky-rail-ad" style="padding-bottom:10px;width:100%;background-color:#ffffff;text-align:center;"></div>';

	if( $first_ad_object.length ) {
		first_ad_limit_final = ( first_ad_limit + $first_ad_object.offset().top );
		var $admz1 = $first_ad_object.parents( '.admz' );
		$admz1.width(ad_container_width);
		$admz1.css('padding','0');
		$admz1.css('text-align','center');
		$first_ad_object.wrap(ad_wrapper);
		$first_ad_object = $first_ad_object.parent();
	}
	if( $second_ad_object.length ) {
		var $admz2 = $second_ad_object.parents( '.admz' );
		$admz2.width(ad_container_width);
		$admz2.css('padding','0');
		$admz2.css('text-align','center');
		$second_ad_object.wrap(ad_wrapper);
		$second_ad_object = $second_ad_object.parent();
	}

	var update_rail_ads_position = _.debounce(function() {
		start_scroll();
	}, 300);

	$(window).on('scroll', update_rail_ads_position );

	reset_scroll = function() {
		$first_ad_object.trigger('detach.ScrollToFixed');
		$first_ad_object.attr("style","");
		$second_ad_object.trigger('detach.ScrollToFixed');
		$second_ad_object.attr("style","");
	};

	/**
	 * Calculates second ad limit.
	 *
	 * @return {number}
	 */
	get_second_ad_limit = function() {
		return ( ( $parent_object.height() + $parent_object.offset().top ) - ( $second_ad_object.outerHeight() + sticky_margin_top ) );
	};

	start_scroll = function() {
		//reset fixed positions
		reset_scroll();
		//recalculate limit as ads take longer time to load
		sticky_margin_top = get_margin_top();

		//only if the give ad object exists.
		//Also if first ad is 600 or above then no sticky
		if( $first_ad_object.length && first_ad_limit > 0 && $first_ad_object.height() < 600 ) {

			$first_ad_object.scrollToFixed(
				{
					limit: first_ad_limit_final,
					marginTop: sticky_margin_top,
					offsets: false,
					removeOffsets: true,
					preFixed: function() {
						$(this).css('background-color', 'white');
						$(this).css('text-align','center');
						$(this).css('padding-bottom','10px');
						$(this).css('padding-top','10px');
					},
					postFixed: function() {
						$(this).css('background-color', 'white');
						$(this).css('text-align','center');
						$(this).css('padding-bottom','10px');
						$(this).css('padding-top','10px');
					}
				}
			);
		}

		if( $parent_object.length && $second_ad_object.length ) {

			second_ad_limit = is_dynamic_content ? get_second_ad_limit : get_second_ad_limit();

			$second_ad_object.scrollToFixed(
				{
					limit: second_ad_limit,
					marginTop: sticky_margin_top,
					offsets: false,
					removeOffsets: true,
					preFixed: function() {
						$(this).css('background-color', 'white');
						$(this).css('text-align','center');
						$(this).css('padding-bottom','10px');
						$(this).css('padding-top','10px');
					},
					postFixed: function() {
						$(this).css('background-color', 'white');
						$(this).css('text-align','center');
						$(this).css('padding-bottom','10px');
						$(this).css('padding-top','10px');
					},
					fixed: function() {
						$(window).off('scroll', update_rail_ads_position );
					}
				}
			);
		}
	};

	get_margin_top = function() {
		var nav_bar_height   = 0;
		var admin_bar_height = 0;

		if( $(nav_bar_selector).length ) {
			nav_bar_height = $(nav_bar_selector).outerHeight( true );
		}
		if( $(admin_bar_selector).length ) {
			admin_bar_height = $(admin_bar_selector).outerHeight();
		}
		return ( nav_bar_height + admin_bar_height );
	};

})(jQuery);


