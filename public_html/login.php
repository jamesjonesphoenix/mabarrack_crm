<?php

namespace Phoenix;

use Phoenix\Page\LoginPage;
use Phoenix\Utility\HTMLTags;

require_once __DIR__ . '/../vendor/autoload.php';

(new Init())->startUp();

$page = new LoginPage(new HTMLTags());
$page->render();