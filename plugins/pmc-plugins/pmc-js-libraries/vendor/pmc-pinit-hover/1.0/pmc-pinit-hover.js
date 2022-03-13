/*jslint browser: true, devel: true, ass: true, eqeq: true, nomen: true, regexp: true, unparam: true, sloppy: true, stupid: true, sub: true, vars: true, white: true */
/*global jQuery: true, PMC_PinIt_Hover: true */

/**
 * Copyright (c) 2015 PMC
 * @version 1.0
 * @since 2012-04-09
 * @author Hau Vong
 * @ref: https://developers.pinterest.com/pin_it/
 */

/*
 * @usage:

PMC_PinIt_Hover( { options } );
default options:
	selector: 'img.pinit-hover',  // default selector
	padding: 10,  // default offset padding
	position: "top-left"	// top-left, top-center, top-right, center, bottom-lef, bottom-center, bottom-right

// to bind pinit hover to multiple selectors
PMC_PinIt_Hover().bindto(['img.class1','img.class2',...]);

// Image override pinit button positioin
<img class="pinit-hover" pinit-position="center" src="..." />

 */

var PMC_PinIt_Hover = PMC_PinIt_Hover || function( args ){
	// expose variable self to allow scope function to access this object
	var self = this;

	if ( typeof self.options === 'undefined' ) {
		self.options = {
			selector: "img.pinit-hover",
			padding: 10,
			position: "top-left",
			min_width: 200,
			min_height: 100,
			exclude_handle: ""
		};
	}
	if ( typeof args != 'undefined' ) {
		jQuery.extend(self.options,args);
	}

	// if object already initialize, just return self
	if ( typeof self.initialized !== 'undefined' ) {
		return self;
	}

	if ( typeof self.binded_selectors === 'undefined' ) {
		self.binded_selectors = {};
	}

	if ( typeof self.selectors === 'undefined' ) {
		self.selectors = [];
	}
	self.initialized = true;

	function bind_selector( selector ) {
		if ( typeof selector === 'undefined' || ! selector ) {
			selector = self.options.selector;
		}
		if ( ! selector ) {
			return;
		}
		if ( typeof self.binded_selectors[selector] !== 'undefined' ) {
			return;
		}
		self.binded_selectors[selector] = true;
		if ( ! jQuery('#pmc-pinit-hover-btn').length ) {
			jQuery("body").append( jQuery("<a/>",{id:"pmc-pinit-hover-btn",style:"display:none;",href:"#"}) );
			jQuery('#pmc-pinit-hover-btn').hover(
				// hover in
				function(e){
					// need this to prevent button flashing show/hide
					jQuery(this).show();
				},
				// hover out
				function(e){
					// hide it due to HL wanting a custom icon on the image's edge
					jQuery(this).hide();
				}
			).on('click',function(e){
				window.open(jQuery(this).attr('href'),'pmc-pinit','height=600,width=800,left=10,top=10,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes')
				return false;
			});
		}
		jQuery('body').on(
				// events
				{
					mouseenter: function(e){

						var bind_element = true;
						if ( 'function' === typeof self.options.exclude_handle ) {
							var exclude_ele = self.options.exclude_handle( this );
							if ( true === exclude_ele ) {
								bind_element = false;
							}
						}

						if ( true === bind_element ) {
							self.show(this);
						}
					},
					mouseleave: function(e){
						jQuery("#pmc-pinit-hover-btn").hide();
					}
				},
				// monitor the selector
				selector
			);
	} // function bind_selector

	// bind all events to the given selectors
	function bind_events() {
		var idx;
		// we need to de-queue all current un-binded selectors
		var selectors = self.selectors;
		self.selectors = []
		for( idx in selectors ) {
			bind_selector(selectors[idx]);
		}
	} // function bind_events

	// initialize and bind events if document is ready or bind to document ready event
	function init() {
		if ( 'loading' == document.readyState ) {
			if ( typeof self.init_event_binded !== 'undefined' ) {
				return;
			}
			self.init_event_binded = true;
			jQuery(document).ready(bind_events);
		} else {
			bind_events();
		}
		return self;
	} // init

	/* Public functions */

	self.show = function( el ) {
		var btn_width  = jQuery('#pmc-pinit-hover-btn').width();
		var btn_height = jQuery('#pmc-pinit-hover-btn').height();
		var img_offset = jQuery(el).offset();
		var img_width  = jQuery(el).width();
		var img_height = jQuery(el).height();
		var position   = jQuery(el).attr('pinit-position');
		var img_url    = jQuery(el).attr('src');
		var meta_description = jQuery("meta[name='description']").attr( 'content' );

		if ( img_width < self.options.min_width || img_height < self.options.min_height ) {
			return;
		}

		if ( typeof img_url === 'undefined' ) {
			img_url = jQuery('img',el).attr('src');
		}

		var pinurl = 'https://pinterest.com/pin/create/button/?'

			+'url='+encodeURIComponent( document.location.href )
			+'&media='+encodeURIComponent( img_url )
			+'&description='+encodeURIComponent( meta_description );
		
		// if element has no position define, use default position
		if ( typeof position === 'undefined' || !position ) {
			position = self.options.position;
		}

		var css = {};

		switch ( position ) {
			case "top-left":
				css = {top: (img_offset.top+self.options.padding) + "px", left: (img_offset.left+self.options.padding) + "px" };
				break;
			case "top-center":
				css = {top: (img_offset.top+self.options.padding) + "px", left: (img_offset.left+(img_width-btn_width)/2) + "px" };
				break;
			case "top-right":
				css = {top: (img_offset.top+self.options.padding) + "px", left: (img_offset.left+img_width-btn_width-self.options.padding) + "px" };
				break;
			case "bottom-left":
				css = {top: (img_offset.top+img_height-btn_height-self.options.padding) + "px", left: (img_offset.left+self.options.padding) + "px" };
				break;
			case "bottom-center":
				css = {top: (img_offset.top+img_height-btn_height-self.options.padding) + "px", left: (img_offset.left+(img_width-btn_width)/2) + "px" };
				break;
			case "bottom-right":
				css = {top: (img_offset.top+img_height-btn_height-self.options.padding) + "px", left: (img_offset.left+img_width-btn_width-self.options.padding) + "px" };
				break;
			case "center":
			default:
				css = {top: (img_offset.top+((img_height-btn_height)/2)) + "px", left: (img_offset.left+(img_width-btn_width)/2) + "px" };
				break;
		}

		jQuery('#pmc-pinit-hover-btn')
			.css(css)
			.attr('href',pinurl)
			.show();
	}; // public show function

	self.bindto = function( selectors ) {
		var idx;
		if ( selectors ) {
			if ( typeof selectors === 'string' ) {
				self.selectors.push(selectors);
			} else {
				for( idx in selectors ) {
					self.selectors.push(selectors[idx]);
				}
			}
		}
		// return self object
		return init();
	}; // public bindto function

	// return a self object
	return self.bindto(self.options.selector);
};
