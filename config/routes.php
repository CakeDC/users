<?php
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * @var \Cake\Routing\RouteBuilder $routes
 */
use Cake\Core\Configure;
use Cake\Routing\RouteBuilder;
$routes->plugin('CakeDC/Users', ['path' => '/users'], function (RouteBuilder $routes) {
    $routes->fallbacks('DashedRoute');
});

$routes->connect('/accounts/validate/*', [
    'plugin' => 'CakeDC/Users',
    'controller' => 'SocialAccounts',
    'action' => 'validate'
]);
// Google Authenticator related routes
if (Configure::read('OneTimePasswordAuthenticator.login')) {
    $routes->connect('/verify', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'verify']);

    $routes->connect('/resetOneTimePasswordAuthenticator', [
        'plugin' => 'CakeDC/Users',
        'controller' => 'Users',
        'action' => 'resetOneTimePasswordAuthenticator'
    ]);
}

$routes->connect('/profile/*', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile']);
$routes->connect('/login', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);
$routes->connect('/logout', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'logout']);
$routes->connect('/link-social/*', [
    'controller' => 'Users',
    'action' => 'linkSocial',
    'plugin' => 'CakeDC/Users',
]);
$routes->connect('/callback-link-social/*', [
    'controller' => 'Users',
    'action' => 'callbackLinkSocial',
    'plugin' => 'CakeDC/Users',
]);
$oauthPath = Configure::read('OAuth.path');
if (is_array($oauthPath)) {
    $routes->scope('/auth', function (RouteBuilder $routes) use ($oauthPath) {
        $routes->connect(
            '/:provider',
            $oauthPath,
            ['provider' => implode('|', array_keys(Configure::read('OAuth.providers')))]
        );
    });
}
