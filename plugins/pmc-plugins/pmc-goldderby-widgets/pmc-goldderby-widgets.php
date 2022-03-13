<?php
/**
 * Class containing PMC GoldDerby widgets.
 * Showing odds for specific league and category
 */
namespace PMC\GD\Widget;

class PMC_Goldderby_Widgets extends \WP_Widget {

	const WIDGET_ID = 'pmc_goldderby_widgets';
	const GOLDDERBY_BASE_SITE_URL = 'http://www.goldderby.com';
	const FEATURED_LEAGUE_FEED_URL = '/feed/gd-league/';
	const FEATURED_LEAGUE_CATEGORY_FEED_URL = '/feed/gd-category/';
	const USER_TYPE_FEED_URL = '/feed/gd-user-type/';
	const PREDICTIONS_FEED_URL = '/feed/predictions/';

	/*
	 * Defines the widget name
	 */
	public function __construct() {
		// Instantiate the parent object
		parent::__construct( self::WIDGET_ID, __( 'Goldderby Prediction Odds Offsite Widget', 'pmc-goldderby' ), array(
			'description' => 'Render prediction odds other sites' ,
			'classname'   => 'pmc-goldderby-widgets',
		) );

	} // __construct

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if ( empty( $instance ) ) {
			return false;
		}
		
		if ( defined( 'PMC_SITE_NAME' ) && 'tvline' == PMC_SITE_NAME ) {
			// CSS for tvline.com
			wp_enqueue_style( 'gd-prediction-offsite-widget', plugin_dir_url( __FILE__ ) . 'css/goldderby-offsite-predictions-tvline.css' );
		} else {
			// Generic CSS
			wp_enqueue_style( 'gd-prediction-offsite-widget', plugin_dir_url( __FILE__ ) . 'css/goldderby-offsite-predictions.css' );
		}

		//Get predictions data as feed for given featured league, category and user type
		$predictions_feed_url = self::GOLDDERBY_BASE_SITE_URL . self::PREDICTIONS_FEED_URL . $instance['featured_league'] . '/' . $instance['featured_league_category'] . '/' . $instance['user_type'] . '/';

		$cache_key = md5( 'gd_offsite_widget_prediction_odds_460' . $instance['featured_league'] . '_' . $instance['featured_league_category'] . '_' . $instance['user_type'] );
		$prediction_data = wp_cache_get( $cache_key, 'pmc-goldderby' );
		if ( empty( $prediction_odds ) || false === $prediction_odds ) {
			$predictions_feed = fetch_feed( $predictions_feed_url );
			$prediction_data = $this->get_prediction_odds_from_rss_feed( $predictions_feed, $instance );
			wp_cache_set( $cache_key, $prediction_data, 'pmc-goldderby', 1 * HOUR_IN_SECONDS );
		}
		$prediction_odds = $prediction_data['prediction_odds'];
		$total_no_of_candidates = $prediction_odds['total_no_of_candidates'];
		$user_type = 'Users';
		if( 'expert' === $instance['user_type'] ){
			$user_type = 'Experts';
		} elseif( 'editor' === $instance['user_type'] ){
			$user_type = 'Editors';
		}

		echo wp_kses_post( $args['before_widget'] );

		if ( defined( 'PMC_SITE_NAME' ) && 'tvline' == PMC_SITE_NAME ) {
			// Template for tvline.com
			$template = __DIR__ . '/template/widget-tvline.php';
		} else {
			// Generic template
			$template = __DIR__ . '/template/widget.php';
		}

		$template = apply_filters( 'pmc_goldderby_widgets_template', $template );
		
		echo \PMC::render_template( $template, array(
			'widget_data' => $prediction_odds,
			'total_no_of_candidates' => $total_no_of_candidates,
			'user_type' => $user_type,
			'instance' => $instance,
			'args' => $args,
			'slug' => trim($prediction_data['slug'] )
		) );

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 *
	 * @return ""
	 */
	public function form( $instance ) {

		wp_enqueue_script( 'gd-prediction-offsite-widget', plugin_dir_url( __FILE__ ) . 'js/script.js', array( 'jquery' ) );

		//Get featured league feed and prepare data
		$leagues_feed_url = self::GOLDDERBY_BASE_SITE_URL . self::FEATURED_LEAGUE_FEED_URL;
		$featured_leagues_feed = fetch_feed( $leagues_feed_url );
		$featured_leagues = $this->get_featured_league_from_rss_feed( $featured_leagues_feed );

		//Get user type feed
		$user_type_feed_url = self::GOLDDERBY_BASE_SITE_URL . self::USER_TYPE_FEED_URL;
		$user_type_feed = fetch_feed( $user_type_feed_url );
		$user_types = $this->get_user_type_from_rss_feed( $user_type_feed );

		//Get categories if featured league is already set
		if( ! empty( $instance['featured_league'] ) ) {

			//Get featured league feed and prepare data
			$category_feed_url = self::GOLDDERBY_BASE_SITE_URL . self::FEATURED_LEAGUE_CATEGORY_FEED_URL . $instance['featured_league'] . '/';
			$featured_league_category_feed = fetch_feed( $category_feed_url );
			$featured_league_categories = $this->get_league_categories_from_rss_feed( $featured_league_category_feed );
		}

		require( 'template/setting-form.php' );
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {

		$new_instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] );
		$new_instance['featured_league'] = sanitize_text_field( $new_instance[ 'featured_league' ] );
		$new_instance['featured_league_category'] = sanitize_text_field( $new_instance[ 'featured_league_category' ] );
		$new_instance['user_type'] = sanitize_text_field( $new_instance[ 'user_type' ] );
		$new_instance['widget_width'] = sanitize_text_field( $new_instance[ 'widget_width' ] );
		$new_instance['total_candidates'] = sanitize_text_field( $new_instance[ 'total_candidates' ] );
		
		return $new_instance;
	}
	
	
	/**
	 * Getting featured league rss feed and Processing data
	 *
	 * @param array $featured_leagues_feed data
	 */
	public function get_featured_league_from_rss_feed( $featured_leagues_feed ) {
		//prepare feed data
		$featured_leagues = array();
		if (!is_wp_error( $featured_leagues_feed ) ) :
			$maxitems = $featured_leagues_feed->get_item_quantity(0);
			$rss_items = $featured_leagues_feed->get_items(0, $maxitems );
			if ($rss_items):
				foreach ( $rss_items as $item ) :
					$id = $item->get_item_tags('','id')[0]['data'];
					$title = $item->get_item_tags('','title')[0]['data'];
					$featured_leagues[ $id ] = $title;
				endforeach;
			endif;
		endif;
		
		return $featured_leagues;
	}
	
	/**
	 * Getting user type rss feed and Processing data
	 *
	 * @param array $user_type_feed data
	 */
	public function get_user_type_from_rss_feed( $user_type_feed ) {
		//prepare feed data
		$user_types = array();
		if (!is_wp_error( $user_type_feed ) ) :
			$maxitems = $user_type_feed->get_item_quantity(0);
			$rss_items = $user_type_feed->get_items(0, $maxitems );
			if ($rss_items):
				foreach ( $rss_items as $item ) :
					$user_types[ $item->get_item_tags('','user_value')[0]['data'] ] = $item->get_item_tags('','title')[0]['data'];
				endforeach;
			endif;
		endif;
		
		return $user_types;
	}
	
	
	/**
	 * Getting league category rss feed and Processing data
	 *
	 * @param array $rss_feed_url Feed url
	 */
	public function get_league_categories_from_rss_feed( $featured_league_category_feed ) {
		$featured_league_categories = array();
		if (!is_wp_error( $featured_league_category_feed ) ) :
			$maxitems = $featured_league_category_feed->get_item_quantity(0);
			$rss_items = $featured_league_category_feed->get_items(0, $maxitems );
			if ($rss_items):
				foreach ( $rss_items as $item ) :
					$featured_league_categories[ $item->get_item_tags('','id')[0]['data'] ] = $item->get_item_tags('','title')[0]['data'];
				endforeach;
			endif;
		endif;
		
		return $featured_league_categories;
	}
	
	/**
	 * Getting league category rss feed and Processing data
	 *
	 * @param array $predictions_feed data and $instance i.e. widget settings
	 */
	public function get_prediction_odds_from_rss_feed( $predictions_feed, $instance ) {

		$display_no_of_candidates = $instance['total_candidates'];
		$prediction_data = array();
		$prediction_odds = array();
		$counter = 1;
		if ( !is_wp_error( $predictions_feed ) ) :
			$maxitems = $predictions_feed->get_item_quantity(0);
			$rss_items = $predictions_feed->get_items(0, $maxitems );
			$slug = $predictions_feed->get_channel_tags('', 'slug')[0]['data'];
			if ($rss_items) {
				foreach ( $rss_items as $item ) :
					if ( $counter > $display_no_of_candidates ) {
						break;
					}
					$prediction_odds['data'][ $counter ]['candidate_title']         = $item->get_item_tags( '', 'candidate_title' )[0]['data'];
					$prediction_odds['data'][ $counter ]['related_candidate_title'] = $item->get_item_tags( '', 'related_candidate_title' )[0]['data'];
					$prediction_odds['data'][ $counter ]['image']                   = $item->get_item_tags( '', 'image' )[0]['data']; //@codeCoverageIgnore
					$odds_index                                                     = 'odds_' . $instance['user_type'] . '_odds';
					$prediction_odds['data'][ $counter ]['odds']                    = $item->get_item_tags( '', $odds_index )[0]['data'];
					$prediction_odds['category_name']                               = $item->get_item_tags( '', 'category_name' )[0]['data'];
					$total_votes_index                                              = 'odds_' . $instance['user_type'] . '_total_votes';
					$prediction_odds['total_no_of_candidates']                      = $item->get_item_tags( '', $total_votes_index )[0]['data'];
					$prediction_odds['total_no_of_candidates']                      = $item->get_item_tags( '', $total_votes_index )[0]['data'];
					$movement_name                                                  = 'odds_' . $instance['user_type'] . '_movement';
					$prediction_odds['data'][ $counter ]['movement']                = $item->get_item_tags( '', $movement_name )[0]['data'];
					$counter ++;
				endforeach;
			}
		endif;
		$prediction_data['slug'] = $slug;
		$prediction_data['prediction_odds'] = $prediction_odds;
		return $prediction_data;
	}

}

add_action( 'widgets_init', function () {
	register_widget( 'PMC\GD\Widget\PMC_Goldderby_Widgets' );
} );

//EOF
