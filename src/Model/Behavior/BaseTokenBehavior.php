<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Behavior;
use CakeDC\Users\Model\Entity\User;

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
     * @param int|null $tokenExpiration seconds to expire from now
     * @return \Cake\Datasource\EntityInterface
     */
    protected function updateActive(EntityInterface $user, bool $validateEmail, ?int $tokenExpiration = null): EntityInterface
    {
        $emailValidated = $user->get('validated');
        if (!$emailValidated && $validateEmail) {
            $user->set('active', false);
            if ($user instanceof User) {
                $user->updateToken($tokenExpiration ?? 0);
            }
        } else {
            $user->set('active', true);
            $user->set('activation_date', new DateTime());
        }

        return $user;
    }

    /**
     * Remove user token for validation
     *
     * @param \Cake\Datasource\EntityInterface $user user object.
     * @return \Cake\Datasource\EntityInterface
     */
    protected function removeValidationToken(EntityInterface $user): EntityInterface
    {
        $user->set('token', null);
        $user->set('token_expires', null);

        return $this->table->save($user);
    }
}
