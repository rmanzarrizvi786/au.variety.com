import React from 'react';

import Pinterest from './../svg/pinterest';

class PinIt extends React.Component {
	render() {
		const { pinterestUrl } = this.props;

		const attributes = {
			'data-pin-custom': true,
			'data-pin-log': 'button_pinit',
			'data-pin-href': pinterestUrl,
		};

		return (
			<a className="c-gallery-slider__pinit u-gallery-pinit" { ...attributes } >
				<Pinterest />
			</a>
		);
	}
}

PinIt.defaultProps = {
	pinterestUrl: '',
};

export default PinIt;
