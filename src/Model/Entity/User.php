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

namespace CakeDC\Users\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\Utility\Text;
use DateTime;

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
        'avatar' => true,
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
        return $this->hashPassword($password);
    }

    /**
     * @param string $password password that will be confirm.
     * @return bool|string
     */
    protected function _setConfirmPassword($password)
    {
        return $this->hashPassword($password);
    }

    /**
     * @param string $tos tos option. It will be set the tos_date
     * @return bool
     */
    protected function _setTos($tos)
    {
        if ((bool)$tos === true) {
            $this->set('tos_date', new DateTime());
        }
        return $tos;
    }

    /**
     * Hash a password using the configured password hasher,
     * use DefaultPasswordHasher if no one was configured
     *
     * @param string $password password to be hashed
     * @return mixed
     */
    public function hashPassword($password)
    {
        $PasswordHasher = $this->getPasswordHasher();
        return $PasswordHasher->hash($password);
    }

    /**
     * Return the configured Password Hasher
     *
     * @return mixed
     */
    public function getPasswordHasher()
    {
        $passwordHasher = Configure::read('Users.passwordHasher');
        if (!class_exists($passwordHasher)) {
            $passwordHasher = '\Cake\Auth\DefaultPasswordHasher';
        }
        return new $passwordHasher;
    }

    /**
     * Checks if a password is correctly hashed
     *
     * @param string $password password that will be check.
     * @param string $hashedPassword hash used to check password.
     * @return bool
     */
    public function checkPassword($password, $hashedPassword)
    {
        $PasswordHasher = $this->getPasswordHasher();
        return $PasswordHasher->check($password, $hashedPassword);
    }

    /**
     * Returns if the token has already expired
     *
     * @return bool
     */
    public function tokenExpired()
    {
        if (empty($this->token_expires)) {
            return true;
        }

        $tokenExpiresTime = $this->token_expires;
        if (is_object($this->token_expires)) {
            $tokenExpiresTime = $this->token_expires->format("Y-m-d H:i");
        }

        return strtotime($tokenExpiresTime) < strtotime("now");
    }

    /**
     * Getter for user avatar
     *
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

    /**
     * Generate token_expires and token in a user
     * @param string $tokenExpiration new token_expires user.
     *
     * @return void
     */
    public function updateToken($tokenExpiration)
    {
        $expires = new DateTime();
        $expires->modify("+ $tokenExpiration secs");
        $this->token_expires = $expires;
        $this->token = str_replace('-', '', Text::uuid());
    }
}
