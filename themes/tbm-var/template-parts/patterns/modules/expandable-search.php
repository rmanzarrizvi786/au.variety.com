<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="expandable-search // js-ExpandableSearch lrv-u-flex lrv-u-height-100p lrv-u-align-items-center lrv-u-width-100p lrv-u-justify-content-center <?php echo esc_attr($expandable_search_classes ?? ''); ?>">

	<div class="expandable-search__trigger // js-ExpandableSearch-trigger">
		<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-icon-button.php', $o_icon_button_search, true); ?>
	</div>

	<div class="expandable-search__target // js-ExpandableSearch-target lrv-a-glue-parent js-fade js-fade-is-out lrv-u-width-100p u-max-width-500 <?php echo esc_attr($expandable_search_classes ?? ''); ?>" hidden>
		<div class="expandable-search__inner lrv-a-glue lrv-a-glue--t-0 lrv-u-height-100p lrv-u-width-100p u-max-width-500 <?php echo esc_attr($expandable_search_inner_classes ?? ''); ?>" data-header-search-trigger="">
			<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/search-form.php', $search_form, true); ?>
		</div>
	</div>

</div>