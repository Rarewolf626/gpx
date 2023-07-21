<?php

use GPX\GPXAdmin\Controller\Resort\AddResortController;
use GPX\GPXAdmin\Router\GpxAdminRouter;
use GPX\GPXAdmin\Controller\TestController;

/** @var GpxAdminRouter $router */
//$router = gpx(GpxAdminRouter::class);

$router->add('hello', [TestController::class ,'index'], ['name']);
$router->add('resorts_add', AddResortController::class);
