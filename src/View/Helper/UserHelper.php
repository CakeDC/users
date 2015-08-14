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

namespace Users\View\Helper;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Hash;
use Cake\View\Helper;
use Users\Controller\Component\UsersAuthComponent;

/**
 * User helper
 */
class UserHelper extends Helper
{

    public $helpers = ['Html', 'Form'];

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * beforeLayou callback loads reCaptcha if enabled
     *
     * @param Event $event event
     * @return void
     */
    public function beforeLayout(Event $event) {
        if (Configure::read('Users.Registration.reCaptcha')) {
            $this->addReCaptchaScript();
        }
    }

    /**
     * Facebook login link
     *
     * @return string
     */
    public function facebookLogin()
    {
        return $this->Html->link($this->Html->tag('i', '', [
                'class' => 'fa fa-facebook'
            ]) . __d('Users', 'Sign in with Facebook'), '/auth/facebook', [
            'escape' => false, 'class' => 'btn btn-social btn-facebook'
            ]);
    }

    /**
     * Twitter login link
     *
     * @return string
     */
    public function twitterLogin()
    {
        return $this->Html->link($this->Html->tag('i', '', [
                'class' => 'fa fa-twitter'
            ]) . __d('Users', 'Sign in with Twitter'), '/auth/twitter', [
            'escape' => false, 'class' => 'btn btn-social btn-twitter'
            ]);
    }

    /**
     * Logout link
     *
     * @param null $message logout message info.
     * @param array $options Array with option data.
     * @return string
     */
    public function logout($message = null, $options = [])
    {
        return $this->Html->link(empty($message) ? __d('Users', 'Logout') : $message, [
            'plugin' => 'Users', 'controller' => 'Users', 'action' => 'logout'
            ], $options);
    }

    /**
     * Generate a link if the target url is authorized for the logged in user
     *
     * @param type $title link's title.
     * @param type $url url that the user is making request.
     * @param array $options Array with option data.
     * @return string
     */
    public function link($title, $url = null, array $options = [])
    {
        $event = new Event(UsersAuthComponent::EVENT_IS_AUTHORIZED, $this, ['url' => $url]);
        $result = $this->_View->eventManager()->dispatch($event);
        if ($result->result) {
            $linkOptions = $options;
            unset($linkOptions['before'], $linkOptions['after']);
            return Hash::get($options, 'before') . $this->Html->link($title, $url, $linkOptions) . Hash::get($options, 'after');
        }

        return false;
    }

    /**
     * Welcome display
     * @return mixed
     */
    public function welcome()
    {
        $userId = $this->request->session()->read('Auth.User.id');
        if (empty($userId)) {
            return;
        }

        $profileUrl = '/profile/' . $userId;
        $label = __d('Users', 'Welcome, {0}', $this->Html->link($this->request->session()->read('Auth.User.first_name'), $profileUrl));
        return $this->Html->tag('span', $label, ['class' => 'welcome']);
    }

    /**
     * Add reCaptcha script
     * @return void
     */
    public function addReCaptchaScript()
    {
        $this->Html->script('https://www.google.com/recaptcha/api.js', [
            'block' => 'script',
        ]);
    }

    /**
     * Add reCaptcha to the form
     * @return mixed
     */
    public function addReCaptcha()
    {
        if (!Configure::check('Users.Registration.reCaptcha')) {
            return false;
        }
        $this->Form->unlockField('g-recaptcha-response');
        return $this->Html->tag('div', '', [
            'class' => 'g-recaptcha',
            'data-sitekey' => Configure::read('reCaptcha.key')
        ]);
    }
}
