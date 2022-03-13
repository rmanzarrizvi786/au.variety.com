<?php
/**
 * This file contains the PMC\Mobile_API\Endpoints\Objects\Article_Object class.
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Objects;

use PMC\Mobile_API\Endpoints\Schema_Definitions\Image;
use PMC\Mobile_API\Route_Registrar;
use stdClass;
use WP_Post;
use WP_Term;

/**
 * Article object.
 */
class Article_Object {

	/**
	 * Post object.
	 *
	 * @var WP_Post
	 */
	public $post;

	/**
	 * Article_Object constructor.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function __construct( WP_Post $post ) {
		$this->post = $post;

		// Iframe handlers.
		add_filter( 'embed_handler_html', [ $this, 'use_iframe_oembeds' ], 10, 3 );
		add_filter( 'embed_oembed_html', [ $this, 'use_iframe_oembeds' ], 10, 3 );
		add_filter( 'protected_embeds_use_form_post', '__return_false' );
	}

	/**
	 * Get post headline.
	 *
	 * @return string
	 */
	public function headline() {

		$post_id = $this->post->ID;

		$title = get_post_meta( $post_id, 'mt_seo_title', true );

		if ( empty( $title ) ) {
			$title = \get_the_title( $post_id );
		}

		return html_entity_decode( $title );
	}

	/**
	 * Get post tagline, aka dek/post_excerpt.
	 *
	 * @return string
	 */
	public function tagline(): string {
		return wp_kses_post( $this->post->post_excerpt ?? '' );
	}

	/**
	 * Get post type.
	 *
	 * @return string
	 */
	public function post_type(): string {
		return wp_kses_post( $this->post->post_type ?? '' );
	}

	/**
	 * Get post body content.
	 *
	 * @return string
	 */
	public function body(): string {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		return \apply_filters( 'the_content', $this->post->post_content ?? '' );
	}

	/**
	 * Get post body preview.
	 *
	 * @return string
	 */
	public function body_preview(): string {
		// Mimic roadblock-teaser without messing with globals.
		if ( ! function_exists( 'pmc_subscription_get_teaser_copy' ) ) {
			return '';
		}

		// Get teaser copy.
		$paragraphs = \pmc_subscription_get_teaser_copy( 1, $this->post->post_content ?? '' );

		return html_entity_decode( wp_kses_post( $paragraphs[0] ?? '' ) );
	}

	/**
	 * Get post published date.
	 *
	 * @return string
	 */
	public function published_at(): string {
		return $this->post->post_date ?? '';
	}

	/**
	 * Get post human time difference.
	 *
	 * @return string
	 */
	public function human_time_diff(): string {

		$date      = strtotime( get_the_date( 'F j, Y g:i a', $this->post->ID ) );
		$time_diff = human_time_diff( $date, current_time( 'timestamp' ) ); // phpcs:ignore

		return sprintf( __( '%s', 'pmc-mobile-api' ), $time_diff ); // phpcs:ignore
	}

	/**
	 * Get post updated date.
	 *
	 * @return string
	 */
	public function updated_at(): string {
		return $this->post->post_modified ?? '';
	}

	/**
	 * Get post featured video.
	 *
	 * @return string
	 */
	public function featured_video(): string {
		$video_url = (string) trim( \get_post_meta( $this->post->ID, 'video_url', true ) );

		if ( empty( $video_url ) ) {
			$video_url = get_post_meta( $this->post->ID, 'pmc_top_video_source', true );
		}

		if ( empty( $video_url ) ) {
			$video_url = get_post_meta( $this->post->ID, '_pmc_featured_video_override_data', true );
		}

		if ( empty( $video_url ) ) {
			return '';
		}

		return $this->get_video_output( $video_url );
	}

	/**
	 * Get post byline.
	 *
	 * @return string
	 */
	public function byline(): string {
		global $coauthors_plus;

		if ( ! is_a( $coauthors_plus, 'coauthors_plus' ) ) {
			return '';
		}

		// Get authors.
		$authors = (array) get_post_meta( $this->post->ID, 'authors', true );

		// Save byline list.
		$byline = '';

		foreach ( $authors as $index => $author ) {
			$author_byline = $coauthors_plus->get_coauthor_by( 'user_login', $author );

			if ( empty( $author_byline ) ) {
				continue;
			}

			$byline .= $author_byline->display_name;

			if ( 2 === count( $authors ) && 0 === $index ) {
				$byline .= ' and ';
			} elseif ( count( $authors ) > 2 && ( count( $authors ) - 2 ) === $index ) {
				$byline .= ', and ';
			} elseif ( count( $authors ) > 2 && $index < count( $authors ) - 2 ) {
				$byline .= ', ';
			}
		}

		return apply_filters( 'pmc_mobile_api_byline', $byline, $this->post->ID );
	}

	/**
	 * Get excerpt.
	 *
	 * @return string
	 */
	public function excerpt(): string {
		$post_id = $this->post->ID;

		if ( ! empty( $post_id ) && ! is_single() ) {

			$dek = get_post_meta( $post_id, 'override_post_excerpt', true );

			if ( ! empty( $dek ) && is_string( $dek ) ) {
				return html_entity_decode( $dek );
			}
		}

		return html_entity_decode( get_the_excerpt( $post_id ) );
	}

	/**
	 * Get post featured image.
	 *
	 * @return array|stdClass
	 */
	public function featured_image() {
		return Image::get_image_from_post( $this->post );
	}

	/**
	 * Get post related content/articles.
	 *
	 * @param array $args Arguments sent to the pmc_related_articles.
	 * @return array
	 */
	public function related_content( $args = [] ): array {

		// Check if PMC function exists.
		if ( ! function_exists( 'pmc_related_articles' ) ) {
			return [];
		}

		$related_posts = \pmc_related_articles( $this->post->ID, $args );

		// Check if we have articles.
		if ( empty( $related_posts ) ) {
			return [];
		}

		// phpcs:disable WordPressVIPMinimum.Variables.VariableAnalysis.UndefinedVariable
		return array_map(
			function( $post_id ) {

				// Save current global post.
				$p = $this->post;

				// Assign related post to global.
				$this->post = \get_post( $post_id );

				// Get post card uses the defined fake global.
				$post_card = $this->get_post_card();

				// Assign the global article back.
				$this->post = $p;

				// Return the post card.
				return $post_card;
			},
			array_values( \wp_list_pluck( $related_posts, 'post_id' ) )
		);
		// phpcs:enable WordPressVIPMinimum.Variables.VariableAnalysis.UndefinedVariable
	}

	/**
	 * Get "Read Next" Post_Card.
	 *
	 * @return array
	 */
	public function read_next(): array {
		global $post;

		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		$old_global_post = $post; // Save global post.
		$post            = $this->post; // Assign it to global.
		$next_post       = wpcom_vip_get_adjacent_post(); // This uses the global post.
		$post            = $old_global_post; // Return the old global back.
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

		if ( empty( $next_post ) ) {
			return [];
		}

		return ( new self( $next_post ) )->get_post_card();
	}

	/**
	 * Get post entitlements.
	 *
	 * @return array
	 */
	public function entitlements(): array {

		// Check required plugin is active.
		if ( ! \class_exists( '\PMC\Subscription_V2\Paywall_Content_Eligibility' ) ) {
			return [];
		}

		// Get entitlements.
		$e      = \PMC\Subscription_V2\Paywall_Content_Eligibility::get_instance();
		$result = $e->get_required_entitlements( $this->post );

		// This is returning an array.
		return $result->entitlements ?? [];
	}

	/**
	 * Get post permalink.
	 *
	 * @return string
	 */
	public function permalink(): string {
		return get_the_permalink( $this->post->ID );
	}

	/**
	 * Get post id.
	 *
	 * @return string
	 */
	public function post_id(): string {
		return $this->post->ID ?? '';
	}

	/**
	 * Get term object from a term ID or WP_Term object.
	 *
	 * @param WP_Term|int $term     Term object or ID.
	 * @param string      $taxonomy Taxonomy slug.
	 * @return array
	 */
	protected function get_term( $term, $taxonomy ): array {

		// Get term object.
		if ( ! is_object( $term ) ) {
			$term = \get_term_by( 'id', $term, $taxonomy );
		}

		if ( ! $term instanceof WP_Term ) {
			return [];
		}

		return ( new Term_Object( $term ) )->get_term();
	}

	/**
	 * Get term object from a post ID.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @param bool   $list     Return the term ID or an array of term IDs.
	 * @return array
	 */
	public function get_term_from_post( $taxonomy, $list = false ): array {
		$terms = \get_the_terms( $this->post, $taxonomy );

		if ( \is_wp_error( $terms ) || ! is_array( $terms ) ) {
			return [];
		}

		if ( $list ) {
			return \wp_list_pluck( $terms, 'term_id' );
		}

		return $this->get_term( current( $terms ), $taxonomy );
	}

	/**
	 * Get term items.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @return array
	 */
	protected function get_term_items( $taxonomy ): array {
		return array_map(
			function( $term_id ) use ( $taxonomy ) {
				// phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UndefinedVariable
				return $this->get_term( $term_id, $taxonomy );
			},
			$this->get_term_from_post( $taxonomy, true ) ?? []
		);
	}

	/**
	 * Get post category term.
	 *
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	public function category( $taxonomy = 'category' ): array {
		return $this->get_term_from_post( $taxonomy );
	}

	/**
	 * Get subcategory.
	 * @return array
	 */
	public function subcategory(): array {
		$post_id = $this->post->ID;

		return $this->get_subcategory_by_id( $post_id );
	}

	/**
	 * Get subcategory by id.
	 *
	 * @param int  $post_id Post id.
	 *
	 * @param bool $name    True returns name.
	 *
	 * @return array|string
	 */
	public function get_subcategory_by_id( $post_id, $name = false ) {
		$subcategory_id = get_post_meta( $post_id, 'subcategories', true );

		$term = get_term_by( 'id', $subcategory_id, 'category' );

		if ( ! $term instanceof \WP_Term ) {
			if ( $name ) {
				return '';
			} else {
				return [];
			}
		}

		if ( $name ) {
			return $term->name;
		} else {
			return ( new Term_Object( $term ) )->get_term();
		}
	}

	/**
	 * Get post tag terms.
	 *
	 * @return array
	 */
	public function tags(): array {
		return $this->get_term_items( 'post_tag' );
	}

	/**
	 * Wrapper for video output.
	 *
	 * @param string $video Video url or shortcode.
	 * @return string
	 */
	public function get_video_output( $video ): string {

		// Check and handle shortcodes, JW Players, first.
		if ( 0 === strpos( $video, '[' ) ) {
			$shortcode_output = \do_shortcode( $video );

			// Try to get the video ID with the media and player.
			if ( preg_match( '/id=\'(.*?)\'/i', $shortcode_output, $output ) ) {

				if ( empty( $output[1] ) ) {
					return $shortcode_output;
				}

				// Get the unique media and player ID.
				// This is better than hardcoding since we can get players overriden from the shortcode.
				preg_match( '/jwplayer_(?P<media>[0-9a-z_]{8})(?:[-_])?(?P<player>[0-9a-z_]{8})?/i', $output[1], $jw_player );
				if ( ! empty( $jw_player['media'] ) && ! empty( $jw_player['player'] ) ) {
					return \sprintf(
						'%1$s-%2$s',
						$jw_player['media'],
						$jw_player['player']
					);
				}
			}

			// Something went wrong, output oEmbed result.
			return $shortcode_output;
		}

		// Output video url.
		return esc_url( $video );
	}

	/**
	 * Use iframe for oembeds.
	 *
	 * @return string
	 */
	public function use_iframe_oembeds( $html, $url ) {

		$html = '<iframe src="' . esc_url( $url ) . '" width="500" height="605" frameborder="0" scrolling="no" allowtransparency="true"></iframe>';

		return $html;
	}

	/**
	 * Get post card response.
	 *
	 * @return array
	 */
	public function get_post_card(): array {

		// Get category from post.
		$taxonomies = [ 'category' ];

		$theme_taxonomies = apply_filters( 'article_post_card_taxonomies', $taxonomies );

		$all_taxonomies = [];

		foreach ( $theme_taxonomies as $taxonomy_name ) {
			$taxonomy = $this->get_term_from_post( $taxonomy_name );

			$all_taxonomies[ $taxonomy_name ] = $taxonomy['name'] ?? '';
		}

		$post_card = [
			'id'              => $this->post->ID,
			'post-title'      => $this->headline(),
			'image'           => $this->featured_image(),
			'byline'          => $this->byline(),
			'excerpt'         => $this->excerpt(),
			'body-preview'    => $this->body_preview(),
			'published-at'    => $this->published_at(),
			'human-time-diff' => $this->human_time_diff(),
			'entitlements'    => $this->entitlements(),
			'post-type'       => $this->post->post_type ?? '',
			'link'            => \rest_url( '/' . Route_Registrar::NAMESPACE . sprintf( '/article/%d', $this->post->ID ) ),
			'permalink'       => $this->permalink(),
			'subcategory'     => $this->get_subcategory_by_id( $this->post->ID, true ),
		];

		$post_card = array_merge( $post_card, $all_taxonomies );

		return $post_card;
	}
}
