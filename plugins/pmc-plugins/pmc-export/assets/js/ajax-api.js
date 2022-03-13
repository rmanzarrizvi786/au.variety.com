var pmc = pmc || {};

pmc.stream = {
	settings: {
		ajax_url: ajaxurl,
		progress_bar: '#progress-bar',
		page_per_file: 100 // 100 records per page => 10k records per file
	},

	progress: function( current, max ) {

		var self    = this;
		var msg     = 'Processing...';
		var percent = 0;

		if ( typeof current === 'string' && current ) {
			msg     = current;
			current = 0;
			max     = 1;
			jQuery('.spin-loader').hide();
			jQuery('#submit').prop('disabled', false);
		} else {
			if ( typeof max === 'undefined' ) {
				max = 100;
			}
			if ( typeof current === 'undefined' ) {
				current = 0;
			}
			if ( max <= 0 ) {
				current = 0;
				max     = 1;
				msg     = 'Error detected';
				jQuery('.spin-loader').hide();
				jQuery('#submit').prop('disabled', false);
			} else {
				percent = ( current / max ) * 100;
				if ( percent > 0 ) {
					msg = percent.toPrecision(3) + '%';
				}
				if ( percent < 100 ) {
					jQuery( '.spin-loader' ).show();
					jQuery('#submit').prop('disabled', true);
				}
			}
		}

		jQuery( self.settings.progress_bar ).progressbar( {
			value: current,
			max: max,
			complete: function() {
				jQuery( self.settings.progress_bar )
					.children('.ui-progressbar-value')
					.html('Completed.')
					.show();
				jQuery('.spin-loader').hide();
				jQuery('#submit').prop('disabled', false);
			}
		} )

		if ( percent < 100 ) {
			jQuery( self.settings.progress_bar )
				.children('.ui-progressbar-value')
				.html( msg )
				.show();
		}

	},

	download: function( stream_id, data, filename ) {
		var self = this;
		var total_pages  = 0;
		var current_page = 0;
		var storage_key  = 'pmc-stream' + stream_id;
		var csv_headers  = '';

		if ( typeof stream_id === 'undefined' || ! stream_id || typeof data === 'undefined' ) {
			return;
		}

		data.action     = 'pmc_stream';
		data.stream_id  = stream_id;
		data.page       = 0;

		if ( typeof filename !== 'string' || ! filename ) {
			filename = 'data';
		}

		// Simulate a buffers storage stream using sessionStorage object
		var buffers = {
			part: 0,
			count: 0,
			reset: function() {
				this.part  = 0;
				this.count = 0;
				sessionStorage.setItem( storage_key, '' );
			},
			append: function( data ) {
				this.count++;
				if ( this.count > self.settings.page_per_file ) {
					if ( ! this.part ) {
						this.part++;
					}
					this.download();
				}
				sessionStorage.setItem( storage_key, sessionStorage.getItem( storage_key ) + data );
			},
			download: function() {
				$part = '';
				if ( this.part > 0 ) {
					$part = '-' + this.part.toString();
				}
				var blob = new Blob( [ sessionStorage.getItem( storage_key ) ], {type: 'text/csv;charset=utf-8'});
				var a      = document.createElement('a');
				a.href     = window.URL.createObjectURL(blob);
				a.text     = filename + $part + '.csv';
				a.download = filename + $part + '.csv';
				jQuery('#download-links').append(jQuery('<div/>').append(a));
				a.click();
				sessionStorage.setItem( storage_key, csv_headers );
				this.part++;
				this.count = 0;
			}
		};

		// recursive function to retrieve data in chunks
		// We do not want this function exposed and should only be call within this code scope
		function download_chunk( data ) {

			// initialize default value, first time being called
			if ( 0 === data.page ) {
				self.progress( 0, 100 );
				csv_headers = '';
				buffers.reset( stream_id );
				jQuery('#download-links').html('');
			}

			jQuery.ajax( {
				type: "POST",
				url: self.settings.ajax_url,

				// Note: We're not using ajax nonce here as we are call the ajax re-cursively
				// We would not want to generating different nonce and invalid our current state without refreshing the page
				data: data,

				success: function(response, textStatus, jqXHR) {
					if ( typeof response.success === 'undefined' ) {
						self.progress( 'Errors detected' );
						return;
					}
					if ( response.success && response.data.pages > 0 ) {
						if ( response.data.page > 0 ) {
							buffers.append( response.data.data );
							self.progress( parseInt( response.data.page ), parseInt( response.data.pages ) );
							// Are we done yet? if not, continue with next block
							if ( response.data.page < response.data.pages ) {
								data.page = parseInt(response.data.page) + 1;
								// @TODO: We might want to do a sleep here if we're worry about server performance
								download_chunk( data );
							} else {
								self.progress( 'Completed.' );
								buffers.download();
							}
						} else {
							csv_headers = response.data.data;
							data.page   = 1;
							download_chunk( data );
						}
					} else {
						if ( response.success && 0 === parseInt( response.data.pages ) ) {
							self.progress( 'No data to download' );
						} else {
							if ( typeof response.data.message ) {
								self.progress( response.data.message );
							} else {
								self.progress( 'Errors detected' );
							}
						}
					}
				},

				error: function (jqXHR, textStatus, errorThrown) {
					self.progress( 'Errors detected' );
				},

				complete: function(jqXHR, textStatus) {
				}

			} );
		}

		download_chunk( data );

	}
};
