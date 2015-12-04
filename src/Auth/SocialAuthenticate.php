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
use Cake\Error\Debugger;
use Cake\Log\Log;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use CakeDC\Users\Auth\Social\Util\SocialUtils;
use CakeDC\Users\Controller\Component\UsersAuthComponent;
use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\UserNotActiveException;
use CakeDC\Users\Model\Table\SocialAccountsTable;
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
        $oauthConfig = Configure::read('OAuth');
        unset($oauthConfig['providers']['twitter']);
        Configure::write('Muffin/OAuth2', $oauthConfig);
        parent::__construct($registry, array_merge($config, $oauthConfig));
    }

    /**
     * Find or create local user
     * @param array $data
     * @return array|bool|mixed
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
        try {
            if (empty($data['provider']) && !empty($this->_provider)) {
                $data['provider'] = SocialUtils::getProvider($this->_provider);
            }
             $user = $User->socialLogin($data, $options);
        } catch (UserNotActiveException $ex) {
            $exception = $ex;
        } catch (AccountNotActiveException $ex) {
            $exception = $ex;
        } catch (MissingEmailException $ex) {
            $exception = $ex;
        }

        if (!empty($exception)) {
            $event = UsersAuthComponent::EVENT_FAILED_SOCIAL_LOGIN;
            $args = ['exception' => $exception, 'rawData' => $data];
            $event = $this->dispatchEvent($event, $args);
            if ($data['provider'] == SocialAccountsTable::PROVIDER_TWITTER) {
                throw $exception;
            }
            return $event->result;
        }

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
        $data = $request->session()->read(Configure::read('Users.Key.Session.social'));
        if (!empty($data) && (!empty($data['email'] || !empty($request->data('email'))))) {
            if (!empty($request->data('email'))) {
                $data['email'] = $request->data('email');
            }
            $user = $data;
            $request->session()->delete(Configure::read('Users.Key.Session.social'));
        } else {
            if (empty($data) && !$rawData = $this->_authenticate($request)) {
                return false;
            }
            if (empty($rawData)) {
                $rawData = $data;
            }
            if (!is_null($this->_provider)) {
                $provider = SocialUtils::getProvider($this->_provider);
            } else {
                $provider = ucfirst($request->param('provider'));
            }
            $providerMapperClass = "\\CakeDC\\Users\\Auth\\Social\\Mapper\\$provider";
            $providerMapper = new $providerMapperClass($rawData);
            $user = $providerMapper();
            $user['provider'] = $provider;

        }

        if (!$user || !$this->config('userModel')) {
            return false;
        }

        if (!$result = $this->_touch($user)) {
            return false;
        }

        return $result;
    }
}
