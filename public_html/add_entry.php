<?php

namespace Phoenix;
define( 'DOING_AJAX', true );

require_once __DIR__ . '/../vendor/autoload.php';

$init = (new Init())->startUp();

$ajax = new Ajax(
    $init->getDB(),
    $init->getMessages()
);

if ( $ajax->init( $_POST ?? [] ) ) {
    $ajax->doFormAction();
}
$ajax->returnData();