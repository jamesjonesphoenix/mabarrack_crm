<?php

namespace Phoenix;

use Phoenix\Page\LoginPage;

require_once __DIR__ . '/../vendor/autoload.php';

$init = (new Init())->startUp();

(new LoginPage( $init->getHtmlUtility() ))
    ->setSystemTitle( $init->getConfig()['system_title'] )
    ->render(
    $init->getMessages()->getMessagesHTML()
);
