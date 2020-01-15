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

namespace CakeDC\Users\Middleware;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use CakeDC\Users\Utility\UsersUrl;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SocialEmailMiddleware extends SocialAuthMiddleware
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute('session');
        if (!(new UsersUrl())->checkActionOnRequest('socialEmail', $request)) {
            $session->delete(Configure::read('Users.Key.Session.social'));

            return $handler->handle($request);
        }

        if (!$session->check(Configure::read('Users.Key.Session.social'))) {
            throw new NotFoundException();
        }

        return $this->goNext($request, $handler);
    }
}
