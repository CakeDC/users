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

namespace CakeDC\Users\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\I18n\Time;
use Cake\Network\Email\Email;
use Cake\ORM\Behavior as BaseBehavior;

/**
 * Covers the user registration
 */
class Behavior extends BaseBehavior
{

    /**
     * Send the templated email to the user
     *
     * @param EntityInterface $user User entity
     * @param string $subject Subject, note the first_name of the user will be prepended if exists
     * @param Email $email instance, if null the default email configuration with the
     * Users.validation template will be used, so set a ->template() if you pass an Email
     * instance
     *
     * @return array email send result
     */
    protected function _sendEmail(EntityInterface $user, $subject, Email $email = null)
    {
        $firstName = isset($user['first_name'])? $user['first_name'] . ', ' : '';
        return $this->_getEmailInstance($email)
                ->to($user['email'])
                ->subject($firstName . $subject)
                ->viewVars($user->toArray())
                ->send();
    }

    /**
     * Get or initialize the email instance. Used for mocking.
     *
     * @param Email $email if email provided, we'll use the instance instead of creating a new one
     * @return Email
     */
    protected function _getEmailInstance(Email $email = null)
    {
        if ($email === null) {
            $email = new Email('default');
            $email->template('Users.validation')
                    ->emailFormat('both');
        }

        return $email;
    }

    /**
     * DRY for update active and token based on validateEmail flag
     *
     * @param EntityInterface $user User to be updated.
     * @param type $validateEmail email user to validate.
     * @param type $tokenExpiration token to be updated.
     * @return EntityInterface
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
     * @param User $user user object.
     * @return EntityInterface
     */
    protected function _removeValidationToken(EntityInterface $user)
    {
        $user->token = null;
        $user->token_expires = null;
        $result = $this->_table->save($user);

        return $result;
    }
}
