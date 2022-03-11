<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $c_link ) ) { ?>
	<div class="o-cta // <?php echo esc_attr( $o_cta_classes ?? '' ); ?>">
		<?php if ( ! empty( $o_cta_text ) ) { ?>
			<p class="o-cta__text // <?php echo esc_html( $o_cta_text_classes ?? '' ); ?>"><?php echo esc_html( $o_cta_text ?? '' ); ?></p>
		<?php } ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link, true ); ?>
	</div>
<?php } ?>
