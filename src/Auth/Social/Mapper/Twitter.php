<?php
/**
 * Created by PhpStorm.
 * User: ajibarra
 * Date: 10/16/15
 * Time: 7:02 AM
 */

namespace CakeDC\Users\Auth\Social\Mapper;


use Cake\Utility\Hash;

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