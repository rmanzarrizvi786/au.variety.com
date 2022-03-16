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
    // add_action('ampforwp_before_meta_info_hook', [$this, 'ampforwp_before_meta_info_hook']);
  }

  public function ampforwp_before_meta_info_hook()
  {
    global $post;
    $post_author = get_field('author') ? get_field('author') : '';
    if ($post_author != '') :

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

    $analytics['var-googleanalytics'] = array(
      'type' => 'googleanalytics',
      'attributes' => array(
        // 'data-credentials' => 'include',
      ),
      'config_data' => array(
        'vars' => array(
          'account' => "G-G9XHKL0YVT"
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
    $font_dir_url = sprintf(
      '%s/assets/public',
      untrailingslashit(get_stylesheet_directory_uri())
    );
?>
    @font-face {
    font-family: 'IBM Plex Mono';
    src: local('IBM Plex Mono'), local('IBMPlexSans'),
    url( "<?php printf('%s/ibm-plex-mono-v5-latin-500.woff2', esc_url($font_dir_url)); ?>" ) format('woff2'),
    url("<?php printf('%s/ibm-plex-mono-v5-latin-500.woff', esc_url($font_dir_url)); ?>" ) format('woff');
    font-style: normal;
    font-weight: 500;
    font-display: swap;
    }

    @font-face {
    font-family: 'IBM Plex Sans';
    src: local('IBM Plex Sans'), local('IBMPlexSans'),
    url( "<?php printf('%s/ibm-plex-sans-v7-latin-regular.woff2', esc_url($font_dir_url)); ?>" ) format('woff2'), /* Chrome 26+, Opera 23+, Firefox 39+ */
    url("<?php printf('%s/ibm-plex-sans-v7-latin-regular.woff', esc_url($font_dir_url)); ?>" ) format('woff'); /* Chrome 6+, Firefox 3.6+, IE 9+, Safari 5.1+ */
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    }

    @font-face {
    font-family: 'IBM Plex Sans';
    src: local('IBM Plex Sans'), local('IBMPlexSans'),
    url( "<?php printf('%s/ibm-plex-sans-v7-latin-700.woff2', esc_url($font_dir_url)); ?>" ) format('woff2'), /* Chrome 26+, Opera 23+, Firefox 39+ */
    url("<?php printf('%s/ibm-plex-sans-v7-latin-700.woff', esc_url($font_dir_url)); ?>" ) format('woff'); /* Chrome 6+, Firefox 3.6+, IE 9+, Safari 5.1+ */
    font-style: normal;
    font-weight: 700;
    font-display: swap;
    }

    @font-face {
    font-family: 'IBM Plex Serif';
    src: local('IBM Plex Serif'), local('IBMPlexSans'),
    url( "<?php printf('%s/ibm-plex-serif-v8-latin-regular.woff2', esc_url($font_dir_url)); ?>" ) format('woff2'), /* Chrome 26+, Opera 23+, Firefox 39+ */
    url( "<?php printf('%s/ibm-plex-serif-v8-latin-regular.woff', esc_url($font_dir_url)); ?>" ) format('woff'); /* Chrome 6+, Firefox 3.6+, IE 9+, Safari 5.1+ */
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    }

    @font-face {
    font-family: 'Graphik XX Cond';
    src: url( "<?php printf('%s/Graphik\ XX\ Cond-Semibold\ BETA.otf', esc_url($font_dir_url)); ?>" ) format( 'opentype' );
    font-style: normal;
    font-weight: 600;
    font-display: swap;
    }

    @font-face {
    font-family: 'Graphik XX Cond';
    src: url( "<?php printf('%s/Graphik\ XX\ Cond-Medium\ BETA.otf', esc_url($font_dir_url)); ?>" ) format( 'opentype' );
    font-style: normal;
    font-weight: 500;
    font-display: swap;
    }

    @font-face {
    font-family: 'Para Supreme Regular';
    src:
    url( "<?php printf('%s/2020.04.03-ParaSupreme-Regular.woff2', esc_url($font_dir_url)); ?>" ) format( 'woff2' ),
    url( "<?php printf('%s/2020.04.03-ParaSupreme-Regular.woff', esc_url($font_dir_url)); ?>" ) format( 'woff' ),
    url( "<?php printf('%s/2020.04.03-ParaSupreme-Regular.ttf', esc_url($font_dir_url)); ?>" ) format( 'truetype' );
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    }

    a, a:visited, a:focus, a:hover {
    color: #0066CC;
    text-decoration: none;
    }

    body {
    font-family: "IBM Plex Serif",serif;
    }
    amp-sidebar {
    background: #d7dee2;
    padding-left: 12px;
    padding-right: 12px;
    }
    amp-sidebar .menu {
    list-style-type: none;
    }
    amp-sidebar .menu li {
    padding: 0;
    border: none;
    border-bottom: 1px solid #9CA9B1;
    }
    amp-sidebar .menu li a {
    display: block;
    padding: 20px 13px 10px 23px;
    height: auto;
    font-family: "IBM Plex Sans",sans-serif;
    font-size: 18px;
    line-height: 23px;
    font-weight: bold;
    }
    amp-sidebar .menu li:last-child,
    amp-sidebar .menu li:nth-last-child(2),
    amp-sidebar .menu li:nth-last-child(3) {
    border: none;
    }
    amp-sidebar .menu li:nth-last-child(3) {
    padding-top: 15px;
    }
    amp-sidebar .menu li:last-child a,
    amp-sidebar .menu li:nth-last-child(2) a,
    amp-sidebar .menu li:nth-last-child(3) a {
    font-size: 13px;
    line-height: 17px;
    text-transform: uppercase;
    padding-top: 6px;
    padding-bottom: 6px;
    }
    nav.amp-wp-title-bar {
    background-color: #1a282f;
    color: #fff;
    padding: 0;
    width: 100%;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.24), 0 0.0625rem 0.0625rem rgba(0,0,0,.12);
    height: 2.9375rem;
    position: relative;
    }
    .hamburger .icon-bar {
    background-color: #fff;
    }
    .amp-mode-touch .amp-wp-content {
    padding: 0 20px;
    display: flex;
    flex-direction: column;
    }
    .amp-mode-touch .amp-wp-content .featured-image {
    margin-left: -20px;
    margin-right: -20px;
    }

    .amp-wp-title {
    font-family: "Para Supreme Regular",serif;
    font-weight: 400;
    font-size: 28px;
    line-height: 1.14;
    color: #000;
    }

    .amp-wp-content h1.amp-wp-title {
    margin-bottom: 8px;
    }

    ul.amp-wp-meta {
    padding: 0;
    margin: 0 0 8px 0;
    list-style-type: none;
    }

    .amp-wp-meta,
    .amp-wp-meta a {
    font-family: "IBM Plex Sans",sans-serif;
    }

    .amp-wp-meta,
    .amp-wp-meta a {
    color: #000000;
    font-size: 12px;
    line-height: 1.25;
    font-family: "IBM Plex Sans", sans-serif;
    }

    .amp-wp-posted-on {
    text-align: left;
    }

    .amp-wp-posted-on {
    font-weight: 300;
    padding: 0;
    font-family: "IBM Plex Mono", monospace;
    letter-spacing: 1.2px;
    color: #89959D;
    vertical-align: middle;
    }

    .amp-wp-byline {
    margin-bottom: 10px;
    font-size: 13px;
    line-height: 1.3em;
    font-weight: bold;
    letter-spacing: 0.5px;
    color: #677981;
    text-transform: capitalize;
    }

    .amp-wp-byline a {
    color: #677981;
    }

    .amp-mode-touch .amp-wp-tax-category {
    box-sizing: border-box;
    display: block;
    margin: 0;
    }

    .amp-mode-touch .amp-wp-tax-category:before {
    content: "";
    margin: 0px;
    }

    .amp-mode-mouse .amp-wp-tax-category a, .amp-mode-touch .amp-wp-tax-category a {
    border: 2px solid #000000;
    padding: 6px;
    text-decoration: none;
    float: left;
    margin: 3px 6px 3px 0;
    color: #000000;
    font-size: 14px;
    line-height: 14px;
    }

    .amp-mode-mouse .amp-wp-tax-category a:after, .amp-mode-touch .amp-wp-tax-category a:after {
    content: "";
    }

    .amp-mode-touch .amp-wp-tax-tag {
    display: none;
    }

    .amp-mode-mouse nav.amp-wp-title-bar a, .amp-mode-touch nav.amp-wp-title-bar a {
    background-image: url( <?php echo esc_url($logo_img_url); ?> );
    background-repeat: no-repeat;
    background-position: center;
    background-size: auto 30px;
    display: block;
    margin: 0 auto;
    outline: none;
    text-align:center;
    text-indent: -9999px;
    white-space: nowrap;
    line-height: 47px;
    }

    .amp-mode-touch .amp-fn-content h3 {
    font-weight: 700;
    font-size: 16px;
    line-height: 1.25;
    color: #000000;
    margin: 8px 0;
    }

    .amp-wp-excerpt,
    .amp-wp-excerpt p {
    font-size: 14px;
    line-height: 1.28;
    }

    .amp-wp-excerpt p {
    margin-bottom: 12px;
    }

    .amp-mode-touch .amp-fn-content,
    .amp-mode-touch .amp-fn-content p {
    font-weight: 300;
    font-size: 15px;
    line-height: 1.6;
    color: #000000;
    }
    .amp-fn-content p {
    margin: 16px 0;
    }

    .amp-mode-touch nav.amp-wp-title-bar div {
    line-height: 90px;
    color: #fff;
    }

    .amp-mode-mouse #callout, .amp-mode-touch #callout {
    font-weight: 700;
    background: #000000;
    position: fixed;
    bottom: 0;
    z-index: 20;
    }

    .gallery-B .data > a {
    color: #000;
    font-weight: bold;
    text-decoration: none;
    }

    .gallery-B .data.amp-comments-link {
    border-color: #ffda08;
    background-color: #ffda08;
    margin: 10px 0;
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.5);
    }
    .amp-comments-link {
    border: 1px solid #000;
    font-size: 13px;
    }
    .amp-comments-link a {
    display: flex;
    color: #6B7B84;
    letter-spacing: 0.65px;
    font-family: "IBM Plex Mono", monospace;
    text-decoration: none;
    text-transform: uppercase;
    align-items: center;
    justify-content: center;
    }
    .amp-comments-link a::before {
    content: '';
    width: 22px;
    height: 24px;
    margin-right: 0.5rem;
    background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2724%27%20height%3D%2725%27%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%3E%3Cg%20stroke%3D%27%23000%27%20fill%3D%27none%27%20fill-rule%3D%27evenodd%27%3E%3Cpath%20d%3D%27M-211.5-11.5h579v48h-579z%27%2F%3E%3Cpath%20d%3D%27M19.092%2016.085a9.867%209.867%200%20001.711-5.553C20.81%205.066%2016.381.64%2010.913.644%205.445.65%201.007%205.088%201%2010.554a9.875%209.875%200%20009.89%209.888c1.63-.001%203.146-.436%204.503-1.134L22%2023.171l-2.908-7.086z%27%20fill%3D%27%23000%27%20stroke-width%3D%271.292%27%2F%3E%3C%2Fg%3E%3C%2Fsvg%3E");
    }
    .pmc-related-link .text {
    font-family: "IBM Plex Sans", sans-serif;
    font-size: 12px;
    font-weight: 700;
    }
    .pmc-related-link .pmc-related-type {
    padding-right: 5px;
    font-family: "IBM Plex Sans", sans-serif;
    font-size: 12px;
    font-weight: bold;
    letter-spacing: 0.6px;
    text-align: left;
    color: #000000;
    text-transform: uppercase;
    }

    .article-breadcrumb-container {
    padding-left: 18px;
    padding-right: 20px;
    }

    .article-header__breadcrumbs {
    line-height: 1.3;
    display: flex;
    padding-top: 8px;
    margin-bottom: 8px;
    }
    .article-header__breadcrumbs li {
    display: flex;
    align-items: center;
    padding-left: 2px;
    background-image: linear-gradient(rgba(245,241,107,0),rgba(245,241,107,0) 50%,rgba(245,241,107,.6) 51%,rgba(245,241,107,.6));
    margin: 0;
    }
    .article-header__breadcrumbs li::before {
    display: inline;
    background: none;
    content: '>';
    position: static;
    transform: none;
    width: auto;
    height: auto;
    color: #6B7B84;
    }
    .article-header__breadcrumbs li a {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 13px;
    padding: 2px 6px 2px 10px;
    letter-spacing: 1.5px;
    text-align: left;
    color: #6B7B84;
    }
    .article-header__breadcrumbs li:first-child a {
    padding-left: 0;
    }
    .amp-fn-content .featured-image {
    margin-bottom: 10px;
    }
    .gallery-image-section {
    margin-bottom: 10px;
    }
    .gallery-image-section .gallery-thumbnails {
    margin-bottom: 10px;
    }
    .amp-social-share-bar-container {
    padding: 10px 0;
    border: none;
    }
    .amp-social-share-bar-container .share-this {
    border:0;
    clip:rect(1px,1px,1px,1px);
    clip-path:inset(50%);
    height:1px;
    margin:-1px;
    overflow:hidden;
    padding:0;
    position:absolute;
    width:1px;
    word-wrap:normal;
    }
    .amp-social-share-facebook {
    background-color: #f3f6cb;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath fill='%23677b85' d='M211.9 197.4h-36.7v59.9h36.7v175.8h70.5V256.5h49.2l5.2-59.1h-54.4v-33.7c0-13.9 2.8-19.5 16.3-19.5h38.2V82.9h-48.8c-52.5 0-76.1 23.1-76.1 67.3-.1 38.6-.1 47.2-.1 47.2z'/%3E%3C/svg%3E");
    }
    .amp-social-share-twitter {
    background-color: #f3f6cb;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='400'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cpath d='M0 0h400v400H0z'/%3E%3Cpath fill='%23677b85' fill-rule='nonzero' d='M153.62 301.59c94.34 0 145.94-78.16 145.94-145.94 0-2.22 0-4.43-.15-6.63A104.36 104.36 0 00325 122.47a102.38 102.38 0 01-29.46 8.07 51.47 51.47 0 0022.55-28.37 102.79 102.79 0 01-32.57 12.45c-15.9-16.906-41.163-21.044-61.625-10.093-20.461 10.95-31.032 34.266-25.785 56.873A145.62 145.62 0 0192.4 107.81c-13.614 23.436-6.66 53.419 15.88 68.47A50.91 50.91 0 0185 169.86v.65c.007 24.416 17.218 45.445 41.15 50.28a51.21 51.21 0 01-23.16.88c6.72 20.894 25.976 35.208 47.92 35.62a102.92 102.92 0 01-63.7 22 104.41 104.41 0 01-12.21-.74 145.21 145.21 0 0078.62 23'/%3E%3C/g%3E%3C/svg%3E");
    }
    .amp-social-share-pinterest {
    background-color: #f3f6cb;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath fill='%23677b85' d='M266.6 76.5c-100.2 0-150.7 71.8-150.7 131.7 0 36.3 13.7 68.5 43.2 80.6 4.8 2 9.2.1 10.6-5.3 1-3.7 3.3-13 4.3-16.9 1.4-5.3.9-7.1-3-11.8-8.5-10-13.9-23-13.9-41.3 0-53.3 39.9-101 103.8-101 56.6 0 87.7 34.6 87.7 80.8 0 60.8-26.9 112.1-66.8 112.1-22.1 0-38.6-18.2-33.3-40.6 6.3-26.7 18.6-55.5 18.6-74.8 0-17.3-9.3-31.7-28.4-31.7-22.5 0-40.7 23.3-40.7 54.6 0 19.9 6.7 33.4 6.7 33.4s-23.1 97.8-27.1 114.9c-8.1 34.1-1.2 75.9-.6 80.1.3 2.5 3.6 3.1 5 1.2 2.1-2.7 28.9-35.9 38.1-69 2.6-9.4 14.8-58 14.8-58 7.3 14 28.7 26.3 51.5 26.3 67.8 0 113.8-61.8 113.8-144.5-.1-62.6-53.1-120.8-133.6-120.8z'/%3E%3C/svg%3E")
    }
    .amp-social-share-email {
    background-color: #f3f6cb;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath fill='%23677b85' d='M101.3 141.6v228.9h309.5V141.6H101.3zm274.4 26.2L256 259.3l-119.6-91.5h239.3zm-248.1 26.3l64.1 49.1-64.1 64.1V194.1zm.2 150.1l84.9-84.9 43.2 33.1 43-32.9 84.7 84.7H127.8zm256.6-36.4L320 243.4l64.4-49.3v113.7z'/%3E%3C/svg%3E")
    }
    .amp-category-posts-container {
    padding: 0 20px;
    }
    .amp-category-posts-container .title {
    padding: 0;
    margin: 0 0 8px 0;
    }

    .amp-category-posts-container .post-title {
    font-size: 12px;
    font-family: Arial;
    line-height: 1.2;
    }
    .amp-wp-byline amp-img {
    border: 1px solid #000000;
    }
    .share-this {
    font-family: "IBM Plex Sans", sans-serif;
    font-size: 12px;
    font-weight: bold;
    letter-spacing: 0.6px;
    text-align: left;
    color: #000000;
    text-transform: uppercase;
    }

    .amp-fn-content .pmc-related-link {
    padding: 10px;
    background: #f5f5f5;
    border-top: solid 4px #000000;
    border-bottom: solid 1px #dbdbdb;
    color: #000;
    }
    .amp-fn-content .pmc-related-link a {
    color: #000;
    }
    @media only screen and (max-width: 320px) {
    .amp-mode-touch nav.amp-wp-title-bar div {
    line-height: 72px;
    color: #fff;
    }
    }

    .amp-fn-content .related-articles {
    margin: 23px -20px;
    }

    .related-articles__heading h2 {
    padding: 7px 20px 0;
    margin: 0 0 10px;
    font-size: 18px;
    line-height: 21px;
    font-weight: bold;
    border-top-width: 7px;
    border-top-style: solid;
    border-top-color: #EEE809;
    font-family: "IBM Plex Sans",sans-serif;
    }
    .related-article {
    padding: 0 20px;
    border-width: 1px 0;
    border-style: solid;
    border-color: #C4CACE;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    flex-flow: row;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    }

    .related-articles .lrv-a-crop-4x3 {
    position: relative;
    padding-bottom: 75%;
    }

    .related-articles .lrv-a-crop-4x3 .u-min-width-110 {
    position: absolute;
    width: 100%;
    height: 100%;
    object-fit: cover;
    }

    .related-articles .c-lazy-image {
    flex: 0 0 auto;
    width: 110px;
    }

    .related-articles .c-lazy-image + .c-heading {
    flex: 0 0 auto;
    width: calc(100% - 125px);
    }

    .related-article .c-heading {
    font-size: 13px;
    line-height: 1.15;
    font-weight: normal;
    margin: 0;
    }

    .related-article .c-heading a {
    color: #000;
    }

    .related-articles .c-card__header {
    flex: 0 0 auto;
    width: 100%;
    padding-top: 0;
    }
    .amp-wp-byline {
    display: none;
    }
    .amp-wp-meta.amp-wp-byline {
    display:block;
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
<?php
  }
}

new TBMAMP();
