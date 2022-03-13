<?php

// Render the meta tags to be loaded in the <head> of html
if ( isset( $meta_tags ) && isset( $allowed_html ) ) {
	echo wp_kses( $meta_tags, $allowed_html );
}
