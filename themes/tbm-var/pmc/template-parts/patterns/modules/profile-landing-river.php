<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section id="<?php echo esc_attr( $profile_landing_river_id_attr ?? '' ); ?>" class="profile-landing-river // <?php echo esc_attr( $profile_landing_river_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_heading ) ) { ?>
		<div class="lrv-a-wrapper a-stacking-context">
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
		</div>
	<?php } ?>

	<div class="lrv-a-wrapper a-children-border--grey-light a-children-border-vertical">
		<?php foreach ( $profile_landing_river_stories ?? [] as $item ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/modules/story.php', $item, true ); ?>
		<?php } ?>
	</div>
</section>
