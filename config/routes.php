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
//Use custom path if url is customized
$baseUsersPath = UsersUrl::isCustom() ? '/users-base' : '/users';
$routes->plugin('CakeDC/Users', ['path' => $baseUsersPath], function (RouteBuilder $routes) {
    $routes->fallbacks('DashedRoute');
});

$routes->connect('/accounts/validate/*', [
    'plugin' => 'CakeDC/Users',
    'controller' => 'SocialAccounts',
    'action' => 'validate'
]);
// Google Authenticator related routes
if (Configure::read('OneTimePasswordAuthenticator.login')) {
    $routes->connect('/verify', UsersUrl::actionParams('verify'));

    $routes->connect('/resetOneTimePasswordAuthenticator', UsersUrl::actionParams('resetOneTimePasswordAuthenticator'));
}

$routes->connect('/profile/*', UsersUrl::actionParams('profile'));
$routes->connect('/login', UsersUrl::actionParams('login'));
$routes->connect('/logout', UsersUrl::actionParams('logout'));
$routes->connect('/link-social/*', UsersUrl::actionParams('linkSocial'));
$routes->connect('/callback-link-social/*', UsersUrl::actionParams('callbackLinkSocial'));
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
