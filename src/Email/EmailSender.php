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
namespace CakeDC\Users\Email;

use Cake\Datasource\EntityInterface;
use Cake\Mailer\Email;
use Cake\Mailer\MailerAwareTrait;

/**
 * Email sender class
 *
 */
class EmailSender
{
    use MailerAwareTrait;

    /**
     * Send validation email
     *
     * @param EntityInterface $user User entity
     * @param Email $email instance, if null the default email configuration with the
     * @return void
     */
    public function sendValidationEmail(EntityInterface $user, Email $email = null)
    {
        $this
            ->getMailer(
                'CakeDC/Users.Users',
                $this->_getEmailInstance($email)
            )
            ->send('validation', [$user, __d('CakeDC/Users', 'Your account validation link')]);
    }

    /**
     * Send the reset password email
     *
     * @param EntityInterface $user User entity
     * @param Email $email instance, if null the default email configuration with the
     * @param string $template email template
     * Users.validation template will be used, so set a ->template() if you pass an Email
     * instance
     * @return array email send result
     */
    public function sendResetPasswordEmail(
        EntityInterface $user,
        Email $email = null,
        $template = 'CakeDC/Users.reset_password'
    ) {
        return $this
            ->getMailer(
                'CakeDC/Users.Users',
                $this->_getEmailInstance($email)
            )
            ->send('resetPassword', [$user, $template]);
    }

    /**
     * Send social validation email to the user
     *
     * @param EntityInterface $socialAccount social account
     * @param EntityInterface $user user
     * @param Email $email Email instance or null to use 'default' configuration
     * @return mixed
     */
    public function sendSocialValidationEmail(EntityInterface $socialAccount, EntityInterface $user, Email $email = null)
    {
        if (empty($email)) {
            $template = 'CakeDC/Users.social_account_validation';
        } else {
            $template = $email->getTemplate();
        }

        return $this
            ->getMailer(
                'CakeDC/Users.Users',
                $this->_getEmailInstance($email)
            )
            ->send('socialAccountValidation', [$user, $socialAccount, $template]);
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
            $email->setEmailFormat('both');
        }

        return $email;
    }
}
