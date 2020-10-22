<?php

namespace Phoenix;
use Phoenix\Utility\HTMLTags;

require_once __DIR__ . '/../vendor/autoload.php';

$init = (new Init())
    ->startUp()
    ->doingAJAX();

$ajax = new Ajax(
    $init->getDB(),
    $init->getMessages(),
    new HTMLTags()
);

if ( $ajax->init( $_POST ?? [] ) ) {
    $ajax->doFormAction();
}
$ajax->returnData();