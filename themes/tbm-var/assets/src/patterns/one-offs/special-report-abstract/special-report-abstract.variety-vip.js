const clonedeep = require( 'lodash.clonedeep' );

const special_report_abstract_prototype = require( './speciaL-report-abstract.prototype' );
const special_report_abstract = clonedeep( special_report_abstract_prototype );

special_report_abstract.special_report_lock = require( '../../modules/special-report-lock/special-report-lock.variety-vip' );

module.exports = {
	...special_report_abstract,
};
