<?php

if (class_exists('DOMDocument') ) {
    $classes = array(
    'IvoPetkov\HTML5DOMDocument'         => __DIR__ . '/parser/HTML5DOMDocument.php',
    'IvoPetkov\HTML5DOMDocument\Internal\QuerySelectors' => __DIR__ . '/parser/HTML5DOMDocument/Internal/QuerySelectors.php',
    'IvoPetkov\HTML5DOMElement'          => __DIR__ . '/parser/HTML5DOMElement.php',
    'IvoPetkov\HTML5DOMNodeList'         => __DIR__ . '/parser/HTML5DOMNodeList.php',
    );
    spl_autoload_register(
        function ( $class ) use ( $classes ) {
            if (isset($classes[ $class ]) ) {
                include $classes[ $class ];
            }
        }
    );
} else {
    error_log('ORIEL: Please install php-xml extension');
}

