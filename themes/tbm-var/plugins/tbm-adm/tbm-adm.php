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

class TBMAds
{

  protected $plugin_title;
  protected $plugin_name;
  protected $plugin_slug;

  protected static $_instance;

  public function __construct()
  {

    $this->plugin_title = 'TBM Ads';
    $this->plugin_name = 'tbm_ads';
    $this->plugin_slug = 'tbm-ads';

    add_action('wp_enqueue_scripts', [$this, 'action_wp_enqueue_scripts']);
    add_action('wp_head', [$this, 'action_wp_head']);
  }

  /*
  * Enqueue JS
  */
  public function action_wp_enqueue_scripts()
  {
    wp_enqueue_script('adm-fuse', 'https://cdn.fuseplatform.net/publift/tags/2/2745/fuse.js', [], '1');
  }

  /*
  * WP Head
  */
  public function action_wp_head()
  {
?>
    <script type="text/javascript">
      const fusetag = window.fusetag || (window.fusetag = {
        que: []
      });

      fusetag.que.push(function() {
        googletag.pubads().enableSingleRequest();
        googletag.enableServices();
      });
    </script>
<?php
  }

  /*
  * Singleton
  */
  public static function get_instance()
  {
    if (!isset(static::$_instance)) {
      static::$_instance = new TBMAds();
    }
    return static::$_instance;
  }

  /*
  * Get Ad Tag
  */
  public function get_ad($ad_location = '', $slot_no = 0, $post_id = null, $device = '', $ad_width = '')
  {
    if ('' == $ad_location)
      return;

    $html = '';

    $fuse_tags = self::fuse_tags();

    if (isset($_GET['screenshot'])) {
      $pagepath = 'screenshot';
    } else if (isset($_GET['dfp_key'])) {
      $pagepath = $_GET['dfp_key'];
    } else if (is_home() || is_front_page()) {
      $pagepath = 'homepage';
    } else {
      $pagepath_uri = substr(str_replace('/', '', $_SERVER['REQUEST_URI']), 0, 40);
      $pagepath_e = explode('?', $pagepath_uri);
      $pagepath = $pagepath_e[0];
    }

    if (function_exists('amp_is_request') && amp_is_request()) {
      if (isset($fuse_tags['amp'][$ad_location]['sticky']) && $fuse_tags['amp'][$ad_location]['sticky']) {
        $html .= '<amp-sticky-ad layout="nodisplay">';
      }
      $html .= '<amp-ad
        width=' . $fuse_tags['amp'][$ad_location]['width']
        . ' height=' . $fuse_tags['amp'][$ad_location]['height']
        . ' type="doubleclick"'
        . ' data-slot="' . $fuse_tags['amp']['network_id'] . $fuse_tags['amp'][$ad_location]['slot'] . '"'
        . '></amp-ad>';
      if (isset($fuse_tags['amp'][$ad_location]['sticky']) && $fuse_tags['amp'][$ad_location]['sticky']) {
        $html .= '</amp-sticky-ad>';
      }
      return $html;
    } else {

      if (in_array($ad_location, ['mrec1', 'mrec_1'])) {
        $ad_location = 'rail1';
      } elseif (in_array($ad_location, ['mrec2', 'mrec_2'])) {
        $ad_location = 'rail2';
      }

      $fuse_id = null;

      $post_type = get_post_type(get_the_ID());

      $section = 'homepage';
      if (is_home() || is_front_page()) {
        $section = 'homepage';
      } elseif (is_category()) {
        $term = get_queried_object();
        if ($term) {
          $category_parent_id = $term->category_parent;
          if ($category_parent_id != 0) {
            $category_parent = get_term($category_parent_id, 'category');
            $category = $category_parent->slug;
          } else {
            $category = $term->slug;
          }
        }
        $section = 'category';
      } elseif (is_archive()) {
        $section = 'category';
      } elseif (in_array($post_type, ['post', 'pmc_list'])) {
        $section = 'article';
        if ($slot_no == 2) {
          $section = 'second_article';
        }

        $categories = get_the_category($post_id);
        if ($categories) {
          foreach ($categories as $category_obj) :
            $category = $category_obj->slug;
            break;
          endforeach;
        }
      }

      if (isset($section)) {
        if (!isset($fuse_tags[$section][$ad_location])) {
          return;
        }
        $fuse_id = $fuse_tags[$section][$ad_location];
      } else {
        $fuse_id = $fuse_tags[$ad_location];
      }
      /**
       * Temporary Placeholders
       */
      $width = 300;
      $height = 250;

      if (
        'leaderboard' == $ad_location
        ||
        (strpos($ad_location, 'incontent') !== FALSE and 'homepage' == $section)
      ) {
        $width = 970;
      } elseif (
        'vrec_2' == $ad_location
        || ('vrec' == $ad_location && 'article' == $section)
      ) {
        $height = 600;
      }
      $html = '<div class="fuse-ad d-flex ' . $ad_location . ' ' . $section . '" style="background-color: #ccc;"><h2>Ad: ' . $ad_location . '</h2></div>';
      /**
       * Temporary Placeholders
       */

      // $html .= '<!--' . $post_id . ' | '  . $section . ' | ' . $ad_location . ' | ' . $slot_no . '-->';
      $html .= '<div data-fuse="' . $fuse_id . '" class="fuse-ad"></div>';

      if ($slot_no > 1) {
        $html .= '<script>
      fusetag.que.push(function(){
        fusetag.loadSlotById("' . $fuse_id . '");
       });
       </script>';
      } else {
        $html .= '<script type="text/javascript">';
        if (isset($category)) {
          $html .= 'fusetag.setTargeting("fuse_category", ["' . $category . '"]);';
        }
        if (isset($pagepath)) {
          $html .= 'fusetag.setTargeting("pagepath", ["' . $pagepath . '"]);';
        }
        $html .= '</script>';
      }

      return $html;
    }
  }

  private static function fuse_tags()
  {
    return [
      'amp' => [
        'network_id' => '/22071836792/SSM_themusicnetwork/',
        'header' => [
          'width' => 320,
          'height' => 50,
          'slot' => 'amp_header',
        ],
        'mrec_1' => [
          'width' => 300,
          'height' => 250,
          'slot' => 'amp_mrec_1',
        ],
        'mrec_2' => [
          'width' => 300,
          'height' => 250,
          'slot' => 'amp_mrec_2',
        ],
        'sticky_footer' => [
          'width' => 320,
          'height' => 50,
          'slot' => 'amp_sticky_footer',
          'sticky' => true
        ]
      ],
      'article' => [
        'skin' =>   '22693233910',
        'leaderboard' =>   '22693233901',
        'mrec' =>   '22693233913',
        'vrec' =>   '22693233919',
        'incontent_1' =>   '22693233925',
        'incontent_2' =>   '22693233916',
      ],
      'second_article' => [
        'skin' =>   '22693233910',
        'leaderboard' =>   '22693233901',
        'mrec' =>   '22693233913',
        'vrec' =>   '22693233919',
        'incontent_1' =>   '22693233925',
        'incontent_2' =>   '22693233916',
      ],
      'category' => [
        'skin' =>   '22693233889',
        'leaderboard' =>   '22693233907',
        'mrec' => '22693555511',
        'vrec_1' => '22693555511',
        'vrec' =>   '22693233895',
        'vrec_2' =>   '22693233895',
      ],
      'homepage' => [
        'skin' => '22693233868',
        'leaderboard' =>   '22693233877',
        'vrec_1' =>   '22693554851',
        'vrec_2' =>   '22693233871',
        'vrec_3' =>   '22693233886',
        'vrec_4' =>   '22693233880',
        'vrec_5' =>   '22693233883',
        'vrec_7' =>   '22339226179',
        'vrec_6' =>   '22339066310',

        'incontent_1' =>   '22693233874',
        'incontent_2' =>   '22693555508',
        'incontent_3' =>   '22693233904',
        'incontent_4' =>   '22693233892',
        'incontent_5' =>   '22693233898',
        'incontent_6' =>   '22339066325',
      ]
    ];
  }
}

TBMAds::get_instance();
