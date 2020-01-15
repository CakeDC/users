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

namespace CakeDC\Users\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\I18n\Time;
use Cake\ORM\Behavior;

/**
 * Covers the user registration
 */
class BaseTokenBehavior extends Behavior
{
    /**
     * DRY for update active and token based on validateEmail flag
     *
     * @param \Cake\Datasource\EntityInterface $user User to be updated.
     * @param bool $validateEmail email user to validate.
     * @param int $tokenExpiration seconds to expire from now
     * @return \Cake\Datasource\EntityInterface
     */
    protected function _updateActive(EntityInterface $user, $validateEmail, $tokenExpiration)
    {
        $emailValidated = $user['validated'];
        if (!$emailValidated && $validateEmail) {
            $user['active'] = false;
            $user->updateToken($tokenExpiration);
        } else {
            $user['active'] = true;
            $user['activation_date'] = new Time();
        }

        return $user;
    }

    /**
     * Remove user token for validation
     *
     * @param \Cake\Datasource\EntityInterface $user user object.
     * @return \Cake\Datasource\EntityInterface
     */
    protected function _removeValidationToken(EntityInterface $user)
    {
        $user['token'] = null;
        $user['token_expires'] = null;
        $result = $this->_table->save($user);

        return $result;
    }
}
