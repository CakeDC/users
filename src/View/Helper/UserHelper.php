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

namespace CakeDC\Users\View\Helper;

use CakeDC\Users\Controller\Component\UsersAuthComponent;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\View\Helper;

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
     * Social login link
     *
     * @param string $name name
     * @param array $options options
     * @return string
     */
    public function socialLogin($name, $options = [])
    {
        if (empty($options['label'])) {
            $options['label'] = 'Sign in with';
        }
        $icon = $this->Html->tag('i', '', [
            'class' => __d('Users', 'fa fa-{0}', strtolower($name)),
        ]);
        $providerTitle = __d('Users', '{0} {1}', Hash::get($options, 'label'), Inflector::camelize($name));
        $providerClass = __d('Users', 'btn btn-social btn-{0} ' . Hash::get($options, 'class') ?: '', strtolower($name));
        return $this->Html->link($icon . $providerTitle, "/auth/$name", [
            'escape' => false, 'class' => $providerClass
        ]);
    }

    /**
     * All available Social Login Icons
     *
     * @return array Links to Social Login Urls
     */
    public function socialLoginList()
    {
        if (!Configure::read('Users.Social.login')) {
            return [];
        }
        $outProviders = [];
        $providers = Configure::read('OAuth.providers');
        foreach ($providers as $provider => $options) {
            if (!empty($options['options']['redirectUri']) &&
                !empty($options['options']['clientId']) &&
                !empty($options['options']['clientSecret'])) {
                $outProviders[] = $this->socialLogin($provider);
            }
        }

        return $outProviders;
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
            'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'logout'
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
        if ($this->isAuthorized($url)) {
            $linkOptions = $options;
            unset($linkOptions['before'], $linkOptions['after']);
            return Hash::get($options, 'before') . $this->Html->link($title, $url, $linkOptions) . Hash::get($options, 'after');
        }

        return false;
    }

    /**
     * Retunrs true if the target url is authorized for the logged in user
     *
     * @param type $url url that the user is making request.
     * @return bool
     */
    public function isAuthorized($url = null)
    {
        $event = new Event(UsersAuthComponent::EVENT_IS_AUTHORIZED, $this, ['url' => $url]);
        $result = $this->_View->eventManager()->dispatch($event);
        return $result->result;
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

        $profileUrl = Configure::read('Users.Profile.route');
        $label = __d('Users', 'Welcome, {0}', $this->Html->link($this->request->session()->read('Auth.User.first_name') ?: $this->request->session()->read('Auth.User.username'), $profileUrl));
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
        if (!Configure::read('Users.reCaptcha.key')) {
            return $this->Html->tag('p', __d('Users', 'reCaptcha is not configured! Please configure Users.reCaptcha.key'));
        }
        $this->addReCaptchaScript();
        $this->Form->unlockField('g-recaptcha-response');
        return $this->Html->tag('div', '', [
            'class' => 'g-recaptcha',
            'data-sitekey' => Configure::read('Users.reCaptcha.key')
        ]);
    }
}
