<?php
/**
 * Template for a styled heading.
 *
 * @package pmc-styled-heading
 * @since 2018-5-31
 */

use PMC\Styled_Heading\Styled_Heading;

if ( isset( $fields ) ) {
?>

<div <?php Styled_Heading::inline_style( $fields['container_fields'] ); ?>>

	<h1>
		<?php foreach ( $fields['text_lines']['text_line'] as $line ) { ?>
			<?php if ( ! empty( $line['text'] ) ) { ?>

				<span <?php Styled_Heading::inline_style( $line ); ?>>
					<?php echo wp_kses_post( $line['text'] ); ?>
				</span>

			<?php } // end if ?>
		<?php } // end foreach ?>
	</h1>

</div>
<?php
} // end if
