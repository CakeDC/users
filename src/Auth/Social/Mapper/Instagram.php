<?php
/**
 * Created by PhpStorm.
 * User: ajibarra
 * Date: 10/16/15
 * Time: 7:02 AM
 */

namespace CakeDC\Users\Auth\Social\Mapper;

use Cake\Utility\Hash;

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
        'avatar' => 'data.profile_picture',
        'id' => 'data.id',
        'full_name' => 'data.full_name',
        'username' => 'data.username'
    ];

    /**
     * @return string
     */
    protected function _link()
    {
        return self::INSTAGRAM_BASE_URL . Hash::get($this->_rawData, $this->_mapFields['username']);
    }
}
