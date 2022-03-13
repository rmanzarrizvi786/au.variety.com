import { __ } from '@wordpress/i18n';

export const cropOptions = [
	{ value: null, label: __( 'Default', 'pmc-gutenberg' ) },
	{ value: 'lrv-a-crop-1x1', label: __( '1x1', 'pmc-gutenberg' ) },
	{ value: 'lrv-a-crop-2x1', label: __( '2x1', 'pmc-gutenberg' ) },
	{ value: 'lrv-a-crop-2x3', label: __( '2x3', 'pmc-gutenberg' ) },
	{ value: 'lrv-a-crop-3x4', label: __( '3x4', 'pmc-gutenberg' ) },
	{ value: 'lrv-a-crop-4x3', label: __( '4x3', 'pmc-gutenberg' ) },
	{ value: 'lrv-a-crop-5x1', label: __( '5x1', 'pmc-gutenberg' ) },
	{ value: 'lrv-a-crop-5x2', label: __( '5x2', 'pmc-gutenberg' ) },
	{ value: 'lrv-a-crop-16x9', label: __( '16x9', 'pmc-gutenberg' ) },
];
