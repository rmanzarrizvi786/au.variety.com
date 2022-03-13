/**
 * PMC Taxonomy Color script for edit tag screen in wp-admin
 *
 * @since ?
 * @version 2013-09-26 Amit Gupta
 */

var farbtastic_txt;
var farbtastic_bg;

var pmc_taxonomy_color = {

	data_id_arr : ["#pmc_taxonomy_seo_title"],

	get_data: function () {
		var tax_data = {};

		if ( pmc_term_meta ) {
			tax_data = pmc_term_meta.get_all();
		}

		if ( tax_data ) {
			return tax_data;
		}

		return {};
	},

	save_data: function (data) {
		if ( pmc_term_meta ) {
			tax_data = pmc_term_meta.set_all( data );
		}
	},

	save_check_boxes: function (id, value) {

		var data = {};
		if (id == "pmc_show_in_river") {
			data['show_in_river'] = value;
			this.save_data(data);
		}
		if (id == "pmc_use_special_template") {
			data['use_special_template'] = value;
			this.save_data(data);
		}
	},

	change: function () {
		var data_id_arr = this.data_id_arr;

		for (var i = 0; i < data_id_arr.length; i++) {

			jQuery(data_id_arr[i]).on('keyup',function () {
				var key = this.id.replace("pmc_taxonomy_", "");
				var data = new Array();
				data[key] = jQuery(this).val();
				pmc_taxonomy_color.save_data(data);
			});
		}
	},

	load: function () {

		var data_id_arr = this.data_id_arr;
		var current_data = this.get_data();

		for (var i = 0; i < data_id_arr.length; i++) {

			var key = data_id_arr[i].replace("#pmc_taxonomy_", "");

			if (typeof(current_data[key]) !== 'undefined') {
				jQuery(data_id_arr[i]).val(current_data[key]);
			}

		}
	},

	load_checkboxes: function(){

		var current_data = this.get_data();

		var show_in_river = false;
		var use_special_template = false;

		if ( typeof(current_data['show_in_river']) !== 'undefined') {
			show_in_river = current_data['show_in_river'];
		}

		if (typeof(current_data['use_special_template']) !== 'undefined') {
			use_special_template = current_data['use_special_template'];
		}

		jQuery( "#pmc_show_in_river").attr('checked', show_in_river );
		jQuery( "#pmc_use_special_template").attr('checked', use_special_template );

		if( use_special_template ) {
			jQuery("#pmc-tax-template-url").show();
		}
	}
}


function pickColor( color, type ) {
	if ( color == null || "undefined" == color ) {
		color = "";
	} else {
		color = "#" + color;
	}
	jQuery( "#pickcolor-" + type ).css( {"background-color": color, "height":30,"width":30, "display":"block","float":"left","margin-right":10,"border":"1px solid black" } );
	jQuery( "#colorpicker-value-" + type ).css( {"background-color": color, "height":30,"width":30, "display":"block","float":"left","margin-right":10,"border":"1px solid black" } );

	var current_colors = pmc_taxonomy_color.get_data();

	if ( "txt" == type ) {
		farbtastic_txt.setColor( color );
		current_colors[type] = color.replace( '#', '' );
	} else {
		farbtastic_bg.setColor( color );
		current_colors[type] = color.replace( '#', '' );
	}

	jQuery( "#colorpicker-value-" + type ).val( color.replace( '#', '' ) );

	pmc_taxonomy_color.save_data(current_colors);
}

function getCurrentColor() {

	var _arHex = pmc_taxonomy_color.get_data();

	var hex_txt = "";
	if( _arHex && typeof(_arHex['txt']) !== 'undefined' ) {
		hex_txt = _arHex['txt'];
	}
	if ( typeof(
			hex_txt
			) === "string" ) {
		hex_txt = hex_txt.replace( /[^a-fA-F0-9]+/, "" );
	} else {
		hex_txt = "";
	}

	var hex_bg = '';
	if (_arHex && typeof(_arHex['bg']) !== 'undefined') {
		hex_bg = _arHex['bg'];
	}
	if ( typeof(
			hex_bg
			) === "string" ) {
		hex_bg = hex_bg.replace( /[^a-fA-F0-9]+/, "" );
	} else {
		hex_bg = "";
	}

	if ( hex_txt.length == 0 || hex_txt.length == 3 || hex_txt.length == 6 ) {
		pickColor( hex_txt, "txt" );
		jQuery( "#colorpicker-value-txt" ).val( hex_txt );
	}

	if ( hex_txt.length == 0 || hex_txt.length == 3 || hex_txt.length == 6 ) {
		pickColor( hex_bg, "bg" );
		jQuery( "#colorpicker-value-bg" ).val( hex_bg );
	}
}

jQuery( document ).ready( function () {
	// Hide the regular description row
	// Note: This may break if another plugin injects a row between the colour picker and the description.
	//cfg: jQuery("#colorpicker-row-txt").prev().hide();

	// Bind a click event to #pickcolor-bg to show the colour picker
	jQuery( "#pickcolor-bg" ).click( function () {
		jQuery( "#colorpicker-wrapper-bg" ).show();
		return false;
	} );

	jQuery( "#pickcolor-txt" ).click( function () {
		jQuery( "#colorpicker-wrapper-txt" ).show();
		return false;
	} );

	// Bind a click event to the Clear Color link to remove the current background color
	jQuery( "#clearcolor-bg" ).click( function () {
		pickColor( null, "bg" );
		return false;
	} );

	jQuery( "#clearcolor-txt" ).click( function () {
		pickColor( null, "txt" );
		return false;
	} );

	// Instantiate Farbtastic colour picker.  Farbtastic is included with WordPress.
	farbtastic_bg = jQuery.farbtastic( "#colorpicker-wheel-bg", function ( color ) {
		pickColor( color.replace( "#", "" ), "bg" );
	} );
	farbtastic_txt = jQuery.farbtastic( "#colorpicker-wheel-txt", function ( color ) {
		pickColor( color.replace( "#", "" ), "txt" );
	} );

	// Set the colour picker background to the current description, if any. Call this here because getCurrentColor() uses pickColor() which requires var farbtastic to be set.
	getCurrentColor();
	pmc_taxonomy_color.load_checkboxes();
	pmc_taxonomy_color.load();
	pmc_taxonomy_color.change();

	// Put a class on the colorpicker-value input so that we can tell if it's being used
	jQuery( "#colorpicker-set-bg" ).click( function () {
		pickColor( jQuery( "#colorpicker-value-bg" ).val(), "bg" );
		jQuery( "#colorpicker-wrapper-bg" ).fadeOut( 2 );
		return false;
	} );
	jQuery( "#colorpicker-set-txt" ).click( function () {
		pickColor( jQuery( "#colorpicker-value-txt" ).val(), "txt" );
		jQuery( "#colorpicker-wrapper-txt" ).fadeOut( 2 );
		return false;
	} );

	jQuery( "#pmc_show_in_river,#pmc_use_special_template").change( function(){

		if( jQuery(this).is(":checked") ) {
			pmc_taxonomy_color.save_check_boxes( this.id, true );
			if ("pmc_use_special_template" == this.id) {
				jQuery("#pmc-tax-template-url").show();
			}
		} else {
			if ("pmc_use_special_template" == this.id) {
				jQuery("#pmc-tax-template-url").hide();
			}
			pmc_taxonomy_color.save_check_boxes( this.id, false );
		}

		return true;
	});

} );

//EOF
