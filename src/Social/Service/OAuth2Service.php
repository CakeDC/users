<?php

namespace CakeDC\Users\Social\Service;

use Cake\Http\Exception\BadRequestException;
use Cake\Http\ServerRequest;
use League\OAuth2\Client\Provider\AbstractProvider;

class OAuth2Service extends OAuthServiceAbstract
{
    /**
     * @var \League\Oauth2\Client\Provider\GenericProvider
     */
    protected $provider;

    /**
     * OAuth2Service constructor.
     * @param array $providerConfig with className and options keys
     */
    public function __construct(array $providerConfig)
    {
        $this->setProvider($providerConfig);
        $this->setConfig($providerConfig);
    }

    /**
     * Check if we are at getUserStep, meaning, we received a callback from provider.
     * Return true when querystring code is not empty
     *
     * @param \Cake\Http\ServerRequest $request Request object.
     * @return bool
     */
    public function isGetUserStep(ServerRequest $request): bool
    {
        return !empty($request->getQuery('code'));
    }

    /**
     * Get a authentication url for user
     *
     * @param \Cake\Http\ServerRequest $request Request object.
     * @return string
     */
    public function getAuthorizationUrl(ServerRequest $request)
    {
        if ($this->getConfig('options.state')) {
            $request->getSession()->write('oauth2state', $this->provider->getState());
        }

        return $this->provider->getAuthorizationUrl();
    }

    /**
     * Get a user in social provider
     *
     * @param \Cake\Http\ServerRequest $request Request object.
     *
     * @throws BadRequestException when oauth2 state is invalid
     * @return array
     */
    public function getUser(ServerRequest $request): array
    {
        if (!$this->validate($request)) {
            throw new BadRequestException('Invalid OAuth2 state');
        }

        $code = $request->getQuery('code');
        $token = $this->provider->getAccessToken('authorization_code', compact('code'));

        return compact('token') + $this->provider->getResourceOwner($token)->toArray();
    }

    /**
     * Validates OAuth2 request.
     *
     * @param \Cake\Http\ServerRequest $request Request object.
     * @return bool
     */
    protected function validate(ServerRequest $request)
    {
        if (!array_key_exists('code', $request->getQueryParams())) {
            return false;
        }

        $session = $request->getSession();
        $sessionKey = 'oauth2state';
        $state = $request->getQuery('state');

        if ($this->getConfig('options.state') &&
            (!$state || $state !== $session->read($sessionKey))) {
            $session->delete($sessionKey);

            return false;
        }

        return true;
    }


    /**
     * Instantiates provider object.
     *
     * @param array $config for provider.
     * @return void
     */
    protected function setProvider($config)
    {
        if (is_object($config['className']) && $config['className'] instanceof AbstractProvider) {
            $this->provider = $config['className'];
        } else {
            $class = $config['className'];

            $this->provider = new $class($config['options'], $config['collaborators']);
        }
    }
}