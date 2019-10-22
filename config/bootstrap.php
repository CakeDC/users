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
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

Configure::load('CakeDC/Users.users');
collection((array)Configure::read('Users.config'))->each(function ($file) {
    Configure::load($file);
});
UsersUrl::setupConfigUrls();

if (!TableRegistry::getTableLocator()->exists('Users')) {
    TableRegistry::getTableLocator()->setConfig('Users', ['className' => Configure::read('Users.table')]);
}
if (!TableRegistry::getTableLocator()->exists('CakeDC/Users.Users')) {
    TableRegistry::getTableLocator()->setConfig('CakeDC/Users.Users', ['className' => Configure::read('Users.table')]);
}

if (Configure::check('Auth.authenticate') || Configure::check('Auth.authorize')) {
    trigger_error("Users plugin configurations keys Auth.authenticate and Auth.authorize were removed, please check migration guide https://github.com/CakeDC/users/blob/master/Docs/Documentation/MigrationGuide.md'");
}
$oauthPath = Configure::read('OAuth.path');
if (is_array($oauthPath)) {
    Router::scope('/auth', function ($routes) use ($oauthPath) {
        $routes->connect(
            '/:provider',
            $oauthPath,
            ['provider' => implode('|', array_keys(Configure::read('OAuth.providers')))]
        );
    });
}
