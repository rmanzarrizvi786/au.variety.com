<?php
$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/issue-item-list.prototype' );

$data['issue_item_list_classes'] = 'lrv-a-grid lrv-a-cols2 lrv-a-cols4@tablet lrv-u-padding-tb-2 lrv-a-unstyle-list lrv-u-background-color-white lrv-u-margin-t-1 u-border-color-brand-primary u-border-t-6@mobile-max u-padding-lr-050@mobile-max lrv-u-padding-tb-1@mobile-max';

if ( $issues ) :
	$issue_item_list = [];

	foreach ( $issues as $issue ) :
		$issue_item = $data['issue_item_list'][0];

		$issue_text = $issue['type'] . date(
			', l, m/d/Y',
			intval( $issue['date'] )
		);

		$issue_item['issue_item_url'] = $issue['url'];

		$issue_item['c_lazy_image']['c_lazy_image_src_url']         = $issue['img320'];
		$issue_item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$issue_item['c_lazy_image']['c_lazy_image_srcset_attr']     = '';
		$issue_item['c_lazy_image']['c_lazy_image_alt_attr']        = $issue_text;
		$issue_item['c_lazy_image']['c_lazy_image_sizes_attr']      = '';

		$issue_item['c_span']['c_span_classes'] = 'lrv-u-display-block lrv-u-padding-t-1';
		$issue_item['c_span']['c_span_text']    = $issue_text;
		$issue_item['c_span']['c_title_text']   = '';

		$issue_item_list[] = $issue_item;
	endforeach;

	$data['issue_item_list'] = $issue_item_list;

	\PMC::render_template(
		CHILD_THEME_PATH . '/template-parts/patterns/modules/issue-item-list.php',
		$data,
		true
	);
endif;

//EOF
