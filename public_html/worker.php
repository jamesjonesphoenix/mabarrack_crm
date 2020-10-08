<?php

namespace Phoenix;

use Phoenix\Entity\CurrentUser;
use Phoenix\Utility\HTMLTags;

require_once __DIR__ . '/../vendor/autoload.php';

$init = (new Init())->startUp();
$director = new WorkerDirector(
    $init->getDB(),
    $init->getMessages(),
    new HTMLTags(),
    CurrentUser::instance()
);
$director->doWorkerAction( array_merge( $_GET, $_POST ) );
$director->getPageBuilder( $_GET )
    ->buildPage()
    ->getPage()
    ->render();