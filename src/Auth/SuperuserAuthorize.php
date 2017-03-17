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

namespace CakeDC\Users\Auth;

use Cake\Auth\BaseAuthorize;
use Cake\Http\ServerRequest;
use Cake\Utility\Hash;

/**
 * Superuser Authorize
 *
 * Detect and give full access to superusers
 *
 */
class SuperuserAuthorize extends BaseAuthorize
{
    /**
     * default config
     *
     * @var array
     */
    protected $_defaultConfig = [
        //superuser field in the Users table
        'superuser_field' => 'is_superuser',
    ];

    /**
     * Check if the user is superuser
     *
     * @param array $user User information object.
     * @param \Cake\Http\ServerRequest $request Cake request object.
     * @return bool
     */
    public function authorize($user, ServerRequest $request)
    {
        $user = (array)$user;
        $superuserField = $this->getConfig('superuser_field');
        if (Hash::check($user, $superuserField)) {
            return (bool)Hash::get($user, $superuserField);
        }

        return false;
    }
}
