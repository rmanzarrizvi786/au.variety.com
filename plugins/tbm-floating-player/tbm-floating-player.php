<?php

/**
 * Plugin Name: TBM Floating Player
 * Plugin URI: https://thebrag.media/
 * Description:
 * Version: 1.0.0
 * Author: Sachin Patel
 * Author URI:
 */

namespace TBM;

class FloatingPlayer
{
  protected $playerId;
  protected $playlistId;
  protected $playerTitle;

  public function __construct()
  {
    $this->playerId = 'x9m1x';

    $this->playlistId = 'x6mqi7';

    try {
      $response = wp_remote_get('https://thebrag.com/wp-json/tbm/floating_dailymotion_playlist_id');
      if ((!is_wp_error($response)) && (200 === wp_remote_retrieve_response_code($response))) {
        $responseBody = json_decode($response['body']);
        if (json_last_error() === JSON_ERROR_NONE) {
          $this->playlistId = $responseBody;
        }
      }
    } catch (\Exception $ex) {
      $this->playlistId = 'x6mqi7';
    }

    $this->playerTitle = "Editor's Picks";

    add_action('wp_footer', [$this, 'wp_footer']);
  }

  public function wp_footer()
  {
    if (!is_single())
      return;
?>
    <style>
      #floating-player-wrap {
        right: 0;
        bottom: 0;
        box-sizing: border-box;
        display: none;
        background-color: #fff;
        border-radius: 2px;
        box-shadow: 0 0 20px 0 rgb(0 0 0 / 25%);
        padding: 0 .5rem .5rem;
        position: fixed;
        width: 415px;
        max-width: 100%;
        height: auto;
        z-index: 5000009;
        margin: 0;
      }

      .floating-player-title {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 13px;
        line-height: 36px;
        min-height: 36px;
        padding: 0 46px 0 0;
      }

      .floating-player-close {
        top: 0;
        background: none;
        color: #000;
        font-size: 18px;
        width: 36px;
        line-height: 32px;
        min-height: 36px;
        border-radius: 0;
        border: 1px solid #fff;
        cursor: pointer;
        position: absolute;
        right: 0;
        text-align: center;
        z-index: 899;
      }

      @media(min-width: 48rem) {
        #floating-player-wrap {
          right: 20px;
          bottom: 20px;
          display: block;
        }
      }
    </style>
    <div id="floating-player-wrap" style="display: none">
      <div class="floating-player-title">
        <?php echo $this->playerTitle; ?>
        <span class="floating-player-close" style="display: inline;">x</span>
      </div>
      <script src="https://geo.dailymotion.com/libs/player/<?php echo $this->playerId; ?>.js"></script>
      <div id="floating-player"></div>

      <script>
        jQuery(document).ready(function($) {
          if (screen.width >= 768) {

            $('.floating-player-close').on('click', function() {
              $('#floating-player-wrap').detach();
            })
            dailymotion
              .createPlayer("floating-player", {
                playlist: "<?php echo $this->playlistId; ?>",
              })
              .then((player) => {
                $('#floating-player-wrap').show();
              })
              .catch((e) => console.error(e));
          }

        });
      </script>
    </div>
<?php
  } // wp_footer();
}

new FloatingPlayer();
