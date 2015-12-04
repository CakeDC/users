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
use Cake\Core\Plugin;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;

Configure::load('CakeDC/Users.users');
collection((array)Configure::read('Users.config'))->each(function ($file) {
    Configure::load($file);
});

if (Configure::check('Users.auth')) {
    Configure::write('Auth.authenticate.all.userModel', Configure::read('Users.table'));
}

if (Configure::read('Users.Social.login')) {
    Plugin::load('Muffin/OAuth2');
    EventManager::instance()->on(\CakeDC\Users\Controller\Component\UsersAuthComponent::EVENT_FAILED_SOCIAL_LOGIN, [new \CakeDC\Users\Controller\UsersController(), 'failedSocialLoginListener']);
}
