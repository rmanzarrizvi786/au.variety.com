<?php
/**
 * Renders a Product shortcode in its expected Apple News format.
 *
 * @var string $title     Title of the product.
 * @var string $image_url URL to the image.
 * @var string $price     Product price.
 * @var string $link      Link to the product.
 *
 * @package bgr
 */

use PMC\Apple_News\Helper;
use PMC\Apple_News\Content_Filter;

// These are all required variables.
if ( empty( $widget_title ) || empty( $title ) || empty( $link ) || empty( $description_text ) ) {
	return;
}

if ( empty( $buy_button_text ) ) {
	$buy_button_text = 'Buy Now';
}

$blue = '#0084FF';
$grey = '#666666';

$font_normal = 'HelveticaNeue';
$font_bold   = 'HelveticaNeue-Bold';

$pricing_layout = [
	'margin' => [
		'top'    => 1,
		'bottom' => 1,
	],
];

$pricing_headercells_style = [
	'horizontalAlignment' => 'right',
	'textStyle'           => [
		'textColor'  => $grey,
		'fontName'   => $font_bold,
		'fontSize'   => 12,
		'lineHeight' => 16,
	],
	'padding'             => [
		'top'    => 0,
		'bottom' => 0,
		'right'  => 10,
		'left'   => 0,
	],
];

$pricing_style = [
	'tableStyle' => [
		'headerCells' => [
			'horizontalAlignment' => 'right',
			'textStyle'           => [
				'textColor'  => $grey,
				'fontName'   => $font_bold,
				'fontSize'   => 12,
				'lineHeight' => 16,
			],
			'padding'             => [
				'top'    => 0,
				'bottom' => 0,
				'right'  => 10,
				'left'   => 0,
			],
		],
		'cells'       => [
			'textStyle' => [
				'textColor'  => '#FF0000',
				'fontName'   => $font_bold,
				'fontSize'   => 12,
				'lineHeight' => 16,
			],
		],
	],
];

$apple_news_json = [
	'role'        => 'container',
	'components'  => [],
	'layout'      =>[
		'margin' => [
			'top'    => 15,
			'bottom' => 15,
			'left'   => 10,
			'right'  => 10,
		],
	],
	'conditional' => [
		[
			'contentDisplay' => [
				'type' => 'horizontal_stack',
			],
			'conditions'     => [
				'minViewportWidth' => 768,
			],
		],
	],
];
if ( ! empty( $image_url ) ) {
	$apple_news_json['components'][] = [
		'role'       => 'container',
		'components' => [
			[
				'role'        => 'image',
				'URL'         => (string) $image_url,
				'layout'      => [
					'maximumContentWidth' => '60cw',
				],
				'conditional' => [
					[
						'layout'     => [
							'maximumContentWidth' => '75cw',
						],
						'conditions' => [
							'minViewportWidth' => 768,
						],
					],
				],
			],
		],
		'style'      => [
			'mask' => [
				'type'   => 'corners',
				'radius' => 10,
			],
		],
		'layout'     => [
			'maximumWidth' => '40pw',
		],
	];
}
$subcomponents                 = [
	'role'       => 'container',
	'components' => [],
];
$rendered_title                = html_entity_decode( wp_kses_post( $title ) );
$subcomponents['components'][] = [
	'role'        => 'heading3',
	'text'        => $rendered_title,
	'layout'      => [
		'margin' => [
			'bottom' => 8,
		],
	],
	'additions'   => [
		[
			'type'        => 'link',
			'URL'         => esc_url_raw( $link ),
			'rangeStart'  => 0,
			'rangeLength' => strlen( $rendered_title ),
		],
	],
	'textStyle'   => [
		'textColor' => '#000000',
		'fontName'  => $font_bold,
	],
	'conditional' => [
		[
			'layout'     => [
				'margin' => [
					'top' => 8,
				],
			],
			'conditions' => [
				'maxViewportWidth' => 767,
			],
		],
	],
];

$subsubcomponents = [
	'role'        => 'container',
	'components'  => [],
	'conditional' => [
		[
			'contentDisplay' => [
				'type' => 'horizontal_stack',
			],
			'conditions'     => [
				'minViewportWidth' => 768,
			],
		],
	],
];

if ( ! empty( $prime_logo ) ) {
	$subsubcomponents['components'][] = [
		'role'        => 'logo',
		'URL'         => esc_url( $prime_logo ),
		'layout'      => [
			'maximumContentWidth' => '60pt',
			'maximumWidth'        => '30pw',
			'margin'              => [
				'top' => 6,
			],
		],
		'conditional' => [
			[
				'hidden'     => true,
				'conditions' => [
					'maxViewportWidth' => 767,
				],
			],
		],
	];
}

$table_component = [
	'role'        => 'container',
	'components'  => [],
	'conditional' => [
		[
			'layout'     => [
				'horizontalContentAlignment' => 'left',
			],
			'conditions' => [
				'minViewportWidth' => 768,
			],
		],
	],
];

if ( $original_price ) {
	$table_component['components'][] = [
		'role'   => 'htmltable',
		'html'   => '<table><tbody><tr><th>List Price:</th><td>' . $original_price . '</td></tr></tbody></table>',
		'layout' => $pricing_layout,
		'style'  => [
			'tableStyle' => [
				'headerCells' => $pricing_headercells_style,
				'cells'       => [
					'textStyle' => [
						'textColor'     => '#92929D',
						'fontName'      => $font_bold,
						'fontSize'      => 12,
						'lineHeight'    => 16,
						'strikethrough' => true,
					],
				],
			],
		],
	];
}
$table_component['components'][] = [
	'role'   => 'htmltable',
	'html'   => '<table><tbody><tr><th>Price:</th><td>' . $price . '</td></tr></tbody></table>',
	'layout' => $pricing_layout,
	'style'  => [
		'tableStyle' => [
			'headerCells' => $pricing_headercells_style,
			'cells'       => [
				'textStyle' => [
					'textColor'  => '#FF0000',
					'fontName'   => $font_bold,
					'fontSize'   => 16,
					'lineHeight' => 16,
				],
			],
		],
	],
];

if ( $discount_amount ) {
	$table_component['components'][] = [
		'role'   => 'htmltable',
		'html'   => '<table><tbody><tr><th>You Save:</th><td>' . $discount_amount . ' (' . $discount_percent . ')</td></tr></tbody></table>',
		'layout' => $pricing_layout,
		'style'  => [
			'tableStyle' => [
				'headerCells' => $pricing_headercells_style,
				'cells'       => [
					'textStyle' => [
						'textColor'  => '#FF0000',
						'fontName'   => $font_bold,
						'fontSize'   => 12,
						'lineHeight' => 16,
					],
				],
			],
		],
	];
}

$subsubcomponents['components'][] = $table_component;

if ( ! empty( $prime_logo ) ) {
	$subsubcomponents['components'][] = [
		'role'        => 'logo',
		'URL'         => esc_url( $prime_logo ),
		'layout'      => [
			'maximumContentWidth' => '60pt',
			'margin'              => [
				'top'    => 1,
				'bottom' => 1,
			],
		],
		'conditional' => [
			[
				'hidden'     => true,
				'conditions' => [
					'minViewportWidth' => 768,
				],
			],
		],
	];
}

$subcomponents['components'][] = $subsubcomponents;

$subcomponents['components'][] = [
	'role'      => 'body',
	'text'      => $description_text,
	'textStyle' => [
		'textColor'     => '#8A8C8C',
		'fontName'      => $font_normal,
		'fontSize'      => 10,
		'lineHeight'    => 12,
		'textAlignment' => 'center',
	],
	'layout'    => [
		'margin' => [
			'top'    => 3,
			'bottom' => 8,
		],
	],
];

$subcomponents['components'][]   = [
	'role'        => 'link_button',
	'text'        => $buy_button_text,
	'URL'         => esc_url_raw( $link ),
	'style'       => [
		'backgroundColor' => $blue,
		'border'          => [
			'all' => [
				'width' => 10,
				'style' => 'solid',
				'color' => $blue,
			],
		],
		'mask'            => [
			'type'   => 'corners',
			'radius' => 4,
		],
	],
	'layout'      => [
		'padding' => [
			'left'  => 100,
			'right' => 100,
		],
	],
	'conditional' => [
		[
			'layout'     => [
				'padding' => [
					'left'  => 300,
					'right' => 300,
				],
			],
			'conditions' => [
				'minViewportWidth' => 768,
			],
		],
	],
	'textStyle'   => [
		'textColor'     => '#FFF',
		'textAlignment' => 'center',
		'fontName'      => $font_bold,
		'fontSize'      => 16,
		'lineHeight'    => 16,
	],
];

$apple_news_json['components'][] = $subcomponents;

$apple_news_json_todays_top_deal = [
	'role'               => 'container',
	'allowAutoplacedAds' => false, // ROP-2214: We do not want ads to be inject inside the current container or its children
	'components'         => [
		[
			'role'   => 'divider',
			'stroke' => [
				'width' => 3,
				'color' => $blue,
			],
		],
		[
			'role'      => 'heading3',
			'text'      => $widget_title,
			'textStyle' => [
				'textColor' => $blue,
				'fontName'  => $font_bold,
			],
		],
		$apple_news_json,
	],
	'layout'     => [
		'margin' => [
			'top'    => 15,
			'bottom' => 15,
		],
	],
];

// This is a custom template that generate APN json data, we need to wrap the data to avoid apple news from converting the data
Helper::get_instance()->wrap_json_data( $apple_news_json_todays_top_deal, true );
