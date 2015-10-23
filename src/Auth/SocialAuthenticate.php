<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Auth;

use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use CakeDC\Users\Auth\Social\Util\SocialUtils;
use Muffin\OAuth2\Auth\OAuthAuthenticate;

/**
 * Class SocialAuthenticate
 */
class SocialAuthenticate extends OAuthAuthenticate
{

    /**
     * Constructor
     *
     * @param \Cake\Controller\ComponentRegistry $registry The Component registry used on this request.
     * @param array $config Array of config to use.
     * @throws \Exception
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        Configure::write('Muffin/OAuth2', Configure::read('OAuth'));
        parent::__construct($registry, array_merge($config, Configure::read('OAuth')));
    }

    /**
     * Finds or creates a local user.
     *
     * @param array $data Mapped user data.
     * @return array
     * @throws \Muffin\OAuth2\Auth\Exception\MissingEventListenerException
     */
    protected function _touch(array $data)
    {
        $userModel = Configure::read('Users.table');
        $User = TableRegistry::get($userModel);
        $options = [
            'use_email' => Configure::read('Users.Email.required'),
            'validate_email' => Configure::read('Users.Email.validate'),
            'token_expiration' => Configure::read('Users.Token.expiration')
        ];
        $user = $User->socialLogin($data, $options, $this->_provider);
        if (!empty($user->username)) {
            $user = $this->_findUser($user->username);
        }
        return $user;
    }

    /**
     * Get a user based on information in the request.
     *
     * @param \Cake\Network\Request $request Request object.
     * @return mixed Either false or an array of user information
     * @throws \RuntimeException If the `Muffin/OAuth2.newUser` event is missing or returns empty.
     */
    public function getUser(Request $request)
    {
        if (!$rawData = $this->_authenticate($request)) {
            return false;
        }
        $provider = SocialUtils::getProvider($this->_provider);
        $providerMapperClass = "\\CakeDC\\Users\\Auth\\Social\\Mapper\\$provider";
        $providerMapper = new $providerMapperClass($rawData);
        $user = $providerMapper();

        if (!$user || !$this->config('userModel')) {
            return false;
        }

        if (!$result = $this->_touch($user)) {
            return false;
        }

        $args = [$this->_provider, $result];
        $this->dispatchEvent('Muffin/OAuth2.afterIdentify', $args);
        return $result;
    }
}
