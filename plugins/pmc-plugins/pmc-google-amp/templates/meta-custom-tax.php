<?php
/**
 * Adding the Taxonomy custom data to AMP Theme, load by AMP Modified Content Class.
 *
 * @ticket PMCVIP-2426
 * @since 2016-10-27 - Debabrata Karfa
 */

$categories = get_the_terms( $post_id, 'category' );

if ( ! empty( $categories ) && is_array( $categories ) ) {
?>
	<div class="amp-wp-content">
		<h1 class="amp-wp-title"><?php esc_html_e( 'Filed Under', 'pmc-google-amp' ); ?></h1>
		<ul class="amp-wp-meta">
			<li class="amp-wp-tax-category">
				<?php foreach ( $categories as $category ) { ?>
					<?php
					if ( ! is_a( $category, 'WP_Term' ) ) {
						continue;
					}

					$category_url = get_term_link( $category->term_id );

					if ( ! is_string( $category_url ) || is_wp_error( $category_url ) ) {
						continue;
					}
					?>
					<a href="<?php echo esc_url( $category_url ); ?>" rel="category tag">
						<?php echo esc_html( $category->name ); ?>
					</a>
				<?php } ?>
			</li>
		</ul>
	</div>
<?php
}

//EOF
