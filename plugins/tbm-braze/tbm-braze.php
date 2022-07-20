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
  protected $sdk_api_key;
  protected $safariWebsitePushId;
  protected $api_url;
  protected $verticalToCanvasId;

  public function __construct()
  {
    $this->plugin_title = 'TBM Braze';
    $this->plugin_name = 'tbm_braze';
    $this->plugin_slug = 'tbm-braze';
    $this->is_sandbox = (isset($_ENV) && isset($_ENV['ENVIRONMENT']) && 'sandbox' == $_ENV['ENVIRONMENT']) || str_contains($_SERVER['SERVER_NAME'], 'staging.') || str_contains($_SERVER['SERVER_NAME'], 'variety.thebrag.com');

    $this->api_key = $this->is_sandbox ? '4bdcad2b-f354-48b5-a305-7a9d77eb356e' : '3570732f-b2bd-4687-9b19-e2cb32f226ae';

    // $this->sdk_api_key = $this->is_sandbox ? 'bba50f73-2e4d-4f8e-97d9-89d8206568bb' : '5fd1c924-ded7-46e7-b75d-1dc4831ecd92'; // The Brag Dev
    // $this->sdk_api_key = $this->is_sandbox ? 'd11a951b-92e7-4bcf-9a17-219c70251fc0' : '5fd1c924-ded7-46e7-b75d-1dc4831ecd92'; // Development Website
    $this->sdk_api_key = $this->is_sandbox ? '83e2253e-1275-4e71-bcd2-e587a3452b71' : 'e62bb798-8639-4eaa-8de3-3da1b59e6b9d'; // Variety Australia

    $this->api_url = 'https://rest.iad-05.braze.com';

    $this->safariWebsitePushId = 'web.com.variety.au';

    $this->verticalToCanvasId = [
      'film' => 'c0172ba9-9753-84ac-8b7c-37b8dca0d581',
      'tv' => 'cf3feafa-fd4d-4ae4-a1d2-9290bcf59282',
      'music' => '94a07ad5-12ac-4030-a934-3ec8ab93782b',
      'radio' => '60a43c40-c389-4d66-b715-8cd9fa62a6bd',
      'awards' => '0d97548e-7e7b-439e-a0f7-04af5eeffc60',
      'tech' => '445825cf-b657-47b5-8b0c-95ef9972d682',
    ];

    // add_action('wp_head', [$this, 'wp_head']);
    add_action('wp_footer', [$this, 'wp_footer']);

    add_action('wp_ajax_get_user_external_id', [$this, 'get_user_external_id']);
    add_action('wp_ajax_nopriv_get_user_external_id', [$this, 'get_user_external_id']);

    add_action('publish_post', [$this, 'publish_post'], 10, 3);
  }

  function publish_post($post_id, $job, $old_status)
  {
    // Stop if post's old status is published
    if ('publish' == $old_status)
      return;

    // Stop if the mappign is not set or is empty
    if (!$this->verticalToCanvasId || !is_array($this->verticalToCanvasId) && empty($this->verticalToCanvasId))
      return;

    // Get verticals for the post
    $verticals = get_the_terms($post_id, 'vertical');

    // Stop if vertical is not set
    if (!$verticals)
      return;
    $verticals = wp_list_pluck($verticals, 'slug');

    // Stop if vertical is not an arry or is empty
    if (!is_array($verticals) || empty($verticals))
      return;

    // Get verticals for which canvases are mapped
    $availableVerticals = array_keys($this->verticalToCanvasId);
    foreach ($verticals as $vertical) {
      // Stop if vertical is not in canvas mapping array
      if (!in_array($vertical, $availableVerticals))
        continue;

      // Get ready with body to send with post request
      $body = [
        'canvas_id' => $this->verticalToCanvasId[$vertical],
        'broadcast' => true,
        'canvas_entry_properties' => [
          'title' => get_the_title($post_id),
          'message' => get_the_excerpt($post_id),
          'url' => get_the_permalink($post_id),
          'image_url' => get_the_post_thumbnail_url($post_id)
        ]
      ];
      $body = wp_json_encode($body);

      // Send post request to braze to trigger canvas send
      wp_remote_post(
        $this->api_url . '/canvas/trigger/send',
        [
          'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->api_key,
          ],
          'body' => $body,
        ]
      );
    }
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
        braze.initialize('<?php echo $this->sdk_api_key; ?>', {
          baseUrl: "sdk.iad-05.braze.com",
          inAppMessageZIndex: 12000,
          allowUserSuppliedJavascript: true,
          safariWebsitePushId: '<?php echo $this->safariWebsitePushId; ?>',
          <?php echo $this->is_sandbox ? 'minimumIntervalBetweenTriggerActionsInSeconds: 2,' : ''; ?>
          <?php echo $this->is_sandbox ? 'enableLogging: true,' : ''; ?>
        });

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
          var shouldDisplay = true;

          if (inAppMessage instanceof window.braze.InAppMessage) {
            // Read the key-value pair for msg-id
            var msgId = inAppMessage.extras["msg-id"];

            // If this is our push primer message
            if (msgId == "push-primer-var") {
              // We don't want to display the soft push prompt to users on browsers that don't support push, or if the user
              // has already granted/blocked permission
              if (
                !window.braze.isPushSupported() ||
                window.braze.isPushPermissionGranted() ||
                window.braze.isPushBlocked()
              ) {
                shouldDisplay = false;
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
