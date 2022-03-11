<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="search-form // <?php echo esc_attr($search_form_classes ?? ''); ?>">
	<div class="search-form__inner <?php echo esc_attr($search_form_inner_classes ?? ''); ?>">
		<?php if (0 && !empty($search_form_is_swiftype)) { ?>
			<div data-st-search-form="small_search_form"></div>
		<?php } else { ?>
			<form class="search-form" action="/<?php // echo esc_url($search_form_action_url ?? ''); 
												?>" role="search" method="get">
				<!-- <label class="<?php echo esc_attr($search_form_input_label_classes ?? ''); ?>">
					<span class="lrv-a-screen-reader-only">Search for:</span>
					<input class="<?php echo esc_attr($search_form_input_classes ?? ''); ?>" type="search" placeholder="<?php echo esc_attr($search_form_input_placeholder_attr ?? ''); ?>" value="" name="s">
				</label>
				<input class="<?php echo esc_attr($search_form_submit_classes ?? ''); ?>" type="submit" value="<?php echo esc_html($search_form_submit_text ?? ''); ?>"> -->

				<div class="search-form__inner lrv-a-glue lrv-a-glue--t-0 lrv-u-height-100p lrv-u-width-100p lrv-u-padding-lr-1">
					<div data-st-search-form="small_search_form">
						<div class="search-input-with-autocomplete" data-reactid=".0">
							<div class="search-form" data-reactid=".0.0"><input type="text" autocomplete="off" id="small_search_form" name="s" value="" placeholder="Search" data-reactid=".0.0.0" class=" js-bound"><input type="submit" name="" value="Go" data-reactid=".0.0.1"></div>
							<div class="swiftype-widget" data-reactid=".0.1">
								<div class="autocomplete inactive" data-reactid=".0.1.0">
									<div class="autocomplete-section autocomplete-section-0 undefined" data-reactid=".0.1.0.$0">
										<div class="ac-section-title" data-reactid=".0.1.0.$0.0">Content</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		<?php } ?>
	</div>
</div>