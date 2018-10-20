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
 * AbstractMapper
 *
 */
abstract class AbstractMapper
{
    /**
     * Provider Raw data
     * @var
     */
    protected $_rawData;

    /**
     * Map for provider fields
     * @var null
     */
    protected $_mapFields;

    /**
     * Default Map for provider fields
     * @var
     */
    protected $_defaultMapFields = [
        'id' => 'id',
        'username' => 'username',
        'full_name' => 'name',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'email' => 'email',
        'avatar' => 'avatar',
        'gender' => 'gender',
        'link' => 'link',
        'bio' => 'bio',
        'locale' => 'locale',
        'validated' => 'validated'
    ];

    /**
     * Constructor
     *
     * @param mixed $mapFields map fields
     */
    public function __construct($mapFields = null)
    {
        if (!is_null($mapFields)) {
            $this->_mapFields = $mapFields;
        }
        $this->_mapFields = array_merge($this->_defaultMapFields, $this->_mapFields);
    }
    /**
     * Invoke method
     *
     * @param mixed $rawData raw data
     * @return mixed
     */
    public function __invoke($rawData)
    {
        return $this->_map($rawData);
    }

    /**
     * If email is present the user is validated
     *
     * @param mixed $rawData raw data
     *
     * @return bool
     */
    protected function _validated($rawData)
    {
        $email = Hash::get($rawData, $this->_mapFields['email']);

        return !empty($email);
    }

    /**
     * Maps raw data using mapFields
     *
     * @param mixed $rawData raw data
     * @return mixed
     */
    protected function _map($rawData)
    {
        $result = [];
        collection($this->_mapFields)->each(function ($mappedField, $field) use (&$result, $rawData) {
            $value = Hash::get($rawData, $mappedField);
            $function = '_' . $field;
            if (method_exists($this, $function)) {
                $value = $this->{$function}($rawData);
            }
            $result[$field] = $value;
        });
        $token = Hash::get($rawData, 'token');
        $result['credentials'] = [
            'token' => is_array($token) ? Hash::get($token, 'accessToken') : $token->getToken(),
            'secret' => is_array($token) ? Hash::get($token, 'tokenSecret') : null,
            'expires' => is_array($token) ? Hash::get($token, 'expires') : $token->getExpires(),
        ];
        $result['raw'] = $rawData;

        return $result;
    }
}
