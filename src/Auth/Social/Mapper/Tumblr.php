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

namespace CakeDC\Users\Auth\Social\Mapper;

use Cake\Utility\Hash;

/**
 * Tumblr Mapper
 *
 */
class Tumblr extends AbstractMapper
{
    /**
     * Map for provider fields
     * @var null
     */
    protected $_mapFields = [
        'id' => 'uid',
        'username' => 'nickname',
        'full_name' => 'name',
        'first_name' => 'firstName',
        'last_name' => 'lastName',
        'email' => 'email',
        'avatar' => 'imageUrl',
        'bio' => 'extra.blogs.0.description',
        'validated' => 'validated',
        'link' => 'extra.blogs.0.url'
    ];

    /**
     * @return string
     */
    protected function _id()
    {
        return crc32($this->_rawData['nickname']);
    }
}
