<?php

namespace Phoenix;

require_once __DIR__ . '/vendor/autoload.php';

$init = (new Init())
    ->startUp();
(new CRON( $init, $argv ))->doActions();

die();