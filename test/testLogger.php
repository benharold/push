<?php

include('../vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('APNS Server Logger');
$log->pushHandler(new StreamHandler('test.log', Logger::WARNING));

$log->warning('Foo');
$log->warning('Bar');
