<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<header class="profile-header // <?php echo esc_attr( $profile_header_classes ?? '' ); ?>">

	<div class="profile-header__banner // <?php echo esc_attr( $profile_header_banner_classes ?? '' ); ?>">
		<div class="lrv-a-wrapper lrv-u-flex lrv-u-flex-direction-column lrv-u-align-items-center lrv-u-padding-tb-1">
			<h1 class="<?php echo esc_attr( $profile_header_title_classes ?? '' ); ?>">
				<?php echo esc_html( $profile_header_title_text ?? '' ); ?>
			</h1>

			<?php if ( ! empty( $o_sponsored_by ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-sponsored-by.php', $o_sponsored_by, true ); ?>
			<?php } ?>

		</div>
	</div>

	<div class="profile-header__nav // <?php echo esc_attr( $profile_header_nav_classes ?? '' ); ?>">
		<div class="lrv-a-wrapper lrv-a-glue-parent">
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav, true ); ?>

			<?php if ( ! empty( $o_select_nav ) ) { ?>
				<div class="<?php echo esc_attr( $profile_header_o_select_nav_classes ?? '' ); ?>">
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-select-nav.php', $o_select_nav, true ); ?>
				</div>
			<?php } ?>
		</div>
	</div>
</header>
