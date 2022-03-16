<?php

/**
 * Plugin Name: Download Variety.com Article
 * Plugin URI: https://thebrag.media
 * Description:
 * Version: 1.0.0
 * Author: Sachin Patel
 * Author URI:
 */

namespace TBM;

class DownloadVarietyComArticle
{

  protected $plugin_name;
  protected $plugin_slug;
  protected $rs_feed;

  public function __construct()
  {
    $this->plugin_name = 'tbm_download_variety_com_article';
    $this->plugin_slug = 'tbm-download-variety-com-article';

    $this->rs_feed = 'https://variety.com/custom-feed/australia/?v=' . time();

    add_action('admin_menu', [$this, 'admin_menu']);

    add_action('wp_ajax_start_download_article_feed', [$this, 'start_download_article_feed']);

    add_action('apple_news_skip_push', [$this, 'apple_news_skip_push'], 10, 2);
  }

  public function admin_menu()
  {
    $main_menu = add_menu_page(
      'Download from Variety.com',
      'Download from Variety.com',
      'edit_posts',
      $this->plugin_slug,
      [$this, 'index_article_feed'],
      'dashicons-download',
      10
    );
    add_action('load-' . $main_menu, [$this, 'load_admin_js']);
  }

  function load_admin_js()
  {
    add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
  }

  public function enqueue_admin_scripts($hook)
  {
    wp_enqueue_script($this->plugin_slug . '-admin-script', CHILD_THEME_URL . '/plugins/tbm-download-pmc-article/js/admin.js', ['jquery'], time(), true);
    wp_localize_script(
      $this->plugin_slug . '-admin-script',
      $this->plugin_name,
      [
        'url'   => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce($this->plugin_name . '_nonce'),
      ]
    );
  }

  function get_inner_html($node, $strip_tags = false)
  {
    $innerHTML = '';
    $children = $node->childNodes;
    if ($children->length > 0) {
      foreach ($children as $child) {
        $innerHTML .= $child->ownerDocument->saveXML($child);
      }
    }
    if ($strip_tags) {
      return trim(strip_tags($innerHTML));
    }
    return $innerHTML;
  }

  public function apple_news_skip_push($bool, $post_id)
  {
    global $wpdb;
    $check_post = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}pmc_imports WHERE post_id = {$post_id} LIMIT 1");
    if ($check_post) {
      return true;
    }
  }

  /*
  * Article (Feed)
  */
  public function index_article_feed()
  {
?>
    <h1>Download Article from Variety.com</h1>

    <input type="url" name="article_url" id="article_url" style="width: 90%; display: block; margin: 1rem 0;" value="" placeholder="https://">
    <button id="start-download-article-feed" class="button button-primary">Download</button>

    <div id="migration-results" style="padding: 10px;"></div>
<?php
  }

  public function start_download_article_feed()
  {
    global $wpdb;
    if (check_ajax_referer($this->plugin_name . '_nonce', 'nonce')) :

      $article_url = isset($_POST['article_url']) ? $_POST['article_url'] : NULL;

      if (is_null($article_url)) :
        wp_send_json_error(['result' => 'Article URL is missing']);
        die();
      endif;

      ini_set('max_execution_time', 600); // 600 seconds = 10 minutes

      include_once(ABSPATH . WPINC . '/feed.php');
      $rss = fetch_feed($this->rs_feed);

      if (is_wp_error($rss)) {
        wp_send_json_error(['result' => '<div style="width: 100%; max-width: 100%:">Error fetching feed!</div>']);
        die();
      }

      $rss_items = $rss->get_items(0);

      if ($rss_items) {
        $articles = [];
        foreach ($rss_items as $item) {
          $item_categories = $item->get_categories();
          $categories = $tags = [];
          if ($item_categories) {
            foreach ($item_categories as $item_category) {
              if (category_exists($item_category->term)) {
                $categories[] = $item_category->term;
              } else {
                $tags[] = $item_category->term;
              }
            }
          }

          $content = $item->get_content();
          $doc = new \DOMDocument();
          @$doc->loadHTML($content);
          $imgs = $doc->getElementsbyTagName('img');

          // wp_send_json_error(['result' => '<pre>' . print_r($imgs, true)]);
          // die();

          if ($imgs && isset($imgs->item)) {
            $image = $imgs->item(0)->getAttribute('src');
          } else {
            $image = '';
          }

          $content = preg_replace("/<img[^>]+\>/i", "", $content, 1);

          $articles[esc_url($item->get_permalink())] = [
            'title' => esc_html($item->get_title()),
            'categories' => $categories,
            'tags' => $tags,
            'author' => $item->get_author()->name,
            'image' => $image,
            'content' => $content,
            'url' => esc_url($item->get_permalink()),
          ];
        }

        // wp_send_json_success(['result' => '<pre>' . print_r(array_keys($articles), true) . '</pre>']);
        // die();

        if (in_array($article_url, array_keys($articles))) {
          $the_article = $articles[$article_url];
          return $this->createArticle($the_article);
        }
      }

      wp_send_json_error(['result' => 'Empty feed!']);
      die();
    endif; // If nonce validated
  }

  /*
  function getImgSrc( $node ) {
    $children = $node->childNodes;
    if ( $children->length > 0 ) {
      foreach ($children as $child) {
        $children2 = $child->childNodes;
        if ( $children2->length > 0 ) {
          foreach ($children2 as $child2) {
            if ( 'img' == $child2->tagName ) {
              return $child2->getAttribute( 'data-src' );
            }
          }
        }
      }
    }
  }
  */

  private function createArticle($article = [])
  {
    global $wpdb;

    if (empty($article) || !isset($article['title']) || '' == trim($article['title']) || !isset($article['url']) || '' == trim($article['url'])) {
      wp_send_json_error(['result' => 'Missing details.']);
      die();
    }

    if (!empty($article['categories'])) {
      foreach ($article['categories'] as $cat_name) {
        $cat_IDs[] = get_cat_ID($cat_name);
      }
    }

    $content = isset($article['content']) ?  $article['content'] . '<p><em>From <a href="' . $article['url'] . '" target="_blank">Variety US</a></em></p>' : '';

    $parsed_url = parse_url($article['url']);
    $post_name_e = explode('/', $parsed_url['path']);
    $post_name_e = array_map('trim', $post_name_e);
    $post_name_e = array_filter($post_name_e);
    $post_name = preg_replace('|((.*))-(\d+)$|', '$1', end($post_name_e));

    $html = file_get_contents($article['url']);
    $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);

    $doc = new \DOMDocument();
    @$doc->loadHTML($html);
    $doc->preserveWhiteSpace = false;

    $meta_og_description = '';
    foreach ($doc->getElementsByTagName('meta') as $meta) {
      if ($meta->getAttribute('property') == 'og:description') {
        $meta_og_description = $meta->getAttribute('content');
      }
      if (!isset($article['image']) || '' == $article['image']) {
        if ($meta->getAttribute('property') == 'og:image') {
          $article['image'] = $meta->getAttribute('content');
        }
      }
    }


    $metas = [
      '_yoast_wpseo_canonical' => $article['url'],
      '_yoast_wpseo_title' => $article['title'],
      '_yoast_wpseo_metadesc' => $meta_og_description,
      'apple_news_is_preview' => '1',
      'apple_news_is_hidden' => '1',
    ];

    if (isset($article['author']) && '' != trim($article['author'])) {
      $metas['author']  = $article['author'];
    }

    /* if (isset($article['image']) && '' != $article['image']) {
      $metas['thumbnail_ext_url'] = $article['image'];
    } */

    $new_article_args = [
      'post_name' => $post_name,
      'post_content' => $content,
      'post_title' => $article['title'],
      'post_excerpt' => $meta_og_description,
      'post_status' => 'draft',
      'post_type' => 'post',
      'post_category' => $cat_IDs,
      'tags_input' => isset($article['tags']) ? $article['tags'] : [],
      'meta_input' => $metas,
    ];

    $new_article_id = wp_insert_post($new_article_args);

    if (!isset($new_article_id) || is_wp_error($new_article_id)) {
      wp_send_json_error(['result' => 'Error creating the article']);
      die();
    }

    $wpdb->insert(
      $wpdb->prefix . 'pmc_imports',
      [
        'post_id' => $new_article_id,
        'article_url' => $article['url'],
        'article_type' => 'post',
      ]
    );

    if (isset($article['image']) && '' != $article['image']) {
      // required libraries for media_sideload_image
      require_once(ABSPATH . 'wp-admin/includes/file.php');
      require_once(ABSPATH . 'wp-admin/includes/media.php');
      require_once(ABSPATH . 'wp-admin/includes/image.php');

      // load the image
      $result = media_sideload_image($article['image'], $new_article_id);

      // then find the last image added to the post attachments
      $attachments = get_posts(['numberposts' => '1', 'post_parent' => $new_article_id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC']);

      if (sizeof($attachments) > 0) {
        // set image as the post thumbnail
        set_post_thumbnail($new_article_id, $attachments[0]->ID);
      }
    }

    wp_send_json_success(['result' => '<p style="color: green; font-weight: bold;">Download FINISHED.</p> <a href="' . home_url('/?p=' . $new_article_id) . '" target="_blank" class="button">View Article</a> <a href="' . get_edit_post_link($new_article_id) . '" target="_blank" class="button">Edit Article</a>']);
    die();
  }
}
new DownloadVarietyComArticle();
