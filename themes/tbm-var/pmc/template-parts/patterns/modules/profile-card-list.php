<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<ul class="profile-card-list // <?php echo esc_attr( $profile_card_list_classes ?? '' ); ?> lrv-a-unstyle-list">
<?php foreach ( $profile_card_list ?? [] as $item ) { ?>
	<li>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/modules/profile-blurb.php', $item, true ); ?>
	</li>
<?php } ?>
</ul>
