/* eslint prefer-rest-params: 0 */

// Returns a function, that, when invoked, will only be triggered at most once
// during a given window of time. Normally, the throttled function will run
// as much as it can, without ever going more than once per `wait` duration;
// but if you'd like to disable the execution on the leading edge, pass
// `{leading: false}`. To disable execution on the trailing edge, ditto.
function throttle(func, wait, options) {
	let context;
	let args;
	let result;
	let timeout = null;
	let previous = 0;

	if (!options) options = {};

	const later = function() {
		previous = false === options.leading ? 0 : Date.now();
		timeout = null;
		result = func.apply(context, args);
		if (!timeout) {
			context = null;
			args = null;
		}
	};

	return function() {
		const now = Date.now();
		if (!previous && false === options.leading) previous = now;
		const remaining = wait - (now - previous);
		context = this;
		args = arguments;
		if (0 >= remaining || remaining > wait) {
			if (timeout) {
				clearTimeout(timeout);
				timeout = null;
			}
			previous = now;
			result = func.apply(context, args);
			if (!timeout) {
				context = null;
				args = null;
			}
		} else if (!timeout && false !== options.trailing) {
			timeout = setTimeout(later, remaining);
		}
		return result;
	};
}

export default throttle;
