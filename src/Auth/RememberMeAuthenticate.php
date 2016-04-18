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

/**
 * Class RememberMeAuthenticate. Login the uses if a valid cookie is present
 */
class RememberMeAuthenticate extends BaseAuthenticate
{

    /**
     * Authenticate callback
     * Reads the stored cookie and auto login the user
     *
     * @param Request $request Cake request object.
     * @param Response $response Cake response object.
     * @return mixed
     */
    public function authenticate(Request $request, Response $response)
    {
        $cookieName = Configure::read('Users.RememberMe.Cookie.name');
        $cookie = $this->_registry->Cookie->read($cookieName);
        if (empty($cookie)) {
            return false;
        }
        $this->config('fields.username', 'id');
        $user = $this->_findUser($cookie['id']);
        if ($user &&
            !empty($cookie['user_agent']) &&
            $request->header('User-Agent') === $cookie['user_agent']) {
            return $user;
        }

        return false;
    }
}
