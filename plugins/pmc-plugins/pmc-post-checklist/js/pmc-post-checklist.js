PMC_Post_Checklist = function( options ) {
	try {
		if ( 'undefined' == typeof pmc.hooks ) {
			this.options = options;
		} else {
			this.options = pmc.hooks.apply_filters( 'pmc-post-checklist-options', options );
		}
		this.bind_events();
	} catch( e ) {
		console.log( e );
	}
}

PMC_Post_Checklist.prototype = {
	/**
	 * Start watching changes
	 */
	bind_events: function() {
		try {

			if ( 'undefined' == typeof this.options.list ) {
				return;
			}

			for( i in this.options.list ) {
				var item = this.options.list[i];
				if ( 'undefined' == typeof item['slug'] || 'undefined' == typeof item['validate'] ) {
					continue;
				}

				var method = 'watch_' + item['validate'];
				if ( 'function' == typeof this[method] ) {
					this[method].call(this,item['slug']);
				}

			}

			this.refresh_status();
		} catch( e ) {
			console.log( e );
		}

	},

	/**
	 * Refresh the check list status
	 */
	refresh_status: function() {
		try {
			for( i in this.options.list ) {
				var item = this.options.list[i];
				if ( 'undefined' == typeof item['slug'] || 'undefined' == typeof item['validate'] ) {
					continue;
				}
				this.update_todo( 'pmc-post-checklist-' + item['slug'], this.check( item['validate'], item['slug'] ) );
			}
		} catch( e ) {
			console.log( e );
		}
	},

	/**
	 * Monitor for taxonomy checking
	 */
	watch_checklist: function( taxonomy ) {
		try {
			var self = this;
			//Watch the taxonomy inputs for changes, i.e. when one is clicked/checked
			jQuery( document ).on( 'click', 'ul#'+taxonomy+'checklist li input[type="checkbox"]', function( e ) {
				self.refresh_status();
			} );
			jQuery( 'ul#'+taxonomy+'checklist' ).on( 'DOMNodeInserted', function( e ) {
				self.refresh_status();
			} );
		} catch( e ) {
			console.log( e );
		}
	}, // watch_checklist

	/**
	 * Monitor for taxonomy input
	 */
	watch_taxinput: function( taxonomy ) {
		try {
			var self = this;
			jQuery( document ).on( 'click', 'a.ntdelbutton', function( e ) {
				self.refresh_status();
			} );
			jQuery( 'div.tagchecklist' ).on( 'DOMNodeInserted', function( e ) {
				self.refresh_status();
			} );

		} catch(e) {
			console.log(e);
		}
	}, // watch_taxinput

	/**
	 * Monitor for taxonomy input
	 */
	watch_textinput: function( id ) {
		try {
			var self = this;

			jQuery( '#' + id ).on( 'input', function( e ) {
				self.refresh_status();
			} );

		} catch(e) {
			console.log(e);
		}
	}, // watch_taxinput

	/**
	 * Monitor for featured image changes
	 */
	watch_featured_image: function() {
		try {
			var self = this;
			jQuery( document ).on( 'click', '#postimagediv a#remove-post-thumbnail', function( e ) {
				self.refresh_status();
			} );
			jQuery( '#postimagediv' ).on( 'DOMNodeInserted', function( e ) {
				self.refresh_status();
			} );
		} catch(e) {
			console.log(e);
		}
	}, // watch_featured_image

	/**
	 * Monitor for attachment changes
	 */
	watch_attachment: function( id ) {
		try {
			var self = this;
			jQuery( document ).on( 'click', 'a#remove-'+ id +'-attachment', function( e ) {
				self.refresh_status();
			} );
			jQuery( '#'+ id ).on( 'DOMNodeInserted', function( e ) {
				self.refresh_status();
			} );
		} catch(e) {
			console.log(e);
		}
	}, // watch_featured_image

    /**
     * Monitor for url slug
     */
    watch_urlslug: function (id) {
        try {
            var self = this;
            jQuery(document).on('input', jQuery('#' + id), function (e) {
                self.refresh_status();
            });
            jQuery(document).on('input', jQuery('#new-post-slug'), function (e) {
                self.refresh_status();
            });

        } catch (e) {
            console.log(e);
        }
    }, // watch_urlslug

	/**
	* Mark a checklist item as To-do or Done
	*
	* @param  $el todo_item A jQuery wrapped DOM element
	* @param  bool is_todo_complete Is the to-do item complete?
	* @return null
	*/
	update_todo: function( todo_id, is_todo_complete ) {
		if ( is_todo_complete ) {
			jQuery( '#' + todo_id ).removeClass( 'to-do' ).addClass('completed');
		} else {
			jQuery( '#' + todo_id ).removeClass( 'completed' ).addClass('to-do');
		}
	}, // update_todo

	/**
	* Determine if there are any terms checked from checklist input taxonomy tree
	*
	* @return bool validity
	*/
	check_checklist: function ( taxonomy ) {
		return 0 < jQuery('ul#'+taxonomy+'checklist li input[type="checkbox"]:checked').length;
	}, // check_checklist

	/**
	* Determine if there are any taxonomy terms added
	*
	* @return bool validity
	*/
	check_taxinput: function ( taxonomy ) {
		return 'undefined' != jQuery('#tax-input-' + taxonomy).val() && '' < jQuery('#tax-input-' + taxonomy).val();
	}, // check_taxinput

	/**
	* Determine if there are any changes in input field
	*
	* @return bool validity
	*/
	check_textinput: function ( id ) {
		return 'undefined' != jQuery('#'+id).val() && '' < jQuery('#'+id).val();
	}, // check_taxinput

	/**
	* Determine if featured image is added
	*
	* @return bool validity
	*/
	check_featured_image: function () {
		return 0 < jQuery('#postimagediv img').length;
	}, // check_featured_image

	/**
	* Determine if attachment is added
	*
	* @return bool validity
	*/
	check_attachment: function ( id ) {
		return 0 < jQuery('#remove-' + id +'-attachment').length;
	}, // check_featured_image

    /**
     * Determine if there are any changes in post url slug
     *
     * @return bool validity
     */
    check_urlslug: function (id) {
        var posttitle = jQuery('#title').val();
        var old_slug = posttitle.toLowerCase().replace(/[^a-zA-Z 0-9]+/g, '').replace(/\s+/g, "-");
        var new_slug = jQuery('#' + id).val();
        var is_url_edited = false;
        var new_edit_slug = '';
        if( jQuery('#new-post-slug').length > 0 ) {
            new_edit_slug = jQuery('#new-post-slug').val();
            is_url_edited = ('' !== new_edit_slug && old_slug !== new_edit_slug );
        }
        if( is_url_edited ){
            if( new_slug !== new_edit_slug ) {
                new_slug = new_edit_slug;
            }
        }
        return '' !== new_slug  && old_slug !== new_slug;
    }, // check_urlslug

	/**
	 * Helper function to do dynamic function call to [method]() or check_[method]()
	 */
	check: function( method, slug ) {
		try {
			if ( ! method == 'undefined' || '' == method ) {
				return false;
			}
			if ( 'undefined' == typeof this[method] ) {
				method = 'check_' + method;
			}
			if ( 'function' == typeof this[method] ) {
				return this[method].call(this,slug);
			}
		} catch ( e ) {
			console.log( e );
		}
		return false;
	} // check
};

jQuery(document).ready(function(){
	if ( typeof pmc_post_checklist_options != 'undefined' ) {
		window.pmc_post_checklist = new PMC_Post_Checklist( pmc_post_checklist_options );
	}
});
