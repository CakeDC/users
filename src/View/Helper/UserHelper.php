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

use Cake\Core\Configure;
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
            'class' => 'fa fa-' . strtolower($name),
        ]);

        if (isset($options['title'])) {
            $providerTitle = $options['title'];
        } else {
            $providerTitle = Hash::get($options, 'label') . ' ' . Inflector::camelize($name);
        }

        $providerClass = 'btn btn-social btn-' . strtolower($name) . ((Hash::get($options, 'class')) ? ' ' . Hash::get($options, 'class') : '');

        return $this->Html->link($icon . $providerTitle, "/auth/$name", [
            'escape' => false, 'class' => $providerClass,
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
            'plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'logout',
        ], $options);
    }

    /**
     * Welcome display
     * @return mixed
     */
    public function welcome()
    {
        $userId = $this->request->getSession()->read('Auth.User.id');
        if (empty($userId)) {
            return;
        }

        $profileUrl = Configure::read('Users.Profile.route');
        $label = __d('CakeDC/Users', 'Welcome, {0}', $this->AuthLink->link($this->request->getSession()->read('Auth.User.first_name') ?: $this->request->getSession()->read('Auth.User.username'), $profileUrl));

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
            'data-sitekey' => Configure::read('Users.reCaptcha.key'),
            'data-theme' => Configure::read('Users.reCaptcha.theme') ?: 'light',
            'data-size' => Configure::read('Users.reCaptcha.size') ?: 'normal',
            'data-tabindex' => Configure::read('Users.reCaptcha.tabindex') ?: '3',
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
    /**
     * Create links for all social providers enabled social link (connect)
     *
     * @param string $name        Provider name in lowercase
     * @param array  $provider    Provider configuration
     * @param bool   $isConnected User is connected with this provider
     *
     * @return string
     */
    public function socialConnectLink($name, $provider, $isConnected = false)
    {
        $linkClass = 'btn btn-social btn-' . strtolower($name) . ((Hash::get($provider['options'], 'class')) ? ' ' . Hash::get($provider['options'], 'class') : '');
        if ($isConnected) {
            $title = __d('CakeDC/Users', 'Connected with {0}', Inflector::camelize($name));

            return "<a class=\"$linkClass disabled\"><span class=\"fa fa-$name\"></span> $title</a>";
        }

        $title = __d('CakeDC/Users', 'Connect with {0}', Inflector::camelize($name));

        return $this->Html->link(
            "<span class=\"fa fa-$name\"></span> $title",
            "/link-social/$name",
            [
                'escape' => false,
                'class' => $linkClass,
            ]
        );
    }

    /**
     * Create links for all social providers enabled social link (connect)
     *
     * @param array $socialAccounts All social accounts connected by a user.
     *
     * @return string
     */
    public function socialConnectLinkList($socialAccounts = [])
    {
        if (!Configure::read('Users.Social.login')) {
            return "";
        }
        $html = "";
        $connectedProviders = array_map(
            function ($item) {
                return strtolower($item->provider);
            },
            (array)$socialAccounts
        );

        $providers = Configure::read('OAuth.providers');
        foreach ($providers as $name => $provider) {
            if (!empty($provider['options']['callbackLinkSocialUri']) &&
                !empty($provider['options']['linkSocialUri']) &&
                !empty($provider['options']['clientId']) &&
                !empty($provider['options']['clientSecret'])
            ) {
                $html .= $this->socialConnectLink($name, $provider, in_array($name, $connectedProviders));
            }
        }

        return $html;
    }
}
