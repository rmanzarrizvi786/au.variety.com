( function() {
	( function( $ ) {
		var AdminPageLocking;
		AdminPageLocking = ( function() {
			function AdminPageLocking() {
				this.currentTime = 0;
				this.lockPeriod = adminPageLockingData.lockPeriod * 1000;
				this.lockPeriodMax = adminPageLockingData.lockPeriodMax * 1000;
				if ( $( '#apl-user' ).length ) {
					alert( $( '#apl-lock-error p' ).text().trim() );
					this.exitScreen();
					return;
				}
				$( window ).on( 'beforeunload', ( function( _this ) {
					return function() {
						_this.releaseLock();
					};
				} ( this ) ) );
				$( document ).on( 'click', '.apl-confirm-button', ( function( _this ) {
					return function( event ) {
						tb_remove();
						return $( document ).trigger( 'apl-confirm', [ $( event.target ).data( 'confirm' ) ] );
					};
				} ( this ) ) );
				this.updateLock();
				if ( this.lockPeriodMax ) {
					this.setMaxTimer();
				}
			}

			AdminPageLocking.prototype.updateLock = function() {
				this.currentTime += this.lockPeriod;
				if ( 0 === this.lockPeriodMax || this.currentTime < this.lockPeriodMax ) {
					$.post( adminPageLockingData.ajaxUrl, {
						action: adminPageLockingData.actionUpdateLock
					}, function( response ) {
						if ( ! response.success ) {
							alert( response.data.message );
							return location.reload();
						}
					} );
					return this.setLockTimer();
				}
			};

			AdminPageLocking.prototype.releaseLock = function() {
				return $.post( adminPageLockingData.ajaxUrl, {
					action: adminPageLockingData.actionReleaseLock
				} );
			};

			AdminPageLocking.prototype.askForMoreTime = function() {
				var backupResponse, promptTimer, respondToConfirm;
				promptTimer = setTimeout( ( function( _this ) {
					return function() {
						alert( adminPageLockingData.errorLockMax );
						return _this.exitScreen();
					};
				} ( this ) ), this.lockPeriod );
				respondToConfirm = ( function( _this ) {
					return function( event, response ) {
						$( document ).off( 'apl-confirm', respondToConfirm );
						if ( 'yes' === response ) {
							_this.currentTime = 0;
							_this.setMaxTimer();
							return _this.setLockTimer();
						} else {
							// Clear timeout as we are already moving to other screen.
							clearTimeout( promptTimer );
							return _this.exitScreen();
						}
					};
				} ( this ) );
				$( document ).on( 'apl-confirm', respondToConfirm );
				backupResponse = this.modalConfirm( adminPageLockingData.moreTime );
				if ( -1 !== backupResponse ) {
					return this.respondToConfirm( null, ( backupResponse ? 'yes' : 'no' ) );
				}
			};

			AdminPageLocking.prototype.setMaxTimer = function() {
				clearTimeout( this.maxTimer );
				return this.maxTimer = setTimeout( ( function( _this ) {
					return function() {
						return _this.askForMoreTime();
					};
				} ( this ) ), this.lockPeriodMax - this.lockPeriod );
			};

			AdminPageLocking.prototype.setLockTimer = function() {
				clearTimeout( this.lockTimer );
				return this.lockTimer = setTimeout( ( function( _this ) {
					return function() {
						return _this.updateLock();
					};
				} ( this ) ), this.lockPeriod );
			};

			AdminPageLocking.prototype.exitScreen = function() {
				return location.href = adminPageLockingData.adminUrl;
			};

			AdminPageLocking.prototype.modalConfirm = function( message ) {
				var tb_overlay,
					tb_close_btn;

				if ( 'undefined' !== typeof tb_show && null !== tb_show ) {
					$( '#apl-message-content' ).text( message );
					tb_show( null, '#TB_inline?inlineId=apl-message&width=300&height=200', false );

					tb_overlay = $( '#TB_overlay' ); // Tickbox overlay instance.
					tb_close_btn = $( '#TB_closeWindowButton' ); // Tickbox close button instance.
					if ( 'undefined' !== typeof tb_overlay ) {
						// Register TB overlay click event to trigger apl-confirm event for Lock-timer extension.
						tb_overlay.on( 'click', function( event ) {
							$( document ).trigger( 'apl-confirm', [ 'yes' ] );
						} );
					}
					if ( 'undefined' !== typeof tb_close_btn ) {
						// Register TB close button click event to trigger apl-confirm event for Lock-timer extension.
						tb_close_btn.on( 'click', function( event ) {
							$( document ).trigger( 'apl-confirm', [ 'yes' ] );
						} );
					}

					return -1;
				} else {
					return confirm( message );
				}
			};

			return AdminPageLocking;

		} () );
		return $( function() {
			return new AdminPageLocking();
		} );
	} ( jQuery ) );

} ).call( this );
