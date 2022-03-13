/*
Author: PMC, Hau Vong
License: PMC proprietary.  All rights reserved.
*/

var PMC_Ad_Condition = function( options ) {
	var self = this;
	this.options = options;
	this.items = [];
	this.track_count = 0;
	if ( typeof this.options.parameters == 'undefined' ) {
		this.options.parameters = {};
	}
	this.refresh();

	jQuery(document).on('click', '.pmc-ad-condition-del-x', function(e) {
		self.del_item( jQuery(this).parent().attr('id') );
		jQuery(this).parent().parent().remove();
	} );

}

PMC_Ad_Condition.prototype = {
	generate_form: function() {
		if ( jQuery(this.options.form).length == 0 ) {
			return;
		}
		var self = this;
		this.item_result = jQuery('<select><option value="true"></option><option value="false">not</option></select>');
		this.item_list = jQuery('<select><option value="">-- condition --</option></select>');
		this.item_param = jQuery('<span />');
		this.item_add_button = jQuery('<input type="button" value="Add" />');
		jQuery(this.options.form)
			.empty()
			.append(this.item_result)
			.append(this.item_list)
			.append('(')
			.append(this.item_param)
			.append(')')
			.append(this.item_add_button)
			;
		for(f in this.options.parameters) {
			this.item_list.append(jQuery('<option/>',{value:f}).html(f));
		}
		this.item_list.on('change', function(e) {
			self.render_item_param( jQuery(this).val() );
		} );
		this.item_add_button.on('click', function(e) {
			var f = self.item_list.val();
			if ( self.options.parameters[f] ) {
				var item = {
					name: f,
					result: 'true' == self.item_result.val(),
					params: []
				};
				self.item_param.find('input[type="text"]').each(function(i,el){
					item.params.push( jQuery(el).val() );
				});
				self.add_item( item );

				jQuery(self.item_result).val('true');
				jQuery(self.item_list).val('')
				self.render_item_param( '' );

			}
		})
	},

	render_item_param: function( f ) {
		if ( typeof this.item_param == 'undefined' ) {
			return;
		}
		this.item_param.empty();
		if ( this.options.parameters[f] ) {
			for(i in this.options.parameters[f] ) {
				var p = this.options.parameters[f][i];
				this.item_param.append(jQuery('<input/>',{type:'text',name:p,placeholder:p}));
			}
		}
	},

	render_item: function( item ) {
		var pstr = '';
		for (i in item.params ) {
			if ( pstr.length > 0 ) {
				pstr += ', ';
			}
			pstr += "'" + item.params[i] + "'";
		}
		jQuery( this.options.display ).append(
			jQuery('<li/>').append(
				jQuery('<div/>',{id: item.id }).html(
						(item.result == false ? '<span class="not">not</span> ' : '') + '<span class="name">' +item.name +'</span>(' + pstr +')'
					).append('<a href="javascript:;" class="pmc-ad-condition-del-x">X</a>')
				)
			);
	},

	del_item: function( id ) {
		var i = 0;
		while( i < this.items.length && this.items[i].id != id ) {
			i += 1;
		}
		if ( i < this.items.length ) {
			this.items.splice( i, 1 );
		}
		this.update();
	},

	add_item: function ( item ) {
		var self = this;
		while( item.params.length > 0 && item.params[item.params.length-1] === '') {
			item.params.pop();
		}
		item.id = ++this.track_count;
		this.items.push( item );
		this.render_item( item );
		this.update();
	},

	update: function() {
		jQuery( this.options.input ).val( JSON.stringify( this.items ) );
	},

	reset: function( items ) {
		if ( items instanceof Array ) {
			this.items = [];
			jQuery( this.options.display ).empty();
			for(i in items ) {
				this.add_item( items[i] );
			}
		}
		this.generate_form();
		this.update();
	},

	refresh: function() {
		this.list = [];
		if ( jQuery( this.options.input ).length > 0 && jQuery( this.options.input ).val().length > 0 ) {
			try {
				var list = JSON.parse( jQuery( this.options.input ).val() );
				if ( list instanceof Array ) {
					this.list = list;
				}
			} catch(e) {}
		}
		this.reset( this.list );
		this.generate_form();
	},

	register_functions: function( list ) {
	}

};

jQuery(document).ready(function(){
	var parameters = {"has_category":["category","post"],"has_tag":["tag","post"],"is_404":[],"is_archive":[],"is_author":["author"],"is_category":["category"],"is_home":[],"is_page":["page"],"is_paginated":[],"is_search":[],"is_single":["post"],"is_singular":["post_type"],"is_tag":["tag"],"is_tax":["taxonomy","term"],"is_vertical":["vertical"],"user_location":["geocode"]};

	if ( typeof pmc_ad_condition_options != 'undefined' && typeof pmc_ad_condition_options.parameters != 'undefined' ) {
		parameters = pmc_ad_condition_options.parameters;
	}

	window.pmc_ad_condition = new PMC_Ad_Condition({
		input: '#pmc-adm-condition-input',
		display: '#pmc-adm-condition-display',
		form: '#pmc-adm-condition-form',
		parameters: parameters
	});

});

