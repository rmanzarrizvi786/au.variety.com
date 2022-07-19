<?php

/**
 * Plugin Name: TBM Push Notifications
 * Plugin URI: https://thebrag.media/
 * Description:
 * Version: 1.0.0
 * Author: Sachin Patel
 * Author URI:
 */

namespace TBM;

class PushNotifications
{

  protected $plugin_title;
  protected $plugin_name;
  protected $plugin_slug;

  public function __construct()
  {
    $this->plugin_title = 'TBM Push Notifications';
    $this->plugin_name = 'tbm_push_notifications';
    $this->plugin_slug = 'tbm-push-notifications';

    // add_action('wp_footer', [$this, 'wp_footer']);

    add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    add_filter("script_loader_tag", [$this, 'add_module_to_script'], 10, 3);
  }

  public function enqueue_assets()
  {
    $url = WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__));
    wp_register_script('tbm-push', $url . 'scripts.js', [], time(), true);
    wp_enqueue_script('tbm-push');
  }

  public function add_module_to_script($tag, $handle, $src)
  {
    if ("tbm-push" === $handle) {
      $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
    }

    return $tag;
  }

  public function wp_footer()
  {
?>
    <!-- <div class="overlay-push-permission"></div>
    <div class="prompt-push-permission-wrap">
      <div class="prompt-push-permission">
        <div class="d-flex align-items-start">
          <div class="icon"><img alt="Variety Australia" src="/wp-content/themes/tbm-var/assets/app/icons/favicon.png"></div>
          <div class="slidedown-body-message">We'd like to show you notifications for the latest news and updates.</div>
          <div class="clearfix"></div>
          <div id="onesignal-loading-container"></div>
        </div>
        <div class="btns-wrap">
          <button class="btn-later">Maybe later</button>
          <button class="btn-allow">Allow</button>
        </div>
      </div>
    </div> -->

    <script src="https://www.gstatic.com/firebasejs/7.18.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.18.0/firebase-messaging.js"></script>

    <script type="module">

    </script>
<?php
  } // wp_footer();
}

new PushNotifications();
