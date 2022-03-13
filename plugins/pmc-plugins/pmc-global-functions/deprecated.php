<?php

function pmc_truncate( $text, $limit = 20, $append = 'ellipsis', $strip_html = false ) {
	return PMC::truncate( $text, $limit, $append, $strip_html );
}

function pmc_convert_chars_to_normal($text, $type='text') {
	return PMC::untexturize( $text, $type );
}


//EOF