<?php
namespace PMC\Facebook_Instant_Articles;

use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Transformer\Transformer;
use PMC\Global_Functions\Traits\Singleton;
use Instant_Articles_Post;

class Plugin {
	use Singleton;

	private $_is_wp_cli     = false;
	private $_rules         = [];
	private $_embed_scripts = [];

	protected function __construct() {
		$this->_is_wp_cli = defined( 'WP_CLI' ) && WP_CLI;
		$this->_setup_hooks();
		$this->_add_wp_cli();
	}

	protected function _setup_hooks() {
		add_action( 'init', [ $this, 'action_init' ] );

		// Adding late bind action to fix fbia supported post types filter to fix problem with theme
		// modifying post types in pre_get_posts without checking is_feed().
		add_action( 'pre_get_posts', [ $this, 'fix_pre_get_posts_post_type_for_feed' ], 15 );
		add_filter( 'instant_articles_should_submit_post', [ $this, 'filter_instant_articles_should_submit_post' ], 10, 2 );
		add_filter( 'instant_articles_transformed_element', [ $this, 'filter_instant_articles_transformed_element' ] );
	}

	/**
	 * Return the support post types for instant articles
	 * @return array
	 */
	public function get_supported_post_types() : array {
		return (array) apply_filters( 'instant_articles_post_types', [ 'post' ] );
	}

	/**
	 * Prevent non supported post types from submitting to fbia
	 *
	 * @param bool $should_submit
	 * @param Instant_Articles_Post $fbia_post
	 * @return bool
	 */
	public function filter_instant_articles_should_submit_post( $should_submit, Instant_Articles_Post $fbia_post ) : bool {
		if ( ! in_array( get_post_type( $fbia_post->get_the_id() ), (array) $this->get_supported_post_types(), true ) ) {
			$should_submit = false;
		}
		return (bool) $should_submit;
	}

	/**
	 * Fix the post types filter for fbia feed queries
	 *
	 * @see function instant_articles_query()
	 *
	 * @param \WP_Query $query
	 */
	public function fix_pre_get_posts_post_type_for_feed( $query ) {
		if ( $query->is_main_query() && $query->is_feed( INSTANT_ARTICLES_SLUG ) ) {
			$query->set( 'post_type', $this->get_supported_post_types() );
		}
	}

	public function action_init() {
		add_filter( 'instant_articles_transformer_custom_rules_loaded', [ $this, 'filter_instant_articles_transformer_custom_rules_loaded' ] );
		add_filter( 'pmc_ecommerce_source', [ $this, 'filter_pmc_ecommerce_source' ] );

		$this->fix_compat();
	}

	protected function _add_wp_cli() {
		if ( $this->_is_wp_cli ) {
			// Once time used script, we do not want to add unit test for this file
			require_once( __DIR__ . '/wp-cli/fbia.php' ); // @codeCoverageIgnore
		}
	}

	public function fix_compat() {
		// We need to fire these code after co-author-plugins loaded in order for fbia to load properly.
		if ( function_exists( 'instant_articles_load_textdomain' ) ) {
			instant_articles_load_textdomain();
		}
		if ( function_exists( 'instant_articles_load_compat' ) ) {
			instant_articles_load_compat();
		}

		// If class Instant_Articles_Co_Authors_Plus doesn't exist, compat.php was loaded improperly
		if ( ! class_exists( 'Instant_Articles_Co_Authors_Plus', false ) ) {
			// Fallback to our own implementation, can't do code coverage because we cannot unload the class
			add_filter( 'instant_articles_authors', [ $this, 'filter_instant_articles_authors' ], 10, 2 ); // @codeCoverageIgnore
		}
	}

	/**
	 * Filter the authors.
	 *
	 * @see Instant_Articles_Co_Authors_Plus::authors()
	 *
	 * @param array $authors The current authors.
	 * @param int   $post_id The current post ID.
	 */
	public function filter_instant_articles_authors( $authors, $post_id ) {
		if ( function_exists( 'get_coauthors' ) ) {
			$coauthors = get_coauthors( $post_id );

			$authors = [];
			foreach ( $coauthors as $coauthor ) {

				$author                = new \stdClass();
				$author->ID            = $coauthor->ID;
				$author->display_name  = is_a( $coauthor, 'WP_User' ) ? $coauthor->data->display_name : $coauthor->display_name;
				$author->first_name    = $coauthor->first_name;
				$author->last_name     = $coauthor->last_name;
				$author->user_login    = is_a( $coauthor, 'WP_User' ) ? $coauthor->data->user_login : $coauthor->user_login;
				$author->user_nicename = is_a( $coauthor, 'WP_User' ) ? $coauthor->data->user_nicename : $coauthor->user_nicename;
				$author->user_email    = is_a( $coauthor, 'WP_User' ) ? $coauthor->data->user_email : $coauthor->user_email;
				$author->user_url      = is_a( $coauthor, 'WP_User' ) ? $coauthor->data->user_url : $coauthor->website;
				$author->bio           = $coauthor->description;

				$authors[] = $author;
			}
		}

		return $authors;
	}

	/**
	 * Filter to override the ecomm tracking source to instant-articles within FBIA
	 * @param string $source
	 * @return mixed|string
	 */
	public function filter_pmc_ecommerce_source( $source ) {
		if ( $this->is_rendering_content() ) {
			$source = 'instant-articles';
		}
		return $source;
	}

	/**
	 * Helper function to determine if we're currently rendering FBIA article
	 *
	 * @return bool
	 */
	public function is_rendering_content() : bool {
		return function_exists( 'is_transforming_instant_article' ) && is_transforming_instant_article();
	}

	/**
	 * Helper function to add custom fbia rules
	 * eg.
	 * [
	 *    'selector' => 'PassThroughRule',
	 * ]
	 *
	 * @param array $args
	 * @return $this
	 */
	public function add_rules( array $args ) : self {
		foreach ( $args as $selector => $class ) {
			// Make sure the rules set are unique
			$this->_rules[ sprintf( '%s=>%s', $selector, $class ) ] = [
				'class'    => $class,
				'selector' => $selector,
			];
		}

		return $this; // Return $this to allow chaining calls
	}

	/**
	 * Filter to load custom facebook instant articles rules
	 * @param Transformer $transformer
	 * @return Transformer
	 */
	public function filter_instant_articles_transformer_custom_rules_loaded( Transformer $transformer ) {

		// Save the default rules
		$save_rules = $this->_rules;

		/**
		 * Signal action to allow additional rules to be added
		 */
		do_action( 'pmc_fbia_load_rules', $this );

		if ( ! empty( $this->_rules ) ) {
			$json = wp_json_encode(
				[
					'rules' => array_values( $this->_rules ),
				]
			);
			$transformer->loadRules( $json );
		}

		// Restore the default rules, rules added in pmc_fbia_load_rules action should not be carried over
		$this->_rules = $save_rules;

		return $transformer;
	}

	/**
	 * Filter to attach our custom embed scripts
	 *
	 * @param InstantArticle $instant_article
	 * @return InstantArticle
	 */
	public function filter_instant_articles_transformed_element( InstantArticle $instant_article ) {

		// Save the default embed scripts
		$saved_scripts = $this->_embed_scripts;

		/**
		 * Signal action to allow additional scripts to be injected
		 */
		do_action( 'pmc_fbia_embed_scripts', $this );

		foreach ( $this->_embed_scripts as $script ) {
			$fbia_embed = \Facebook\InstantArticles\Elements\Analytics::create()->withHTML( $script );
			$instant_article->addChild( $fbia_embed );
		}

		// Restore the default embed scripts, scripts added in pmc_fbia_embed_scripts action should not be carried over
		$this->_embed_scripts = $saved_scripts;

		return $instant_article;
	}

	/**
	 * Add embed scripts to FBIA
	 *
	 * @param string|array $scripts String or array of strings contains the valid full <script> tag
	 * @return $this
	 */
	public function add_embed_script( $scripts ) : self {
		if ( is_string( $scripts ) ) {
			$scripts = [ $scripts ];
		}
		foreach ( $scripts as $script ) {
			$script = trim( $script );
			if ( ! empty( $script ) ) {
				// Make sure the scripts are unique
				$this->_embed_scripts[ hash( 'sha256', $script ) ] = $script;
			}
		}

		return $this; // Return $this to allow chaining calls
	}
}
