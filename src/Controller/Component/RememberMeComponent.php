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

namespace CakeDC\Users\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Security;
use InvalidArgumentException;

/**
 * RememberMe Component.
 *
 * Saves a cookie to keep the user logged into the application even when the session expires
 *
 * @link http://book.cakephp.org/3.0/en/controllers/components/cookie.html
 */
class RememberMeComponent extends Component
{

    /**
     * Components
     *
     * @var array
     */
    public $components = ['Cookie', 'Auth'];

    /**
     * Name of the cookie
     * @var string
     */
    protected $_cookieName = null;

    /**
     * Initialize config data and properties.
     *
     * @param array $config The config data.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->_cookieName = Configure::read('Users.RememberMe.Cookie.name');
        $this->_validateConfig();
        $this->setCookieOptions();
        $this->_attachEvents();
    }

    /**
     * Validate component config
     *
     * @throws InvalidArgumentException
     * @return void
     */
    protected function _validateConfig()
    {
        if (mb_strlen(Security::salt(), '8bit') < 32) {
            throw new InvalidArgumentException(
                __d('CakeDC/Users', 'Invalid app salt, app salt must be at least 256 bits (32 bytes) long')
            );
        }
    }

    /**
     * Attach the afterLogin and beforeLogount events
     *
     * @return void
     */
    protected function _attachEvents()
    {
        $eventManager = $this->getController()->eventManager();
        $eventManager->on(UsersAuthComponent::EVENT_AFTER_LOGIN, [], [$this, 'setLoginCookie']);
        $eventManager->on(UsersAuthComponent::EVENT_BEFORE_LOGOUT, [], [$this, 'destroy']);
    }

    /**
     * Sets cookie configuration options
     *
     * @return void
     */
    public function setCookieOptions()
    {
        $cookieConfig = Configure::read('Users.RememberMe.Cookie.Config');
        $this->Cookie->configKey($this->_cookieName, $cookieConfig);
    }

    /**
     * Sets the login cookie that handles the remember me feature
     *
     * @param Event $event event
     * @return void
     */
    public function setLoginCookie(Event $event)
    {
        $user['id'] = $this->Auth->user('id');
        if (empty($user)) {
            return;
        }
        $user['user_agent'] = $this->getController()->request->getHeaderLine('User-Agent');
        $this->Cookie->write($this->_cookieName, $user);
    }

    /**
     * Destroys the remember me cookie
     *
     * @param Event $event event
     * @return void
     */
    public function destroy(Event $event)
    {
        if ($this->Cookie->check($this->_cookieName)) {
            $this->Cookie->delete($this->_cookieName);
        }
    }

    /**
     * Reads the stored cookie and auto login the user if present
     *
     * @param Event $event event
     * @return mixed
     */
    public function beforeFilter(Event $event)
    {
        $user = $this->Auth->user();
        if (!empty($user) ||
            $this->getController()->request->is(['post', 'put']) ||
            $this->getController()->request->getParam('action') === 'logout' ||
            $this->getController()->request->session()->check(Configure::read('Users.Key.Session.social')) ||
            $this->getController()->request->getParam('provider')) {
            return;
        }

        $user = $this->Auth->identify();
        // No user no cookies
        if (empty($user)) {
            return;
        }
        $this->Auth->setUser($user);
        $event = $this->getController()->dispatchEvent(UsersAuthComponent::EVENT_AFTER_COOKIE_LOGIN);
        if (is_array($event->result)) {
            return $this->getController()->redirect($event->result);
        }
        $url = $this->getController()->request->getRequestTarget();

        return $this->getController()->redirect($url);
    }
}
