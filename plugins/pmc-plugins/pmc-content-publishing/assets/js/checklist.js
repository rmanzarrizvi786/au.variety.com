/* global pmc, pmc_content_checklist_options */
( function( $ ) {

	PMC_Content_Checklist = function (options) {
		try {
			if ('undefined' === typeof pmc.hooks) {
				this.options = options;
			} else {
				this.options = pmc.hooks.apply_filters('pmc-content-checklist-options', options);
			}

			this.bind_events();
		} catch (e) {
		}
	}

	PMC_Content_Checklist.prototype = {

		$form_button_clicked: '',
		submit_form_anyway: false,

		/**
		 * Start watching changes
		 */
		bind_events: function () {
			try {

				if ('undefined' === typeof this.options.list) {
					return;
				}

				for (i in this.options.list) {
					var item = this.options.list[i];

					if ('undefined' === typeof item['explanation'] || 'undefined' === typeof item['validate']) {
						continue;
					}

					var method = 'watch_' + item['validate'];

					if ('function' === typeof this[method]) {
						this[method].call(this, item['explanation']);
					}

				}

				this.refresh_status();

				// Enforce the checklist if that option has been enabled
				// in the site's global cheezcap settings
				if ( 'undefined' !== typeof this.options.enforce_checklist_popup ) {
					if ( "1" === this.options.enforce_checklist_popup ) {
						this.enforce_checklist_popup();
					}
				}

			} catch (e) {
			}
		},

		/**
		 * Refresh the check list status
		 */
		refresh_status: function () {
			try {
				for (i in this.options.list) {
					var item = this.options.list[i];
					if ('undefined' === typeof item['explanation'] || 'undefined' === typeof item['validate']) {
						continue;
					}
					this.update_todo(item['explanation'], this.check(item['validate'], item['explanation']));
				}
			} catch (e) {
			}
		},

		/**
		 * Monitor for taxonomy checking
		 */
		watch_checklist: function (taxonomy) {

			try {
				var self = this;
				//Watch the taxonomy inputs for changes, i.e. when one is clicked/checked
				$(document).on('click', 'ul#' + taxonomy + 'checklist li input[type="checkbox"]', function (e) {
					self.refresh_status();
				});
				$('ul#' + taxonomy + 'checklist').on('DOMNodeInserted', function (e) {
					self.refresh_status();
				});
			} catch (e) {
			}
		}, // watch_checklist

		/**
		 * Monitor for child term.
		 *
		 * @param  string taxonomy
		 *
		 * @return void
		 */
		watch_child_term_checklist: function( taxonomy ) {
			this.watch_checklist( taxonomy );
		},

		/**
		 * Watch an FM <select> field for changes
		 *
		 * @param string taxonomy Watch <select>s with this name attribute
		 */
		watch_fm_select: function( taxonomy ) {

			var self = this,
				element = jQuery( 'select[name="' + taxonomy + '"]' );

			try {

				element.change( function() {
					self.refresh_status();
				});

				element.on( 'DOMNodeInserted', function() {
					self.refresh_status();
				});

				element.on( 'DOMNodeRemoved', function() {

					// Adding some delay because field manager will dynamically add/remove option in select tag.
					setTimeout( function() {
						self.refresh_status();
					}, 2000 );

				});

			} catch ( e ) {

				// Catch block

			}
		},

		/**
		 * Monitor for taxonomy input
		 */
		watch_taxinput: function( taxonomy ) {
			var self = this;

			try {

				$( document ).on( 'click', '.ntdelbutton', function() {
					self.refresh_status();
				});

				$( '#' + taxonomy + ' .tagchecklist' ).on( 'DOMNodeInserted', function() {
					self.refresh_status();
				});


			} catch ( e ) {

				// catch

			}
		}, // watch_taxinput

		/**
		 * Monitor for taxonomy input
		 */
		watch_hiddeninput: function (taxonomy) {
			try {

				var self = this;
				if( 'post_tag' === taxonomy ){
					var hidden_input = $('input[type="hidden"][name="relationships[' + taxonomy + '][terms][0]"]');
				} else {
					var hidden_input = $('input[type="hidden"][name^="relationships[' + taxonomy + '][terms]"]');
				}

				hidden_input.trigger('change');

				$(document).on('click', $('.fm-' + taxonomy + ' .fmjs-remove'), function () {
					setTimeout(function () {
						self.refresh_status();
					}, 600);
				});

				$(document).on('change', hidden_input, function (e) {
					self.refresh_status();
				});

				hidden_input.on('change', function (e) {
					self.refresh_status();
				});

			} catch (e) {
			}
		}, // watch_taxinput

		/**
		 * Monitor for taxonomy input
		 */
		watch_textinput: function (id) {
			try {
				var self = this;

				$('#' + id).on('input', function (e) {
					self.refresh_status();
				});

			} catch (e) {
			}
		}, // watch_taxinput

		/**
		 * Monitor for featured image changes
		 */
		watch_featured_image: function () {
			try {
				var self = this;
				$(document).on('click', '#postimagediv a#remove-post-thumbnail', function (e) {
					self.refresh_status();
				});
				$('#postimagediv').on('DOMNodeInserted', function (e) {
					self.refresh_status();
				});
			} catch (e) {
			}
		}, // watch_featured_image

		/**
		 * Monitor for attachment changes
		 */
		watch_attachment: function (id) {
			try {

				var self = this;
				$(document).on('click', 'a#remove-' + id + '-attachment', function (e) {
					self.refresh_status();
				});
				$('#' + id).on('DOMNodeInserted', function (e) {
					self.refresh_status();
				});
			} catch (e) {
			}
		}, // watch_featured_image

		/**
		 * Monitor for url slug
		 */
		watch_urlslug: function (id) {
			try {
				var self = this;
				$(document).on('input', $('#' + id), function (e) {
					self.refresh_status();
				});
				$(document).on('input', $('#new-post-slug'), function (e) {
					self.refresh_status();
				});

			} catch (e) {
			}
		}, // watch_urlslug

		/**
		 * To monitor alt text of gallery images.
		 *
		 * @return void
		 */
		watch_gallery_alt_text: function() {
			if ( 'undefined' === typeof wp.media.pmc_gallery ) {
				return;
			}

			var self = this,
				library = wp.media.pmc_gallery._frame.state( 'gallery-edit' ).get( 'library' );

			library.on( 'change', function() {
				self.refresh_status();
			});

			library.on( 'bulk:add', function() {
				self.refresh_status();
			});

			library.on( 'bulk:remove', function() {
				self.refresh_status();
			});

		},

		/**
		 * To monitor alt text of featured image.
		 *
		 * @return void
		 */
		watch_featured_image_alt_text: function() {
			if ( 'undefined' === typeof wp.media.featuredImage ) {
				return;
			}

			var self = this,
				library = wp.media.featuredImage._frame.state( 'featured-image' ).get( 'library' );

			library.on( 'change', function() {
				self.refresh_status();
			});

			library.on( 'close', function() {
				self.refresh_status();
			});

			library.on( 'select', function() {
				self.refresh_status();
			});

			$( document ).on( 'blur', 'label.setting[data-setting="alt"]', function() {
				self.refresh_status();
			});

		},

		/**
		 * Mark a checklist item as To-do or Done
		 *
		 * @param  $el todo_item A jQuery wrapped DOM element
		 * @param  bool is_todo_complete Is the to-do item complete?
		 * @return null
		 */
		update_todo: function (todo_id, is_todo_complete) {

			var list_item = $('li[title="' + todo_id + '"]');

			if (is_todo_complete) {
				list_item.removeClass('to-do').addClass('done');
				list_item.find('span.dashicons').removeClass('dashicons-no-alt').addClass('dashicons-yes');
			} else {
				list_item.removeClass('done').addClass('to-do');
				list_item.find('span.dashicons').removeClass('dashicons-yes').addClass('dashicons-no-alt');
			}

			var todo = $('.publishing-checklist-items span.dashicons-no-alt').length;
			var done = $('.publishing-checklist-items span.dashicons-yes').length;
			var total = todo + done;
			var publishing_list = $('.publishing-checklist-items-complete');
			var progress = publishing_list.find('progress');

			publishing_list.text(done + ' of ' + total + ' tasks complete');
			publishing_list.append(progress);
			publishing_list.find('progress').attr('value', done).attr('max', total);

		}, // update_todo

		/**
		 * Determine if there are any terms checked from checklist input taxonomy tree
		 *
		 * @return bool validity
		 */
		check_checklist: function( taxonomy ) {
			return ( 0 < $( 'ul#' + taxonomy + 'checklist li input[type="checkbox"]:checked' ).length );
		}, // check_checklist

		/**
		 * Determine if there are any child terms checked from checklist input taxonomy tree
		 *
		 * @return bool validity
		 */
		check_child_term_checklist: function( taxonomy ) {

			// Because, For child term we append `sub_` in original taxonomy.
			taxonomy = taxonomy.substr( 4 );

			return ( 0 < $( 'ul#' + taxonomy + 'checklist li > ul.children li input[type="checkbox"]:checked' ).length );
		},

		/**
		 * Determine if a FM <select> field has been properly filled out
		 *
		 * @param string taxonomy Look for selects with this name attribute
		 *
		 * @returns {boolean}
		 */
		check_fm_select: function( taxonomy ) {
			return ! pmc.is_empty( jQuery( 'select[name="' + taxonomy + '"]' ).val() );
		},

		/**
		 * Determine if there are any taxonomy terms added
		 *
		 * @return bool validity
		 */
		check_taxinput: function (taxonomy) {
			return ! pmc.is_empty( $('#tax-input-' + taxonomy).val() ) ;
		}, // check_taxinput

		/**
		 * Determine if there are any changes in input field
		 *
		 * @return bool validity
		 */
		check_textinput: function (id) {
			return ! pmc.is_empty( $('#' + id).val() );
		}, // check_taxinput

		/**
		 * Determine if there are any changes in hidden field
		 *
		 * @return bool validity
		 */
		check_hiddeninput: function (taxonomy) {
			var returnbool = false;

			var hidden_input = $('.fm-' + taxonomy + ' .fm-element input[type="hidden"]');

			jQuery.each(hidden_input, function (i, value) {
				if ($(value).attr('name') != 'relationships[' + taxonomy + '][terms][proto]') {
					var hidden_val = $(value).val();
					if ('undefined' !== typeof hidden_val && '' != hidden_val && jQuery.isNumeric(hidden_val) && Math.floor(hidden_val) == hidden_val ) {
						returnbool = true;
						return false; //break
					}
				}
			});

			/* this is specific to uncategorized category */
			if ('category' == taxonomy) {
				var found_uncategorized = false;
				var text_input = $('.fm-category .fm-element input[type="text"]');
				if ('uncategorized' == $(text_input).val().toLowerCase()) {
					returnbool = false;
				}
			}

			if('post_tag' == taxonomy){
				// there is a prototype hidden input field hence we check for value more than one
				returnbool = returnbool && 1 < $('.fm-' + taxonomy + ' .fm-element input[type="hidden"]').length;
			}

			return returnbool;
		}, // check_hiddeninput

		/**
		 * Determine if featured image is added
		 *
		 * @return bool validity
		 */
		check_featured_image: function () {
			return 0 < $('#postimagediv img').length;
		}, // check_featured_image

		/**
		 * Determine if featured image has alt text.
		 *
		 * @return bool validity
		 */
		check_featured_image_alt_text: function() {

			var selectedImage, altText;

			// If the featured image is not set then return false.
			if ( ! this.check_featured_image() ) {
				return false;
			}

			// Check if alt text has been updated for the image.
			selectedImage = $( '#_thumbnail_id' ).val();
			if ( '-1' === selectedImage ) {
				return false;
			}

			altText = wp.media.attachment( selectedImage ).attributes.alt;

			if ( altText ) {
				$( '#postimagediv img' ).attr({'alt': altText});
			}

			if ( ! pmc.is_empty( altText ) ) {
				return true;
			}

			// Check if img tag has alt text when edit page is loaded.
			return ! pmc.is_empty( $( '#postimagediv img' ).attr( 'alt' ) );
		}, // check_featured_image_alt_text

		/**
		 * Determine if attachment is added
		 *
		 * @return bool validity
		 */
		check_attachment: function (id) {
			return 0 < $('#remove-' + id + '-attachment').length;
		}, // check_featured_image

		/**
		 * Determine if there are any changes in post url slug
		 *
		 * @return bool validity
		 */
		check_urlslug: function (id) {
			var posttitle = $('#title').val();
			var postid = $('#post_ID').val();
			var old_slug = posttitle.toLowerCase().replace(/[^a-zA-Z 0-9]+/g, '').replace(/\s+/g, "-");
			var old_slug_with_id = old_slug + '-' + postid;
			var permalink = $('#editable-post-name-full').text().trim();
			var new_slug = permalink.substr(permalink.lastIndexOf('/') + 1);
			var is_url_edited = false;
			var new_edit_slug = '';
			if ($('#new-post-slug').length > 0) {
				new_edit_slug = $('#new-post-slug').val();
				is_url_edited = ('' !== new_edit_slug && old_slug !== new_edit_slug );
			}
			if (is_url_edited) {
				if (new_slug !== new_edit_slug) {
					new_slug = new_edit_slug;
				}
			}


			return '' !== new_slug && old_slug !== new_slug && old_slug_with_id !== new_slug ;
		}, // check_urlslug

		/**
		 * Check if every slide added in gallery have alt text or not.
		 *
		 * @returns {boolean}
		 */
		check_gallery_alt_text: function() {

			var library = wp.media.pmc_gallery._frame.state( 'gallery-edit' ).get( 'library' ),
				models = library.models,
				i = false;

			for ( i in models ) {
				if ( pmc.is_empty( models[ i ].get( 'alt' ) ) ) {
					return false;
				}
			}

			return true;
		},

		/**
		 * Helper function to do dynamic function call to [method]() or check_[method]()
		 */
		check: function (method, slug) {
			try {
				if (!method == 'undefined' || '' == method) {
					return false;
				}
				if ('undefined' === typeof this[method]) {
					method = 'check_' + method;
				}
				if ('function' == typeof this[method]) {
					return this[method].call(this, slug);
				}
			} catch (e) {
			}
			return false;
		}, // check

		/**
		 * Helper function to return the remaining to-do <li> elements
		 *
		 * @returns jQuery object of <li class="to-do"> elements
		 */
		get_incomplete_items: function() {
			return $( '.publishing-checklist .publishing-checklist-items ul li.to-do' );
		},

		/**
		 * Enforce incomplete checklist items on post save/publish
		 * by displaying a popup to the user prompting them to complete
		 * their checklist.
		 */
		enforce_checklist_popup: function() {

			var oThis = this;

			// Hook into the submit buttons and flag which one
			// submitted the form. document.activeElement works
			// great in everything except Safari :(
			$( 'form#post :submit' ).click( function() {
				oThis.$form_button_clicked = $( this );
			} );

			// Hook into the edit post form submission
			// Have to do this on button click event since jquery.validate.min.js conflicts with some brands.
			$( '#publish, #save-post' ).on( 'click', function( event ) {

				if ( false === oThis.edit_form_submit() ) {
					event.preventDefault();
					event.stopImmediatePropagation();
					return false;
				}

				return true;

			});

			$( 'form#post' ).submit( function( event ) {

				// Don't do anything when clicked on preview.
				if ( 'dopreview' === $( '#wp-preview', event.target ).val() ) {
					return true;
				}

				if ( false === oThis.edit_form_submit() ) {
					event.preventDefault();
					event.stopImmediatePropagation();
					return false;
				}

				return true;

			} );

			// Handle the 'Continue Editing' button in the checklist popup
			$( 'button#checklist-popup-continue-editing' ).click( function() {
				$( '#content-publishing-checklist-popup' ).hide();
			} );

			// Handle the 'Save Anyway' button in the checklist popup
			$( 'button#checklist-popup-continue-anyway' ).click( function() {

				// Set a flag to indicate that the user has opted
				// to submit the form anyway. This flag is checked
				// within edit_form_submit() below.
				oThis.submit_form_anyway = true;

				// Hide the enforcement popup
				$('#content-publishing-checklist-popup').hide();

				// Trigger the form to submit via the initiating
				// click element, e.g. 'Save Post' vs 'Publish', etc.
				oThis.$form_button_clicked.trigger( 'click' );

				// Reset this flag so it's cleared out
				oThis.submit_form_anyway = false;
			} );
		},

		/**
		 * Run some code when the edit post form submits
		 *
		 * Possibly halt the form submit if checklist items are not yet completed.
		 *
		 * @returns {boolean} True when checklist is completed. False when it's not.
		 */
		edit_form_submit: function() {

			var $incomplete_items = this.get_incomplete_items(),
				hidden_post_status,
				updated_post_status;

			// Possibly display the checklist enforcement popup modal
			//
			// The checklist enforcement popup halts the edit post form submission
			// and displays a popup modal prompting the user to complete their post's
			// checklist. The popup only shows when a post is changing to a specificly-
			// selected post status—when the post is advancing to a new post status.
			//
			// User's may select these post statuses via cheezcap in Global Theme Options.

			// Has the user already submitted the form, seen our popup, and chosen to
			// submit the form anyway? If so, allow the form to submit.
			if ( this.submit_form_anyway || 0 === $incomplete_items.length ) {
				return true;
			}

			updated_post_status = this.get_updated_post_status();
			hidden_post_status  = $( 'input#hidden_post_status' );
			hidden_post_status  = ( 0 < hidden_post_status.length && 'undefined' !== hidden_post_status.val() ) ? hidden_post_status.val().toLowerCase() : '';

			// If the post statuses are changing..
			if (
				'undefined' !== typeof hidden_post_status &&
				'undefined' !== typeof updated_post_status &&
				( hidden_post_status !== updated_post_status )
			) {

				// And we have statuses saved for this featured in cheezcap..
				if ( 'undefined' !== typeof this.options.enforce_checklist_popup_statuses ) {
					if ( this.options.enforce_checklist_popup_statuses.length > 0 ) {

						// And the new status is in our 'show the popup to' list..
						if ( _.contains( this.options.enforce_checklist_popup_statuses, updated_post_status ) ) {

							// Show the enforcer..
							this.show_checklist_enforcement_popup( $incomplete_items );

							// Halt the form submission
							return false;
						}
					}
				}
			}

			return true;
		},

		/**
		 * Show the checklist enforcement popup modal
		 *
		 * @returns {boolean}
		 */
		show_checklist_enforcement_popup: function( $incomplete_items ) {

			// No, the checklist is not yet complete..
			var $checklist_popup = $('#content-publishing-checklist-popup');
			var _self = this;
			var can_proceed = true;

			// this.$form_button_clicked is the input which triggered the form submission
			// This may be the save button, preview button, or the update|publish button

			// Don't hinder the submission when the 'Preview' button is pressed
			if ( this.$form_button_clicked.is( '#post-preview' ) ) {
				return true;
			}

			$( '#checklist-popup-continue-anyway' ).text( this.$form_button_clicked.val() + ' Anyway' );

			var $checklist_popup_ul = $checklist_popup.find( 'ul' );

			// Clear out the items already in the checklist popup
			$checklist_popup_ul.empty();

			var _checklist_popup_li_template = _.template( $( 'script#content-publishing-checklist-popup-li' ).html() );

			// List the incomplete items in the hidden popup
			$incomplete_items.each( function( key, value ) {

				var $value = $( value );

				if ( false !== can_proceed && 'undefined' !== $value.attr( 'title' ) ) {
					can_proceed = _self.is_force_check_checklist_done( $value.attr( 'title' ) );
				}

				$checklist_popup_ul
					.append(
						_checklist_popup_li_template( {
							li_class: $value.attr( 'class' ),
							li_text: $value.text(),
							span_class: $value.find( 'span.dashicons' ).attr( 'class' )
						} )
					);
			} );

			if ( false === can_proceed ) {
				$( '#checklist-popup-continue-anyway' ).prop( 'disabled', true );
			} else {
				$( '#checklist-popup-continue-anyway' ).prop( 'disabled', false );
			}

			// Show the warning popup
			$checklist_popup.show();
		},

		/**
		 * To check we are completing all force check checklist.
		 * If any of the force checklist is not completed then we can't proceed with post publish.
		 *
		 * @param {string} title checklist title.
		 *
		 * @return {boolean}
		 */
		is_force_check_checklist_done: function( title ) {

			var to_return = true;

			if ( 'undefined' === typeof this.options.list ) {
				return to_return;
			}

			$.each( this.options.list, function( key, value ) {
				if (
					'undefined' !== typeof title &&
					'undefined' !== typeof value.explanation &&
					'undefined' !== typeof value.force_check &&
					title === value.explanation &&
					'1' === value.force_check
				) {
					to_return = false;
					return false;
				}
			});

			return to_return;
		},

		/**
		 * To get the post status that is being updated in the request.
		 */
		get_updated_post_status: function() {

			var post_status,
				publish_value;

			// When 'Save' button is clicked, determined which post status is being saved.
			if ( this.$form_button_clicked.is( '#save-post' ) ) {

				// Get the value of the 'Status:' select box.
				post_status = $( '#post_status' ).val();

			} else if ( this.$form_button_clicked.is( '#publish' ) ) {

				publish_value = this.$form_button_clicked.val();

				if ( 'publish' === publish_value.toLowerCase() ) {

					post_status = 'publish';

				} else if ( 'update' === publish_value.toLowerCase() ) {

					// Get the value of the 'Status:' select box.
					post_status = $( '#post_status' ).val();

				}

			}

			return post_status;
		}
	};

	window.onload = function() {

		if (typeof pmc_content_checklist_options !== 'undefined') {
			window.pmc_content_checklist_options = new PMC_Content_Checklist(pmc_content_checklist_options);
		}

		var checklist_container = $('.publishing-checklist');

		var show_list_btn = checklist_container.find(".publishing-checklist-show-list");
		var hide_list_btn = checklist_container.find(".publishing-checklist-hide-list");
		var todo_list_items = checklist_container.find(".publishing-checklist-items");

		// Open the checklist by default
		if (show_list_btn.is(':visible')) {
			show_list_btn.hide();
			todo_list_items.show();
			hide_list_btn.show();
		}
	}

} )( jQuery );
