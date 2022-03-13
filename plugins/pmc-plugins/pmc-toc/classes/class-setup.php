<?php
namespace PMC\TOC;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Post_Options\API;

/*
 * Setup class.
 */
class Setup {

	use Singleton;

	/**
	 * Constant for post option term slug.
	 */
	const TERM_SLUG = 'add-toc';

	/**
	 * @var \PMC\Post_Options\API
	 */
	protected $_post_options;

	/**
	 * Setup constructor.
	 */
	protected function __construct() {
		$this->_post_options = API::get_instance();

		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {
		add_action( 'init', [ $this, 'register_post_option' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_larvaless' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		// Make slightly higher priority, so not affected pmc-ecomm plugin that adds tracking info to anchor tags.
		add_filter( 'the_content', [ $this, 'maybe_add_toc' ], 11 );
		add_filter( 'pmc-google-amp-styles', [ $this, 'get_amp_styles' ] );
	}

	/**
	 * Register post option term to filter sponsored posts.
	 *
	 * @return void
	 */
	public function register_post_option() : void {
		$this->_post_options->register_global_options(
			[
				self::TERM_SLUG => [
					'label'       => __( 'Add TOC', 'pmc-toc' ),
					'description' => __( 'Posts with this term will create a table of contents.', 'pmc-toc' ),
				],
			]
		);
	}

	/**
	 * Checks if TOC post.
	 *
	 * @return bool
	 */
	protected function _is_toc_post() : bool {
		return (
			( is_single() || is_feed() )
			&& $this->_post_options->post( get_the_ID() )->has_option( self::TERM_SLUG )
		);
	}

	/**
	 * Enqueue styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts() : void {
		if ( $this->_is_toc_post() ) {
			\PMC\Global_Functions\Styles::get_instance()->inline(
				'pmc-toc',
				rtrim( PMC_TOC_PATH, '/' ) . '/assets/css/'
			);
		}
	}

	/**
	 * Enqueue defined Larva utility classes required for plugin styling.
	 *
	 * @return void
	 */
	public function enqueue_larvaless() : void {
		$is_larva_active = apply_filters(
			'pmc_toc_larva_active',
			(bool) ( defined( 'PMC_LARVA_ACTIVE' ) && true === PMC_LARVA_ACTIVE )
		);

		if ( $this->_is_toc_post() && ! $is_larva_active ) {
			\PMC\Global_Functions\Styles::get_instance()->inline(
				'pmc-toc-larva',
				rtrim( PMC_TOC_PATH, '/' ) . '/assets/css/'
			);
		}
	}

	/**
	 * Possibly adds a TOC to the post content.
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function maybe_add_toc( $content ) : string {
		if ( ! $this->_is_toc_post() ) {
			return $content;
		}

		$tag     = $this->_get_tag();
		$items   = $this->_get_items( $tag, $content );
		$toc     = $this->_get_toc( $items );
		$content = $this->_update_content( $items, $content );

		return $toc . $content;
	}

	/**
	 * Include styles for AMP pages.
	 *
	 * @param $styles
	 *
	 * @return string
	 */
	public function get_amp_styles( $styles ) : string {
		$toc_larva_styles = \PMC::render_template( sprintf( '%s/assets/css/pmc-toc-larva.css', PMC_TOC_PATH ) );
		$toc_styles       = \PMC::render_template( sprintf( '%s/assets/css/pmc-toc.css', PMC_TOC_PATH ) );

		return $styles . $toc_larva_styles . $toc_styles;
	}

	/**
	 * Retrieves and validates HTML tag.
	 *
	 * @return string
	 */
	protected function _get_tag() : string {
		$default_tag = 'h2';
		$tag         = strtolower( (string) apply_filters( 'pmc_toc_tag', $default_tag ) );
		$valid_tags  = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];

		if ( in_array( $tag, (array) $valid_tags, true ) ) {
			return $tag;
		}

		return $default_tag;
	}

	/**
	 * Retrieves the TOC navigation if there are items to populate it.
	 *
	 * @param array $items
	 *
	 * @return string
	 */
	protected function _get_toc( array $items ) : string {
		if ( empty( $items ) ) {
			return '';
		}

		return (string) \PMC::render_template(
			PMC_TOC_PATH . '/templates/navigation.php',
			[ 'items' => $items ]
		);
	}

	/**
	 * Looks for headlines in the content to create a TOC from.
	 *
	 * @param string $tag
	 * @param string $content
	 *
	 * @return array
	 */
	protected function _get_items( string $tag, string $content ) : array {
		// Regex: https://regex101.com/r/C6K5jG/2
		$pattern = sprintf( '/<(%1$s)([^>]*)[^>]*>(.+?)%1$s>/i', $tag );

		preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER );

		$matches = $this->_add_ids( $matches );

		return $matches;
	}

	/**
	 * Adds unique ID to each item to use as anchor.
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	private function _add_ids( array $items ) : array {
		$used_ids = [];
		foreach ( $items as $i => $item ) {
			$item[3] = rtrim( $item[3], '</' ); // cleanup from regex.
			$text    = $item[3];
			$id      = sanitize_title_with_dashes( wp_strip_all_tags( $text ) );
			$count   = 2;
			$orig_id = $id;

			// Ensure ID is unique.
			while ( in_array( $id, (array) $used_ids, true ) && $count < 50 ) {
				$id = sprintf( '%s-%d', $orig_id, $count );
				$count++;
			}

			$used_ids[]     = $id;
			$items[ $i ][]  = $id;
			$items[ $i ][3] = $text; // update item from cleanup.
		}

		return $items;
	}

	/**
	 * Updates content with ID, jumpto links, and accessibility markup.
	 *
	 * @param array  $items
	 * @param string $content
	 *
	 * @return string
	 */
	protected function _update_content( array $items, string $content ) : string {
		$matches      = [];
		$replacements = [];
		$jump_to_top  = (bool) apply_filters( 'pmc_toc_jump_to_top', true );

		foreach ( $items as $i => $item ) {
			$matches[]      = $item[0];
			$replacements[] = $this->_get_replacement( $item, $i, $jump_to_top );
		}

		if ( ! empty( $replacements ) ) {
			if ( count( array_unique( (array) $matches ) ) !== count( (array) $matches ) ) {
				foreach ( (array) $matches as $i => $match ) {
					$content = preg_replace( '/' . preg_quote( $match, '/' ) . '/', $replacements[ $i ], $content, 1 );
				}
			} else {
				$content = str_replace( $matches, $replacements, $content );
			}
		}

		return $content;
	}

	/**
	 * Prepares replacement content for header items.
	 *
	 * @param array $item
	 * @param int   $i
	 * @param bool  $jump_to_top
	 *
	 * @return string
	 */
	private function _get_replacement( array $item, int $i, bool $jump_to_top ) {
		return (string) \PMC::render_template(
			PMC_TOC_PATH . '/templates/header.php',
			[
				'item'        => $item,
				'i'           => $i,
				'jump_to_top' => $jump_to_top,
			]
		);
	}

}
