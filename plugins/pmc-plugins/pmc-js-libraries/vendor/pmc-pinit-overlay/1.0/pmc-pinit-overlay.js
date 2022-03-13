/*jslint browser: true, devel: true, ass: true, eqeq: true, nomen: true, regexp: true, unparam: true, sloppy: true, stupid: true, sub: true, vars: true, white: true */
/*global jQuery: true, PMC_PinIt_Overlay: true */
/**
 * Copyright (c) 2015 PMC
 * @version 1.0
 * @since 2012-04-09
 * @author Hau Vong
 * @ref: https://developers.pinterest.com/pin_it/
 */

/*
 * @usage:

PMC_PinIt_Overlay( { options } );
default options:
	selector: 'img.pinit-overlay',  // default selector
	padding: 10,  // default offset padding
	position: "top-left"	// top-left, top-center, top-right, center, bottom-lef, bottom-center, bottom-right

// to bind pinit overlay to multiple selectors
PMC_PinIt_Overlay().bindto(['img.class1','img.class2',...]);

// Use this function to dynamic place the pinit button over an image
PMC_PinIt_Overlay().overlay( jQuery('img1').get(0) );

//For example update pinit button when gallery image render
jQuery(document).on('pmc-gallery-image-rendered',function(data){
	if ( typeof data.node != 'undefined' ) {
		PMC_PinIt_Overlay({
			selector: false,
			position: 'top-center'
		}).overlay( jQuery(data.node).find( 'img' ).get(0) );
	}
});

// Image override pinit button positioin
<img class="pinit-overlay" pinit-position="center" src="..." />
*/

var PMC_PinIt_Overlay = PMC_PinIt_Overlay || function( args ){
	// expose variable self to allow scope function to access this object
	var self = this;

	if ( typeof self.options === 'undefined' ) {
		self.options = {
			selector: "img.pinit-overlay",
			padding: 10,
			position: "top-left",
			min_width: 200,
			min_height: 100
		};
	}
	if ( typeof args != 'undefined' ) {
		jQuery.extend(self.options,args);
	}

	// if object already initialize, just return self
	if ( typeof self.initialized !== 'undefined' ) {
		return self;
	}

	if ( typeof self.selectors === 'undefined' ) {
		self.selectors = [];
	}
	self.initialized = true;

	function overlay_selectors() {
		var idx;
		// we need to de-queue all current un-binded selectors
		var selectors = self.selectors;
		self.selectors = [];
		for( idx in selectors ) {
			jQuery(selectors[idx] + ':visible').each(function(){
				self.attach(this);
			});
		}
	} // function overlay_selectors

	// initialize and bind events if document is ready or bind to document ready event
	function init() {
		if ( 'loading' == document.readyState ) {
			if ( typeof self.init_event_binded !== 'undefined' ) {
				return;
			}
			jQuery(document).ready(overlay_selectors);
		} else {
			overlay_selectors();
		}

		if ( typeof self.init_event_binded === 'undefined' ) {
			// image position might be change after document is fully loaded
			jQuery(window).load(function(){
				// we need to re-attach/refresh the pinit button if image changed position
				jQuery('[pmc-pinit-attached]').each(function(){
					self.attach(this);
				});
			});
		}

		self.init_event_binded = true;
		return self;
	} // init

	/* Public functions */

	self.overlay = function( el ) {
		if ( typeof el === 'undefined' || ! el ) {
			return;
		}
		self.attach(el,'pmc-pinit-overlay-btn');
		if ( 'complete' == document.readyState ) {
			if ( ! jQuery(el).attr('pmc-pinit-onload') ) {
				jQuery(el).attr('pmc-pinit-onload',true);
				var overlay_options = jQuery.extend({},self.options);
				jQuery(el).on('load',function(){
					var saved_options = self.options;
					self.options = overlay_options;
					self.attach(this,'pmc-pinit-overlay-btn');
					self.options = saved_options;
				});
			}
		}
	};

	// Attach pinit button to an element or refresh existing attached button position
	// when id is passed, re-use and move that pinit button instead
	self.attach = function( el, id ) {

		if ( typeof el === 'undefined' || ! el ) {
			return;
		}
		if ( typeof id === 'undefined' || !id ) {
			id = jQuery(el).attr('pmc-pinit-attached');
		} else {
			jQuery('[pmc-pinit-attached="' + id + '"]').removeAttr('pmc-pinit-attached');
		}
		if ( typeof id === 'undefined' || ! id ) {
			id = "pmc-pinit-overlay-btn" + Math.round(new Date().getTime() + (Math.random() * 100));
		}
		if ( ! jQuery('#'+id).length ) {
			jQuery(el).attr('pmc-pinit-attached',id);
			jQuery('body').append( jQuery("<a/>",{id:id,class:"pmc-pinit-overlay-btn",href:"#",style:"display:none"}) );
		}

		var btn_width  = jQuery('#'+id).width();
		var btn_height = jQuery('#'+id).height();
		var img_offset = jQuery(el).offset();
		var img_width  = jQuery(el).width();
		var img_height = jQuery(el).height();
		var position   = jQuery(el).attr('pinit-position');
		var img_url    = jQuery(el).attr('src');

		if ( img_width < self.options.min_width || img_height < self.options.min_height ) {
			return;
		}

		if ( typeof img_url === 'undefined' ) {
			img_url = jQuery('img',el).attr('src');
		}

		var pinurl = 'https://pinterest.com/pin/create/button/?'
			+'url='+encodeURIComponent(document.location.href)
			+'&media='+encodeURIComponent(img_url);

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
		jQuery('#'+id)
			.css(css)
			.attr('href',pinurl)
			.show();
	}; // public attach function

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
