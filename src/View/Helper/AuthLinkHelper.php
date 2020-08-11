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

use Cake\View\Helper\HtmlHelper;
use CakeDC\Auth\Traits\IsAuthorizedTrait;

/**
 * AuthLink helper
 *
 * @property \Cake\View\Helper\FormHelper $Form
 */
class AuthLinkHelper extends HtmlHelper
{
    use IsAuthorizedTrait;

    public $helpers = ['Url', 'Form'];

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
        $allowed = $options['allowed'] ?? null;

        if ($allowed === false) {
            return '';
        }
        if ($allowed === true || $this->isAuthorized($url)) {
            return ($options['before'] ?? '') .
                parent::link($title, $url, $linkOptions) .
                ($options['after'] ?? '');
        }

        return '';
    }

    /**
     * Wrapper for FormHelper.postLink.
     * Write the link only if user is authorized.
     *
     * @param string $title Link's title
     * @param string|array $url Link's url
     * @param array $options Link's options
     * @return string Link as a string.
     */
    public function postLink($title, $url = null, array $options = []): string
    {
        return $this->isAuthorized($url)
                    ? $this->Form->postLink($title, $url, $options)
                    : '';
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
