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

namespace Users\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity.
 */
class User extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'username' => true,
        'email' => true,
        'password' => true,
        'confirm_password' => true,
        'first_name' => true,
        'last_name' => true,
        'token' => true,
        'token_expires' => true,
        'api_token' => true,
        'activation_date' => true,
        //tos is a boolean, coming from the "accept the terms of service" checkbox but it is not stored onto database
        'tos' => true,
        'tos_date' => true,
        'active' => true,
        'social_accounts' => true,
        'current_password' => true
    ];

    /**
     * @param string $password password that will be set.
     * @return bool|string
     */
    protected function _setPassword($password)
    {
        return (new DefaultPasswordHasher)->hash($password);
    }

    /**
     * @param string $password password that will be confirm.
     * @return bool|string
     */
    protected function _setConfirmPassword($password)
    {
        return (new DefaultPasswordHasher)->hash($password);
    }

    /**
     * Checks if a password is correctly hashed
     * @param string $password password that will be check.
     * @param string $hashedPassword hash used to check password.
     * @return bool
     */
    public function checkPassword($password, $hashedPassword)
    {
        return (new DefaultPasswordHasher)->check($password, $hashedPassword);
    }

    /**
     * Returns if the token has already expired
     *
     * @return bool
     */
    public function tokenExpired()
    {
        return empty($this->token_expires) || strtotime($this->token_expires) < strtotime("now");
    }

    /**
     * Getter for user avatar
     * @return string|null avatar
     */
    protected function _getAvatar()
    {
        $avatar = null;
        if (!empty($this->_properties['social_accounts'][0])) {
            $avatar = $this->_properties['social_accounts'][0]['avatar'];
        }
        return $avatar;
    }
}
