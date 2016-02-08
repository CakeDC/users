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
namespace CakeDC\Users\Mailer;

use Cake\Datasource\EntityInterface;
use Cake\Mailer\Email;
use Cake\Mailer\Mailer;

/**
 * User Mailer
 *
 */
class UsersMailer extends Mailer
{
    /**
     * Send the templated email to the user
     *
     * @param EntityInterface $user User entity
     * @param string $subject Subject, note the first_name of the user will be prepended if exist
     * @param string $template string, note the first_name of the user will be prepended if exists
     *
     * @return array email send result
     */
    protected function validation(EntityInterface $user, $subject, $template = 'CakeDC/Users.validation')
    {
        $firstName = isset($user['first_name'])? $user['first_name'] . ', ' : '';
        $this
            ->to($user['email'])
            ->subject($firstName . $subject)
            ->viewVars($user->toArray())
            ->template($template);
    }

    /**
     * Send the reset password email to the user
     *
     * @param EntityInterface $user User entity
     * @param string $template string, note the first_name of the user will be prepended if exists
     *
     * @return array email send result
     */
    protected function resetPassword(EntityInterface $user, $template = 'CakeDC/Users.reset_password')
    {
        $firstName = isset($user['first_name'])? $user['first_name'] . ', ' : '';
        $subject = __d('Users', '{0}Your reset password link', $firstName);

        $this
            ->to($user['email'])
            ->subject($subject)
            ->viewVars($user->toArray())
            ->template($template);
    }

    /**
     * Send account validation email to the user
     *
     * @param EntityInterface $user User entity
     * @param EntityInterface $socialAccount SocialAccount entity
     *
     * @return array email send result
     */
    protected function socialAccountValidation(EntityInterface $user, EntityInterface $socialAccount)
    {
        $firstName = isset($user['first_name'])? $user['first_name'] . ', ' : '';
        //note: we control the space after the username in the previous line
        $subject = __d('Users', '{0}Your social account validation link', $firstName);
        $this
            ->to($user['email'])
            ->subject($subject)
            ->viewVars(compact('user', 'socialAccount'));
    }
}
