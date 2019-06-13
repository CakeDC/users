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

namespace CakeDC\Users\Authenticator;

use Authentication\Authenticator\AbstractAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Authentication\UrlChecker\UrlCheckerTrait;
use Cake\Core\Configure;
use CakeDC\Users\Controller\Traits\ReCaptchaTrait;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Social Pending Email authenticator
 *
 * Authenticates an identity based on user social data and email POST data of the request.
 */
class SocialPendingEmailAuthenticator extends AbstractAuthenticator
{
    use ReCaptchaTrait;
    use SocialAuthTrait;
    use UrlCheckerTrait;

    public const FAILURE_INVALID_RECAPTCHA = 'FAILURE_INVALID_RECAPTCHA';

    public const FAILURE_ACCOUNT_NOT_ACTIVE = 'FAILURE_ACCOUNT_NOT_ACTIVE';

    public const FAILURE_USER_NOT_ACTIVE = 'FAILURE_USER_NOT_ACTIVE';

    /**
     * Default config for this object.
     * - `fields` The fields to use to identify a user by.
     * - `loginUrl` Login URL or an array of URLs.
     * - `urlChecker` Url checker config.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'loginUrl' => [
            'plugin' => 'CakeDC/Users',
            'controller' => 'Users',
            'action' => 'socialEmail',
        ],
        'urlChecker' => 'Authentication.CakeRouter',
    ];

    /**
     * Prepares the error object for a login URL error
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @return \Authentication\Authenticator\ResultInterface
     */
    protected function _buildLoginUrlErrorResult($request)
    {
        $errors = [
            sprintf(
                'Login URL `%s` did not match `%s`.',
                (string)$request->getUri(),
                implode('` or `', (array)$this->getConfig('loginUrl'))
            ),
        ];

        return new Result(null, Result::FAILURE_OTHER, $errors);
    }

    /**
     * Authenticates the identity contained in a request. Will use the `config.userModel`, and `config.fields`
     * to find POST data that is used to find a matching record in the `config.userModel`. Will return false if
     * there is no post data, either username or password is missing, or if the scope conditions have not been met.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @return \Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        if (!$this->_checkUrl($request)) {
            return $this->_buildLoginUrlErrorResult($request);
        }
        $rawData = $request->getAttribute('session')->read(Configure::read('Users.Key.Session.social'));
        $body = $request->getParsedBody();
        $email = $body['email'] ?? null;

        if (empty($rawData) || empty($email)) {
            return new Result(null, Result::FAILURE_CREDENTIALS_MISSING);
        }
        $rawData['email'] = $email;

        return $this->identify($rawData);
    }
}
