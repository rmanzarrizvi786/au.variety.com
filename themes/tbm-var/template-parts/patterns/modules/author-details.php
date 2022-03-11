<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="author-details // u-margin-t-150@mobile-max u-padding-a-125 lrv-u-margin-b-1 <?php echo esc_attr( $author_details_classes ?? '' ); ?>">
	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-title.php', $c_title, true ); ?>

	<?php if ( ! empty( $c_tagline ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $c_link_twitter_profile ) ) { ?>
		<div class="lrv-u-flex lrv-u-align-items-center">
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link_twitter_profile, true ); ?>
		</div>
	<?php } ?>

	<ul class="author-details__list lrv-a-unstyle-list u-margin-t-075">
		<?php if ( ! empty( $author_details_list_text ) ) { ?>
			<h4 class="author-details-list__title <?php echo esc_attr( $author_details_list_title_classes ?? '' ); ?>"><?php echo esc_html( $author_details_list_text ?? '' ); ?></h4>
		<?php } ?>

		<?php foreach ( $stories ?? [] as $item ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/author-details-item.php', $item, true ); ?>
		<?php } ?>
	</ul>

	<?php if ( ! empty( $c_link_view_all ) ) { ?>
		<div class="lrv-u-flex lrv-u-align-items-center">
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link_view_all, true ); ?>
		</div>
	<?php } ?>

  <?php if ( ! empty( $c_icon_close ) ) { ?>
		<div data-collapsible-toggle="always-show">
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $c_icon_close, true ); ?>
		</div>
  <?php } ?>
</section>
