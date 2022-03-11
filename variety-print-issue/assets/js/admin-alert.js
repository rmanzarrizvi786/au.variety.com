/* globals jQuery, _varietyPrintIssueAlertExports */
/* exported varietyPrintIssueAlert */

var varietyPrintIssueAlert = ( function( $ ) {
	'use strict';

	var self = {
		ajaxUrl: '',
		ajaxAction: '',
		input: {
			volume: '#variety-print-info-volume',
			issue: '#variety-print-info-issue',
			name: '#variety-print-info-name',
			slug: '#variety-print-info-slug',
			date: '#variety-print-info-date',
			termId: '#variety-print-info-term-id'
		},
		l10n: {
			invalidDate: '',
			invalidDateFormat: '',
			invalidVolume: '',
			invalidIssue: ''
		}
	};

	if ( 'undefined' !== typeof _varietyPrintIssueAlertExports ) {
		$.extend( self, _varietyPrintIssueAlertExports );
	}

	self.init = function() {
		$( document ).ready( function() {
			self.overlay = $( '#variety-print-issue-alert-overlay' );
			self.alert = $( '#variety-print-issue-alert' );
			self.btnCancel = $( '#variety-btn-cancel' );
			self.btnUpdate = $( '#variety-btn-update' );
			self.btnNo = $( '#variety-btn-no' );
			self.btnYes = $( '#variety-btn-yes' );
			self.promptDecide = $( '#variety-prompt-decide' );
			self.promptUpdate = $( '#variety-prompt-update' );

			if ( 0 === self.alert.length ) {
				return;
			}

			self.nonceVal = $( '#variety-print-info-nonce' ).val();
			self.overlay.prependTo( 'body' );
			self.alert.prependTo( 'body' ).css( { left: '165px' } );
			self.btnNo.on( 'click', self.showFixPanel );
			self.btnCancel.on( 'click', self.hideFixPanel );
			self.btnUpdate.on( 'click', self.submitUpdate );
			self.btnYes.on( 'click', self.submitYes );
			$( self.input.date ).datepicker( { dateFormat: 'yy-mm-dd', defaultDate: $( self.input.date ).val() } );
		} );
	};

	self.submitYes = function() {
		$.ajax( {
			url: self.ajaxUrl,
			type: 'POST',
			data: {
				action: self.ajaxAction,
				cmd: 'confirm-marker-issue',
				slug: $( self.input.slug ).val(),
				nonce: self.nonceVal
			}
		} ).success( function() {
			self.overlay.remove();
			self.alert.remove();
		} );
		return false;
	};

	self.submitUpdate = function() {
		var errorMsg = '',
			d = '',
			volume = $( self.input.volume ).val(),
			issue = $( self.input.issue ).val(),
			date = $( self.input.date ).val(),

			parts = /([0-9]{4})-([0-9]{2})-([0-9]{2})/.exec( date );

		if ( parts ) {
			d = new Date( parts[1], parts[2] - 1, parts[3] );
			if ( date !== $.datepicker.formatDate( 'yy-mm-dd', d ) ) {
				errorMsg += self.l10n.invalidDate + '\n';
			}
		} else {
			errorMsg += self.l10n.invalidDateFormat + '\n';
		}

		if ( isNaN( volume ) || volume < 1 ) {
			errorMsg += self.l10n.invalidVolume + '\n';
		}

		if ( isNaN( issue ) || issue < 1 ) {
			errorMsg += self.l10n.invalidIssue + '\n';
		}

		if ( '' !== errorMsg ) {
			alert( errorMsg );
			return false;
		}

		$.ajax( {
			url: self.ajaxUrl,
			type: 'POST',
			data: {
				action: self.ajaxAction,
				cmd: 'fix-marker-issue',
				date: date,
				volume: volume,
				issue: issue,
				name: $( self.input.name ).val(),
				slug: $( self.input.slug ).val(),
				term_id: $( self.input.termId ).val(),
				nonce: self.nonceVal
			}
		} ).success( function() {
			self.overlay.remove();
			self.alert.remove();
		} );

		return false;
	};

	self.showFixPanel = function() {
		self.promptDecide.hide();
		self.promptUpdate.show();
	};

	self.hideFixPanel = function() {
		self.promptUpdate.hide();
		self.promptDecide.show();
	};

	return self;

} )( jQuery );
