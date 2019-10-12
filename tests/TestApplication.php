<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test;

use Cake\Http\MiddlewareQueue;

/**
 * Class TestApplication
 *
 * @package CakeDC\Users\Test
 */
class TestApplication extends \Cake\Http\BaseApplication
{
    /**
     * Setup the middleware queue
     *
     * @param \Cake\Http\MiddlewareQueue $middleware The middleware queue to set in your App Class
     *
     * @return \Cake\Http\MiddlewareQueue
     */

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            ->add(ErrorHandlerMiddleware::class)
            ->add(AssetMiddleware::class)
            ->add(new RoutingMiddleware($this, null));

        return $middlewareQueue;
    }

    /**
     * {@inheritDoc}
     */
    public function bootstrap(): void
    {
        parent::bootstrap();
        $this->addPlugin('CakeDC/Users', [
            'path' => dirname(dirname(__FILE__)) . DS,
            'routes' => true,
        ]);
    }
}
