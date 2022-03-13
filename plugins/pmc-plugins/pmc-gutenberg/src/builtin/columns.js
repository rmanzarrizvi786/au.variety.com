/**
 * External dependencies.
 */
import { merge, omit } from 'lodash';

/**
 * WordPress dependencies.
 */
import { Path, SVG } from '@wordpress/components';
import { addFilter, removeFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

const blockName = 'core/columns';
const filterTag = 'blocks.registerBlockType';
const filterNamespace = 'pmc-gutenberg/builtin/columns';

/**
 * Modify Columns block to use variations supported by Larva, powered by Larva
 * classes rather than relative widths.
 *
 * @param {Object} settings Block settings.
 * @param {string} name     Block name.
 * @return {Object}          Modified block settings.
 */
const modifyBlock = ( settings, name ) => {
	if ( blockName !== name ) {
		return settings;
	}

	// Do not modify block's deprecations.
	// Needed until https://github.com/WordPress/gutenberg/pull/36628 is merged.
	removeFilter( filterTag, filterNamespace );

	const innerBlock = 'core/column';
	const innerBlockClassName = 'lrv-a-grid-item';

	return merge(
		{},
		// Overwriting, not merging, certain nodes.
		omit( settings, [ 'variations' ] ),
		{
			supports: {
				color: { text: false },
			},
			variations: [
				{
					name: 'two-columns-equal',
					title: __( '50 / 50', 'pmc-gutenberg' ),
					description: __(
						'Two columns; equal split',
						'pmc-gutenberg'
					),
					icon: (
						<SVG
							width="48"
							height="48"
							viewBox="0 0 48 48"
							xmlns="http://www.w3.org/2000/svg"
						>
							<Path
								fillRule="evenodd"
								clipRule="evenodd"
								d="M39 12C40.1046 12 41 12.8954 41 14V34C41 35.1046 40.1046 36 39 36H9C7.89543 36 7 35.1046 7 34V14C7 12.8954 7.89543 12 9 12H39ZM39 34V14H25V34H39ZM23 34H9V14H23V34Z"
							/>
						</SVG>
					),
					isDefault: true,
					attributes: {
						className:
							'lrv-a-grid lrv-a-cols lrv-a-cols2@desktop lrv-a-cols2@tablet',
					},
					innerBlocks: [
						[ innerBlock, { className: innerBlockClassName } ],
						[ innerBlock, { className: innerBlockClassName } ],
					],
					scope: [ 'block' ],
				},
				{
					name: 'two-columns-one-third-two-thirds',
					title: __( '33 / 67', 'pmc-gutenberg' ),
					description: __(
						'Two columns; one-third, two-thirds split',
						'pmc-gutenberg'
					),
					icon: (
						<SVG
							width="48"
							height="48"
							viewBox="0 0 48 48"
							xmlns="http://www.w3.org/2000/svg"
						>
							<Path
								fillRule="evenodd"
								clipRule="evenodd"
								d="M39 12C40.1046 12 41 12.8954 41 14V34C41 35.1046 40.1046 36 39 36H9C7.89543 36 7 35.1046 7 34V14C7 12.8954 7.89543 12 9 12H39ZM39 34V14H20V34H39ZM18 34H9V14H18V34Z"
							/>
						</SVG>
					),
					attributes: {
						className:
							'lrv-a-grid lrv-a-cols lrv-a-cols3@desktop lrv-a-cols3@tablet',
					},
					innerBlocks: [
						[ innerBlock, { className: innerBlockClassName } ],
						[
							innerBlock,
							{
								className: `${ innerBlockClassName } lrv-a-span2`,
							},
						],
					],
					scope: [ 'block' ],
				},
				{
					name: 'two-columns-two-thirds-one-third',
					title: __( '67 / 33', 'pmc-gutenberg' ),
					description: __(
						'Two columns; one-third, two-thirds split',
						'pmc-gutenberg'
					),
					icon: (
						<SVG
							width="48"
							height="48"
							viewBox="0 0 48 48"
							xmlns="http://www.w3.org/2000/svg"
						>
							<Path
								fillRule="evenodd"
								clipRule="evenodd"
								d="M39 12C40.1046 12 41 12.8954 41 14V34C41 35.1046 40.1046 36 39 36H9C7.89543 36 7 35.1046 7 34V14C7 12.8954 7.89543 12 9 12H39ZM39 34V14H30V34H39ZM28 34H9V14H28V34Z"
							/>
						</SVG>
					),
					attributes: {
						className:
							'lrv-a-grid lrv-a-cols lrv-a-cols3@tablet lrv-a-cols3@desktop',
					},
					innerBlocks: [
						[
							innerBlock,
							{
								className: `${ innerBlockClassName } lrv-a-span2`,
							},
						],
						[ innerBlock, { className: innerBlockClassName } ],
					],
					scope: [ 'block' ],
				},
				{
					name: 'three-columns-equal',
					title: __( '33 / 34 / 33', 'pmc-gutenberg' ),
					description: __(
						'Three columns; equal split',
						'pmc-gutenberg'
					),
					icon: (
						<SVG
							width="48"
							height="48"
							viewBox="0 0 48 48"
							xmlns="http://www.w3.org/2000/svg"
						>
							<Path
								fillRule="evenodd"
								d="M41 14a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v20a2 2 0 0 0 2 2h30a2 2 0 0 0 2-2V14zM28.5 34h-9V14h9v20zm2 0V14H39v20h-8.5zm-13 0H9V14h8.5v20z"
							/>
						</SVG>
					),
					isDefault: true,
					attributes: {
						className:
							'lrv-a-grid lrv-a-cols lrv-a-cols3@desktop lrv-a-cols3@tablet',
					},
					innerBlocks: [
						[ innerBlock, { className: innerBlockClassName } ],
						[ innerBlock, { className: innerBlockClassName } ],
						[ innerBlock, { className: innerBlockClassName } ],
					],
					scope: [ 'block' ],
				},
				{
					name: 'three-columns-first-larger',
					title: __( '50 / 25 / 25', 'pmc-gutenberg' ),
					description: __(
						'Three columns; half, quarter, quarter split',
						'pmc-gutenberg'
					),
					attributes: {
						className:
							'lrv-a-grid lrv-a-cols lrv-a-cols4@tablet lrv-a-cols4@desktop',
					},
					innerBlocks: [
						[
							innerBlock,
							{
								className: `${ innerBlockClassName } lrv-a-span2`,
							},
						],
						[ innerBlock, { className: innerBlockClassName } ],
						[ innerBlock, { className: innerBlockClassName } ],
					],
					scope: [ 'block' ],
				},
				{
					name: 'three-columns-middle-larger',
					title: __( '25 / 50 / 25', 'pmc-gutenberg' ),
					description: __(
						'Three columns; quarter, half, quarter split',
						'pmc-gutenberg'
					),
					icon: (
						<SVG
							width="48"
							height="48"
							viewBox="0 0 48 48"
							xmlns="http://www.w3.org/2000/svg"
						>
							<Path
								fillRule="evenodd"
								d="M41 14a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v20a2 2 0 0 0 2 2h30a2 2 0 0 0 2-2V14zM31 34H17V14h14v20zm2 0V14h6v20h-6zm-18 0H9V14h6v20z"
							/>
						</SVG>
					),
					attributes: {
						className:
							'lrv-a-grid lrv-a-cols lrv-a-cols4@tablet lrv-a-cols4@desktop',
					},
					innerBlocks: [
						[ innerBlock, { className: innerBlockClassName } ],
						[
							innerBlock,
							{
								className: `${ innerBlockClassName } lrv-a-span2`,
							},
						],
						[ innerBlock, { className: innerBlockClassName } ],
					],
					scope: [ 'block' ],
				},
				{
					name: 'three-columns-right-larger',
					title: __( '25 / 25 / 50', 'pmc-gutenberg' ),
					description: __(
						'Three columns; quarter, quarter, half split',
						'pmc-gutenberg'
					),
					attributes: {
						className:
							'lrv-a-grid lrv-a-cols lrv-a-cols4@tablet lrv-a-cols4@desktop',
					},
					innerBlocks: [
						[ innerBlock, { className: innerBlockClassName } ],
						[ innerBlock, { className: innerBlockClassName } ],
						[
							innerBlock,
							{
								className: `${ innerBlockClassName } lrv-a-span2`,
							},
						],
					],
					scope: [ 'block' ],
				},
				{
					name: 'four-columns',
					title: __( '25 / 25 / 25 / 25', 'pmc-gutenberg' ),
					description: __(
						'Four columns; equal split',
						'pmc-gutenberg'
					),
					attributes: {
						className:
							'lrv-a-grid lrv-a-cols lrv-a-cols2@tablet lrv-a-cols4@desktop',
					},
					innerBlocks: [
						[ innerBlock, { className: innerBlockClassName } ],
						[ innerBlock, { className: innerBlockClassName } ],
						[ innerBlock, { className: innerBlockClassName } ],
						[ innerBlock, { className: innerBlockClassName } ],
					],
					scope: [ 'block' ],
				},
			],
		}
	);
};

addFilter( filterTag, filterNamespace, modifyBlock );
