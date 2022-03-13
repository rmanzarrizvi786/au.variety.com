import React from 'react';

import Divider from './../../svg/divider';

/**
 * Counter component for runway gallery.
 */
class Counter extends React.Component {
	render() {
		const { currentSlide, totalSlide, i10n, isMediumSize } = this.props;
		const lookText = ( ! isMediumSize ) ? i10n.look + ' ' : '';
		const divider = isMediumSize ? i10n.of : <Divider />;
		const componentClass = isMediumSize ? 'c-gallery-runway-counter c-gallery-runway-counter--mobile' : 'c-gallery-runway-counter c-gallery-runway-counter--desktop';

		return (
			<div className={ componentClass } >
				<span className="c-gallery-runway-counter__current">{ lookText + currentSlide }</span>
				<span className="c-gallery-runway-counter__divider"> { divider } </span>
				<span className="c-gallery-runway-counter__total">{ totalSlide }</span>
			</div>
		);
	}
}

Counter.defaultProps = {
	currentSlide: '',
	totalSlide: '',
	isMediumSize: false,
	i10n: {
		look: '',
	},
};

export default Counter;
