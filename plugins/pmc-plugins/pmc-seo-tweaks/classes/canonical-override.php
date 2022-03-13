<?php

namespace PMC\SEO_Tweaks;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

class Canonical_Override {

	use Singleton;

	const nonce_name = 'pmc_seo_tweaks_canonical_override';
	protected $_exclude_post_types = array ( 'pmc-long-options' );

	protected function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'action_add_meta_boxes' ) , 10, 2);
		add_action( 'save_post', array( $this, 'action_save_post' ) );
		// PPT-3118 Run this last
		add_filter( 'pmc_canonical_url', array( $this, 'filter_canonical_url' ), 99 );
	}

	/**
	 * Filter in canonical URL on single post if override is used
	 *
	 * @since 2014-08-22 Amit Sannad PPT-3118 Do not render canonical url for pages that have meta noindex.
	 *
	 * @param string $url
	 * @uses get_post_meta, esc_url, is_single
	 * @return string
	 */
	public function filter_canonical_url( $url ) {

		// For pages that have noindex dont render canonical
		if ( Helpers::noindex_check() ) {
				return false;
		}

		if ( is_single() ) {
			global $post;

			if ( ! $post ) {
				return $url;
			}

			$canonical = get_post_meta( $post->ID, '_pmc_canonical_override', true );
			if ( ! empty( $canonical ) ) {
				return esc_url( $canonical );
			}

			// don't render canonical tag when _mt_pmc_exclude_from_seo value is `on` and _pmc_canonical_override meta has no value
			$pmc_exclude_from_seo = get_post_meta( $post->ID, '_mt_pmc_exclude_from_seo', true );
			if ( empty( $canonical ) && 'on' === $pmc_exclude_from_seo ) {
				return '';
			}
		}

		return $url;
	}

	/**
	 * Saves canonical meta info
	 *
	 * @param int $post_id
	 * @uses current_user_can, get_post_type, wp_verify_nonce, update_post_meta, esc_url_raw, delete_post_meta
	 * @return void
	 */
	public function action_save_post( $post_id ) {

		$excluded_post_types = apply_filters( 'pmc_canonical_override_exclude_post_types', $this->_exclude_post_types );

		if ( in_array( get_post_type( $post_id ), $excluded_post_types ) ) {
			return;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) || 'revision' == get_post_type( $post_id ) )
			return;


		if ( ! empty( $_POST[self::nonce_name] ) && wp_verify_nonce( $_POST[self::nonce_name], __FILE__ ) ) {

			if ( ! empty( $_POST['pmc_canonical_override'] ) ) {
				$url_parts = parse_url( esc_url_raw( $_POST['pmc_canonical_override'] ) );

				if ( is_array( $url_parts ) && ! empty( $url_parts['host'] ) )
					update_post_meta( $post_id, '_pmc_canonical_override', esc_url_raw( $_POST['pmc_canonical_override'] ) );
				else
					delete_post_meta( $post_id, '_pmc_canonical_override' );
			} else {
				delete_post_meta( $post_id, '_pmc_canonical_override' );
			}
		}
	}

	/**
	 * Add meta boxes for canonical override
	 *
	 * @uses add_meta_box
	 * @return void
	 */
	public function action_add_meta_boxes() {
		$post_types = apply_filters( 'pmc_canonical_overrides_post_types', array( 'post' ) );
		foreach( $post_types as $pt ) {
			add_meta_box( 'pmc-seo-tweaks-canonical-override', 'Canonical Override', array( $this, 'meta_box_canonical_override' ), sanitize_text_field( $pt ) );
		}
	}

	/**
	 * Output canonical override metabox
	 *
	 * @param object post
	 * @uses wp_nonce_field, get_post_meta, esc_url
	 * @return void
	 */
	public function meta_box_canonical_override( $post ) {
		wp_nonce_field( __FILE__, self::nonce_name );
		$canonical_override = get_post_meta( $post->ID, '_pmc_canonical_override', true );
		?>
		<p>
			<label for="pmc_canonical_override">URL:</label>
			<input type="text" class="widefat" value="<?php echo esc_url( $canonical_override ); ?>" name="pmc_canonical_override" id="pmc_canonical_override" />
		</p>
		<?php
	}
}

// EOF
