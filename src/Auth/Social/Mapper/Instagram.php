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
 * Instagram Mapper
 *
 */
class Instagram extends AbstractMapper
{
    /**
     * Url constants
     */
    const INSTAGRAM_BASE_URL = 'https://instagram.com/';

    /**
     * Map for provider fields
     * @var
     */
    protected $_mapFields = [
        'full_name' => 'full_name',
        'avatar' => 'profile_picture',
    ];

    /**
     * @return string
     */
    protected function _link()
    {
        return self::INSTAGRAM_BASE_URL . Hash::get($this->_rawData, $this->_mapFields['username']);
    }
}
