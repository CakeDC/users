<?php
declare(strict_types=1);
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
 * Cognito Mapper
 *
 */
class Cognito extends AbstractMapper
{
    /**
     * Map for provider fields
     * @var
     */
    protected $_mapFields = [
        'id' => 'sub',
        'zoneinfo' => 'zoneinfo',
        'link' => 'website',
        'bio' => 'profile',
        'first_name' => 'given_name',
        'avatar' => 'picture',
        'last_name' => 'family_name',
    ];

    /**
     * @return string
     */
    protected function _link()
    {
        return Hash::get($this->_rawData, $this->_mapFields['link'], '#');
    }

    /**
     * @return mixed
     */
    protected function _firstName()
    {
        return Hash::get($this->_rawData, $this->_mapFields['first_name'], Hash::get(explode(' ', Hash::get($this->_rawData, 'name', '')), 0));
    }

    /**
     * @return mixed
     */
    protected function _lastName()
    {
        return Hash::get($this->_rawData, $this->_mapFields['last_name'], Hash::get(explode(' ', Hash::get($this->_rawData, 'name', '')), 1));
    }
}
