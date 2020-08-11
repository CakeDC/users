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

use Authorization\Exception\Exception;
use Authorization\Exception\ForbiddenException;
use Authorization\Exception\MissingIdentityException;
use Authorization\Middleware\UnauthorizedHandler\CakeRedirectHandler;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\Routing\Router;
use Psr\Http\Message\ResponseInterface;
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
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'login',
        ],
        'queryParam' => 'redirect',
        'statusCode' => 302,
        'flash' => [],
    ];

    /**
     * @inheritDoc
     */
    public function handle(Exception $exception, ServerRequestInterface $request, array $options = []): ResponseInterface
    {
        $options += $this->defaultOptions;
        $response = parent::handle($exception, $request, $options);
        $session = $request->getAttribute('session');
        if ($session instanceof Session) {
            $this->addFlashMessage($session, $options);
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    protected function getUrl(ServerRequestInterface $request, array $options): string
    {
        $url = $options['url'];
        if (is_callable($url)) {
            return $url($request, $options);
        }

        if ($request->getAttribute('identity') && $request instanceof ServerRequest) {
            return $request->referer() ?? '/';
        }

        if ($options['queryParam'] !== null) {
            $url['?'][$options['queryParam']] = (string)$request->getUri();
        }

        return Router::url($url);
    }

    /**
     * Add a flash message informing location is not authorized.
     *
     * @param \Cake\Http\Session $session The CakePHP session.
     * @param array $options Defined options.
     * @return void
     */
    protected function addFlashMessage(Session $session, $options): void
    {
        $messages = (array)$session->read('Flash.flash');
        $messages[] = $this->createFlashMessage($options);
        $session->write('Flash.flash', $messages);
    }

    /**
     * Create a flash message data.
     *
     * @param array $options Handler options
     * @return array
     */
    protected function createFlashMessage($options): array
    {
        $message = (array)($options['flash'] ?? []);
        $message += [
            'message' => __d('cake_d_c/users', 'You are not authorized to access that location.'),
            'key' => 'flash',
            'element' => 'flash/error',
            'params' => [],
        ];

        return $message;
    }
}
