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
 * Google Mapper
 *
 */
class Amazon extends AbstractMapper
{

    /**
     * Url constants
     */
    const AMAZON_BASE_URL = 'https://amazon.com/gp/profile/';

    /**
     * Map for provider fields
     * @var
     */
    protected $_mapFields = [
        'id' => 'user_id'
    ];

    /**
     * @return string
     */
    protected function _link()
    {
        return self::AMAZON_BASE_URL . Hash::get($this->_rawData, $this->_mapFields['id']);
    }
}
