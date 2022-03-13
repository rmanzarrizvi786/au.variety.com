import { ToggleControl } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';

const Render = ( props ) => {
	const { currentValue, help, label, updateValue } = props;
	const isChecked = !! currentValue;

	return (
		<ToggleControl
			label={ label }
			help={ help[ isChecked ] }
			checked={ isChecked }
			onChange={ ( value ) => {
				updateValue( value );
			} }
		/>
	);
};

const MetaToggle = compose( [
	withSelect( ( select, { metaKey } ) => {
		const currentValue = select( 'core/editor' ).getEditedPostAttribute(
			'meta'
		)[ metaKey ];

		return {
			currentValue,
		};
	} ),
	withDispatch( ( dispatch, { metaKey } ) => {
		return {
			updateValue: ( value ) => {
				// Delete the key by returning `null` rather than `false`.
				const newValue = !! value ? true : null;

				dispatch( 'core/editor' ).editPost( {
					meta: {
						[ metaKey ]: newValue,
					},
				} );
			},
		};
	} ),
] )( Render );

export default MetaToggle;
