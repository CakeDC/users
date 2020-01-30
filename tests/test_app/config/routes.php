<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::extensions('json');
Router::scope('/', function (RouteBuilder $routes) {
    $routes->fallbacks();
});
