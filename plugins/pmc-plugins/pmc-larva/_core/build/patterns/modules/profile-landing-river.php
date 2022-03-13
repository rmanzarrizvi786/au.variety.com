<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section id="<?php echo esc_attr( $profile_landing_river_id_attr ?? '' ); ?>" class="profile-landing-river // <?php echo esc_attr( $profile_landing_river_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_heading ) ) { ?>
		<div class="lrv-a-wrapper a-stacking-context">
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-heading', $c_heading, true ); ?>
		</div>
	<?php } ?>

	<div class="lrv-a-wrapper a-children-border--grey-light a-children-border-vertical">
		<?php foreach ( $profile_landing_river_stories ?? [] as $item ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'modules/story', $item, true ); ?>
		<?php } ?>
	</div>
</section>
