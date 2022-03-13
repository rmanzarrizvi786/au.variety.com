<?php
/**
 * Template for Promo Module
 */

use PMC\Apple_News\Helper;
use PMC\Apple_News\Content_Filter;

$apple_news_json_data = [
	'role' => 'container',
	'allowAutoplacedAds' => false, // ROP-2214: We do not want ads to be inject inside the current container or its children
	'components' => [
		[
			'role' => 'heading2',
			'text' => strtoupper( $heading ),
			'format' => 'html',
			'textStyle' => 'default-heading-2',
			'layout' => [
				'margin' => [
					'top'    => 30,
					'bottom' => 5,
				],
			],
		],
		[
			'role' => 'photo',
			'URL' => $img_url,
			'layout' => [
				'margin' => [
					'top'    => 0,
					'bottom' => 0,
				],
			],
		],
		[
			'role' => 'container',
			'layout' => [
				'margin' => [
					'bottom' => 30,
				],
			],
			'style'     => [
				'border' => [
					'all' => [
						'width' => 3,
						'color' => '#cecece',
					],
					'top' => false,
				],
			],
			'components' => [
				[
					'role' => 'body',
					'text' => sprintf('<p><a href="%s"><b>%s</b></a></p>', esc_url( $url ), esc_html( $title ) ),
					'format' => 'html',
					'textStyle' => [
						'textColor'  => '#000',
						'fontWeight' => 'normal',
						'fontSize'   => 18,
						'lineHeight' => 20,
						'linkStyle'  => [
							'textColor' => '#000',
						],
					],
					'layout'    => [
						'margin'  => [
							'top'    => 5,
							'bottom' => 20,
							'left'   => 5,
							'right'  => 5,
						],
						'padding' => [
							'left'  => 5,
							'right' => 5,
						],
					],
				],
			],
		],
	],
];

Content_Filter::get_instance()->require_default_style( 'default-heading-2' );

// This is a custom template that generate APN json data, we need to wrap the data to avoid apple news from converting the data
Helper::get_instance()->wrap_json_data( $apple_news_json_data, true );
