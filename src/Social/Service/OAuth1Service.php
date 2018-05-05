<?php

namespace CakeDC\Users\Social\Service;

use Cake\Http\ServerRequest;
use League\OAuth1\Client\Server\Server;

class OAuth1Service extends OAuthServiceAbstract
{
    /**
     * @var \League\OAuth1\Client\Server\Server
     */
    protected $provider;

    /**
     * OAuth2Service constructor.
     * @param array $providerConfig with className and options keys
     */
    public function __construct(array $providerConfig)
    {
        $map = [
            'identifier' => 'clientId',
            'secret' => 'clientSecret',
            'callback_uri' => 'redirectUri'
        ];

        foreach ($map as $to => $from) {
            if (array_key_exists($from, $providerConfig['options'])) {
                $providerConfig['options'][$to] = $providerConfig['options'][$from];
                unset($providerConfig['options'][$from]);
            }
        }
        $providerConfig += ['signature' => null];
        $this->setProvider($providerConfig);
        $this->setConfig($providerConfig);
    }

    /**
     * Check if we are at getUserStep, meaning, we received a callback from provider.
     *
     * @param \Cake\Http\ServerRequest $request Request object.
     * @return bool
     */
    public function isGetUserStep(ServerRequest $request): bool
    {
        $oauthToken = $request->getQuery('oauth_token');
        $oauthVerifier = $request->getQuery('oauth_verifier');

        return !empty($oauthToken) && !empty($oauthVerifier);
    }

    /**
     * Get a authentication url for user
     *
     * @param \Cake\Http\ServerRequest $request Request object.
     * @return string
     */
    public function getAuthorizationUrl(ServerRequest $request)
    {
        $temporaryCredentials = $this->provider->getTemporaryCredentials();
        $request->getSession()->write('temporary_credentials', $temporaryCredentials);

        return $this->provider->getAuthorizationUrl($temporaryCredentials);
    }

    /**
     * Get a user in social provider
     *
     * @param \Cake\Http\ServerRequest $request Request object.
     * @return array
     */
    public function getUser(ServerRequest $request): array
    {
        $oauthToken = $request->getQuery('oauth_token');
        $oauthVerifier = $request->getQuery('oauth_verifier');

        $temporaryCredentials = $request->getSession()->read('temporary_credentials');
        $tokenCredentials = $this->provider->getTokenCredentials($temporaryCredentials, $oauthToken, $oauthVerifier);
        $user = (array)$this->provider->getUserDetails($tokenCredentials);
        $user['token'] = [
            'accessToken' => $tokenCredentials->getIdentifier(),
            'tokenSecret' => $tokenCredentials->getSecret(),
        ];

        return $user;
    }

    /**
     * Instantiates provider object.
     *
     * @param array $config for provider.
     * @return void
     */
    protected function setProvider($config)
    {
        if (is_object($config['className']) && $config['className'] instanceof Server) {
            $this->provider = $config['className'];
        } else {
            $class = $config['className'];

            $this->provider = new $class($config['options'], $config['signature']);
        }
    }
}
