import AddToCalendar from './AddToCalendar';

// Initialize all Dropdowns.
export default function initAddToCalendar() {
	const addToCalendars = [
		...document.querySelectorAll( '.js-AddToCalendar' ),
	];
	addToCalendars.forEach(
		( el ) => ( el.pmcAddToCalendar = new AddToCalendar( el ) )
	);
}
