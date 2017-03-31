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
 * Twitter Mapper
 *
 */
class Twitter extends AbstractMapper
{

    /**
     * Url constants
     */
    const TWITTER_BASE_URL = 'https://twitter.com/';

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
        'bio' => 'description',
        'validated' => 'validated'
    ];

    /**
     * @return string
     */
    protected function _link()
    {
        return self::TWITTER_BASE_URL . Hash::get($this->_rawData, $this->_mapFields['username']);
    }
}
