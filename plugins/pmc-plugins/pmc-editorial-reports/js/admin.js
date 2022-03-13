/**
 * JS for admin UI of PMC Editorial Reports
 *
 * @author Amit Gupta
 *
 * @since 2013-06-07
 * @version 2013-06-07
 * @version 2013-06-13
 * @version 2013-06-17
 */

jQuery(document).ready(function($) {
	/**
	 * This function accepts a date in MM/DD/YYYY format and day of week (either 0 or 6)
	 * and changes the date to that day of week. If day of week passed is 0 then date
	 * is changed to previous Sunday, if day of week passed is 6 then date is changed
	 * to next Saturday
	 *
	 * @since 2013-06-17 Amit Gupta
	 * @version 2013-06-17 Amit Gupta
	 */
	function get_date_snapped_to_weekend( txt_date, weekend ) {
		if( ! txt_date || typeof weekend == 'undefined' ) {
			return;
		}

		weekend = ( weekend === 0 ) ? 0 : 6;	//it can either be Sunday or Saturday

		if( txt_date.indexOf('/') < 1 || txt_date.length < 8 ) {
			return;
		}

		txt_date = txt_date.split( '/' );

		if( txt_date.length !== 3 ) {
			return;
		}

		var day_ms = 24 * 3600 * 1000;	//day in milliseconds
		var o_date = new Date( txt_date[2], ( txt_date[0] - 1 ), txt_date[1] );	//selected date's object
		var day_of_week = o_date.getDay();

		var timestamp = o_date.getTime();

		switch( weekend ) {
			case 0:
				if( day_of_week !== weekend ) {
					timestamp = timestamp - ( day_of_week * day_ms );
				}

				break;

			case 6:
			default:
				if( day_of_week !== weekend ) {
					timestamp = timestamp + ( ( weekend - day_of_week ) * day_ms );
				}

				break;
		}

		var o_new_date = new Date( timestamp );

		var new_day = o_new_date.getDate();
		new_day = ( new_day < 10 ) ? '0' + new_day : new_day;

		var new_month = o_new_date.getMonth() + 1;
		new_month = ( new_month < 10 ) ? '0' + new_month : new_month;

		return new_month + '/' + new_day + '/' + o_new_date.getFullYear();
	}

	/**
	 * This function changes start and end dates in form to previous Sunday and
	 * next Saturday respectively (if there is any change in selected dates)
	 *
	 * @since 2013-06-17 Amit Gupta
	 * @version 2013-06-17 Amit Gupta
	 */
	function snap_dates() {
		//run on start date
		var start_date = $('#pmc_er_start_date').val();

		//run only if start date has changed
		if( ( ! arr_dates[0] || arr_dates[0] !== start_date ) && start_date.length >= 8 ) {
			start_date = get_date_snapped_to_weekend( start_date, 0 );	//get last Sunday's date

			if( start_date ) {
				arr_dates[0] = start_date;
				$('#pmc_er_start_date_cal').datepicker( "setDate", new Date( start_date ) );
			}
		}

		//run on end date
		var end_date = $('#pmc_er_end_date').val();

		//run only if end date has changed
		if( ( ! arr_dates[1] || arr_dates[1] !== end_date ) && end_date.length >= 8 ) {
			end_date = get_date_snapped_to_weekend( end_date, 6 );	//get next Saturday's date

			if( end_date ) {
				arr_dates[1] = end_date;
				$('#pmc_er_end_date_cal').datepicker( "setDate", new Date( end_date ) );
			}
		}

		setTimeout( snap_dates, 500 );	//call self in 500 ms
	}

	/**
	 * Setup datepickers for start & end dates
	 *
	 * @since 2013-06-07 Amit Gupta
	 * @version 2013-06-13 Amit Gupta
	 * @version 2013-06-17 Amit Gupta
	 */
	$( function() {
		$("#pmc_er_start_date_cal").datepicker( {
			altField: "#pmc_er_start_date",
			changeMonth: true,
			changeYear: true,
			numberOfMonths: 1
		} );

		$("#pmc_er_end_date_cal").datepicker( {
			altField: "#pmc_er_end_date",
			changeMonth: true,
			changeYear: true,
			numberOfMonths: 1
		} );
	} );

	/**
	 * basic form validation
	 *
	 * @since 2013-06-17 Amit Gupta
	 * @version 2013-06-17 Amit Gupta
	 */
	$('#pmc_er_form').on( 'submit', function() {
		var start_date = $('#pmc_er_start_date').val();
		var end_date = $('#pmc_er_end_date').val();

		if( ! start_date || ! end_date ) {
			alert( "You must select both Start and End dates" );
			return false;
		}

		var o_start_date = new Date( start_date );
		var start_ts = o_start_date.getTime();

		var o_end_date = new Date( end_date );
		var end_ts = o_end_date.getTime();

		if( start_ts >= end_ts ) {
			alert( "Start date must be earlier than End date" );
			return false;
		}
	} );

	var arr_dates = [];	//global array that stores start & end dates (Sun & Sat)
	setTimeout( snap_dates, 500 );

});


//EOF
