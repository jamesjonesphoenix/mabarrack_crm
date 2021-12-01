<?php

namespace Phoenix;

require_once __DIR__ . '/vendor/autoload.php';
(new CRON( new Init(), $argv ))->doActions();
die();