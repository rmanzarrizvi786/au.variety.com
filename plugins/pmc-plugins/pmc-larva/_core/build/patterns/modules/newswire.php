<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="newswire // lrv-a-wrapper lrv-u-padding-b-2 lrv-u-margin-t-2">
	<?php if ( ! empty( $c_heading ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-heading', $c_heading, true ); ?>
	<?php } ?>
	<ul class="lrv-a-unstyle-list lrv-a-grid lrv-a-cols2 lrv-a-cols5@desktop lrv-a-grid-first-child-span-all@mobile-max"
		<?php if ( ! empty( $newswire_aria_labelledby_attr ) ) { ?>
			aria-labelledby="<?php echo esc_attr( $newswire_aria_labelledby_attr ?? '' ); ?>"
		<?php } ?>
	>
		<?php foreach ( $newswire_items ?? [] as $item ) { ?>
			<li class="lrv-a-grid-item lrv-u-height-100p">
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-card', $item, true ); ?>
			</li>
		<?php } ?>
	</ul>
</section>
