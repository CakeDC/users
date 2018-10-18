<?php

namespace CakeDC\Users\Controller\Traits;

use Cake\Core\Configure;
use CakeDC\Users\Authentication\AuthenticationService;

trait GoogleVerifyTrait
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
        $loginAction = Configure::read('Auth.AuthenticationComponent.loginAction');
        if ($this->isVerifyAllowed()) {
            return $this->redirect($loginAction);
        }

        $temporarySession = $this->request->getSession()->read(AuthenticationService::GOOGLE_VERIFY_SESSION_KEY);
        $secretVerified = $temporarySession['secret_verified'];
        // showing QR-code until shared secret is verified
        if (!$secretVerified) {
            $secret = $this->onVerifyGetSecret($temporarySession);
            if (empty($secret)) {
                return $this->redirect($loginAction);
            }

            $secretDataUri = $this->GoogleAuthenticator->getQRCodeImageAsDataUri(
                $temporarySession['email'],
                $secret
            );
            $this->set(compact('secretDataUri'));
        }

        if ($this->request->is('post')) {
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
        if (!Configure::read('Users.GoogleAuthenticator.login')) {
            $message = __d('CakeDC/Users', 'Please enable Google Authenticator first.');
            $this->Flash->error($message, 'default', [], 'auth');

            return true;
        }

        $temporarySession = $this->request->getSession()->read(AuthenticationService::GOOGLE_VERIFY_SESSION_KEY);

        if (empty($temporarySession)) {
            $message = __d('CakeDC/Users', 'Could not find user data');
            $this->Flash->error($message, 'default', [], 'auth');

            return true;
        }

        return false;
    }

    /**
     * Get the Google Authenticator secret of user, if not exists try to create one and save
     *
     * @param \CakeDC\Users\Model\Entity\User $user user data present on session
     *
     * @return string if empty the creation has failed
     */
    protected function onVerifyGetSecret($user)
    {
        if (isset($user['secret']) && $user['secret']) {
            return $user['secret'];
        }

        $secret = $this->GoogleAuthenticator->createSecret();

        // catching sql exception in case of any sql inconsistencies
        try {
            $query = $this->getUsersTable()->query();
            $query->update()
                ->set(['secret' => $secret])
                ->where(['id' => $user['id']]);
            $query->execute();
        } catch (\Exception $e) {
            $this->request->getSession()->destroy();
            $message = $e->getMessage();
            $this->Flash->error($message, 'default', [], 'auth');

            return '';
        }

        return $secret;
    }

    /**
     * Handle the action when user post the form with code
     *
     * @param array $loginAction url to login page used in redirect
     *
     * @return \Cake\Http\Response
     */
    protected function onPostVerifyCode($loginAction)
    {
        $codeVerified = false;
        $verificationCode = $this->request->getData('code');
        $user = $this->request->getSession()->read(AuthenticationService::GOOGLE_VERIFY_SESSION_KEY);
        $entity = $this->getUsersTable()->get($user['id']);

        if (!empty($entity['secret'])) {
            $codeVerified = $this->GoogleAuthenticator->verifyCode($entity['secret'], $verificationCode);
        }

        if (!$codeVerified) {
            $this->request->getSession()->destroy();
            $message = __d('CakeDC/Users', 'Verification code is invalid. Try again');
            $this->Flash->error($message, 'default', [], 'auth');

            return $this->redirect($loginAction);
        }

        return $this->onPostVerifyCodeOkay($loginAction, $user);
    }

    /**
     * Handle the part of action when user post the form with valid code
     *
     * @param array $loginAction url to login page used in redirect
     * @param \CakeDC\Users\Model\Entity\User $user user data present on session
     *
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

        $this->request->getSession()->delete(AuthenticationService::GOOGLE_VERIFY_SESSION_KEY);
        $this->request->getSession()->write('GoogleTwoFactor.User', $user);

        return $this->redirect($loginAction);
    }
}
