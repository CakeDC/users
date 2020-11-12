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

namespace CakeDC\Users\Provider;

use Cake\Core\Configure;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Trait ServiceProviderLoaderTrait
 *
 * @package CakeDC\Users\Provider
 */
trait ServiceProviderLoaderTrait
{
    /**
     * Load a service defined in configuration $loaderKey
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param string $loaderKey service loader key
     * @return mixed
     */
    protected function loadService(ServerRequestInterface $request, $loaderKey)
    {
        $serviceLoader = $this->getLoader($loaderKey);

        return $serviceLoader($request);
    }

    /**
     * Get the loader callable
     *
     * @param string $loaderKey loader configuration key
     * @return callable
     */
    protected function getLoader($loaderKey)
    {
        $serviceLoader = Configure::read($loaderKey);
        if (is_string($serviceLoader)) {
            $serviceLoader = new $serviceLoader();
        }

        return $serviceLoader;
    }
}
