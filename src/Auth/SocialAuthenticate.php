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

use Cake\Auth\BaseAuthenticate;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * Class SocialAuthenticate
 */
class SocialAuthenticate extends BaseAuthenticate
{

    /**
     * Authenticate callback
     *
     * @param Request $request Cake request object.
     * @param Response $response Cake response object.
     * @return bool|mixed
     */
    public function authenticate(Request $request, Response $response)
    {
        $data = $request->session()->read(Configure::read('Users.Key.Session.social'));

        if (empty($data)) {
            return false;
        }
        $socialMail = Hash::get((array)$data->info, Configure::read('Users.Key.Data.email'));

        if (!empty($socialMail)) {
            $data->email = $socialMail;
            $data->validated = true;
        } else {
            $data->email = $request->data(Configure::read('Users.Key.Data.email'));
            $data->validated = false;
        }
        $user = $this->_findOrCreateUser($data);
        return $user;
    }

    /**
     * Checks the social user against the database
     *
     * @param array $data User data array.
     * @return mixed
     */
    protected function _findOrCreateUser($data)
    {
        $userModel = Configure::read('Users.table');
        $User = TableRegistry::get($userModel);
        $options = [
            'use_email' => Configure::read('Users.Email.required'),
            'validate_email' => Configure::read('Users.Email.validate'),
            'token_expiration' => Configure::read('Users.Token.expiration')
        ];
        $user = $User->socialLogin($data, $options);
        if (!empty($user->username)) {
            $user = $this->_findUser($user->username);
        }
        return $user;
    }
}
