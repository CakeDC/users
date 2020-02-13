<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2020, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Middleware\UnauthorizedHandler;

use Authorization\Exception\ForbiddenException;
use Authorization\Exception\MissingIdentityException;
use Authorization\Middleware\UnauthorizedHandler\CakeRedirectHandler;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This handler will redirect the response if one of configured exception classes is encountered.
 *
 * CakePHP Router compatible array URL syntax is supported.
 */
class DefaultRedirectHandler extends CakeRedirectHandler
{
    /**
     * @inheritDoc
     */
    protected $defaultOptions = [
        'exceptions' => [
            'MissingIdentityException' => MissingIdentityException::class,
            'ForbiddenException' => ForbiddenException::class,
        ],
        'url' => [
            'controller' => 'Users',
            'action' => 'login',
        ],
        'queryParam' => 'redirect',
        'statusCode' => 302,
    ];

    /**
     * @inheritDoc
     */
    protected function getUrl(ServerRequestInterface $request, array $options): string
    {
        $url = $options['url'];
        if (is_callable($url)) {
            return $url($request, $options);
        }

        if ($request->getAttribute('identity')) {
            return $request->referer() ?? '/';
        }

        if ($options['queryParam'] !== null) {
            $url['?'][$options['queryParam']] = (string)$request->getUri();
        }

        return Router::url($url);
    }
}
