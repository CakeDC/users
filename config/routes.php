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
use CakeDC\Users\Utility\UsersUrl;

$routes->connect('/accounts/validate/*', [
    'plugin' => 'CakeDC/Users',
    'controller' => 'SocialAccounts',
    'action' => 'validate'
]);
// Google Authenticator related routes
if (Configure::read('OneTimePasswordAuthenticator.login')) {
    $routes->connect('/verify', UsersUrl::actionRouteParams('verify'));

    $routes->connect('/resetOneTimePasswordAuthenticator', UsersUrl::actionRouteParams('resetOneTimePasswordAuthenticator'));
}

$routes->connect('/profile/*', UsersUrl::actionRouteParams('profile'));
$routes->connect('/login', UsersUrl::actionRouteParams('login'));
$routes->connect('/logout', UsersUrl::actionRouteParams('logout'));
$routes->connect('/link-social/*', UsersUrl::actionRouteParams('linkSocial'));
$routes->connect('/callback-link-social/*', UsersUrl::actionRouteParams('callbackLinkSocial'));
$routes->connect('/register', UsersUrl::actionRouteParams('register'));

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

$routes->connect('/social-accounts/:action/*', [
    'plugin' => 'CakeDC/Users',
    'controller' => 'SocialAccounts',
]);
$routes->connect('/users/:action/*', UsersUrl::actionRouteParams(null));
