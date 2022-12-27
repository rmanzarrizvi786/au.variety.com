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

class FloatingPlayer {
    protected $playerId;
    protected $playlistId;
    protected $playerTitle;

    public function __construct() {
        $this->playerId = get_option('tbm_floating_dm_player_id', '');
        $this->playlistId = get_option('tbm_floating_dm_playlist_id', '');
        $this->adId = get_option('tbm_gam_ad_unit_id', '');
        $this->playerTitle = "Editor's Picks";

        add_action('wp_footer', [$this, 'wp_footer']);
    }

    public function wp_footer() {
        if (!is_single()) {
            return;
        }
        if (!$this->playerId || '' == trim($this->playerId)) {
            return;
        }
        if (!$this->playlistId || '' == trim($this->playlistId)) {
            return;
        }
        if (function_exists('get_field') && (get_field('disable_ads', $post->id) || get_field('disable_ads_in_content'))) {
            return;
        }
        if (function_exists('get_field') && (get_field('disable_player', $post->id))) {
            return;
        }
?>
<style>
    #floating-player-wrap {
        right: 0;
        bottom: 65px;
        box-sizing: border-box;
        display: none;
        background-color: #fff;
        border-radius: 2px;
        box-shadow: 0 0 20px 0 rgb(0 0 0 / 25%);

        position: fixed;
        width: 415px;
        height: auto;
        z-index: 5000009;
        margin: 0;
        display: flex;
        background-color: #2f2d2d;

        max-width: 66%;
        flex-direction: column;
        padding: 0;
    }

    #floating-player-wrap.scrolled {
        top: 0;
        bottom: unset;
        flex-direction: column-reverse;
    }

    .floating-player-title {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 13px;
        line-height: 24px;
        min-height: 24px;
        padding: 0 46px 0 10px;
        position: relative;
        font-family: Graphik, sans-serif;
        color: #fff;
    }

    .floating-player-close {
        top: 0;
        background: none;
        color: #fff;
        font-size: 18px;
        width: 18px;
        line-height: 24px;
        min-height: 24px;
        border-radius: 0;
        border: none;
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
            max-width: unset;
            flex-direction: column;
        }

        #floating-player-wrap.scrolled {
            top: unset;
            bottom: 20px;
        }
    }

    #dailymotion-pip-small-viewport {
        --position-top: 0;
        --position-right: 0;
        max-width: 66%;
        left: unset !important;
        right: 0 !important;
    }
</style>

<div id="floating-player-wrap" style="display: none">
    <div class="floating-player-title">
        <span class="floating-player-close" style="display: inline;">x</span>
    </div>
    <div id="floating-player"></div>
</div>
<script src="https://geo.dailymotion.com/libs/player/<?php echo $this->playerId; ?>.js"></script>
<script>
    jQuery(document).ready(function($) {
        $(window).on('scroll', function() {
            if (screen.width < 768) {
                if ($(window).scrollTop() >= screen.height / 2) {
                    $('#floating-player-wrap').addClass('scrolled')
                } else {
                    $('#floating-player-wrap').removeClass('scrolled')
                }
            }
        })

        $('.floating-player-close').on('click', function() {
            $('#floating-player-wrap').detach()
        })
        dailymotion.createPlayer("floating-player", {
            playlist: '<?php echo $this->playlistId; ?>',
            params: {
                customConfig: {
                    customParams: '/22071836792/<?php echo $this->adId; ?>/preroll'
                }
            }
        })
    .then((player) => {
        $('#floating-player-wrap').show()
        player.setMute(true)
    })
    .catch((e) => console.error(e))
    })
</script>
<?php
    } // wp_footer();
}

new FloatingPlayer();
