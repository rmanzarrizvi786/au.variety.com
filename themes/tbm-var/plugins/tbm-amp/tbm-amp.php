<?php

/**
 * Plugin Name: AMP Addon (TBM Use)
 * Plugin URI: https://thebrag.media
 * Description:
 * Version: 1.0.0
 * Author: Sachin Patel
 * Author URI:
 */

class TBMAMP
{

  protected $plugin_name;
  protected $plugin_slug;

  public function __construct()
  {
    $this->plugin_name = 'tbm_amp';
    $this->plugin_slug = 'tbm-amp';

    add_action('amp_post_template_css', [$this, 'amp_post_template_css']);
    add_filter('amp_content_max_width', [$this, 'amp_content_max_width']);
    add_filter('amp_post_article_header_meta', [$this, 'amp_post_article_header_meta']);
    add_filter('amp_post_template_metadata', [$this, 'amp_post_template_metadata'], 10, 2);
    add_filter('amp_post_template_analytics', [$this, 'amp_post_template_analytics']);
    add_action('ampforwp_before_meta_info_hook', [$this, 'ampforwp_before_meta_info_hook']);
  }

  public function ampforwp_before_meta_info_hook()
  {
    global $post;
    $post_author = get_field('author') ? get_field('author') : '';
    if ($post_author != '') :
?>
      <style>
        .amp-wp-meta-date:before {
          content: " By <?php echo $post_author; ?>";
        }
      </style>
    <?php
    endif;
  }

  public function amp_post_template_analytics($analytics)
  {
    global $amp_post_id;
    $post = get_post($amp_post_id);
    if (!is_array($analytics)) {
      $analytics = array();
    }

    if (get_field('author')) {
      $author = get_field('author');
    } else if (get_field('Author')) {
      $author = get_field('Author');
    } else {
      if ('' != get_the_author_meta('first_name', $post->post_author) && '' != get_the_author_meta('last_name', $post->post_author)) {
        $author = get_the_author_meta('first_name', $post->post_author) . ' ' . get_the_author_meta('last_name', $post->post_author);
      } else {
        $author = get_the_author_meta('display_name', $post->post_author);
      }
    }

    $analytics['ssm-googleanalytics'] = array(
      'type' => 'googleanalytics',
      'attributes' => array(
        // 'data-credentials' => 'include',
      ),
      'config_data' => array(
        'vars' => array(
          'account' => "UA-101631840-1"
        ),
        'triggers' => array(
          'trackPageview' => array(
            'on' => 'visible',
            'request' => 'pageview',
            'extraUrlParams' => array(
              'cd3' => str_replace('&', 'and', $author)
            )
          ),
        ),
      ),
    );

    $analytics['rsau-googleanalytics'] = array(
      'type' => 'googleanalytics',
      'attributes' => array(
        // 'data-credentials' => 'include',
      ),
      'config_data' => array(
        'vars' => array(
          'account' => "UA-156845367-1"
        ),
        'triggers' => array(
          'trackPageview' => array(
            'on' => 'visible',
            'request' => 'pageview',
            'extraUrlParams' => array(
              'cd3' => str_replace('&', 'and', $author)
            )
          ),
        ),
      ),
    );

    return $analytics;
  }

  public function amp_post_template_metadata($metadata, $post)
  {
    if (get_field('author')) {
      $metadata['author']['name'] = get_field('author');
    } else if (get_field('Author')) {
      $metadata['author']['name'] = get_field('Author');
    }
    $metadata['publisher']['logo'] = array(
      '@type' => 'ImageObject',
      'url' => get_stylesheet_directory_uri() . '/assets/build/svg/brand-logo.png',
      'width' => 450,
      'height' => 160,
    );
    if (!isset($metadata['image'])) {
      $metadata['image'] = array(
        '@type' => 'ImageObject',
        'url' => get_stylesheet_directory_uri() . '/assets/build/svg/brand-logo.png',
        'width' => '450',
        'height' => '160',
      );
    }
    return $metadata;
  }

  public function amp_post_article_header_meta($meta_parts)
  {
    foreach (array_keys($meta_parts, 'meta-time', true) as $key) {
      unset($meta_parts[$key]);
    }
    // if (get_field('author')) :
    //   $author = get_field('author');
    // elseif ($post_author) :
    //   $author = $post_author->display_name;
    // endif;
    $meta_parts['meta-author'] = CHILD_THEME_PATH . '/amp/meta-author';
    return $meta_parts;
  }

  public function amp_content_max_width($content_max_width)
  {
    return 970;
  }

  public function amp_post_template_css($amp_template)
  {
    ?>
    html { background: #ffffff; }
    body { font-family: 'Sans-serif', 'Arial'; background: #fff; }
    a, a:visited, a:hover, a:active, a:focus { color: #06c; }
    .amp-wp-header {
    padding: 0;
    background: #ffffff;
    position: absolute; top: 0; margin: auto; width: 100%; z-index: 999999;
    }
    .amp-wp-byline {
    display: none;
    }
    .amp-wp-header div.amp-wp-header-inner {
    position: fixed;
    top: 0;
    background: #1a282f;
    margin: auto;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    padding: 7px;
    }
    .amp-wp-header a {
    background-image: url( '<?php echo get_stylesheet_directory_uri(); ?>/assets/build/svg/brand-logo.svg' );
    background-repeat: no-repeat;
    background-size: contain;
    display: block;
    height: 30px;
    width: 170px;
    margin: 0 auto;
    text-indent: -9999px;
    background-position: center;
    }
    .amp-wp-title { color: #000; font-weight: 500 }
    .amp-wp-article, .amp-wp-article-header { margin-top: 0; background: #fff; }
    .amp-wp-article { padding: 10px 0; }
    #pagination { border-top: 1px solid #ccc; }
    #pagination .prev a, #pagination .next a {
    display: block;
    margin-bottom: 12px;
    background: #fefefe;
    text-decoration: none;
    font-size: 0.8rem;
    padding: 5px 15px;
    color: #666;
    }
    #pagination .prev a { text-align: left; }
    #pagination .next a { text-align: right; }
    .related-stories-wrap { background:#1fcabf; background: #fff; margin-top: 0px; padding: 10px 0; width: 100%; border-top: 1px solid #cecece; }
    .related-stories-wrap .title { margin:0 0 15px 0; padding:0 10px; text-transform:uppercase; font-size:20px; line-height:22px; }
    .related-story { min-height: 100px; clear: both; border-bottom: 1px solid #dedede; padding: 10px 0; }
    .related-story .post-thumbnail { float: left; overflow: hidden; padding: 0 10px; }
    .related-story .post-thumbnail amp-img { width: 100px; height: auto; }
    .related-story .post-content { padding: 0 10px; margin-left: 80px; }
    .related-story .post-content .excerpt { font-size: 0.8rem; line-height: 1.2rem; }
    .related-story h2 { font-size: 1.2rem; line-height: 1rem; margin: 0 0 5px 0; }
    .related-story a { text-decoration: none; font-size: 14px; }
    .share-buttons-bottom {
    position:fixed; text-align: center; bottom: 0; padding-top: 10px; width: 100%; background: #fff; z-index: 9999;
    }
<?php
  }
}

new TBMAMP();
