/* globals jQuery, _varietyPrintIssueSettingExports */
/* exported varietyPrintIssueSetting */

var varietyPrintIssueSetting = ( function( $ ) {
	'use strict';

	var self = {
		saveButtonActive: false,
		ajaxUrl: '',
		ajaxAction: '',
		input: {
			userList: '#variety-input-user-list',
			volume: '#variety-input-volume',
			issue: '#variety-input-issue',
			date: '#variety-input-date'
		},
		l10n: {
			invalidDate: '',
			invalidDateFormat: '',
			invalidVolume: '',
			invalidIssue: '',
			msgScheduleError: '',
			msgScheduleUpdated: '',
			msgScheduleRemoved: '',
			msgUserListSaved: '',
			msgEdit: '',
			msgRemove: '',
			msgToDo: ''
		}
	};

	if ( 'undefined' !== typeof _varietyPrintIssueSettingExports ) {
		$.extend( self, _varietyPrintIssueSettingExports );
	}

	self.init = function() {
		$( document ).ready( function() {
			if ( $( '#variety-print-issue-setting' ).length < 1 ) {
				return;
			}
			self.nonceVal = $( '#variety-print-info-nonce' ).val();
			self.settingMessage = $( '#variety-setting-message' );
			self.btnSave = $( '#variety-btn-save' );
			self.btnAdd = $( '#variety-btn-add' );
			self.dateHint = $( '#variety-date-hint' );

			self.refreshData();
			self.btnAdd.on( 'click', self.addClick );
			self.btnSave.attr( 'disabled', 'disabled' ).on( 'click', self.saveClick );
			$( self.input.userList ).on( 'keyup', self.activeSaveButton );

			$( self.input.date + ', ' + self.input.volume + ', ' + self.input.issue ).on( 'keyup change', self.refreshOverlay );

			self.dateHint.on( 'click', function() {
				$( self.input.date ).trigger( 'focus' );
			} );

			$( self.input.date ).datepicker( { dateFormat: 'yy-mm-dd', defaultDate: $( self.input.date ).val() } );
			self.refreshOverlay();
		} );
	};

	self.validateData = function( showError ) {
		var d, parts, error = '';
		$( '#variety-print-issue-setting input' ).removeClass( 'variety-error' );
		self.inputDateVal = $( self.input.date ).val();
		self.inputVolumeVal = parseInt( $( self.input.volume ).val() );
		self.inputIssueVal = parseInt( $( self.input.issue ).val() );

		parts = /([0-9]{4})-([0-9]{2})-([0-9]{2})/.exec( self.inputDateVal );
		if ( parts ) {
			d = new Date( parts[1], parts[2] - 1, parts[3] );
			if ( self.inputDateVal !== $.datepicker.formatDate( 'yy-mm-dd', d ) ) {
				$( self.input.date ).addClass( 'variety-error' );
				error += self.l10n.invalidDate + '\n';
			}
		} else {
			$( self.input.date ).addClass( 'variety-error' );
			error += self.l10n.invalidDateFormat + '\n';
		}

		if ( isNaN( self.inputVolumeVal ) || self.inputVolumeVal < 1 ) {
			$( self.input.volume ).addClass( 'variety-error' );
			error += self.l10n.invalidVolume + '\n';
		}

		if ( isNaN( self.inputIssueVal ) || self.inputIssueVal < 1 ) {
			$( self.input.issue ).addClass( 'variety-error' );
			error += self.l10n.invalidIssue + '\n';
		}

		if ( '' !== error ) {
			if ( showError ) {
				alert( error );
			}
			return false;
		}
		return true;
	};

	self.addClick = function() {
		if ( ! self.validateData( true ) ) {
			return false;
		}

		$.ajax( {
			url: self.ajaxUrl,
			type: 'POST',
			data: {
				action: self.ajaxAction,
				nonce: self.nonceVal,
				cmd: 'add-print-volume-schedule',
				date_str: self.inputDateVal,
				volume: self.inputVolumeVal,
				issue: self.inputIssueVal
			}
		} ).success( function( data ) {
			$( self.input.date + ', ' + self.input.volume ).val( '' );
			$( self.input.issue ).val( 1 );
			if ( false === data ) {
				self.showMessage( self.l10n.msgScheduleError );
				return false;
			}
			self.refreshOverlay();
			self.refreshData();
			self.showMessage( self.l10n.msgScheduleUpdated );
			return false;
		} );
		return false;
	};

	self.saveClick = function() {
		$.ajax( {
			url: self.ajaxUrl,
			type: 'POST',
			data: {
				action: self.ajaxAction,
				nonce: self.nonceVal,
				cmd: 'update-notify-user-list',
				list: $( self.input.userList ).val()
			}
		} ).success( function( data ) {
			if ( false !== data ) {
				console.log( data );
				$( self.input.userList ).val( data );
				self.btnSave.attr( 'disabled', 'disabled' ).removeClass( 'button-primary' );
				self.saveButtonActive = false;
				self.showMessage( self.l10n.msgUserListSaved );
			}
		} );
		return false;
	};

	self.activeSaveButton = function() {
		if ( ! self.saveButtonActive ) {
			self.btnSave.removeAttr( 'disabled' ).addClass( 'button-primary' );
			self.hideMessage();
			self.saveButtonActive = true;
		}
	};

	self.refreshOverlay = function() {
		if ( '' === $( self.input.date ).val() ) {
			self.dateHint.removeClass( 'variety-hide' );
		} else {
			self.dateHint.addClass( 'variety-hide' );
		}

		if ( self.validateData( false ) ) {
			self.btnAdd.removeAttr( 'disabled' ).addClass( 'button-primary' );
			self.hideMessage();
		} else {
			self.btnAdd.attr( 'disabled', 'disabled' ).removeClass( 'button-primary' );
		}
	};

	self.removeSchedule = function( data ) {
		$.ajax( {
			url: self.ajaxUrl,
			type: 'POST',
			data: {
				action: self.ajaxAction,
				nonce: self.nonceVal,
				cmd: 'remove-print-volume-schedule',
				date: data['date']
			}
		} ).success( function( data ) {
			$( '#variety-sch-' + data ).remove();
			self.showMessage( self.l10n.msgScheduleRemoved );
		} );
	};

	self.renderRow = function( data, label ) {
		var button = '*locked*';
		if ( ! data['locked'] ) {
			button = $( '<input type="button" class="button" value="' + label + '">' )
				.data( data )
				.on( 'click', function() {
					if ( 'Remove' === $( this ).val() ) {
						self.removeSchedule( data );
					} else {
						alert( self.l10n.msgToDo );
					}
				} );
		} else if ( true !== data['locked'] ) {
			button = '*' + data['locked'] + '*';
		}

		return $( '<tr></tr>' ).attr( 'id', 'variety-sch-' + data.date )
			.append( $( '<td></td>' ).html( data.date_str ) )
			.append( $( '<td></td>' ).html( data.volume ) )
			.append( $( '<td></td>' ).html( data.issue ) )
			.append( $( '<td></td>' ).append( button ) );
	};

	self.refreshData = function() {
		$.ajax( {
			url: self.ajaxUrl,
			type: 'POST',
			data: {
				action: self.ajaxAction,
				nonce: self.nonceVal,
				cmd: 'get-all'
			}
		} ).success( function( data ) {
			var grid = $( '#variety-data-grid' );
			grid.empty();
			if ( data['print-info'] ) {
				grid.append( self.renderRow( data['print-info'], self.l10n.msgEdit ) );
			}
			if ( data['volume-schedule'] ) {
				$.each( data['volume-schedule'], function( idx, item ) {
					grid.append( self.renderRow( item, self.l10n.msgRemove ) );
				} );
			}
			if ( data['user-list'] ) {
				$( self.input.userList ).val( data['user-list'] );
			}
		} );
	};

	self.showMessage = function( message ) {
		if ( '' !== message ) {
			self.settingMessage.html( '<p>' + message + '</p>' ).removeClass( 'variety-hide' );
		} else {
			self.hideMessage();
		}
	};

	self.hideMessage = function() {
		self.settingMessage.html( '' ).addClass( 'variety-hide' );
	};

	return self;

} )( jQuery );
