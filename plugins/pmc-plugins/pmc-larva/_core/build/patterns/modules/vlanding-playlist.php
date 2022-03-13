<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="vlanding-playlist // lrv-u-padding-tb-2 lrv-u-padding-tb-1@mobile-max">
	<?php if ( ! empty( $o_header ) ) { ?>
		<div class="lrv-a-wrapper">
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-header', $o_header, true ); ?>
		</div>
	<?php } ?>

	<div class="lrv-a-wrapper lrv-a-scrollable-grid@desktop-max lrv-a-grid lrv-a-cols2@desktop">
		<?php if ( ! empty( $vlanding_playlist_large_card ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-card', $vlanding_playlist_large_card, true ); ?>
		<?php } ?>
		<div class="lrv-a-scrollable-grid__nested@desktop-max lrv-a-grid lrv-a-cols2@desktop">
			<?php foreach ( $vlanding_playlist_small_cards ?? [] as $item ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-card', $item, true ); ?>
			<?php } ?>
		</div>
	</div>
</section>


