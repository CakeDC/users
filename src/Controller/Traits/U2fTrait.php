<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace CakeDC\Users\Controller\Traits;

use Cake\Core\Configure;
use CakeDC\Auth\Authentication\AuthenticationService;
use CakeDC\Auth\Authentication\U2fAuthenticationCheckerFactory;
use CakeDC\Auth\Authenticator\TwoFactorAuthenticator;
use u2flib_server\U2F;

/**
 * Class U2fTrait
 *
 * @package App\Controller\Traits
 * @mixin \Cake\Controller\Controller
 */
trait U2fTrait
{
    /**
     * Perform redirect keeping current query string
     *
     * @param array $url base url
     * @return \Cake\Http\Response
     */
    public function redirectWithQuery($url)
    {
        $url['?'] = $this->getRequest()->getQueryParams();
        if (empty($url['?'])) {
            unset($url['?']);
        }

        return $this->redirect($url);
    }

    /**
     * U2f entry point
     *
     * @return \Cake\Http\Response|null
     */
    public function u2f()
    {
        $data = $this->getU2fData();
        if (!$data['valid']) {
            return $this->redirectWithQuery([
                'action' => 'login',
            ]);
        }

        if (!$data['registration']) {
            return $this->redirectWithQuery([
                'action' => 'u2fRegister',
            ]);
        }

        return $this->redirectWithQuery([
            'action' => 'u2fAuthenticate',
        ]);
    }

    /**
     * Show u2f register start step
     *
     * @return \Cake\Http\Response|null
     * @throws \u2flib_server\Error
     */
    public function u2fRegister()
    {
        $data = $this->getU2fData();
        if (!$data['valid']) {
            return $this->redirectWithQuery([
                'action' => 'login',
            ]);
        }

        if (!$data['registration']) {
            [$registerRequest, $signs] = $this->createU2fLib()->getRegisterData();
            $this->getRequest()->getSession()->write('U2f.registerRequest', json_encode($registerRequest));
            $this->set(compact('registerRequest', 'signs'));

            return null;
        }

        return $this->redirectWithQuery([
            'action' => 'u2fAuthenticate',
        ]);
    }

    /**
     * Show u2f register finish step
     *
     * @return \Cake\Http\Response|null
     */
    public function u2fRegisterFinish()
    {
        $data = $this->getU2fData();
        $request = json_decode($this->getRequest()->getSession()->read('U2f.registerRequest'));
        $response = json_decode($this->getRequest()->getData('registerResponse'));
        try {
            $result = $this->createU2fLib()->doRegister($request, $response);
            $additionalData = $data['user']->additional_data;
            $additionalData['u2f_registration'] = $result;
            $data['user']->additional_data = $additionalData;
            $this->getUsersTable()->saveOrFail($data['user'], ['checkRules' => false]);
            $this->getRequest()->getSession()->delete('U2f.registerRequest');

            return $this->redirectWithQuery([
                'action' => 'u2fAuthenticate',
            ]);
        } catch (\Exception $e) {
            $this->getRequest()->getSession()->delete('U2f.registerRequest');

            return $this->redirectWithQuery([
                'action' => 'u2fRegister',
            ]);
        }
    }

    /**
     * Show u2f authenticate start step
     *
     * @return \Cake\Http\Response|null
     */
    public function u2fAuthenticate()
    {
        $data = $this->getU2fData();
        if (!$data['valid']) {
            return $this->redirectWithQuery([
                'action' => 'login',
            ]);
        }

        if (!$data['registration']) {
            return $this->redirectWithQuery([
                'action' => 'u2fRegister',
            ]);
        }
        $authenticateRequest = $this->createU2fLib()->getAuthenticateData([$data['registration']]);
        $this->getRequest()->getSession()->write('U2f.authenticateRequest', json_encode($authenticateRequest));
        $this->set(compact('authenticateRequest'));

        return null;
    }

    /**
     * Show u2f Authenticate finish step
     *
     * @return \Cake\Http\Response|null
     */
    public function u2fAuthenticateFinish()
    {
        $data = $this->getU2fData();
        $request = json_decode($this->getRequest()->getSession()->read('U2f.authenticateRequest'));
        $response = json_decode($this->getRequest()->getData('authenticateResponse'));

        try {
            $registration = $data['registration'];
            $result = $this->createU2fLib()->doAuthenticate($request, [$registration], $response);
            $registration->counter = $result->counter;
            $additionalData = $data['user']->additional_data;
            $additionalData['u2f_registration'] = $result;
            $data['user']->additional_data = $additionalData;
            $this->getUsersTable()->saveOrFail($data['user'], ['checkRules' => false]);
            $this->getRequest()->getSession()->delete('U2f');
            $this->request->getSession()->delete(AuthenticationService::U2F_SESSION_KEY);
            $this->request->getSession()->write(TwoFactorAuthenticator::USER_SESSION_KEY, $data['user']);

            return $this->redirectWithQuery(Configure::read('Auth.AuthenticationComponent.loginAction'));
        } catch (\Exception $e) {
            $this->getRequest()->getSession()->delete('U2f.authenticateRequest');

            return $this->redirectWithQuery([
                'action' => 'u2fAuthenticate',
            ]);
        }
    }

    /**
     * Create a u2f lib
     *
     * @return \u2flib_server\U2F
     * @throws \u2flib_server\Error
     */
    protected function createU2fLib()
    {
        $appId = $this->getRequest()->scheme() . '://' . $this->getRequest()->host();

        return new U2F($appId);
    }

    /**
     * Get essential U2f data
     *
     * @return array
     */
    protected function getU2fData()
    {
        $data = [
            'valid' => false,
            'user' => null,
            'registration' => null,
        ];
        $user = $this->getRequest()->getSession()->read(AuthenticationService::U2F_SESSION_KEY);
        if (!isset($user['id'])) {
            return $data;
        }
        if (!$this->request->is('ssl')) {
            throw new \UnexpectedValueException(__d('cake_d_c/users', 'U2F requires SSL.'));
        }
        $entity = $this->getUsersTable()->get($user['id']);
        $data['user'] = $user;
        $data['valid'] = $this->getU2fAuthenticationChecker()->isEnabled();
        $data['registration'] = $entity->u2f_registration;

        return $data;
    }

    /**
     * Get the configured u2f authentication checker
     *
     * @return \CakeDC\Auth\Authentication\U2fAuthenticationCheckerInterface
     */
    protected function getU2fAuthenticationChecker()
    {
        return (new U2fAuthenticationCheckerFactory())->build();
    }
}
