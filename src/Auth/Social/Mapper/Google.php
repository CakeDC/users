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

namespace CakeDC\Users\Auth\Social\Mapper;

/**
 * Google Mapper
 *
 */
class Google extends AbstractMapper
{
    /**
     * Map for provider fields
     * @var
     */
    protected $_mapFields = [
        'avatar' => 'image.url',
        'full_name' => 'displayName',
        'email' => 'emails.0.value',
        'first_name' => 'name.givenName',
        'last_name' => 'name.familyName',
        'bio' => 'aboutMe',
        'link' => 'url'
    ];
}
