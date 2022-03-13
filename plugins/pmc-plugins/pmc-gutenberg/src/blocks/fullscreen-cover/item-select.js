/**
 * WordPress dependencies.
 */
import { SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ItemSelects = ( {
	props: {
		items,
		storyOptions,
		setAttributes,
		isSelected,
		classNameGridWrapper,
		classNameGridItem,
	},
} ) => {
	const options = [];

	for ( let i = 0; i <= 5; i++ ) {
		// When block isn't selected, hide inputs in favor of titles.
		if ( ! isSelected ) {
			if ( !! items[ i ].postId ) {
				let title = items[ i ].title;

				if ( ! title ) {
					const storyOption = storyOptions.filter(
						( option ) => option.value === items[ i ].postId
					);

					if ( storyOption.length ) {
						title = storyOption[ 0 ].label;
					} else {
						continue;
					}
				}

				options.push(
					<li key={ i } className={ classNameGridWrapper }>
						{ title }
					</li>
				);
			}

			continue;
		}

		// When block is selected, render select for post and title override.
		options.push(
			<li key={ i } className={ classNameGridWrapper }>
				<div className={ classNameGridItem }>
					<SelectControl
						label={ __(
							'Select post to highlight',
							'pmc-gutenberg'
						) }
						hideLabelFromVision
						options={ storyOptions }
						value={ items[ i ].postId }
						onChange={ ( value ) => {
							const newItems = [ ...items ];
							newItems[ i ].postId = parseInt( value, 10 );

							setAttributes( { items: newItems } );
						} }
					/>
				</div>

				<TextControl
					value={ items[ i ].title }
					placeholder={ __(
						'Enter title override',
						'pmc-gutenberg'
					) }
					label={ __( 'Enter title override', 'pmc-gutenberg' ) }
					hideLabelFromVision
					onChange={ ( value ) => {
						const newItems = [ ...items ];
						newItems[ i ].title = value;

						setAttributes( { items: newItems } );
					} }
					className={ classNameGridItem }
				/>
			</li>
		);
	}

	return options;
};

export default ItemSelects;
