jQuery(function() {

var s,
	PMC_Groups = {

		/**
		 * Register settings for JS object
		 *
		 * @since 2015-07-15
		 * @version 2015-07-15 Javier Martinez - PPT-4923
		 */
		settings: {
			ajax_url:          pmc_groups_options.url,
			admin_url:         pmc_groups_options.admin_url,
			pmc_groups_nonce : pmc_groups_options.pmc_groups_nonce,
			groups:            pmc_groups_options.pmc_groups,
			wp_users:          pmc_groups_options.wp_users,
			current_group: null,
			ui:{
				list:              jQuery( "#pmc-group-list" ),
				modal_users_table: jQuery( "#pmc-group-list-modal"),
				btn_save:          jQuery('#save'),
				placeholder_row:   jQuery('<tr>').addClass('pmc-groups-row').append(
					jQuery('<td>').addClass('pmc-groups-login'),
					jQuery('<td>').addClass('pmc-groups-displayname'),
					jQuery('<td>').addClass('pmc-groups-emailaddress'),
					jQuery('<td>').addClass('pmc-groups-action').append(
						jQuery('<a>').addClass('ui-button btn-action').data({action:'none', member:'no',groupkey:'', groupuser:'' })
					)
				),
				btn_show_users:    jQuery( ".btn-show-users" ),
				user_list_dialog:  jQuery( "#pmc-group-users-modal" )
			}
		},


		/**
		 * Kick things off
		 */
		init: function() {

			// Make sure all variables exist.
			if( PMC_Groups.is_valid() === true ){
				s = this.settings;

				s.ui.user_list_dialog.dialog({
					autoOpen: false,
					modal:    true,
					height:   400,
					width:    650,
					buttons: {
						Save: function() {
							PMC_Groups.save();
						},
						Close: function() {
							jQuery( this ).dialog( "close" );
						}
					},
					dialogClass: "pmc-group-users-dialog", // deprecated in jqui 1.12
					classes: {
						"ui-dialog": "pmc-group-users-dialog", // for jqui >= 1.12
					},
					create: function() {
						jQuery('.pmc-group-users-dialog .ui-dialog-buttonset').prepend( jQuery('<div>').addClass('spinner pmc-groups-spinner') );
					}
				});

				this.bindUIActions();
			}

		},

		/**
		 * Validate localized variables
		 *
		 * @since 2015-07-15
		 * @version 2015-07-15 Javier Martinez - PPT-4923
		 */
		is_valid: function(){
			var is_valid = true;

			if ( typeof pmc_groups_options.url == 'undefined'
				|| typeof pmc_groups_options.pmc_groups !== 'object'
				|| typeof pmc_groups_options.wp_users !== 'object'
				) {

				is_valid = false;
			}

			return is_valid;
		},

		/**
		 * Bind all UI actions here
		 *
		 * @since 2015-07-15
		 * @version 2015-07-15 Javier Martinez - PPT-4923
		 */
		bindUIActions: function() {

			/**
			 * Show modal with group users
			 */
			s.ui.btn_show_users.on("click", function() {
				var data = jQuery(this).data();
				s.current_group = data.groupkey;
				//PMC_Groups.updateGroupUserCount( data.groupkey );
				var users = PMC_Groups.getUsers( data.groupkey );

				// Set title
				s.ui.user_list_dialog.dialog({
					title: 'Users for ' + data.groupkey
				});

				// Clear rows in group modal to prevent duplicates
				PMC_Groups.clearRows();

				// Show current users first
				jQuery.each( users, function( index, username ) {
					var user = PMC_Groups.getUserByLogin( username );
					var newRow = s.ui.placeholder_row.clone( true, true );
					jQuery( '.pmc-groups-login', newRow ).text( username );
					jQuery( '.pmc-groups-displayname', newRow ).text( user.display_name );
					jQuery( '.pmc-groups-emailaddress', newRow ).text( user.user_email );
					jQuery( '.pmc-groups-action .btn-action', newRow ).data({ groupkey: s.current_group, groupuser: username, member: 'yes' }).addClass('remove-user').text('Remove');
					newRow.appendTo(s.ui.modal_users_table);
				});

				// Show prospective users
				jQuery.each (PMC_Groups.settings.wp_users, function (index, wpUser ){

					if( jQuery.inArray( wpUser.user_login , users ) === -1 ){
						var newRow = s.ui.placeholder_row.clone( true, true );
						jQuery( '.pmc-groups-login', newRow ).text( wpUser.user_login );
						jQuery( '.pmc-groups-displayname', newRow ).text( wpUser.display_name );
						jQuery( '.pmc-groups-emailaddress', newRow ).text( wpUser.user_email );
						jQuery( '.pmc-groups-action .btn-action', newRow ).data({ groupkey: s.current_group, groupuser: wpUser.user_login, member: 'no' }).addClass('add-user').text('Add');
						newRow.appendTo(s.ui.modal_users_table);
					}
				});

				s.ui.user_list_dialog.dialog('open');

				return false;
			});

			/**
			 * Button to add/remove a user from a group
			 */
			s.ui.modal_users_table.on( "click", "tr a.btn-action", function() {
				var data = jQuery(this).data();
				var el = jQuery( this );
				var parent_row = el.parents('tr').eq(0);


				PMC_Groups.toggleUser( s.current_group, data.groupuser );

				// Removing a user
				if ( el.hasClass('remove-user') ) {
					el.removeClass('remove-user').addClass('add-user').text('Add');
					parent_row.removeClass('add-member');

					// if this is an existing member, strikethrough the text to indicate a change
					if ( 'yes' === el.data('member') ) {
						parent_row.addClass('remove-member');
					}
				} else if ( el.hasClass('add-user') ) { // Adding a user
					el.removeClass('add-user').addClass('remove-user').text('Remove');
					parent_row.removeClass('remove-member');

					// if this is a new member, modify the text to indicate a change
					if ( 'no' === el.data('member') ) {
						parent_row.addClass('add-member');
					}
				}

				return false;
			});

			/**
			 * Save button click
			 */
			s.ui.btn_save.on( "click", function(e) {
				e.preventDefault();
				PMC_Groups.save( PMC_Groups.settings.current_group );
			});

		},

		/**
		 * Clears table shows to prevent stacking when modal opens
		 *
		 * @since 2015-07-15
		 * @version 2015-07-15 Javier Martinez - PPT-4923
		 */
		clearRows: function(){
			jQuery('#pmc-group-list-modal tbody tr.pmc-groups-row').remove();
		},

		/**
		 * Returns users for a given groupkey
		 *
		 * @param groupkey
		 * @return {*}
		 *
		 * @since 2015-07-15
		 * @version 2015-07-15 Javier Martinez - PPT-4923
		 */
		getUsers: function( groupkey ){
			try {
				if ( groupkey && PMC_Groups.settings.groups.hasOwnProperty(groupkey) ) {
					return PMC_Groups.settings.groups[groupkey].users;
				}
			} catch ( ignore ) {}
			return [];
		},

		/**
		 * Looks up a user object by their username.
		 *
		 * @param username
		 */
		getUserByLogin: function ( username ){
			var user = {}, default_user = {
				ID: 0,
				user_login: username,
				display_name: username,
				user_email: ''
			};

			if ( PMC_Groups.settings.wp_users.hasOwnProperty(username) && 'object' === typeof PMC_Groups.settings.wp_users[username] ) {
				user = PMC_Groups.settings.wp_users[username];
			}
			return jQuery.extend( {}, default_user, user );
		},

		/**
		 * Toggled a user from 'in group' to 'out of group' in the JS object only
		 *
		 * @param groupKey
		 * @param userName
		 *
		 * @since 2015-07-15
		 * @version 2015-07-15 Javier Martinez - PPT-4923
		 */
		toggleUser: function (groupKey, userName ){

			var users = PMC_Groups.settings.groups[groupKey].users;

			if ( jQuery.inArray( userName, users ) > -1 ){
				users.splice( jQuery.inArray( userName, users), 1 );
			}else{
				PMC_Groups.settings.groups[groupKey].users.push( userName );
			}

			PMC_Groups.updateGroupUserCount( groupKey );

		},

		/**
		 * Updated the user count in the group listings.
		 *
		 * @param groupKey
		 *
		 * @since 2015-07-15
		 * @version 2015-07-15 Javier Martinez - PPT-4923
		 */
		updateGroupUserCount: function( groupKey ){
			var targetRow = PMC_Groups.settings.ui.list.find("tr." + groupKey);
			PMC_Groups.settings.groups[groupKey].user_count = PMC_Groups.settings.groups[groupKey].users.length;

			targetRow.find( "td.count a").text( PMC_Groups.settings.groups[groupKey].user_count );
		},

		/**
		 * Update a group's userlist on the main admin screen
		 *
		 * @param groupKey
		 *
		 * @since 2015-07-15
		 * @version 2015-07-15 Javier Martinez - PPT-4923
		 */
		updateGroupUsersList: function( groupKey ) {

			var target = PMC_Groups.settings.ui.list.find("tr." + groupKey + " td.users");
			var groupUsers = PMC_Groups.getUsers( groupKey );

			// Clear out existing users so we can refresh the list
			target.empty();

			// Loop through current group users and add them to the list
			jQuery.each(groupUsers, function (index, userName ){
				var link_to_append = jQuery( "<a />" )
					.attr( "href", PMC_Groups.settings.admin_url + userName )
					.attr( "id", 'pmc-group-user-' + userName)
					.html( userName );

				target.append( link_to_append );
				if ( index + 1 < groupUsers.length ) {
					target.append( ', ' );
				}
			});

		},

		/**
		 * AJAX call to save group config
		 *
		 * @since 2015-07-15
		 * @version 2015-07-15 Javier Martinez - PPT-4923
		 */
		save: function( ){
			jQuery('.spinner.pmc-groups-spinner').css('visibility', 'visible');
			jQuery.get(PMC_Groups.settings.ajax_url, {
				action: 'groups_crud',
				method: 'save',
				pmc_groups_nonce: PMC_Groups.settings.pmc_groups_nonce,
				data:  {
					'group': PMC_Groups.settings.current_group,
					'users': PMC_Groups.getUsers( PMC_Groups.settings.current_group )
				}

			}, PMC_Groups.ajaxCallback, 'json')

			.done(function() {
				jQuery('.spinner.pmc-groups-spinner').css('visibility', 'hidden');
			});

		},

		/**
		 * Handle the response of an AJAX call.
		 *
		 * @param {Object} response
		 *
		 * @since 2015-07-15
		 * @version 2015-07-15 Javier Martinez - PPT-4923
		 */
		ajaxCallback: function(response) {

			if (response.success) {
				PMC_Groups.updateGroupUsersList( PMC_Groups.settings.current_group );
			}
		}
	};

	/**
	 * Init
	 *
	 * @since 2015-07-15
	 * @version 2015-07-15 Javier Martinez - PPT-4923
	 */
	if ( typeof pmc_groups_options != 'undefined' ) {
		PMC_Groups.init();
	}

});
