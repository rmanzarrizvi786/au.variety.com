<?php
/**
 * Card: River Item Template
 *
 * @package pmc-variety-2017
 * @since   2017.1.0
 */

$item = variety_normalize_post( $item );

if ( empty( $item ) || ! is_object( $item ) ) {
	$item = $post;
}

?>
<li class="l-list__item">
	<?php
	/* @todo change to new template.
	PMC::render_template(
		sprintf( '%s/template-parts/article/card-river.php', untrailingslashit( CHILD_THEME_PATH ) ),
		compact( 'item' ),
		true
	);*/
	?>
</li>
