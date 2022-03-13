<?php
/**
 * Template for admin taxonomy filter.
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2018-20-30
 *
 * @package pmc-gift-guide
 */

if ( empty( $taxonomy ) || empty( $terms ) || ! is_array( $terms ) ) {
	return;
}

$selected = ( ! empty( $selected ) ) ? $selected : false;
?>

<label class="screen-reader-text" for="filter-by-<?php echo esc_attr( $taxonomy ); ?>">Filter by <?php echo esc_html( $taxonomy ); ?></label>

<select id="filter-by-<?php echo esc_attr( $taxonomy ); ?>" name="<?php echo esc_attr( $taxonomy ); ?>">
	<option value="" selected>All</option>
	<?php
	foreach ( $terms as $term ) {
		printf( '<option value="%s" %s>%s</option>', esc_attr( $term->slug ), selected( $selected, $term->slug, false ), esc_html( $term->name ) );
	}
	?>
</select>
