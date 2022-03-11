<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="author-social // <?php echo esc_attr( $author_social_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_sponsored_social ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/components/c-sponsored.php', $c_sponsored_social, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $author ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/author.php', $author, true ); ?>
	<?php } ?>

	<div class="author-social__share // <?php echo esc_attr( $author_social_share_desktop_classes ?? '' ); ?>">
		<?php if ( ! empty( $o_comments_link ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-comments-link.php', $o_comments_link, true ); ?>
		<?php } ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/social-share.php', $social_share, true ); ?>
	</div>

	<?php if ( ! empty( $c_timestamp ) ) { ?>
		<div class="author-social__timestamp // <?php echo esc_attr( $author_social_timestamp_classes ?? '' ); ?>">
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-timestamp.php', $c_timestamp, true ); ?>
		</div>
		<div class="author-social__share // <?php echo esc_attr( $author_social_share_mobile_classes ?? '' ); ?>">
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/social-share.php', $social_share, true ); ?>
		</div>
	<?php } ?>
</div>
