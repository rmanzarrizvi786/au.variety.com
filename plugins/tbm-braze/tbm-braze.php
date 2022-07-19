<?php

/**
 * Plugin Name: TBM Braze
 * Plugin URI: https://thebrag.media/
 * Description:
 * Version: 1.0.0
 * Author: Sachin Patel
 * Author URI:
 */

namespace TBM;

class Braze
{

  protected $plugin_title;
  protected $plugin_name;
  protected $plugin_slug;
  protected $is_sandbox;
  protected $api_key;

  public function __construct()
  {
    $this->plugin_title = 'TBM Braze';
    $this->plugin_name = 'tbm_braze';
    $this->plugin_slug = 'tbm-braze';
    $this->is_sandbox = (isset($_ENV) && isset($_ENV['ENVIRONMENT']) && 'sandbox' == $_ENV['ENVIRONMENT']) || str_contains($_SERVER['SERVER_NAME'], 'staging.');
    $this->api_key = $this->is_sandbox ? 'bba50f73-2e4d-4f8e-97d9-89d8206568bb' : '5fd1c924-ded7-46e7-b75d-1dc4831ecd92';

    // add_action('wp_head', [$this, 'wp_head']);
    add_action('wp_footer', [$this, 'wp_footer']);

    add_action('wp_ajax_get_user_external_id', [$this, 'get_user_external_id']);
    add_action('wp_ajax_nopriv_get_user_external_id', [$this, 'get_user_external_id']);
  }

  public function get_user_external_id()
  {
    global $wpdb;
    if (is_user_logged_in()) {
      $user_id = get_current_user_id();
      if (!$user_id)
        return;
      $auth0_user_id = get_user_meta($user_id, $wpdb->prefix . 'auth0_id', true);
      wp_send_json_success($auth0_user_id);
      wp_die();
    } // If user is logged in
    wp_send_json_error('Not logged in');
    wp_die();
  }

  public function wp_footer()
  {
?>
    <script>
      var user_external_id = null;

      window.callBraze = () => {
        + function(a, p, P, b, y) {
          a.braze = {};
          a.brazeQueue = [];
          for (var s = "BrazeSdkMetadata DeviceProperties Card Card.prototype.dismissCard Card.prototype.removeAllSubscriptions Card.prototype.removeSubscription Card.prototype.subscribeToClickedEvent Card.prototype.subscribeToDismissedEvent Card.fromContentCardsJson Banner CaptionedImage ClassicCard ControlCard ContentCards ContentCards.prototype.getUnviewedCardCount Feed Feed.prototype.getUnreadCardCount ControlMessage InAppMessage InAppMessage.SlideFrom InAppMessage.ClickAction InAppMessage.DismissType InAppMessage.OpenTarget InAppMessage.ImageStyle InAppMessage.Orientation InAppMessage.TextAlignment InAppMessage.CropType InAppMessage.prototype.closeMessage InAppMessage.prototype.removeAllSubscriptions InAppMessage.prototype.removeSubscription InAppMessage.prototype.subscribeToClickedEvent InAppMessage.prototype.subscribeToDismissedEvent InAppMessage.fromJson FullScreenMessage ModalMessage HtmlMessage SlideUpMessage User User.Genders User.NotificationSubscriptionTypes User.prototype.addAlias User.prototype.addToCustomAttributeArray User.prototype.addToSubscriptionGroup User.prototype.getUserId User.prototype.incrementCustomUserAttribute User.prototype.removeFromCustomAttributeArray User.prototype.removeFromSubscriptionGroup User.prototype.setCountry User.prototype.setCustomLocationAttribute User.prototype.setCustomUserAttribute User.prototype.setDateOfBirth User.prototype.setEmail User.prototype.setEmailNotificationSubscriptionType User.prototype.setFirstName User.prototype.setGender User.prototype.setHomeCity User.prototype.setLanguage User.prototype.setLastKnownLocation User.prototype.setLastName User.prototype.setPhoneNumber User.prototype.setPushNotificationSubscriptionType InAppMessageButton InAppMessageButton.prototype.removeAllSubscriptions InAppMessageButton.prototype.removeSubscription InAppMessageButton.prototype.subscribeToClickedEvent automaticallyShowInAppMessages destroyFeed hideContentCards showContentCards showFeed showInAppMessage toggleContentCards toggleFeed changeUser destroy getDeviceId initialize isPushBlocked isPushPermissionGranted isPushSupported logCardClick logCardDismissal logCardImpressions logContentCardsDisplayed logCustomEvent logFeedDisplayed logInAppMessageButtonClick logInAppMessageClick logInAppMessageHtmlClick logInAppMessageImpression logPurchase openSession requestPushPermission removeAllSubscriptions removeSubscription requestContentCardsRefresh requestFeedRefresh requestImmediateDataFlush enableSDK isDisabled setLogger setSdkAuthenticationSignature addSdkMetadata disableSDK subscribeToContentCardsUpdates subscribeToFeedUpdates subscribeToInAppMessage subscribeToSdkAuthenticationFailures toggleLogging unregisterPush wipeData handleBrazeAction".split(" "), i = 0; i < s.length; i++) {
            for (var m = s[i], k = a.braze, l = m.split("."), j = 0; j < l.length - 1; j++) k = k[l[j]];
            k[l[j]] = (new Function("return function " + m.replace(/\./g, "_") + "(){window.brazeQueue.push(arguments); return true}"))()
          }
          window.braze.getCachedContentCards = function() {
            return new window.braze.ContentCards
          };
          window.braze.getCachedFeed = function() {
            return new window.braze.Feed
          };
          window.braze.getUser = function() {
            return new window.braze.User
          };
          (y = p.createElement(P)).type = 'text/javascript';
          y.src = 'https://js.appboycdn.com/web-sdk/4.0/braze.min.js';
          y.async = 1;
          (b = p.getElementsByTagName(P)[0]).parentNode.insertBefore(y, b)
        }(window, document, 'script');

        // initialize the SDK
        braze.initialize('<?php echo $this->api_key; ?>', {
          baseUrl: "sdk.iad-05.braze.com",
          <?php echo $this->is_sandbox ? 'enableLogging: true' : ''; ?>
        });

        var message = new braze.SlideUpMessage("Welcome to Braze! This is an in-app message.");
        message.slideFrom = braze.InAppMessage.SlideFrom.TOP;
        braze.showInAppMessage(message);

        jQuery.get('<?php echo admin_url('admin-ajax.php'); ?>', {
          action: 'get_user_external_id',
        }, function(res) {
          if (res.success && res.data) {
            user_external_id = res.data;
            braze.changeUser(user_external_id);
          }
        });


        braze.logCustomEvent("prime-for-push");

        window.braze.subscribeToInAppMessage(function(inAppMessage) {
          // console.log(inAppMessage);
          var shouldDisplay = true;

          if (inAppMessage instanceof window.braze.InAppMessage) {
            // Read the key-value pair for msg-id
            var msgId = inAppMessage.extras["msg-id"];

            // If this is our push primer message
            if (msgId == "push-primer") {
              // We don't want to display the soft push prompt to users on browsers that don't support push, or if the user
              // has already granted/blocked permission
              if (
                !window.braze.isPushSupported() ||
                window.braze.isPushPermissionGranted() ||
                window.braze.isPushBlocked()
              ) {
                shouldDisplay = false;
              }
              if (inAppMessage.buttons[1] != null) {
                // Prompt the user when the second button is clicked
                inAppMessage.buttons[1].subscribeToClickedEvent(function() {
                  window.braze.requestPushPermission();
                });
              }
            }
          }

          // Display the message
          if (shouldDisplay) {
            window.braze.showInAppMessage(inAppMessage);
          }
        });

      }
    </script>
<?php
  } // wp_footer();
}

new Braze();
