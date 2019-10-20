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
use CakeDC\Users\Utility\UsersUrl;
use Cake\Core\Configure;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
//Use custom path if url is customized
$baseUsersPath = UsersUrl::isCustom() ? '/users-base' : '/users';
Router::plugin('CakeDC/Users', ['path' => $baseUsersPath], function (RouteBuilder $routes) {
    $routes->fallbacks('DashedRoute');
});
Router::connect('/accounts/validate/*', [
    'plugin' => 'CakeDC/Users',
    'controller' => 'SocialAccounts',
    'action' => 'validate'
]);
// Google Authenticator related routes
if (Configure::read('OneTimePasswordAuthenticator.login')) {
    Router::connect('/verify', UsersUrl::actionUrl('verify'));

    Router::connect('/resetOneTimePasswordAuthenticator', UsersUrl::actionUrl('resetOneTimePasswordAuthenticator'));
}

Router::connect('/profile/*', UsersUrl::actionUrl('profile'));
Router::connect('/login', UsersUrl::actionUrl('login'));
Router::connect('/logout', UsersUrl::actionUrl('logout'));
Router::connect('/link-social/*', UsersUrl::actionUrl('linkSocial'));
Router::connect('/callback-link-social/*', UsersUrl::actionUrl('callbackLinkSocial'));
