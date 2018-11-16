<?php

namespace CakeDC\Users\Authentication;

use Authentication\AuthenticationService as BaseService;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Authentication\Authenticator\StatelessInterface;
use CakeDC\Users\Auth\TwoFactorAuthenticationCheckerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class AuthenticationService extends BaseService
{
    const NEED_GOOGLE_VERIFY = 'NEED_GOOGLE_VERIFY';

    const GOOGLE_VERIFY_SESSION_KEY = 'temporarySession';

    /**
     * All failures authenticators
     *
     * @var Failure[]
     */
    protected $failures = [];
    /**
     * Proceed to google verify action after a valid result result
     *
     * @param ServerRequestInterface $request response to manipulate
     * @param ResponseInterface $response base response to manipulate
     * @param ResultInterface $result valid result
     * @return array with result, request and response keys
     */
    protected function proceedToGoogleVerify(ServerRequestInterface $request, ResponseInterface $response, ResultInterface $result)
    {
        $request->getSession()->write(self::GOOGLE_VERIFY_SESSION_KEY, $result->getData());

        $result = new Result(null, self::NEED_GOOGLE_VERIFY);

        $this->_successfulAuthenticator = null;
        $this->_result = $result;

        return compact('result', 'request', 'response');
    }

    /**
     * Get the configured two factory authentication
     *
     * @return \CakeDC\Users\Auth\TwoFactorAuthenticationCheckerInterface
     */
    protected function getTwoFactorAuthenticationChecker()
    {
        return (new TwoFactorAuthenticationCheckerFactory())->build();
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException Throws a runtime exception when no authenticators are loaded.
     */
    public function authenticate(ServerRequestInterface $request, ResponseInterface $response)
    {
        if ($this->authenticators()->isEmpty()) {
            throw new RuntimeException(
                'No authenticators loaded. You need to load at least one authenticator.'
            );
        }

        $twoFaCheck = $this->getTwoFactorAuthenticationChecker();
        $this->failures = [];
        $result = null;
        foreach ($this->authenticators() as $authenticator) {
            $result = $authenticator->authenticate($request, $response);

            if ($result->isValid()) {
                $twoFaRequired = $twoFaCheck->isRequired($result->getData()->toArray());
                if ($twoFaRequired && $authenticator->getConfig('skipGoogleVerify') !== true) {
                    return $this->proceedToGoogleVerify($request, $response, $result);
                }

                if (!($authenticator instanceof StatelessInterface)) {
                    $requestResponse = $this->persistIdentity($request, $response, $result->getData());
                    $request = $requestResponse['request'];
                    $response = $requestResponse['response'];
                }

                $this->_successfulAuthenticator = $authenticator;
                $this->_result = $result;

                return [
                    'result' => $result,
                    'request' => $request,
                    'response' => $response
                ];
            } else {
                $this->failures[] = new Failure($authenticator, $result);
            }

            if (!$result->isValid() && $authenticator instanceof StatelessInterface) {
                $authenticator->unauthorizedChallenge($request);
            }
        }

        $this->_successfulAuthenticator = null;
        $this->_result = $result;

        return [
            'result' => $result,
            'request' => $request,
            'response' => $response
        ];
    }

    /**
     * Get list the list of failures processed
     *
     * @return Failure[]
     */
    public function getFailures()
    {
        return $this->failures;
    }
}
