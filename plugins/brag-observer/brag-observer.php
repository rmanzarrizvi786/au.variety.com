<?php

/**
 * Plugin Name: The Brag Observer
 * Plugin URI: https://thebrag.media/
 * Description:
 * Version: 1.0.0
 * Author: Sachin Patel
 * Author URI:
 */

class BragObserver
{

  protected $plugin_name;
  protected $plugin_slug;

  protected $rest_api_key;
  protected $api_url;

  public function __construct()
  {

    $this->plugin_name = 'brag_observer';
    $this->plugin_slug = 'brag-observer';

    $this->is_sandbox = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);

    $this->rest_api_key = '3ce4efdd-a39c-4141-80f7-08a828500831';
    if ($this->is_sandbox) {
      $this->api_url = 'https://the-brag.com/wp-json/brag_observer/v1/';
    } else {
      $this->api_url = 'https://thebrag.com/wp-json/brag_observer/v1/';
    }

    // Shortcodes
    add_shortcode('observer_tastemaker_form', [$this, 'shortcode_observer_tastemaker_form']);
    add_shortcode('observer_subscribe', [$this, 'shortcode_subscribe_form']);
    add_shortcode('observer_lead_generator_form', [$this, 'shortcode_observer_lead_generator_form']);

    // AJAX
    add_action('wp_ajax_save_tastemaker_review', [$this, 'save_tastemaker_review']);
    add_action('wp_ajax_nopriv_save_tastemaker_review', [$this, 'save_tastemaker_review']);

    add_action('wp_ajax_save_lead_generator_response', [$this, 'save_lead_generator_response']);
    add_action('wp_ajax_nopriv_save_lead_generator_response', [$this, 'save_lead_generator_response']);

    // REST API Endpoints
    add_action('rest_api_init', [$this, '_rest_api_init']);

    // AJAX
    add_action('wp_ajax_subscribe_observer', array($this, 'ajax_subscribe_observer'));
    add_action('wp_ajax_nopriv_subscribe_observer', array($this, 'ajax_subscribe_observer'));
  }

  /*
  * Shortcode: Tastemaker
  */
  public function shortcode_observer_tastemaker_form($atts)
  {

    $tastemaker_atts = shortcode_atts(array(
      'id' => NULL,
      'background' => '#e9ecef',
      'border' => '#fff',
      'width' => NULL
    ), $atts);

    if (is_null($tastemaker_atts['id']))
      return;

    $id = absint($tastemaker_atts['id']);

    wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . '/js/scripts.min.js', array('jquery'), '20211014', true);
    $args = array(
      'url'   => admin_url('admin-ajax.php'),
      // 'ajax_nonce' => wp_create_nonce( $this->plugin_slug . '-nonce' ),
    );
    wp_localize_script($this->plugin_name, $this->plugin_name, $args);

    // $api_url = $this->api_url . 'get_tastemaker_form?key=' . $this->rest_api_key . '&id=' . $id;
    $api_url = $this->api_url . 'get_tastemaker_form?key=' . $this->rest_api_key . '&' . http_build_query($tastemaker_atts);

    $response = wp_remote_get($api_url, ['sslverify' => !$this->is_sandbox]);

    $responseBody = wp_remote_retrieve_body($response);
    if ($responseBody) {
      $resonseJson = json_decode($responseBody);
      $form_html = $resonseJson->data;
    } else {
      $form_html = '';
    }

    return $form_html;
  } // shortcode_observer_tastemaker_form()

  /*
  * Shortcode: Lead Generator
  */
  public function shortcode_observer_lead_generator_form($atts)
  {

    $lead_generator_atts = shortcode_atts(array(
      'id' => NULL,
      'background' => '#e9ecef',
      'border' => '#fff',
      'width' => NULL
    ), $atts);

    if (is_null($lead_generator_atts['id']))
      return;

    $id = absint($lead_generator_atts['id']);

    wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . '/js/scripts.min.js', array('jquery'), '20211014', true);
    $args = array(
      'url'   => admin_url('admin-ajax.php'),
      // 'ajax_nonce' => wp_create_nonce( $this->plugin_slug . '-nonce' ),
    );
    wp_localize_script($this->plugin_name, $this->plugin_name, $args);

    $api_url = $this->api_url . 'get_lead_generator_form?key=' . $this->rest_api_key . '&' . http_build_query($lead_generator_atts);

    $response = wp_remote_get($api_url, ['sslverify' => !$this->is_sandbox]);

    $responseBody = wp_remote_retrieve_body($response);
    if ($responseBody) {
      $resonseJson = json_decode($responseBody);
      $form_html = isset($resonseJson->success) && $resonseJson->success ? $resonseJson->data : '';
    } else {
      $form_html = '';
    }

    return $form_html;
  } // shortcode_observer_lead_generator_form()



  /*
  * Shortcode: Subscribe to Genre
  */
  public function shortcode_subscribe_form($atts)
  {

    $params = shortcode_atts(array(
      'id' => NULL,
      'reload' => false,
      'reload_url' => NULL,
    ), $atts);

    if (is_null($params['id']))
      return;

    $post_id = $params['id'];

    $topic_id = 16; // Film & TV ( ID in Brag Observer Lists)

    ob_start();
?>
    <?php
    if ($topic_id) {
      $topics = $this->get_observer_topics($topic_id);
      $topic = isset($topics[0]) ? $topics[0] : null;

      if (is_null($topic)) {
        return;
      }

      $topic->title = trim(str_ireplace('Observer', '', $topic->title));
    ?>
      <div class="observer-sub-form justify-content-center my-3 p-0 d-flex align-items-stretch bg-dark text-white">
        <div class="img-wrap" style="background-image: url(<?php echo $topic->image_url; ?>);">
          <img src="<?php echo $topic->image_url; ?>" width="200" height="200" style="visibility: hidden;">
        </div>
        <div class="p-3 d-flex justify-content-center align-items-center">
          <div>
            <div class="mb-2">
              <h2 class="h5 mb-0">Never miss industry news</h2>
            </div>
            <p class="mb-2">
              Get the latest music industry news, insights, and updates straight to your inbox.
              <a href="<?php echo $topic->link; ?>" class="l-learn-more text-dark" target="_blank" rel="noopener">Learn more</a>
            </p>
            <?php if (!is_user_logged_in()) : ?>
              <button class="button btn btn-primary btn-join" style="color: #fff !important">JOIN</button>
            <?php endif; ?>
            <form action="#" method="post" id="observer-subscribe-form<?php echo $post_id; ?>" name="observer-subscribe-form" class="observer-subscribe-form <?php echo !is_user_logged_in() ? 'd-none bg-white' : ''; ?>" <?php if ($params['reload']) : ?> data-reload="<?php echo $params['reload']; ?>" <?php endif; ?> <?php if (!is_null($params['reload_url'])) : ?> data-reload_url="<?php echo $params['reload_url']; ?>" <?php endif; ?>>
              <div class="d-flex justify-content-start">
                <input type="hidden" name="list" value="<?php echo $topic_id; ?>">
                <?php if (!is_user_logged_in()) : ?>
                  <input type="email" name="email" class="form-control observer-sub-email" placeholder="Your email" value="">
                <?php endif; ?>
                <div class="d-flex submit-wrap rounded">
                  <input type="submit" value="Join" name="subscribe" class="button btn btn-primary rounded" style="color: #fff !important">
                </div>
              </div>
            </form>
            <div class="alert alert-info d-none js-msg-subscribe mt-2"></div>
            <div class="alert alert-danger d-none js-errors-subscribe mt-2"></div>
          </div>
        </div>
      </div>
<?php
    }
    $html = ob_get_contents();
    ob_end_clean();

    return $html;
  } // shortcode_subscribe_form()

  public function ajax_subscribe_observer($formData = [], $return_json = true)
  {

    if (!empty($formData) || (defined('DOING_AJAX') && DOING_AJAX)) :

      if (empty($formData)) {
        if (isset($_POST['formData'])) {
          parse_str($_POST['formData'], $formData);
        } else {
          $formData = $_POST;
        }
      }

      $formData['list'] = 16; // Film & TV ( ID in Brag Observer Lists)

      /* if (is_user_logged_in()) :
        $current_user = wp_get_current_user();
        $formData['email'] = $current_user->user_email;
      endif; */

      $brag_api_url = 'https://thebrag.com/wp-json/brag_observer/v1/sub_unsub/';

      $response = wp_remote_post(
        $brag_api_url,
        [
          'method'      => 'POST',
          'body'        => array(
            'email' => $formData['email'],
            'list' => $formData['list'],
            'source' => isset($formData['source']) ? $formData['source'] : 'VarietyAU',
            'status' => isset($formData['status']) && in_array($formData['status'], ['subscribed', 'unsubscribed']) ? $formData['status'] : 'subscribed',
          ),
          'sslverify' => !in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']),
        ]
      );
      $responseBody = wp_remote_retrieve_body($response);
      $resonseJson = json_decode($responseBody);
      if (isset($resonseJson->success) && $resonseJson->success == 1) {
        if ($return_json) {
          wp_send_json_success($resonseJson->data);
          wp_die();
        } else {
          return true;
        }
      }
      if ($return_json) {
        wp_send_json_error(['error' => ['message' => $resonseJson->data->error->message]]);
        wp_die();
      } else {
        return false;
      }
    endif;
  } // ajax_subscribe_observer() }}

  /*
  * Save Review - Frontend
  */
  public function save_tastemaker_review()
  {

    if (defined('DOING_AJAX') && DOING_AJAX) :

      parse_str($_POST['formData'], $formData);

      // wp_send_json_error( $formData );

      $errors = [];

      $tastemaker_id = isset($formData['id']) ? $formData['id'] : null;
      if (is_null($tastemaker_id)) {
        $errors[] = 'Invalid submission.';
      }

      $formData['rating'] = isset($formData['rating']) ? absint($formData['rating']) : 0;
      if (!isset($formData['rating']) || !in_array($formData['rating'], [1, 2, 3, 4, 5])) {
        $errors[] = 'Please select valid star rating.';
      }

      $formData['email'] = trim($formData['email']);
      if (!isset($formData['email']) || !is_email($formData['email'])) {
        $errors[] = 'Please enter valid email address.';
      }

      if (count($errors) > 0) {
        wp_send_json_error($errors);
      }

      $formData['key'] = $this->rest_api_key;

      $api_url = $this->api_url . 'create_tastemaker_review';
      $response = wp_remote_post(
        $api_url,
        [
          'method' => 'POST',
          'timeout' => 45,
          'body' => $formData,
          'sslverify' => !$this->is_sandbox
        ]
      );
      $responseBody = wp_remote_retrieve_body($response);
      $resonseJson = json_decode($responseBody);
      if ($resonseJson->success) {
        wp_send_json_success($resonseJson->data);
      } else {
        wp_send_json_error($resonseJson->data);
      }
    // return $resonseJson->data;
    endif;
  } // save_tastemaker_review()

  /*
  * Save Lead Generator Response - Frontend
  */
  public function save_lead_generator_response()
  {

    if (defined('DOING_AJAX') && DOING_AJAX) :

      parse_str($_POST['formData'], $formData);

      $errors = [];

      $tastemaker_id = isset($formData['id']) ? $formData['id'] : null;
      if (is_null($tastemaker_id)) {
        $errors[] = 'Invalid submission.';
      }

      $formData['email'] = trim($formData['email']);
      if (!isset($formData['email']) || !is_email($formData['email'])) {
        $errors[] = 'Please enter valid email address.';
      }

      if (count($errors) > 0) {
        wp_send_json_error($errors);
      }

      $formData['key'] = $this->rest_api_key;

      $api_url = $this->api_url . 'create_lead_generator_response';
      $response = wp_remote_post(
        $api_url,
        [
          'method' => 'POST',
          'timeout' => 45,
          'body' => $formData,
          'sslverify' => !$this->is_sandbox
        ]
      );
      $responseBody = wp_remote_retrieve_body($response);
      $resonseJson = json_decode($responseBody);
      if ($resonseJson->success) {
        wp_send_json_success($resonseJson->data);
      } else {
        wp_send_json_error($resonseJson->data);
      }
    endif;
  } // save_lead_generator_response()

  /*
  * REST: Endpoints
  */
  public function _rest_api_init()
  {
    register_rest_route('api/v1', '/observer/articles', array(
      'methods' => 'GET',
      'callback' => [$this, 'get_articles_for_topic'],
      'permission_callback' => '__return_true',
    ));
  }

  /*
  * REST: get articles
  */
  function get_articles_for_topic($data)
  {
    $topic = isset($_GET['topic']) ? $_GET['topic'] : null;

    if (is_null($topic))
      return;

    $topics = array_map('trim', explode(',', $topic));
    $keywords = implode('+', $topics);

    $posts_per_page = isset($_GET['size']) ? (int) $_GET['size'] : 10;

    $timezone = new DateTimeZone('Australia/Sydney');

    if (isset($_GET['after'])) :
      $after_dt = new DateTime(date_i18n('Y-m-d H:i:s', strtotime(trim($_GET['after']))));
      $after_dt->setTimezone($timezone);
      $after = $after_dt->format('Y-m-d H:i:s');
    else :
      $after = NULL;
    endif;

    if (isset($_GET['before'])) :
      $before_dt = new DateTime(date_i18n('Y-m-d H:i:s', strtotime(trim($_GET['before']))));
      $before_dt->setTimezone($timezone);
      $before = $before_dt->format('Y-m-d H:i:s');
    else :
      $before = NULL;
    endif;

    if (is_null($after) || is_null($before))
      return;

    $return = array();

    $args = [
      'date_query' => array(
        'after' => $after,
        'before' => $before,
      ),
      'fields' => 'ids',
      'post_type' => array('post', 'country'),
      'post_status' => 'publish',
      'posts_per_page' => $posts_per_page,
    ];

    $posts_keyword = new WP_Query(
      $args +
        [
          's' => $keywords,
          'exact' => isset($_GET['exact']), // true,
          'sentence' => isset($_GET['sentence']), // true,
        ]
    );

    // Genre + Artist + Category
    $posts_genre = new WP_Query(
      $args +
        [
          'tax_query' => [
            'relation' => 'OR',
            [
              'taxonomy' => 'genre',
              'field' => 'slug',
              'terms' => $topics,
            ],
            [
              'taxonomy' => 'artist',
              'field' => 'slug',
              'terms' => $topics,
            ],
            [
              'taxonomy' => 'category',
              'field' => 'slug',
              'terms' => $topics,
            ],
          ],
        ]
    );

    // Tags
    $posts_tags = new WP_Query(
      $args +
        [
          'tag' => $keywords
        ]
    );

    $combined_ids = array_merge(
      $posts_keyword->posts,
      $posts_genre->posts,
      $posts_tags->posts
    );
    $combined_ids = array_unique($combined_ids);

    if (count($combined_ids) < 1)
      return;

    $posts = new WP_Query(['post__in' => $combined_ids]);

    global $post;
    if ($posts->have_posts()) {
      while ($posts->have_posts()) {
        $posts->the_post();
        $url = get_the_permalink();
        $author = get_field('Author') ? get_field('Author') : get_the_author();

        $src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'medium_large');

        $return[] = array(
          'title' => get_the_title(),
          'link' => $url,
          'publish_date' => mysql2date('c', get_post_time('c', true), false),
          'description' => get_post_meta(get_the_ID(), '_yoast_wpseo_metadesc', true) ?: get_the_excerpt(),
          'image' => $src[0],
        );
      }
    }
    return $return;
  }

  /*
  * Get Observer topics
  */
  public function get_observer_topics($topic = NULL)
  {
    $api_url = $this->api_url . 'get_topics?key=' . $this->rest_api_key;
    if (!is_null($topic)) {
      $api_url .= '&id=' . $topic;
    }
    $api_url .= '&site=theindustryobserver.thebrag.com';
    $response = wp_remote_get($api_url, ['sslverify' => !$this->is_sandbox]);

    $responseBody = wp_remote_retrieve_body($response);
    if ($responseBody) {
      $resonseJson = json_decode($responseBody);
      $topics = isset($resonseJson->success) && $resonseJson->success ? $resonseJson->data : [];
    } else {
      $topics = '';
    }
    return $topics;
  }

  /*
  * Call Remote API
  */
  private static function callAPI($method, $url, $data = '', $content_type = '')
  {
    $curl = curl_init();
    switch ($method) {
      case "POST":
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data)
          curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        break;
      case "PUT":
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        if ($data)
          curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        break;
      default:
        if ($data)
          $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    // OPTIONS:
    curl_setopt($curl, CURLOPT_URL, $url);
    if ($content_type !== false) {
      curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
      ));
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    if (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    // EXECUTE:
    $result = curl_exec($curl);

    // error_log( $url );
    // if ( 'POST' == $method ) {
    // echo '<pre>'; var_dump( curl_error( $curl ) ); echo '</pre>';
    // }
    if (!$result)
      return;
    curl_close($curl);
    return $result;
  }
}

new BragObserver();
