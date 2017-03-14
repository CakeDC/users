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
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Utility\Hash;
use Cake\View\Helper;
use Cake\View\Helper\HtmlHelper;

/**
 * AuthLink helper
 */
class AuthLinkHelper extends HtmlHelper
{

    /**
     * Generate a link if the target url is authorized for the logged in user
     *
     * @param string $title link's title.
     * @param string|array|null $url url that the user is making request.
     * @param array $options Array with option data. Extra options include
     * 'before' and 'after' to quickly inject some html code in the link, like icons etc
     * 'allowed' to manage if the link should be displayed, default is null to check isAuthorized
     * @return string|bool
     */
    public function link($title, $url = null, array $options = [])
    {
        $linkOptions = $options;
        unset($linkOptions['before'], $linkOptions['after'], $linkOptions['allowed']);
        $allowed = Hash::get($options, 'allowed');

        if ($allowed === false) {
            return false;
        }
        if ($allowed === true || $this->isAuthorized($url)) {
            return Hash::get($options, 'before') .
                parent::link($title, $url, $linkOptions) .
                Hash::get($options, 'after');
        }

        return false;
    }

    /**
     * Returns true if the target url is authorized for the logged in user
     *
     * @param string|array|null $url url that the user is making request.
     * @return bool
     */
    public function isAuthorized($url = null)
    {
        $event = new Event(UsersAuthComponent::EVENT_IS_AUTHORIZED, $this, ['url' => $url]);
        $result = EventManager::instance()->dispatch($event);

        return $result->result;
    }
}
