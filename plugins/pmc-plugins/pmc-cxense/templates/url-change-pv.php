<?php
/**
 *  Js code to send pv on global url change.
 */
?>
if ('undefined' !== typeof window.cxpmc && 'function' === typeof window.cxpmc.report_gallery_pv) {
	window.cxpmc.report_gallery_pv();
}
