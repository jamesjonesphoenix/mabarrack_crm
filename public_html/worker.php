<?php

namespace Phoenix;

use Phoenix\Entity\CurrentUser;

$init = (new Init())->startUp();
$director = new WorkerDirector(
    $init->getDB(),
    $init->getMessages(),
    CurrentUser::instance()
);
$director->doWorkerAction( $_GET );
$director->getPageBuilder( $_GET )->buildPage()->getPage()->render();