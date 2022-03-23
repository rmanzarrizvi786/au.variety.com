<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<nav class="mega-menu // js-MegaMenu a-mega-overflow-outside">
	<div class="a-mega-overflow-middle">

		<div class="mega-menu__wrap // a-mega-overflow-inside u-background-color-geyser u-background-color-brand-accent-100-b@tablet lrv-u-flex u-flex-direction-column u-min-height-100p">
			<div class="mega-menu__main // js-MegaMenu lrv-a-wrapper lrv-u-padding-a-00 u-padding-a-1@tablet lrv-u-width-100p u-flex-1">

				<div class="mega-menu__header lrv-u-flex lrv-u-justify-content-space-between lrv-u-color-white u-padding-lr-3@tablet u-margin-b-250@tablet">
					<div class="a-hidden@mobile-max lrv-u-margin-r-1@desktop">
						<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/components/c-logo.php', $c_logo, true); ?>
					</div>

					<div class="lrv-u-margin-lr-auto lrv-u-width-100p u-margin-l-025@tablet u-max-width-618 lrv-u-font-family-secondary lrv-u-margin-t-2@mobile-max u-padding-a-1@mobile-max">
						<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/search-form.php', $search_form, true); ?>
					</div>

					<button class="mega-menu__close-button // js-MegaMenu-Trigger lrv-u-justify-content-center lrv-u-align-items-center lrv-u-flex-shrink-0 lrv-u-border-a-0 a-become-close-button a-become-close-button--trigger a-hidden@mobile-max u-width-30 u-background-color-brand-accent-100-b u-color-brand-secondary-30@tablet">
						<span class="lrv-a-screen-reader-only">Close the menu</span>
					</button>
				</div>

				<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/mega-menu-content.php', $mega_menu_content, true); ?>

				<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-nav.php', $mobile_navigation, true); ?>
				<?php
				// echo '<pre>' . print_r($region_selector_mobile, true) . '</pre>';
				\PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/region-selector.php', $region_selector_mobile, true); ?>
			</div>

			<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/mega-menu-footer.php', $mega_menu_footer, true); ?>

		</div>

	</div>
</nav>