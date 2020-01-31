<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace TestApp;

use Cake\Core\Configure;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use CakeDC\Users\Plugin;

class Application extends BaseApplication
{
    public const EVENT_AFTER_PLUGIN_BOOTSTRAP = 'TestApp.afterPluginBootstrap';

    /**
     * @inheritDoc
     */
    public function bootstrap(): void
    {
        parent::bootstrap();
        if (!\Cake\Mailer\TransportFactory::getConfig('default')) {
            \Cake\Mailer\TransportFactory::setConfig([
                'default' => [
                    'className' => \Cake\Mailer\Transport\DebugTransport::class,
                ],
            ]);
        }
        if (!\Cake\Mailer\Email::getConfig('default')) {
            \Cake\Mailer\Email::setConfig([
                'default' => [
                    'transport' => 'default',
                    'from' => 'you@localhost',
                ],
            ]);
        }
        $this->addPlugin(Plugin::class);
    }

    /**
     * @inheritDoc
     */
    public function pluginBootstrap(): void
    {
        Configure::write('Users.config', ['users']);
        parent::pluginBootstrap();
        $this->dispatchEvent(static::EVENT_AFTER_PLUGIN_BOOTSTRAP);
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))
            ->add(new RoutingMiddleware($this));

        return $middlewareQueue;
    }
}
