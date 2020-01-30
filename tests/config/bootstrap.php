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

use Cake\Core\Configure;

Configure::write('App', [
    'namespace' => 'TestApp',
    'encoding' => 'UTF-8',
    'base' => false,
    'baseUrl' => false,
    'dir' => 'src',
    'webroot' => WEBROOT_DIR,
    'wwwRoot' => WWW_ROOT,
    'fullBaseUrl' => 'http://localhost',
    'imageBaseUrl' => 'img/',
    'jsBaseUrl' => 'js/',
    'cssBaseUrl' => 'css/',
    'paths' => [
        'plugins' => [dirname(APP) . DS . 'plugins' . DS],
        'templates' => [dirname(APP) . DS . 'templates' . DS],
    ],
]);
\Cake\Mailer\TransportFactory::setConfig([
    'default' => [
        'className' => \Cake\Mailer\Transport\DebugTransport::class,
    ],
]);
\Cake\Mailer\Email::setConfig([
    'default' => [
        'transport' => 'default',
        'from' => 'you@localhost',
    ],
]);
\Cake\Utility\Security::setSalt('yoyz186elmi66ab9pz4imbb3tgy9vnsgsfgwe2r8tyxbbfdygu9e09tlxyg8p7dq');

