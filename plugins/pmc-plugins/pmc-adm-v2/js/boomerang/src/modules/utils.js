/**
 * Util functions.
 *
 */

/**
 * Check if the ad path has a specific keyword
 * @param slot {object} Ad unit
 * @param keyword {string} partial sting fo the ad unit path
 *
 * @return {boolean}
 */
function has_adunit_path(slot, keyword) {
    return ('object' === typeof slot && 'string' === typeof slot.subAdUnitPath && slot.subAdUnitPath.includes(keyword))
}

/**
 * Check and return interrupt adunit id if there is one on page
 *
 * @return {string} id of the adunit
 */
function get_interrupt_ad_id() {
    const interrupt_ad_container = '#pmc-adm-ad-interrupts div.pmc-adm-boomerang-pub-div div';
    const interrupt_ad_unit = document.querySelector( interrupt_ad_container );
    let adunit_id = '';

    if ( interrupt_ad_unit ) {
        adunit_id = interrupt_ad_unit.id;
    }

    return adunit_id;
}
export {
    has_adunit_path,
    get_interrupt_ad_id
}
