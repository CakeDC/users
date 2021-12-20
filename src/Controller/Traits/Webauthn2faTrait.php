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
use Cake\Http\Exception\BadRequestException;
use Cake\Log\Log;
use Cake\Routing\Router;
use CakeDC\Auth\Authenticator\TwoFactorAuthenticator;
use CakeDC\Users\Webauthn\AuthenticateAdapter;
use CakeDC\Users\Webauthn\RegisterAdapter;

trait Webauthn2faTrait
{
    /**
     * Main page tof webauthn 2fa
     *
     * @return void
     */
    public function webauthn2fa()
    {
        $adapter = $this->getWebauthn2faRegisterAdapter();
        $user = $adapter->getUser();
        $this->set('isRegister', !$adapter->hasCredential());
        $this->set('username', $user->webauthn_username ?? $user->username);
    }

    /**
     * Action to provide register options to frontend (from js requests)
     *
     * @return \Cake\Http\Response
     */
    public function webauthn2faRegisterOptions()
    {
        $adapter = $this->getWebauthn2faRegisterAdapter();
        if (!$adapter->hasCredential()) {
            return $this->getResponse()
                ->withStringBody(json_encode($adapter->getOptions()));
        }

        throw new BadRequestException(
            __d('cake_d_c/users', 'User already has configured webauthn2fa')
        );
    }

    /**
     * Action to verify and save the new credential based on the webauthn register response.
     *
     * @return \Cake\Http\Response
     * @throws \Throwable
     */
    public function webauthn2faRegister(): \Cake\Http\Response
    {
        try {
            $adapter = $this->getWebauthn2faRegisterAdapter();
            if (!$adapter->hasCredential()) {
                $adapter->verifyResponse();

                return $this->getResponse()->withStringBody(json_encode(['success' => true]));
            }
            throw new BadRequestException(
                __d('cake_d_c/users', 'User already has configured webauthn2fa')
            );
        } catch (\Throwable $e) {
            $user = $this->request->getSession()->read('Webauthn2fa.User');
            Log::debug(__('Register error with webauthn for user id: {0}', $user['id'] ?? 'empty'));
            throw $e;
        }
    }

    /**
     * Action to provide authenticate options to frontend (from js requests)
     *
     * @return \Cake\Http\Response
     */
    public function webauthn2faAuthenticateOptions(): \Cake\Http\Response
    {
        $adapter = $this->getWebauthn2faAuthenticateAdapter();

        return $this->getResponse()->withStringBody(
            json_encode($adapter->getOptions())
        );
    }

    /**
     * Action to authenticate user based on the webauthn authenticate response.
     *
     * @return \Cake\Http\Response
     * @throws \Throwable
     */
    public function webauthn2faAuthenticate(): \Cake\Http\Response
    {
        try {
            $adapter = $this->getWebauthn2faAuthenticateAdapter();
            $adapter->verifyResponse();
            $redirectUrl = Configure::read('Auth.AuthenticationComponent.loginAction') + [
                '?' => $this->getRequest()->getQueryParams(),
            ];
            $this->getRequest()->getSession()->delete('Webauthn2fa');
            $this->getRequest()->getSession()->write(
                TwoFactorAuthenticator::USER_SESSION_KEY,
                $adapter->getUser()
            );

            return $this->getResponse()->withStringBody(json_encode([
                'success' => true,
                'redirectUrl' => Router::url($redirectUrl),
            ]));
        } catch (\Throwable $e) {
            $user = $this->request->getSession()->read('Webauthn2fa.User');
            Log::debug(__('Register error with webauthn for user id: {0}', $user['id'] ?? 'empty'));
            throw $e;
        }
    }

    /**
     * @return \CakeDC\Users\Webauthn\RegisterAdapter
     */
    protected function getWebauthn2faRegisterAdapter(): RegisterAdapter
    {
        return new RegisterAdapter($this->getRequest(), $this->getUsersTable());
    }

    /**
     * @return \CakeDC\Users\Webauthn\AuthenticateAdapter
     */
    protected function getWebauthn2faAuthenticateAdapter(): AuthenticateAdapter
    {
        return new AuthenticateAdapter($this->getRequest());
    }
}
