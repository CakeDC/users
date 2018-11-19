<?php

namespace CakeDC\Users\Authenticator;

use Authentication\Authenticator\AbstractAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\UrlChecker\UrlCheckerTrait;
use CakeDC\Auth\Social\MapUser;
use CakeDC\Auth\Social\Service\ServiceInterface;
use CakeDC\Users\Exception\SocialAuthenticationException;
use Cake\Http\Exception\BadRequestException;
use Cake\Log\LogTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Social authenticator
 *
 * Authenticates an identity based on request attribute socialService (CakeDC\Auth\Social\Service\ServiceInterface)
 */
class SocialAuthenticator extends AbstractAuthenticator
{
    use LogTrait;
    use SocialAuthTrait;
    use UrlCheckerTrait;

    const SOCIAL_SERVICE_ATTRIBUTE = 'socialService';

    const FAILURE_ACCOUNT_NOT_ACTIVE = 'FAILURE_ACCOUNT_NOT_ACTIVE';

    const FAILURE_USER_NOT_ACTIVE = 'FAILURE_USER_NOT_ACTIVE';
    /**
     * Default config for this object.
     * - `fields` The fields to use to identify a user by.
     * - `loginUrl` Login URL or an array of URLs.
     * - `urlChecker` Url checker config.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * Authenticates the identity contained in a request. Will use the `config.userModel`, and `config.fields`
     * to find POST data that is used to find a matching record in the `config.userModel`. Will return false if
     * there is no post data, either username or password is missing, or if the scope conditions have not been met.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @param \Psr\Http\Message\ResponseInterface $response Unused response object.
     * @throws \Exception
     * @throws SocialAuthenticationException
     * @return \Authentication\Authenticator\ResultInterface
     */
    /**
     * @param ServerRequestInterface $request the cake request.
     * @param ResponseInterface $response the cake response.
     * @return Result|\Authentication\Authenticator\ResultInterface
     * @throws \Exception
     */
    public function authenticate(ServerRequestInterface $request, ResponseInterface $response)
    {
        $service = $request->getAttribute(self::SOCIAL_SERVICE_ATTRIBUTE);
        if ($service === null) {
            return new Result(null, Result::FAILURE_CREDENTIALS_MISSING);
        }

        $rawData = $this->getRawData($request, $service);
        if (empty($rawData)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
        }

        return $this->identify($rawData);
    }

    /**
     * Get user raw data from social provider
     *
     * @param ServerRequestInterface $request request object
     * @param ServiceInterface $service social service
     * @throws \Exception
     * @return array|null
     */
    private function getRawData(ServerRequestInterface $request, ServiceInterface $service)
    {
        $rawData = null;
        try {
            $rawData = $service->getUser($request);

            return (new MapUser())($service, $rawData);
        } catch (\Exception $exception) {
            $list = [BadRequestException::class, \UnexpectedValueException::class];
            $this->throwIfNotInlist($exception, $list);
            $message = sprintf(
                "Error getting an access token / retrieving the authorized user's profile data. Error message: %s %s",
                $exception->getMessage(),
                $exception
            );
            $this->log($message);

            return null;
        }
    }

    /**
     * Throw the exception if not in the list
     *
     * @param \Exception $exception exception thrown
     * @param array $list list of allowed exception classes
     * @throws \Exception
     * @return void
     */
    private function throwIfNotInlist(\Exception $exception, array $list)
    {
        $className = get_class($exception);
        if (!in_array($className, $list)) {
            throw $exception;
        }
    }
}
