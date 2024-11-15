<?php

use App\Config\ErrorLog;
use App\Config\ResponseHttp;
use App\Routes\Router;

require dirname(__DIR__) . '/vendor/autoload.php';

ErrorLog::activateErrorLog(); 

$router = new Router;
$router->run();