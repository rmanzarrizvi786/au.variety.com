<?php

/**
 * Plugin Name: Theme Options
 * Plugin URI: https://thebrag.media/
 * Description: Theme Options
 * Version: 1.0.0
 * Author: Sachin Patel
 * Author URI: https://thebrag.media/
 */

add_action('admin_menu', 'tbm_theme_options_plugin_menu');
function tbm_theme_options_plugin_menu()
{
    add_menu_page('Theme Options', 'Theme Options', 'edit_pages', 'tbm_theme_options', 'tbm_theme_options');
}

function tbm_theme_options()
{
    wp_enqueue_style('bs', 'https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css');

    /*
     * Save options
     */

    if (isset($_POST['force_most_viewed'])) :
        $force_most_viewed = absint($_POST['force_most_viewed']);
        if ($force_most_viewed > 0) :
            update_option('force_most_viewed', absint($_POST['force_most_viewed']));
            update_option('most_viewed_yesterday', absint($_POST['force_most_viewed']));
        else :
            update_option('force_most_viewed', '');
        endif;
    endif; // force_most_viewed

    if (isset($_POST) && count($_POST) > 0) :
        foreach ($_POST as $key => $value) :
            if (strpos($key, 'tbm_') !== false) :
                update_option($key, sanitize_text_field($value));
            endif;
        endforeach;
        echo '<div class="alert alert-success">Options have been saved!</div>';
    endif;
?>
    <style>
        label.reset {
            background: #ccc;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            color: #fff;
            text-align: center;
        }
    </style>

    <div class="container-fluid">
        <h1>Theme Options</h1>
        <form method="post" class="form">

            <div class="row">
                <div class="col-12 col-md-6">
                    <h3>Featured Article for Infinite Scroll</h3>

                    <div class="form-group">
                        <label>Post ID</label>
                        <label class="reset">x</label>
                        <input name="tbm_featured_infinite_ID" id="tbm_featured_infinite_ID" type="text" value="<?php echo stripslashes(get_option('tbm_featured_infinite_ID')); ?>" placeholder="" class="form-control">
                    </div>
                </div>
                <!-- Featured Article for Infinite Scroll ID -->

                <div class="col-12 col-md-6">
                    <h3>DailyMotion Player</h3>

                    <div class="form-group">
                        <label>Player ID</label>
                        <label class="reset">x</label>
                        <input name="tbm_floating_dm_player_id" id="tbm_floating_dm_player_id" type="text" value="<?php echo get_option('tbm_floating_dm_player_id'); ?>" placeholder="" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Playlist ID</label>
                        <label class="reset">x</label>
                        <input name="tbm_floating_dm_playlist_id" id="tbm_floating_dm_playlist_id" type="text" value="<?php echo get_option('tbm_floating_dm_playlist_id'); ?>" placeholder="" class="form-control">
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <h3>GAM Ad Unit</h3>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>ID</label>
                                <label class="reset">x</label>
                                <input name="tbm_gam_ad_unit_id" id="tbm_gam_ad_unit_id" type="text" value="<?php echo get_option('tbm_gam_ad_unit_id'); ?>" placeholder="" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="col-12 col-md-6">
                    <h3>Force Trending on Home page</h3>

                    <div class="form-group">
                        <label>Post ID</label>
                        <label class="reset">x</label>
                        <input name="force_most_viewed" id="force_most_viewed" type="number" value="<?php echo stripslashes(get_option('force_most_viewed')); ?>" placeholder="" class="form-control">
                    </div>
                </div> -->
                <!-- Force Trending on Home page -->
            </div>

            <div class="row">
                <div class="col-12">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
                </div>
            </div>
        </form>
    </div>
<?php
}
