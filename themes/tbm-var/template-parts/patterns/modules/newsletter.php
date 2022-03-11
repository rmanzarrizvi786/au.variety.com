<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="newsletter // lrv-u-width-100p <?php echo esc_attr($newsletter_classes ?? ''); ?>">
	<div class="newsletter__inner // <?php echo esc_attr($newsletter_inner_classes ?? ''); ?>">
		<?php if (!empty($c_heading)) { ?>
			<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true); ?>
		<?php } ?>

		<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-email-capture-form.php', $o_email_capture_form, true); ?>
	</div>
</section>