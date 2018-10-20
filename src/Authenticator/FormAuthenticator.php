<?php

namespace CakeDC\Users\Authenticator;

use Authentication\Authenticator\AuthenticatorInterface;
use Authentication\Authenticator\FormAuthenticator as BaseFormAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Identifier\IdentifierInterface;
use CakeDC\Users\Controller\Traits\ReCaptchaTrait;
use Cake\Core\Configure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FormAuthenticator implements AuthenticatorInterface, AuthenticatorFeedbackInterface
{
    use ReCaptchaTrait;

    /**
     * Failure due invalid reCAPTCHA
     */
    const FAILURE_INVALID_RECAPTCHA = 'FAILURE_INVALID_RECAPTCHA';

    /**
     * @var \Authentication\Authenticator\FormAuthenticator
     */
    protected $baseAuthenticator;

    /**
     * Identifier or identifiers collection.
     *
     * @var \Authentication\Identifier\IdentifierInterface
     */
    protected $identifier;

    /**
     * Settings for base authenticator
     *
     * @var array
     */
    protected $config;

    /**
     * @var Result|null
     */
    protected $lastResult;

    /**
     * Constructor
     *
     * @param \Authentication\Identifier\IdentifierInterface $identifier Identifier or identifiers collection.
     * @param array $config Configuration settings.
     */
    public function __construct(IdentifierInterface $identifier, array $config = [])
    {
        $this->identifier = $identifier;
        $this->config = $config;
    }

    /**
     * Gets the actual base authenticator
     *
     * @return \Authentication\Authenticator\FormAuthenticator
     */
    protected function getBaseAuthenticator()
    {
        if ($this->baseAuthenticator === null) {
            $this->baseAuthenticator = $this->createBaseAuthenticator($this->identifier, $this->config);
        }

        return $this->baseAuthenticator;
    }

    /**
     * Create the base authenticator
     *
     * @param \Authentication\Identifier\IdentifierInterface $identifier Identifier or identifiers collection.
     * @param array $config Configuration settings.
     *
     * @return \Authentication\Authenticator\AuthenticatorInterface
     */
    protected function createBaseAuthenticator(IdentifierInterface $identifier, array $config = [])
    {
        if (!isset($config['baseClassName'])) {
            return new BaseFormAuthenticator($identifier, $config);
        }

        $className = $config['baseClassName'];
        unset($config['baseClassName']);
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(__("Base class for FormAuthenticator {0} does not exist", $className));
        }

        return new $className($identifier, $config);
    }

    /**
     * Get the last result of authenticator
     *
     * @return Result|null
     */
    public function getLastResult()
    {
        return $this->lastResult;
    }

    /**
     * Authenticates the identity contained in a request. Wrapper for Authentication\Authenticator\FormAuthenticator
     * to also check reCaptcha. Will use the `config.userModel`, and `config.fields`
     * to find POST data that is used to find a matching record in the `config.userModel`. Will return false if
     * there is no post data, either username or password is missing, or if the scope conditions have not been met.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request that contains login information.
     * @param \Psr\Http\Message\ResponseInterface $response Unused response object.
     * @return \Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request, ResponseInterface $response)
    {
        $result = $this->getBaseAuthenticator()->authenticate($request, $response);
        if (!Configure::read('Users.reCaptcha.login') || in_array($result->getStatus(), [Result::FAILURE_OTHER, Result::FAILURE_CREDENTIALS_MISSING])) {
            return $this->lastResult = $result;
        }

        $data = $request->getParsedBody();
        $captcha = $data['g-recaptcha-response'] ? $data['g-recaptcha-response'] : null;

        $valid = $this->validateReCaptcha(
            $captcha,
            $request->clientIp()
        );

        if ($valid) {
            return $this->lastResult = $result;
        }

        return $this->lastResult = new Result(null, self::FAILURE_INVALID_RECAPTCHA);
    }

    /**
     * Call base authenticator methods
     *
     * @param string $name base authentication method name
     * @param array $arguments used in base authenticator method
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->getBaseAuthenticator()->$name(...$arguments);
    }
}
