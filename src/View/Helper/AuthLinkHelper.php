<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\View\Helper;

use Cake\Utility\Hash;
use Cake\View\Helper\HtmlHelper;
use CakeDC\Auth\Traits\IsAuthorizedTrait;

/**
 * AuthLink helper
 */
class AuthLinkHelper extends HtmlHelper
{
    use IsAuthorizedTrait;

    /**
     * Generate a link if the target url is authorized for the logged in user
     *
     * @param string $title link's title.
     * @param string|array|null $url url that the user is making request.
     * @param array $options Array with option data. Extra options include
     * 'before' and 'after' to quickly inject some html code in the link, like icons etc
     * 'allowed' to manage if the link should be displayed, default is null to check isAuthorized
     * @return string
     */
    public function link($title, $url = null, array $options = []): string
    {
        $linkOptions = $options;
        unset($linkOptions['before'], $linkOptions['after'], $linkOptions['allowed']);
        $allowed = Hash::get($options, 'allowed');

        if ($allowed === false) {
            return '';
        }
        if ($allowed === true || $this->isAuthorized($url)) {
            return Hash::get($options, 'before') .
                parent::link($title, $url, $linkOptions) .
                Hash::get($options, 'after');
        }

        return '';
    }

    /**
     * Get the current request
     *
     * @return \Cake\Http\ServerRequest
     */
    public function getRequest()
    {
        return $this->getView()->getRequest();
    }
}
