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
    Router::connect('/verify', UsersUrl::actionParams('verify'));

    Router::connect('/resetOneTimePasswordAuthenticator', UsersUrl::actionParams('resetOneTimePasswordAuthenticator'));
}

Router::connect('/profile/*', UsersUrl::actionParams('profile'));
Router::connect('/login', UsersUrl::actionParams('login'));
Router::connect('/logout', UsersUrl::actionParams('logout'));
Router::connect('/link-social/*', UsersUrl::actionParams('linkSocial'));
Router::connect('/callback-link-social/*', UsersUrl::actionParams('callbackLinkSocial'));
