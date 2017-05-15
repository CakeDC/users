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
 * Facebook Mapper
 *
 */
class Facebook extends AbstractMapper
{

    /**
     * Url constants
     */
    const FB_GRAPH_BASE_URL = 'https://graph.facebook.com/';

    /**
     * Map for provider fields
     * @var
     */
    protected $_mapFields = [
        'full_name' => 'name',
    ];

    /**
     * Get avatar url
     * @return string
     */
    protected function _avatar()
    {
        return self::FB_GRAPH_BASE_URL . Hash::get($this->_rawData, 'id') . '/picture?type=large';
    }
}
