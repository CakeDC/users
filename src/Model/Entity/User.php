<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Model\Entity;

use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\Utility\Security;

/**
 * User Entity.
 *
 * @property string $email
 * @property string $role
 * @property string $username
 * @property bool $is_superuser
 * @property \Cake\I18n\Time $token_expires
 * @property string $token
 * @property string $api_token
 * @property array $additional_data
 * @property \CakeDC\Users\Model\Entity\SocialAccount[] $social_accounts
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'is_superuser' => false,
        'role' => false,
    ];

    /**
     * Fields that are excluded from JSON an array versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password',
        'token',
        'token_expires',
        'api_token',
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
            $this->set('tos_date', Time::now());
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

        return $PasswordHasher->hash((string)$password);
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

        return new $passwordHasher();
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

        return new Time($this->token_expires) < Time::now();
    }

    /**
     * Getter for user avatar
     *
     * @return string|null avatar
     */
    protected function _getAvatar()
    {
        $avatar = null;
        if (isset($this->social_accounts[0])) {
            $avatar = $this->social_accounts[0]['avatar'];
        }

        return $avatar;
    }

    /**
     * Return the u2f_registration inside additional_data
     *
     * @return object|null
     */
    protected function _getU2fRegistration()
    {
        if (!isset($this->additional_data['u2f_registration'])) {
            return null;
        }
        $object = (object)$this->additional_data['u2f_registration'];

        return isset($object->keyHandle) ? $object : null;
    }

    /**
     * Generate token_expires and token in a user
     *
     * @param int $tokenExpiration seconds to expire the token from Now
     * @return void
     */
    public function updateToken($tokenExpiration = 0)
    {
        $expiration = new Time('now');
        $this->token_expires = $expiration->addSeconds($tokenExpiration);
        $this->token = bin2hex(Security::randomBytes(16));
    }
}
