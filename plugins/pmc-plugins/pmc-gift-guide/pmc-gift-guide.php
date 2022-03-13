<?php
define( 'PMC_GIFT_GUIDE_DIR', __DIR__ );

require_once( PMC_GIFT_GUIDE_DIR . '/dependencies.php' );

\PMC\Gift_Guide\Common::get_instance();
\PMC\Gift_Guide\Link_Content::get_instance();

//EOF
