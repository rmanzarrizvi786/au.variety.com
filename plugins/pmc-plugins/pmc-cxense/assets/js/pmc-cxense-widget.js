/* eslint-disable */
window.addEventListener('load', () => {
	const cxense = document.querySelectorAll('.cxense-widget-div');
	const cX = (window.cX = window.cX || {});
	cX.callQueue = cX.callQueue || [];
	cX.CCE = cX.CCE || {};
	cX.CCE.callQueue = cX.CCE.callQueue || [];
	let i;
	for (i = 0; i < cxense.length; i++) {
		const el = cxense[i];
		if ('undefined' !== typeof cX && '' !== el.dataset.widget_id) {
			cX.CCE.callQueue.push([
				'run',
				{
					widgetId: el.dataset.widget_id,
					targetElementId: el.id,
				},
			]);
		}
	}
});
