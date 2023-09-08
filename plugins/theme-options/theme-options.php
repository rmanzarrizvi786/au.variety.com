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

add_action('rest_api_init', 'tbm_theme_options_rest_api_init');

function tbm_theme_options_rest_api_init()
{
    register_rest_route('tbm', '/latest', array(
        'methods' => 'GET',
        'callback' => 'rest_get_latest',
        'permission_callback' => '__return_true',
    ));
}

function rest_get_latest()
{
    global $post;

    $trending_story_args = [
        'post_status' => 'publish',
        'posts_per_page' => 1,
    ];
    if (get_option('most_viewed_yesterday')) {
        $trending_story_args['p'] = get_option('most_viewed_yesterday');
    }
    $trending_story_query = new WP_Query($trending_story_args);
    if ($trending_story_query->have_posts()) :
        while ($trending_story_query->have_posts()) :
            $trending_story_query->the_post();
            $trending_story_ID = get_the_ID();
            $exclude_posts[] = $trending_story_ID;
            $args['exclude_posts'][] = $trending_story_ID;
        endwhile;
        wp_reset_query();
    endif;

    $posts_per_page = 6;
    $news_args = array(
        'post_status' => 'publish',
        'post_type' => array('post', 'snaps', 'dad'),
        'ignore_sticky_posts' => 1,
        'post__not_in' => $exclude_posts,
        'posts_per_page' => $posts_per_page,
    );
    $news_query = new WP_Query($news_args);
    $no_of_columns = 2;
    if ($news_query->have_posts()) :
        $count = 1;
        $articles_arr = [
            'read_more' => [
                'title' => 'Read More',
                'link' => 'https://au.variety.com',
            ],
            'articles' => []
        ];

        while ($news_query->have_posts()) :
            $news_query->the_post();
            $post_id = get_the_ID();

            $category = '';

            if ('snaps' == $post->post_type) :
                $category = 'GALLERY';
            elseif ('dad' == $post->post_type) :
                $categories = get_the_terms(get_the_ID(), 'dad-category');
                if ($categories) :
                    if ($categories[0] && 'Uncategorised' != $categories[0]->name) :
                        $category = $categories[0]->name;
                    elseif (isset($categories[1])) :
                        $category = $categories[1]->name;
                    else :
                    endif; // If Uncategorised 
                endif; // If there are Dad categories 
            else :
                $categories = get_the_category();
                if ($categories) :
                    if (isset($categories[0]) && 'Evergreen' != $categories[0]->cat_name) :
                        if (0 == $categories[0]->parent) :
                            $category = $categories[0]->cat_name;
                        else : $parent_category = get_category($categories[0]->parent);
                            $category = $parent_category->cat_name;
                        endif;
                    elseif (isset($categories[1])) :
                        if (0 == $categories[1]->parent) :
                            $category = $categories[1]->cat_name;
                        else : $parent_category = get_category($categories[1]->parent);
                            $category = $parent_category->cat_name;
                        endif;
                    endif; // If Evergreen 
                endif; // If there are Dad categories 
            endif; // If Photo Gallery 
            
            // Image
            // Title
            // Brand logo
            // Brand Link
            // Excerpt
            // Link

            $image = '' !== get_the_post_thumbnail() ? get_the_post_thumbnail_url() : '';
            $metadesc = get_post_meta(get_the_ID(), '_yoast_wpseo_metadesc', true);
            $excerpt = tbm_the_excerpt( $metadesc );

            $articles_arr['articles'][] = [
                'image' => $image,
                'title' => get_the_title(),
                'category' => $category,
                'brand_logo' => 'https://images.thebrag.com/common/brands/The-Brag_combo-light.svg',
                'brank_link' => 'https://thebrag.com',
                'excerpt' => $excerpt,
                'link' => get_the_permalink(),
            ];
            
            $count++;
        endwhile;
    endif;

    return $articles_arr;
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
