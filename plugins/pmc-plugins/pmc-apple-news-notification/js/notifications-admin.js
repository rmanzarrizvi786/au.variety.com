var smg = smg || {};

smg[ 'plugins' ] = smg.plugins || {};

( function( $, plugins ) {

  var self;

  plugins.appleNews = {
    init: function() {
      self = this;

      self.$thickbox = $( '#notification-thickbox' );
      self.postId = '';
      self.$errorMsg = self.$thickbox.find( '.error' );
      self.$successMsg = self.$thickbox.find( '.success' );
      self.$explanationMsg = self.$thickbox.find( '.explanation' );
      self.$submitButton = self.$thickbox.find( '#notification-submit' );
      self.$textArea = self.$thickbox.find( '#notification-body' );
      self.$loadingImg = self.$thickbox.find( '#loading-img' );

      self.setupListeners();
    },

    setupListeners: function() {
      $( '.notification-thickbox-link' ).click( self.handleThickbox );
      self.$submitButton.click( self.sendAJAX );
    },

    handleThickbox: function( e ) {
      var $this = $( this );
      e.preventDefault();
      e.stopPropagation();

      self.resetStatuses();

      self.$textArea.val( $this.attr( 'data-anf-post-title' ) );
      self.postId = $this.attr( 'data-post-id' );
      tb_show( 'Send a notification for \"' + $this.attr( 'data-anf-post-title' ) + '\"', '#TB_inline?width=600&height=300&inlineId=notification-thickbox' );
      $( document ).trigger( 'resize-window' );
    },

    //thickbox seems to do some weird auto-magic when determining window size
    //so just when the apple news windows spawn, I will override the inline-css
    //appended to the window
    handleWindowSize: function( e ) {
      var $window = $( '#TB_window' );
      $window.css( { height: 'auto', width: 'auto' } );
    },
    handleWindowResize: function( e ) {
      var $window = $( '#TB_window' );
      if ( $window ) {
        $( document ).trigger( 'resize-window' );
      }
    },
    sendAJAX: function( e ) {
      e.preventDefault();
      e.stopPropagation();

      self.resetStatuses();

      var data = {
        action: 'send_notification_request',
        post_id: self.postId,
        body: $( '.notification-input' ).serializeArray()[ 0 ].value.trim(),
        nonce: anfVars.nonce,
      };

      if ( ! data.body ) {
        self.showError( 'NO_BODY' );
      } else {
        self.$submitButton.prop( 'disabled', true );
        self.$submitButton.val( 'Sending...' );
        self.$loadingImg.show();

        $.ajax( ajaxurl, {
          type: 'POST',
          data: data,
          success: function( r ) {
            if ( r.success ) {
              var left = r.data.limit - r.data.sent;
              self.showSuccess( 'The notification was sent successfully!' );
              self.showExplanation( 'You can send ' + left + ' more today.' );
            } else {
              self.showError( r.data.error );
            }
          },
          complete: function() {
            self.$submitButton.val( 'Send the notification' );
            self.$submitButton.prop( 'disabled', false );
            self.$loadingImg.hide();
          },
        } );
      }
    },

    resetStatuses: function() {
      self.$errorMsg.hide().text( '' );
      self.$successMsg.hide().text( '' );
      self.$explanationMsg.hide().text( '' );
    },

    showError: function( msg ) {
      self.$errorMsg.show();
      var explanation = '';

      if ( msg === 'NO_BODY' ) {
        msg = 'You must include a headline with a notification. The headline cannot be blank.';
      } else {
        switch ( msg ) {
          case 'UNAUTHORIZED':
            explanation = 'Authorization failed. This usually means your ID and secret isn\'t correct. Check those, and try again.';
            break;
          case 'ALREADY_EXISTS':
            explanation = 'You have sent this notification already within the past 5 minutes. You can send it again once that time is up.';
            break;
          case 'NOTIFICATION_NOT_ALLOWED':
            explanation = 'Sorry, but your Apple News channel is not allowed to send notifications.';
            break;
          case 'NOT_FOUND':
            explanation = 'The article you\'re trying to send a notification for doesn\'t exist on Apple News. If you just posted this article, wait a few minutes and try again.';
            break;
          case 'NOTIFICATION_QUOTA_REACHED':
            explanation = 'Your Apple News channel has reached the daily notification quota, and you cannot send any more for today.';
            break;
          case 'NONCE':
            explanation = 'The browser session has expired. Please refresh this page and try again.';
            break;
          default:
            explanation = 'An unspecified error has occurred.';
        }

        msg = 'Apple rejected the notification with this error: ' + msg;
      }

      self.$errorMsg.text( msg );
      self.showExplanation( explanation );
    },

    showSuccess: function( msg ) {
      self.$successMsg.show().text( msg );

    },

    showExplanation: function( msg ) {
      self.$explanationMsg.show().text( msg );
    },
  };

  $( document ).ready( function() {
    plugins.appleNews.init();
  } );
  $( document ).on( 'resize-window', function() {
    plugins.appleNews.handleWindowSize();
  } );
  $( window ).bind( 'resize', function() {
    plugins.appleNews.handleWindowResize();
  } );

} )( jQuery, smg.plugins );
