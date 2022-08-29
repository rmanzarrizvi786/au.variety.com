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

class DownloadVarietyContent
{

  protected $plugin_name;
  protected $plugin_slug;
  protected $rs_feed;
  protected $author_id;

  public function __construct()
  {
    $this->plugin_name = 'tbm_download_variety_com_content';
    $this->plugin_slug = 'tbm-download-variety-com-content';

    $this->rs_feed = ''; // 'https://variety.com/custom-feed/australia/?v=' . time();

    $this->author_id = 16;

    // Admin menu
    add_action('admin_menu', [$this, 'admin_menu']);

    // AJAX
    add_action('wp_ajax_tbm_start_download_article_feed', [$this, 'start_download_article_feed']);
    add_action('wp_ajax_tbm_start_download_list', array($this, 'start_download_list'));
    add_action('wp_ajax_tbm_continue_download_list', array($this, 'continue_download_list'));

    // Apple News, skip because of canonical
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
      35
    );

    $submenu_article = add_submenu_page(
      $this->plugin_slug,
      'Download Variety Article',
      'Article',
      'edit_posts',
      $this->plugin_slug,
      [$this, 'index_article_feed']
    );

    $submenu_list = add_submenu_page(
      $this->plugin_slug,
      'Download Variety List',
      'List',
      'edit_posts',
      $this->plugin_slug . '-list',
      [$this, 'index_list']
    );

    add_action('load-' . $main_menu, [$this, 'load_admin_js']);
    add_action('load-' . $submenu_list, array($this, 'load_admin_js'));
  }

  function load_admin_js()
  {
    add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
  }

  public function enqueue_admin_scripts($hook)
  {
    wp_enqueue_script($this->plugin_slug . '-admin-script', CHILD_THEME_URL . '/plugins/tbm-download-variety-content/js/admin.js', ['jquery'], time(), true);
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
        if (isset($child->tagName)) {

          if ('div' == $child->tagName) {
            foreach ($child->attributes as $key => $value) {
              if (
                ('id' == $key && in_array($child->getAttribute($key), ['pmc-gallery-vertical', 'article-comments', 'comments-loading', 'cx-paywall'])) ||
                ('class' == $key &&
                  (in_array($child->getAttribute($key), ['admz', 'article-tags', 'c-featured-article__post-actions']) ||
                    strpos($child->getAttribute($key), 'article-tags') !== FALSE ||
                    strpos($child->getAttribute($key), 'o-comments-link') !== FALSE
                  )
                )
              ) {
                continue (2);
              }
            }
          } elseif ('script' == $child->tagName) {
            continue;
          }
        }

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

      if ('' != $this->rs_feed) {


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
      } // If Feed url is not empty

      // Not found in feed, try the other way
      return $this->downloadArticleFromUrl($article_url);

      wp_send_json_error(['result' => 'Empty feed!']);
      die();
    endif; // If nonce validated
  }

  private function downloadArticleFromUrl($article_url)
  {
    if (!is_null($article_url)) :
      $html = file_get_contents($article_url);
      $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);

      $html = preg_replace(
        '/<p(.*?)?>(<script .*?><\/script>)(.*)?<\/p>/',
        '$2',
        $html
      );

      $html = str_replace('<p></a></p>', '</a>', $html);

      $doc = new \DOMDocument();
      @$doc->loadHTML($html);
      $doc->preserveWhiteSpace = false;

      $author = $meta_og_description = '';
      $authors = [];
      $categories = $tags = [];
      foreach ($doc->getElementsByTagName('meta') as $meta) {
        if ($meta->getAttribute('property') == 'og:description') {
          $meta_og_description = $meta->getAttribute('content');
        }
        if ($meta->getAttribute('property') == 'og:title') {
          $article_title = $meta->getAttribute('content');
        }
        if ($meta->getAttribute('property') == 'og:image') {
          $featured_img = $meta->getAttribute('content');
        }
        if ($meta->getAttribute('class') == 'swiftype') {
          if ($meta->getAttribute('name') == 'tags') {
            array_push($tags, $meta->getAttribute('content'));
          }
          if ($meta->getAttribute('name') == 'topics') {
            array_push($categories, $meta->getAttribute('content'));
          }
          if ($meta->getAttribute('name') == 'author') {
            // $authors[] = $meta->getAttribute('content');
            $meta_authors = explode(',', $meta->getAttribute('content'));
            foreach ($meta_authors as $meta_author) {
              if (!in_array($meta_author, $authors)) {
                $authors[] = $meta_author;
              }
            }
          }
        }
      }

      $author = implode(', ', $authors);

      // Image Credit
      /* foreach ($doc->getElementsByTagName('figcaption') as $figcaption) {
        if ($figcaption->nodeValue) {
          $featured_img_credit = $figcaption->nodeValue;
          break;
        }
      } */

      $dom_xpath = new \DOMXpath($doc);

      $featured_img_elements = $dom_xpath->query("//*[contains(concat(' ', @class, ' '),'c-figcaption')]");
      $featured_img_caption = $featured_img_credit = '';
      foreach ($featured_img_elements as $element) {
        $children = $element->childNodes;
        if ($children->length > 0) {
          foreach ($children as $child) {
            $featured_img_credit = $this->get_inner_html($child);
            break;
          }
        }
      }

      // Featured Image Alt
      $featured_img_alt = $this->getFeaturedImgAlt($dom_xpath);

      // wp_send_json_error(array('result' => '<pre>' . print_r($featured_img_alt, true) . '</pre>'));
      // die();

      $content = '';
      $content_elements = $dom_xpath->query("//*[contains(concat(' ', @class, ' '),'vy-cx-page-content ')]");

      if ($content_elements->length == 0) {
        $content_elements = $dom_xpath->query("//*[contains(concat(' ', @class, ' '),'a-featured-article-grid__content')]");
      }
      if ($content_elements->length == 0) {
        wp_send_json_error(['result' => 'Unknown format!']);
        die();
      }

      foreach ($content_elements as $element) {

        $children = $element->childNodes;

        if ($children->length > 0) {
          foreach ($children as $child) {
            if (!isset($child->tagName))
              continue;

            // p
            if ('p' == $child->tagName) { //} && trim($child->nodeValue) != '') {
              $content .= '<p>' . $this->get_inner_html($child) . '</p>';
            }

            // blockquote
            if ('blockquote' == $child->tagName && trim($child->nodeValue) != '') {
              $blockquote_attr = '';
              foreach ($child->attributes as $key => $value) {
                $blockquote_attr .= ' ' . $key . '="' . $child->getAttribute($key)  . '"';
              }
              $content .= '<blockquote' . $blockquote_attr . '>' . $this->get_inner_html($child) . '</blockquote>';
            }

            // iframe
            if ('iframe' == $child->tagName) {
              $iframe_attr = '';
              foreach ($child->attributes as $key => $value) {
                $iframe_attr .= ' ' . $key . '="' . $child->getAttribute($key) . '"';
              }
              $content .= '<iframe' . $iframe_attr . '>' . $child->nodeValue . '</iframe>';
            }

            // script
            if ('script' == $child->tagName) {
              continue;
              $script_attr = '';
              foreach ($child->attributes as $key => $value) {
                if ('' != $child->getAttribute($key)) {
                  $script_attr .= ' ' . $key . '="' . $child->getAttribute($key) . '"';
                } else {
                  $script_attr .= ' ' . $key . ' ';
                }
              }
              $content .= '<script' . $script_attr . '>' . $child->nodeValue . '</script>';
            }

            // div
            if ('div' == $child->tagName) {
              $div_attr = '';
              foreach ($child->attributes as $key => $value) {
                if (
                  ('id' == $key && in_array($child->getAttribute($key), ['pmc-gallery-vertical', 'article-comments', 'comments-loading', 'cx-paywall'])) ||
                  ('class' == $key &&
                    (in_array($child->getAttribute($key), ['admz', 'article-tags', 'c-featured-article__post-actions'])
                      || strpos($child->getAttribute($key), 'article-tags') !== FALSE
                      || strpos($child->getAttribute($key), 'o-comments-link') !== FALSE
                      || strpos($child->getAttribute($key), 'widget_cxense') !== FALSE
                    )
                  )
                ) {
                  continue (2);
                }
                $div_attr .= ' ' . $key . '="' . $child->getAttribute($key)  . '"';
              }
              $content .= '<div' . $div_attr . '>' . $this->get_inner_html($child) . '</div>';
            }

            // img
            $content = str_replace('data-lazy-', '', $content);
          }

          // wp_send_json_error(array('result' => '<div style="width: 100%; max-width: 100%:"><code>' . str_replace(['<', '>',], ['&lt;', '&gt;',], $content) . '</code></div>'));
          // die();
        }
      }

      if ('' != $content) {
        $the_article = [
          'title' => $article_title,
          'categories' => $categories,
          'tags' => $tags,
          'author' => $author,
          'image' => '',
          'content' => $content,
          'url' => $article_url,
        ];
        if (isset($featured_img_credit)) {
          $the_article['featured_img_credit'] = $featured_img_credit;
        }
        if (isset($featured_img_caption)) {
          $the_article['featured_img_caption'] = $featured_img_caption;
        }
        if (isset($featured_img_alt)) {
          $the_article['featured_img_alt'] = $featured_img_alt;
        }

        return $this->createArticle($the_article);
      }
    endif;
    wp_send_json_error(['result' => 'Unable to download!']);
    die();
  }

  private function createArticle($article = [])
  {
    global $wpdb;

    if (empty($article) || !isset($article['title']) || '' == trim($article['title']) || !isset($article['url']) || '' == trim($article['url'])) {
      wp_send_json_error(['result' => 'Missing details.']);
      die();
    }

    $cat_IDs = [];
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

    // wp_send_json_error(['result' => '<pre>' . print_r($article['url'], true)]);
    // die();

    $post_name = preg_replace('|((.*))-(\d+)$|', '$1', end($post_name_e));

    $html = file_get_contents($article['url']);
    $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);

    $doc = new \DOMDocument();
    @$doc->loadHTML($html);
    $doc->preserveWhiteSpace = false;

    $meta_description = $meta_og_description = '';
    foreach ($doc->getElementsByTagName('meta') as $meta) {
      if ($meta->getAttribute('name') == 'description') {
        $meta_description = $meta->getAttribute('content');
      }
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

    // Set Primary Category
    if (isset($post_name_e[3])) {
      $primary_category =  $post_name_e[3];
      $metas['_yoast_wpseo_primary_category'] = get_cat_ID($primary_category);
    }

    $new_article_args = [
      'post_name' => $post_name,
      'post_content' => $content,
      'post_title' => $article['title'],
      'post_excerpt' => $meta_description,
      'post_status' => 'draft',
      'post_type' => 'post',
      'post_category' => $cat_IDs,
      'post_author' => $this->author_id,
      'tags_input' => isset($article['tags']) ? $article['tags'] : [],
      'meta_input' => $metas,
    ];

    $new_article_id = wp_insert_post($new_article_args);

    if (!isset($new_article_id) || is_wp_error($new_article_id)) {
      wp_send_json_error(['result' => 'Error creating the article']);
      die();
    }

    // Set Vertical Taxonomy
    if (isset($post_name_e[2])) {
      $vertical =  $post_name_e[2];
      if (term_exists($vertical, 'vertical')) {
        wp_set_object_terms($new_article_id, $vertical, 'vertical');
      }
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
        $attachment_id = $attachments[0]->ID;

        // set image as the post thumbnail
        set_post_thumbnail($new_article_id, $attachment_id);

        if (isset($article['featured_img_credit'])) {
          update_post_meta($attachment_id, '_image_credit', trim($article['featured_img_credit']));
        }
        if (isset($article['featured_img_caption'])) {
          wp_update_post(['ID' => $attachment_id, 'post_excerpt' => trim($article['featured_img_caption'])]);
        }
        if (isset($article['featured_img_alt'])) {
          update_post_meta($attachment_id, '_wp_attachment_image_alt', trim($article['featured_img_alt']));
        }
      }
    }

    wp_send_json_success(['result' => '<p style="color: green; font-weight: bold;">Download FINISHED.</p> <a href="' . home_url('/?p=' . $new_article_id) . '" target="_blank" class="button">View Article</a> <a href="' . get_edit_post_link($new_article_id) . '" target="_blank" class="button">Edit Article</a>']);
    die();
  }

  /*
  * List
  */
  public function index_list()
  {
  ?>
    <h1>Download List from Variety.com</h1>
    <input type="url" name="list_url" id="list_url" style="width: 90%; display: block; margin: 1rem 0;" value="" placeholder="https://">

    <button id="start-download-list" class="button button-primary">Download</button>

    <div id="migration-results" style="padding: 10px;"></div>
<?php
  }

  public function start_download_list()
  {
    if (check_ajax_referer($this->plugin_name . '_nonce', 'nonce')) :

      ini_set('max_execution_time', 600); // 600 seconds = 10 minutes

      global $wpdb;

      $list_url = isset($_POST['list_url']) ? $_POST['list_url'] : NULL;

      if (!is_null($list_url)) :

        $html = file_get_contents($list_url);

        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);

        // wp_send_json_error(array('result' => '<pre>' . print_r(str_replace(['<', '>',], ['&lt;', '&gt;',], $html), true) . '</pre>'));
        // die();

        $metas = ['_yoast_wpseo_canonical' => $list_url];

        $doc = new \DOMDocument();
        @$doc->loadHTML($html);
        $doc->preserveWhiteSpace = false;

        $meta_og_description = '';
        foreach ($doc->getElementsByTagName('meta') as $meta) {
          if ($meta->getAttribute('property') == 'og:description') {
            $meta_og_description = $meta->getAttribute('content');
          }

          if ($meta->getAttribute('property') == 'og:image') {
            $featured_img = $meta->getAttribute('content');
          }

          if ($meta->getAttribute('name') == 'author') {
            $metas['author'] = $meta->getAttribute('content');
          }
        }

        $dom_xpath = new \DOMXpath($doc);

        $next_page_elements = $dom_xpath->query("//*[@rel='next']");
        foreach ($next_page_elements as $element) {
          $hrec = $element->getAttribute('href');
          // if ( strpos( $hrec, '?' ) !== FALSE ) {
          if ($hrec) {
            $next_page = $hrec;
            break;
          }
        }

        $list_title = '';
        $title_elements = $dom_xpath->query("//*[contains(concat(' ', @class, ' '),'c-heading larva  a-font-primary-regular-2xl')]");
        foreach ($title_elements as $element) {
          $list_title = $this->get_inner_html($element);
          break;
        }

        $list_content = '';

        $lead_elements = $dom_xpath->query("//*[contains(concat(' ', @class, ' '),'vy-cx-page-content')]");
        foreach ($lead_elements as $element) {
          $list_content = $this->get_inner_html($element);
          break;
        }

        /* $author_elements = $dom_xpath->query("//*[contains(concat(' ', @class, ' '),'author')]");
        foreach ($author_elements as $element) {
          $author = $this->get_inner_html($element, true);
          $author = str_ireplace('by ', '', $author);
          $metas['author']  = $author;
          break;
        } */

        $list_content .= '<p><em>From <a href="' . $list_url . '" target="_blank">Variety US</a></em></p>';

        foreach ($doc->getElementsByTagName('figcaption') as $figcaption) {
          if ($figcaption->nodeValue) {
            $featured_img_credit = $figcaption->nodeValue;
            break;
          }
        }

        // Categories - get from breadcrumbs
        $breadcrumbs = $cat_IDs = array();
        $breadcrumbs_elements = $dom_xpath->query("//*[contains(concat(' ', @class, ' '),'o-nav-breadcrumblist__list')]");
        foreach ($breadcrumbs_elements as $element) {
          $dom = new \DOMDocument();
          @$dom->loadHTML($this->get_inner_html($element));

          $a_elements = $dom->getElementsByTagName('a');
          if ($a_elements->length > 0) {
            foreach ($a_elements as $element) {
              $cat_name = trim(strtolower($element->nodeValue));
              if (!in_array($cat_name, array('home')) && !in_array($cat_name, $breadcrumbs)) {
                $breadcrumbs[] = $cat_name;
              }
            }
          }
        }

        // wp_send_json_error(array('result' => '<pre>' . print_r($breadcrumbs, true) . '</pre>'));
        // exit;

        if (!empty($breadcrumbs)) {
          if (isset($breadcrumbs[0]))
            $vertical = $breadcrumbs[0];
          if (isset($breadcrumbs[1]))
            $cat_IDs[] = get_cat_ID($breadcrumbs[1]);
        }

        $new_list_args = array(
          'post_content' => $list_content,
          'post_title' => $list_title,
          'post_excerpt' => $meta_og_description,
          'post_status' => 'draft',
          'post_type' => 'pmc_list',
          'post_category' => $cat_IDs,
          'post_author' => $this->author_id,
          'meta_input' => $metas,
        );

        $new_list_id = wp_insert_post($new_list_args);

        if (isset($vertical)) {
          if (term_exists($vertical, 'vertical')) {
            wp_set_object_terms($new_list_id, $vertical, 'vertical');
          }
        }

        if (!isset($new_list_id) || is_wp_error($new_list_id)) {
          wp_send_json_error(['result' => 'Error creating main list']);
          die();
        }

        // $list_item_elements = $dom_xpath->query("//*[@class='c-list__item c-list__item--artist']");
        // $list_item_elements = $dom_xpath->query('//div[contains(@class,"c-list__item")]');

        if (isset($featured_img) && '' != $featured_img) {
          // required libraries for media_sideload_image
          require_once(ABSPATH . 'wp-admin/includes/file.php');
          require_once(ABSPATH . 'wp-admin/includes/media.php');
          require_once(ABSPATH . 'wp-admin/includes/image.php');

          // load the image
          $result = media_sideload_image($featured_img, $new_list_id);

          // then find the last image added to the post attachments
          $attachments = get_posts(['numberposts' => '1', 'post_parent' => $new_list_id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC']);


          if (sizeof($attachments) > 0) {
            $attachment_id = $attachments[0]->ID;
            if (isset($featured_img_credit))
              update_post_meta($attachment_id, '_image_credit', trim($featured_img_credit));
            // set image as the post thumbnail
            set_post_thumbnail($new_list_id, $attachment_id);
          }
        }

        $pmc_list_relation = wp_insert_term(
          $new_list_id,
          'pmc_list_relation'
        );

        $term_taxonomy_id = !is_wp_error($pmc_list_relation) ? $pmc_list_relation['term_taxonomy_id'] : (isset($pmc_list_relation->error_data['term_exists']) ? $pmc_list_relation->error_data['term_exists'] : null);

        $list_items = $this->download_list_items($dom_xpath, $term_taxonomy_id, 0, $new_list_id);
        $total_list_items = $list_items['total_list_items'];

        if (isset($list_items['next_page']) && '' != $list_items['next_page']) { // If there is a next page, notify to continue download
          $next_page = $list_items['next_page'];
          wp_send_json_success(
            [
              'result' => 'Downloading more from next page <a href="' . $next_page . '" target="_blank">' . $next_page . '</a>, please wait...<br><a href="' . home_url('/?post_type=pmc_list&preview=true&p=' . $new_list_id) . '" target="_blank">View List so far</a>',
              'list_url' => $next_page,
              'list_id' => $new_list_id,
              'total_list_items' => $total_list_items,
              'term_taxonomy_id' => $term_taxonomy_id,
              'has_next_page' => true,
            ]
          );
          wp_die();
        } else { // If there is NO next page, download has been finished and notify with the newly created list
          wp_send_json_success(['result' => '<span style="color: green; font-weight: bold;">Download FINISHED.</span> <a href="' . home_url('/?post_type=pmc_list&preview=true&p=' . $new_list_id) . '" target="_blank">View List</a>']);
          wp_die();
        }
      endif; // If $list_url is NOT NULL
    endif; // If nonce validated
  }

  public function continue_download_list()
  {
    if (check_ajax_referer($this->plugin_name . '_nonce', 'nonce')) :
      global $wpdb;

      $list_url = isset($_POST['list_url']) ? $_POST['list_url'] : NULL;

      if ($list_url) :
        $html = file_get_contents($list_url);

        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);

        $doc = new \DOMDocument();
        @$doc->loadHTML($html);
        $doc->preserveWhiteSpace = false;

        $dom_xpath = new \DOMXpath($doc);

        $next_page_elements = $dom_xpath->query("//*[@rel='next']");
        foreach ($next_page_elements as $element) {
          $hrec = $element->getAttribute('href');
          if (strpos($hrec, '?') !== FALSE) {
            $next_page = $hrec;
            break;
          }
        }

        $term_taxonomy_id = isset($_POST['term_taxonomy_id']) ? absint($_POST['term_taxonomy_id']) : 0;
        $total_list_items = isset($_POST['total_list_items']) ? absint($_POST['total_list_items']) : 0;
        $new_list_id = isset($_POST['list_id']) ? absint($_POST['list_id']) : 0;

        $list_items = $this->download_list_items($dom_xpath, $term_taxonomy_id, $total_list_items, $new_list_id);
        $total_list_items = $list_items['total_list_items'];

        if ($list_items['next_page']) { // If there is a next page, notify to continue download
          $next_page = $list_items['next_page'];
          wp_send_json_success(
            [
              'result' => 'Downloading more from next page <a href="' . $next_page . '" target="_blank">' . $next_page . '</a>, please wait...<br><a href="' . home_url('/?post_type=pmc_list&preview=true&p=' . $new_list_id) . '" target="_blank">View List so far</a>',
              'list_url' => $next_page,
              'list_id' => $new_list_id,
              'total_list_items' => $total_list_items,
              'term_taxonomy_id' => $term_taxonomy_id,
              'has_next_page' => true,
            ]
          );
          wp_die();
        } else { // If there is NO next page, download has been finished and notify with the newly created list
          wp_send_json_success(['result' => '<span style="color: green; font-weight: bold;">Download FINISHED.</span> <a href="' . home_url('/?post_type=pmc_list&preview=true&p=' . $new_list_id) . '" target="_blank">View List</a>']);
          wp_die();
        }
      endif; // If $list_url is NOT NULL
    else :
      wp_send_json_error(['result' => 'Error with nonce']);
      die();
    endif; // If nonce validated
  }

  function download_list_items($dom_xpath, $term_taxonomy_id, $count_elements = 0, $list_id = null)
  {
    $js_list_items = $dom_xpath->query("//*[contains(concat(' ', @id, ' '),'pmc-lists-front-js-extra')]");
    foreach ($js_list_items as $i => $element) {
      $nodes = $element->childNodes;
      $list_item = [];
      $meta_input = [];

      foreach ($nodes as $node) {
        $text = str_replace('var pmcGalleryExports = ', '', $node->wholeText);
        $text = str_replace(['\n', '\r', '\t',], ['', '', '',], $text);
        $text = str_replace(',"closeButtonLink":"\/"};', '}', $text);

        // $first_occ_extra_var = strpos($text, '</script>');
        // if ($first_occ_extra_var !== FALSE) {
        //   $text = substr($text, 0, $first_occ_extra_var);
        // }

        // wp_send_json_error(['result' => '<div style="width: 100%; max-width: 100%:">' . $text . '</div>']);
        // die();

        $items = json_decode($text, true);

        switch (json_last_error()) {
          case JSON_ERROR_NONE:
            break;
          case JSON_ERROR_DEPTH:
            error_log(' - Maximum stack depth exceeded');
            break;
          case JSON_ERROR_STATE_MISMATCH:
            error_log(' - Underflow or the modes mismatch');
            break;
          case JSON_ERROR_CTRL_CHAR:
            error_log(' - Unexpected control character found');
            break;
          case JSON_ERROR_SYNTAX:
            error_log(' - Syntax error, malformed JSON');
            break;
          case JSON_ERROR_UTF8:
            error_log(' - Malformed UTF-8 characters, possibly incorrectly encoded');
            break;
          default:
            break;
        }
        // wp_send_json_error(array('result' => '<div style="width: 100%; max-width: 100%:"><code>' . str_replace(['<', '>',], ['&lt;', '&gt;.',], $text) . '</code></div>'));
        // die();

        $pmc_list_order = [];
        if ($items && $items['gallery']) {
          foreach ($items['gallery'] as $item) {

            $list_item = array();
            $meta_input = array();

            // $list_item['counter'] = $count_elements;

            $list_item['title'] = trim($item['title']);

            $list_item['img'] = strtok($item['image'], '?') . '?w=1000';
            $meta_input['thumbnail_ext_url'] = $list_item['img'];
            list($width, $height) = getimagesize($list_item['img']);
            $meta_input['thumbnail_ext_url_dims'] = [$width, $height];

            if (isset($item['video'])) {

              $iframe_embed_root = simplexml_load_string($item['video']);
              $iframe_embed_src = (string) $iframe_embed_root['data-src'];
              $iframe_embed_src = strtok($iframe_embed_src, '?');
              $list_item['iframe'] = str_replace('/embed/', '/watch?v=', $iframe_embed_src);
              $meta_input['pmc_top_video_source'] = $list_item['iframe'];
            }

            if (isset($item['image_credit'])) {
              $list_item['_image_credit'] = trim($item['image_credit']);
              $meta_input['thumbnail_ext_image_credit'] = trim($item['image_credit']);
            }

            // $list_item['content'] = wpautop(strip_tags($item['description']));
            $list_item['content'] = $item['description'];

            // $meta_input['pmc_list_order'] = $list_item['counter'];

            if (!empty($list_item)) {
              $new_list_item_args = array(
                'post_content' => $list_item['content'],
                'post_title' => $list_item['title'],
                'post_status' => 'publish',
                'post_type' => 'pmc_list_item',
                'post_author' => $this->author_id,
                'tax_input' => array(
                  'pmc_list_relation' => array(
                    'pmc_list_relation' => $term_taxonomy_id
                  ),
                ),
                // 'menu_order' => $list_item['counter'],
                'meta_input' => $meta_input,
              );

              $new_list_item_id = wp_insert_post($new_list_item_args);

              // wp_send_json_success(array('result' => '<pre>' . print_r($new_list_item_args, true) . '</pre>'));
              // wp_die();

              $pmc_list_order[$count_elements] = $new_list_item_id;
              $count_elements++;

              // break;
            } // If $list_item is NOT empty
          }

          if (!empty($pmc_list_order) && !is_null($list_id) && $list_id > 0) {
            update_post_meta($list_id, 'pmc_list_order', ($pmc_list_order));
          }

          $return = [
            'total_list_items' => $count_elements,
            'next_page' => false,
          ];

          if ($items['nextPageLink'] && '' != $items['nextPageLink']) {
            $return['next_page'] = $items['nextPageLink']; // true;
          }

          return $return;
        }
      }
    }
  }

  private function getFeaturedImgAlt($dom_xpath, $element = null)
  {
    if (!is_null($element)) {
      if ('img' == $element->tagName) {
        foreach ($element->attributes as $attr) {
          if ('alt' == $attr->name)
            return $attr->value;
        }
        return $element;
      }
      if ('' != $element->tagName) {
        foreach ($element->childNodes as $e) {
          if (isset($e->tagName) && '' != $e->tagName) {
            return $this->getFeaturedImgAlt($dom_xpath, $e);
          }
        }
      }
    } else {
      $featured_img_elements = $dom_xpath->query("//*[contains(concat(' ', @class, ' '),'o-figure')]");
      foreach ($featured_img_elements as $elem) {
        if ('img' != $elem->tagName) {
          foreach ($elem->childNodes as $e) {
            return $this->getFeaturedImgAlt($dom_xpath, $e);
          }
        }
      }
    }
    return '';
  }
}
new DownloadVarietyContent();
