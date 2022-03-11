<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="outbrain-widget // <?php echo esc_attr( $outbrain_widget_classes ?? '' ); ?>">
	<div class="heading-pmc // <?php echo esc_attr( $heading_classes ?? '' ); ?>">
		<?php if ( ! empty( $c_pmc_icon ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $c_pmc_icon, true ); ?>
		<?php } ?>
		
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
	</div>
	
	<div class="OUTBRAIN" data-src="https://variety.com"
		data-widget-id="<?php echo esc_attr( $outbrain_widget_id_attr ?? '' ); ?>"
		data-ob-template="<?php echo esc_attr( $outbrain_ob_template_attr ?? '' ); ?>">
	</div>

	<script async src='<?php echo esc_url( $outbrain_script_url ?? '' ); ?>'></script>
</section>
