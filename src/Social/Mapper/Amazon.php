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

namespace CakeDC\Users\Social\Mapper;

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
     *
     * @var array
     */
    protected $_mapFields = [
        'id' => 'user_id'
    ];

    /**
     * Get link property value
     *
     * @param mixed $rawData raw data
     *
     * @return string
     */
    protected function _link($rawData)
    {
        return self::AMAZON_BASE_URL . Hash::get($rawData, $this->_mapFields['id']);
    }
}
