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

use Cake\Utility\Hash;

/**
 * CakePHP Mapper
 *
 */
class CakePHP extends AbstractMapper
{
    /**
     * Url constants
     */
    const CAKEPHP_BASE_URL = 'http://cakephp.org/';

    /**
     * Map for provider fields
     * @var
     */
    protected $_mapFields = [];

    /**
     * @return string
     */
    protected function _link()
    {
        return self::CAKEPHP_BASE_URL . Hash::get($this->_rawData, $this->_mapFields['username']);
    }
}
