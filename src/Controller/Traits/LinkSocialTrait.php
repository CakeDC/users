<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller\Traits;

use CakeDC\Users\Model\Table\SocialAccountsTable;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use League\OAuth1\Client\Server\Twitter;

/**
 * Ações para "linkar" contas sociais
 *
 */
trait LinkSocialTrait
{
    /**
     *  Init link and auth process against provider
     *
     * @param string $alias of the provider.
     *
     * @throws \Cake\Http\Exception\NotFoundException Quando o provider informado não existe
     * @return  \Cake\Http\Response Redirects on successful
     */
    public function linkSocial($alias = null)
    {
        $provider = $this->_getSocialProvider($alias);

        $temporaryCredentials = [];
        if (ucfirst($alias) === SocialAccountsTable::PROVIDER_TWITTER) {
            $temporaryCredentials = $provider->getTemporaryCredentials();
            $this->request->getSession()->write('temporary_credentials', $temporaryCredentials);
        }
        $authUrl = $provider->getAuthorizationUrl($temporaryCredentials);
        if (empty($temporaryCredentials)) {
            $this->request->session()->write('SocialLink.oauth2state', $provider->getState());
        }

        return $this->redirect($authUrl);
    }

    /**
     * Callback to get user information from provider
     *
     * @param string $alias of the provider.
     *
     * @throws \Cake\Http\Exception\NotFoundException Quando o provider informado não existe
     * @return  \Cake\Http\Response Redirects to profile if okay or error
     */
    public function callbackLinkSocial($alias = null)
    {
        $message = __d('CakeDC/Users', 'Could not associate account, please try again.');
        $provider = $this->_getSocialProvider($alias);
        $error = false;
        if (ucfirst($alias) === SocialAccountsTable::PROVIDER_TWITTER) {
            $server = new Twitter([
                'identifier' => Configure::read('OAuth.providers.twitter.options.clientId'),
                'secret' => Configure::read('OAuth.providers.twitter.options.clientSecret'),
                'callbackUri' => Configure::read('OAuth.providers.twitter.options.callbackLinkSocialUri'),
            ]);
            $oauthToken = $this->request->getQuery('oauth_token');
            $oauthVerifier = $this->request->getQuery('oauth_verifier');
            if (!empty($oauthToken) && !empty($oauthVerifier)) {
                $temporaryCredentials = $this->request->getSession()->read('temporary_credentials');
                try {
                    $tokenCredentials = $server->getTokenCredentials($temporaryCredentials, $oauthToken, $oauthVerifier);
                    $data = (array)$server->getUserDetails($tokenCredentials);
                    $data['token'] = [
                        'accessToken' => $tokenCredentials->getIdentifier(),
                        'tokenSecret' => $tokenCredentials->getSecret(),
                    ];
                } catch (\Exception $e) {
                    $error = $e;
                }
            }
        } else {
            if (!$this->_validateCallbackSocialLink()) {
                $this->Flash->error($message);

                return $this->redirect(['action' => 'profile']);
            }
            $code = $this->request->getQuery('code');
            try {
                $token = $provider->getAccessToken('authorization_code', compact('code'));

                $data = compact('token') + $provider->getResourceOwner($token)->toArray();
            } catch (\Exception $e) {
                $error = $e;
            }
        }

        if (!empty($error) || empty($data)) {
            $log = sprintf(
                "Error getting an access token. Error message: %s %s",
                $error->getMessage(),
                $error
            );
            $this->log($log);

            $this->Flash->error($message);

            return $this->redirect(['action' => 'profile']);
        }

        try {
            $data = $this->_mapSocialUser($alias, $data);

            $user = $this->getUsersTable()->get($this->Auth->user('id'));

            $this->getUsersTable()->linkSocialAccount($user, $data);

            if ($user->getErrors()) {
                $this->Flash->error($message);
            } else {
                $this->Flash->success(__d('CakeDC/Users', 'Social account was associated.'));
            }
        } catch (\Exception $e) {
            $log = sprintf(
                "Error retrieving the authorized user's profile data. Error message: %s %s",
                $e->getMessage(),
                $e
            );
            $this->log($log);

            $this->Flash->error($message);
        }

        return $this->redirect(['action' => 'profile']);
    }

    /**
     * Get the provider name based on the request or on the provider set.
     *
     * @param string $alias of the provider.
     * @param array $data User data.
     *
     * @throws MissingProviderException
     * @return array
     */
    protected function _mapSocialUser($alias, $data)
    {
        $alias = ucfirst($alias);
        $providerMapperClass = "\\CakeDC\\Users\\Auth\\Social\\Mapper\\$alias";
        $providerMapper = new $providerMapperClass($data);
        $user = $providerMapper();
        $user['provider'] = $alias;

        return $user;
    }

    /**
     * Instantiates provider object.
     *
     * @param string $alias of the provider.
     *
     * @throws \Cake\Http\Exception\NotFoundException
     * @return \League\OAuth2\Client\Provider\AbstractProvider|\League\OAuth1\Client\Server\Twitter
     */
    protected function _getSocialProvider($alias)
    {
        $config = Configure::read('OAuth.providers.' . $alias);
        if (!$config || !isset($config['options'], $config['options']['callbackLinkSocialUri'])) {
            throw new NotFoundException;
        }

        if (!isset($config['options']['clientId'], $config['options']['clientSecret'])) {
            throw new NotFoundException;
        }

        return $this->_createSocialProvider($config, ucfirst($alias));
    }

    /**
     * Instantiates provider object.
     *
     * @param array $config for social provider.
     * @param string $alias provider alias
     *
     * @throws \Cake\Http\Exception\NotFoundException
     * @return \League\OAuth2\Client\Provider\AbstractProvider|\League\OAuth1\Client\Server\Twitter
     */
    protected function _createSocialProvider($config, $alias = null)
    {
        if ($alias === SocialAccountsTable::PROVIDER_TWITTER) {
            $server = new Twitter([
                'identifier' => Configure::read('OAuth.providers.twitter.options.clientId'),
                'secret' => Configure::read('OAuth.providers.twitter.options.clientSecret'),
                'callback_uri' => Configure::read('OAuth.providers.twitter.options.callbackLinkSocialUri'),
            ]);

            return $server;
        }
        $class = $config['className'];
        $redirectUri = $config['options']['callbackLinkSocialUri'];

        unset($config['options']['callbackLinkSocialUri'], $config['options']['linkSocialUri']);

        $config['options']['redirectUri'] = $redirectUri;

        return new $class($config['options'], []);
    }

    /**
     * Validates OAuth2 request.
     *
     * @return bool
     */
    protected function _validateCallbackSocialLink()
    {
        $queryParams = $this->request->getQueryParams();

        if (isset($queryParams['error']) && !empty($queryParams['error'])) {
            $this->log('Got error in _validateCallbackSocialLink: ' . htmlspecialchars($queryParams['error'], ENT_QUOTES, 'UTF-8'));

            return false;
        }

        if (!array_key_exists('code', $queryParams)) {
            return false;
        }

        $sessionKey = 'SocialLink.oauth2state';
        $oauth2state = $this->request->session()->read($sessionKey);
        $this->request->session()->delete($sessionKey);
        $state = $queryParams['state'];

        return $oauth2state === $state;
    }
}
