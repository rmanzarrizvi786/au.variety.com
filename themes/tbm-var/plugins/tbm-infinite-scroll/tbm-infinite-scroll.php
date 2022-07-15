<?php

/**
 * Plugin Name: TBM Ads Manager
 * Plugin URI: https://thebrag.media/
 * Description:
 * Version: 1.0.0
 * Author: Sachin Patel
 * Author URI:
 */

namespace TBM;

class InfiniteScroll
{

  protected $plugin_title;
  protected $plugin_name;
  protected $plugin_slug;

  protected static $_instance;

  public function __construct()
  {

    $this->plugin_title = 'TBM Infinite Scroll';
    $this->plugin_name = 'infinite_scroll';
    $this->plugin_slug = 'infinite-scroll';

    add_action('wp_enqueue_scripts', [$this, 'action_wp_enqueue_scripts']);

    add_action('wp_ajax_tbm_ajax_load_next_post', [$this, 'tbm_ajax_load_next_post']);
    add_action('wp_ajax_nopriv_tbm_ajax_load_next_post', [$this, 'tbm_ajax_load_next_post']);
  }

  /*
  * Enqueue JS
  */
  public function action_wp_enqueue_scripts()
  {
    if (!is_singular('post'))
      return;

    wp_enqueue_script('tbm-infinite-scroll', CDN_URL . 'js/infinite-scroll.js', ['jquery'], '1');

    $admin_ajax_url = admin_url('admin-ajax.php');
    global $post;
    $args = array(
      'url'   => $admin_ajax_url,
      'exclude_posts' => isset($post) ? $post->ID : NULL,
      'current_post' => isset($post) ? $post->ID : NULL
    );
    wp_localize_script('tbm-infinite-scroll', 'tbm_infinite_scroll', $args);
  }

  /*
	 * Get Next Post AJAX
	 */
  public function tbm_ajax_load_next_post()
  {
    global $post;

    $exclude_posts = (!is_null($_POST['exclude_posts']) && $_POST['exclude_posts'] != '') ? $_POST['exclude_posts'] : '';
    $exclude_posts_array = explode(',', $exclude_posts);

    $tbm_featured_infinite_IDs = trim(get_option('tbm_featured_infinite_ID'));
    if ($tbm_featured_infinite_IDs) :
      $tbm_featured_infinite_IDs = array_map('trim', explode(',', $tbm_featured_infinite_IDs));
      $tbm_featured_infinite_IDs = array_map('absint', $tbm_featured_infinite_IDs);
      $tbm_featured_infinite_ID = $tbm_featured_infinite_IDs[array_rand($tbm_featured_infinite_IDs)];
    endif;

    if (isset($tbm_featured_infinite_ID) && $_POST['id'] != $tbm_featured_infinite_ID && !in_array($tbm_featured_infinite_ID, $exclude_posts_array)) :
      $prevPost = get_post($tbm_featured_infinite_ID);
    else :
      $post = get_post($_POST['id']);
      $prevPost = get_previous_post();
    endif;

    if ($prevPost) :
      if (in_array($prevPost->ID, $exclude_posts_array)) {
        $data['content'] = '';
        $data['loaded_post'] = $prevPost->ID;
        wp_send_json_success($data);
        wp_die();
      }
      $post = $prevPost;
      $data['exclude_post'] = $prevPost->ID;
      ob_start();
      $main_post = false;

      global $wp_query;
      $wp_query = new \WP_Query(
        ['p' => $prevPost->ID]
      );

      // include(get_template_directory() . '/template-parts/article/single.php');
      \PMC::render_template(
        sprintf('%s/template-parts/article/single.php', untrailingslashit(CHILD_THEME_PATH)),
        [],
        true
      );

      // wp_reset_query();
      // wp_reset_postdata();
      $data['content'] =  ob_get_clean();
      $data['loaded_post'] = $prevPost->ID;
      $data['page_title'] = html_entity_decode(get_the_title($prevPost));
      $author = get_the_author_meta('first_name', $post->post_author) . ' ' . get_the_author_meta('last_name', $post->post_author);
      $data['author'] = $author;

      $categories = get_the_category($prevPost->ID);
      if ($categories) {
        foreach ($categories as $category_obj) :
          $category = $category_obj->slug;
          break;
        endforeach;
        $data['category'] = $category;
      }

      $pagepath = parse_url(get_the_permalink($prevPost->ID), PHP_URL_PATH);
      $pagepath = substr(str_replace('/', '', $pagepath), 0, 40);
      $data['pagepath'] = $pagepath;

      wp_send_json_success($data);
    endif;
    wp_die();
  }


  /*
  * Singleton
  */
  public static function get_instance()
  {
    if (!isset(static::$_instance)) {
      static::$_instance = new InfiniteScroll();
    }
    return static::$_instance;
  }
}

InfiniteScroll::get_instance();
