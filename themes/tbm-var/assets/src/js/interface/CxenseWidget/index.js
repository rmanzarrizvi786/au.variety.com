export default function initCxenseWidget() {
	const cxense = [ ...document.querySelectorAll( '.cxense-widget-div' ) ];
	cxense.forEach( ( el ) => {
		if ( 'undefined' !== typeof cX && '' !== el.dataset.widget_id ) {
			cX.CCE.run( {
				widgetId: el.dataset.widget_id,
				targetElementId: el.id,
			} );
		}
	} );
}
