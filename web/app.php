<?php
/** @var string $medium */
$api = require_once dirname(__DIR__) . '/init/boot.php';

require dirname(__DIR__) . "/init/config_{$medium}.php";
require dirname(__DIR__) . '/init/controllers.php';

$api->run();