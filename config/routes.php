<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
use Cake\Core\Configure;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use CakeDC\Users\Middleware\SocialAuthMiddleware;

Router::plugin('CakeDC/Users', ['path' => '/users'], function (RouteBuilder $routes) {
    $routes->fallbacks('DashedRoute');
});

Router::connect('/accounts/validate/*', [
    'plugin' => 'CakeDC/Users',
    'controller' => 'SocialAccounts',
    'action' => 'validate'
]);
// Google Authenticator related routes
if (Configure::read('Users.GoogleAuthenticator.login')) {
    Router::connect('/verify', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'verify']);

    Router::connect('/resetGoogleAuthenticator', [
        'plugin' => 'CakeDC/Users',
        'controller' => 'Users',
        'action' => 'resetGoogleAuthenticator'
    ]);
}

Router::connect('/profile/*', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'profile']);
Router::connect('/login', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'login']);
Router::connect('/logout', ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'logout']);
Router::connect('/link-social/*', [
    'controller' => 'Users',
    'action' => 'linkSocial',
    'plugin' => 'CakeDC/Users',
]);
Router::connect('/callback-link-social/*', [
    'controller' => 'Users',
    'action' => 'callbackLinkSocial',
    'plugin' => 'CakeDC/Users',
]);
