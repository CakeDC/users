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

namespace CakeDC\Users\Controller\Traits;

use Cake\Core\Configure;
use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Auth\Authenticator\TwoFactorAuthenticator;

trait OneTimePasswordVerifyTrait
{
    /**
     * Verify for Google Authenticator
     * If Google Authenticator's enabled we need to verify
     * authenticated user. To avoid accidental access to
     * other URL's we store auth'ed used into temporary session
     * to perform code verification.
     *
     * @return mixed
     */
    public function verify()
    {
        $loginAction = array_merge(
            Configure::read('Auth.AuthenticationComponent.loginAction'),
            [
                '?' => $this->getRequest()->getQueryParams(),
            ]
        );
        if (!$this->isVerifyAllowed()) {
            return $this->redirect($loginAction);
        }

        $temporarySession = $this->getRequest()->getSession()->read(
            AuthenticationService::TWO_FACTOR_VERIFY_SESSION_KEY
        );
        $secretVerified = $temporarySession['secret_verified'] ?? null;
        // showing QR-code until shared secret is verified
        if (!$secretVerified) {
            $secret = $this->onVerifyGetSecret($temporarySession);
            if (empty($secret)) {
                return $this->redirect($loginAction);
            }

            $secretDataUri = $this->OneTimePasswordAuthenticator->getQRCodeImageAsDataUri(
                $temporarySession['email'],
                $secret
            );
            $this->set(compact('secretDataUri'));
        }

        if ($this->getRequest()->is('post')) {
            return $this->onPostVerifyCode($loginAction);
        }
    }

    /**
     * Check If Google Authenticator's enabled we need to verify
     * authenticated user and if temporySession is present
     *
     * @return bool
     */
    protected function isVerifyAllowed()
    {
        if (!Configure::read('OneTimePasswordAuthenticator.login')) {
            $message = __d('cake_d_c/users', 'Please enable Google Authenticator first.');
            $this->Flash->error($message, [
                'key' => 'auth',
                'element' => 'default',
                'params' => [],
            ]);

            return false;
        }

        $temporarySession = $this->getRequest()->getSession()->read(
            AuthenticationService::TWO_FACTOR_VERIFY_SESSION_KEY
        );

        if (empty($temporarySession) || !isset($temporarySession['id'])) {
            $message = __d('cake_d_c/users', 'Could not find user data');
            $this->Flash->error($message, [
                'key' => 'auth',
                'element' => 'default',
                'params' => [],
            ]);

            return false;
        }

        return true;
    }

    /**
     * Get the Google Authenticator secret of user, if not exists try to create one and save
     *
     * @param \CakeDC\Users\Model\Entity\User $user user data present on session
     * @return string if empty the creation has failed
     */
    protected function onVerifyGetSecret($user)
    {
        if (isset($user['secret']) && $user['secret']) {
            return $user['secret'];
        }

        $secret = $this->OneTimePasswordAuthenticator->createSecret();

        // catching sql exception in case of any sql inconsistencies
        try {
            $query = $this->getUsersTable()->query();
            $query->update()
                ->set(['secret' => $secret])
                ->where(['id' => $user['id']]);
            $query->execute();
            $user['secret'] = $secret;
            $this->getRequest()->getSession()->write(AuthenticationService::TWO_FACTOR_VERIFY_SESSION_KEY, $user);
        } catch (\Exception $e) {
            $this->getRequest()->getSession()->destroy();
            $this->log($e);
            $message = __d('cake_d_c/users', 'Could not verify, please try again');
            $this->Flash->error($message, [
                'key' => 'auth',
                'element' => 'default',
                'params' => [],
            ]);

            return '';
        }

        return $secret;
    }

    /**
     * Handle the action when user post the form with code
     *
     * @param array $loginAction url to login page used in redirect
     * @return \Cake\Http\Response
     */
    protected function onPostVerifyCode($loginAction)
    {
        $codeVerified = false;
        $verificationCode = $this->getRequest()->getData('code');
        $user = $this->getRequest()->getSession()->read(AuthenticationService::TWO_FACTOR_VERIFY_SESSION_KEY);
        $entity = $this->getUsersTable()->get($user['id']);

        if (!empty($entity['secret'])) {
            $codeVerified = $this->OneTimePasswordAuthenticator->verifyCode($entity['secret'], $verificationCode);
        }

        if (!$codeVerified) {
            $this->getRequest()->getSession()->destroy();
            $message = __d('cake_d_c/users', 'Verification code is invalid. Try again');
            $this->Flash->error($message, [
                'key' => 'auth',
                'element' => 'default',
                'params' => [],
            ]);

            return $this->redirect($loginAction);
        }

        return $this->onPostVerifyCodeOkay($loginAction, $user);
    }

    /**
     * Handle the part of action when user post the form with valid code
     *
     * @param array $loginAction url to login page used in redirect
     * @param \CakeDC\Users\Model\Entity\User $user user data present on session
     * @return \Cake\Http\Response
     */
    protected function onPostVerifyCodeOkay($loginAction, $user)
    {
        unset($user['secret']);

        if (!$user['secret_verified']) {
            $this->getUsersTable()->query()->update()
                ->set(['secret_verified' => true])
                ->where(['id' => $user['id']])
                ->execute();
        }

        $this->getRequest()->getSession()->delete(AuthenticationService::TWO_FACTOR_VERIFY_SESSION_KEY);
        $this->getRequest()->getSession()->write(TwoFactorAuthenticator::USER_SESSION_KEY, $user);

        return $this->redirect($loginAction);
    }
}
