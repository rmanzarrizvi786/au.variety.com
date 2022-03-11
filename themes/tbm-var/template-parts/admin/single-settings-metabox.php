<?php
/**
 * Template part of Single Settings metabox.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-09-01 Milind More CDWE-499
 */

?>
<p>
	<label for="variety-sub-heading"><?php esc_html_e( 'Sub Heading', 'pmc-variety' ); ?></label><br />
	<input class="widefat" type="text" name="variety-sub-heading" id="variety-sub-heading" value="<?php echo esc_attr( $linked_value ); ?>" size="30" />
	<span class="example"><?php esc_html_e( 'Sub Headings are shown below the Title.', 'pmc-variety' ); ?></span>
</p>
