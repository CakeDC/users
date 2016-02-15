<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (+1 702 425 5085) (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (+1 702 425 5085) (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Core\Exception\MissingPluginException;
use Cake\Core\Plugin;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

Configure::load('CakeDC/Users.users');
collection((array)Configure::read('Users.config'))->each(function ($file) {
    Configure::load($file);
});

if (Configure::check('Users.auth')) {
    Configure::write('Auth.authenticate.all.userModel', Configure::read('Users.table'));
}

if (Configure::read('Users.Social.login') && php_sapi_name() != 'cli') {
    try {
        EventManager::instance()->on(\CakeDC\Users\Controller\Component\UsersAuthComponent::EVENT_FAILED_SOCIAL_LOGIN, [new \CakeDC\Users\Controller\UsersController(), 'failedSocialLoginListener']);
    } catch (MissingPluginException $e) {
       Log::error($e->getMessage());
    }
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