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
    public $helpers = ['Html', 'Form', 'CakeDC/Users.AuthLink'];

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
            $options['label'] = __d('CakeDC/Users', 'Sign in with');
        }
        $icon = $this->Html->tag('i', '', [
            'class' => __d('CakeDC/Users', 'fa fa-{0}', strtolower($name)),
        ]);

        if (isset($options['title'])) {
            $providerTitle = $options['title'];
        } else {
            $providerTitle = __d('CakeDC/Users', '{0} {1}', Hash::get($options, 'label'), Inflector::camelize($name));
        }

        $providerClass = __d(
            'CakeDC/Users',
            'btn btn-social btn-{0} ' . Hash::get($options, 'class') ?: '',
            strtolower($name)
        );

        return $this->Html->link($icon . $providerTitle, "/auth/$name", [
            'escape' => false, 'class' => $providerClass
        ]);
    }

    /**
     * All available Social Login Icons
     *
     * @param array $providerOptions Provider link options.
     * @return array Links to Social Login Urls
     */
    public function socialLoginList(array $providerOptions = [])
    {
        if (!Configure::read('Users.Social.login')) {
            return [];
        }
        $outProviders = [];
        $providers = Configure::read('OAuth.providers');
        foreach ($providers as $provider => $options) {
            if (!empty($options['options']['redirectUri']) &&
                !empty($options['options']['clientId']) &&
                !empty($options['options']['clientSecret'])
            ) {
                if (isset($providerOptions[$provider])) {
                    $options['options'] = Hash::merge($options['options'], $providerOptions[$provider]);
                }

                $outProviders[] = $this->socialLogin($provider, $options['options']);
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
        return $this->AuthLink->link(empty($message) ? __d('CakeDC/Users', 'Logout') : $message, [
            'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'logout'
        ], $options);
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
        $label = __d('CakeDC/Users', 'Welcome, {0}', $this->AuthLink->link($this->request->session()->read('Auth.User.first_name') ?: $this->request->session()->read('Auth.User.username'), $profileUrl));

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
            return $this->Html->tag('p', __d('CakeDC/Users', 'reCaptcha is not configured! Please configure Users.reCaptcha.key'));
        }
        $this->addReCaptchaScript();
        $this->Form->unlockField('g-recaptcha-response');

        return $this->Html->tag('div', '', [
            'class' => 'g-recaptcha',
            'data-sitekey' => Configure::read('Users.reCaptcha.key')
        ]);
    }

    /**
     * Generate a link if the target url is authorized for the logged in user
     *
     * @deprecated Since 3.2.1. Use AuthLinkHelper::link() instead
     *
     * @param string $title link's title.
     * @param string|array|null $url url that the user is making request.
     * @param array $options Array with option data.
     * @return string
     */
    public function link($title, $url = null, array $options = [])
    {
        trigger_error(
            'UserHelper::link() deprecated since 3.2.1. Use AuthLinkHelper::link() instead',
            E_USER_DEPRECATED
        );

        return $this->AuthLink->link($title, $url, $options);
    }

    /**
     * Returns true if the target url is authorized for the logged in user
     *
     * @deprecated Since 3.2.1. Use AuthLinkHelper::link() instead
     *
     * @param string|array|null $url url that the user is making request.
     * @return bool
     */
    public function isAuthorized($url = null)
    {
        trigger_error(
            'UserHelper::isAuthorized() deprecated since 3.2.1. Use AuthLinkHelper::isAuthorized() instead',
            E_USER_DEPRECATED
        );

        return $this->AuthLink->isAuthorized($url);
    }
}
