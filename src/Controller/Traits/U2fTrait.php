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
     *
     * @return \Cake\Http\Response
     */
    public function redirectWithQuery($url)
    {
        $url['?'] = $this->request->getQueryParams();
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
            $this->request->getSession()->write('U2f.registerRequest', json_encode($registerRequest));
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
        $request = json_decode($this->request->getSession()->read('U2f.registerRequest'));
        $response = json_decode($this->request->getData('registerResponse'));
        try {
            $result = $this->createU2fLib()->doRegister($request, $response);
            $additionalData = $data['user']->additional_data;
            $additionalData['u2f_registration'] = $result;
            $data['user']->additional_data = $additionalData;
            $this->getUsersTable()->saveOrFail($data['user'], ['checkRules' => false]);
            $this->request->getSession()->delete('U2f.registerRequest');

            return $this->redirectWithQuery([
                'action' => 'u2fAuthenticate',
            ]);
        } catch (\Exception $e) {
            $this->request->getSession()->delete('U2f.registerRequest');

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
        $this->request->getSession()->write('U2f.authenticateRequest', json_encode($authenticateRequest));
        $this->set(compact('authenticateRequest'));
    }

    /**
     * Show u2f Authenticate finish step
     *
     * @return \Cake\Http\Response|null
     */
    public function u2fAuthenticateFinish()
    {
        $data = $this->getU2fData();
        $request = json_decode($this->request->getSession()->read('U2f.authenticateRequest'));
        $response = json_decode($this->request->getData('authenticateResponse'));

        try {
            $registration = $data['registration'];
            $result = $this->createU2fLib()->doAuthenticate($request, [$registration], $response);
            $registration->counter = $result->counter;
            $additionalData = $data['user']->additional_data;
            $additionalData['u2f_registration'] = $result;
            $data['user']->additional_data = $additionalData;
            $this->getUsersTable()->saveOrFail($data['user'], ['checkRules' => false]);
            $this->request->getSession()->delete('U2f');
            $this->Auth->setUser($data['user']->toArray());

            return $this->redirect($this->Auth->redirectUrl());
        } catch (\Exception $e) {
            $this->request->getSession()->delete('U2f.authenticateRequest');

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
        $appId = $this->request->scheme() . '://' . $this->request->host();

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
        $user = $this->request->getSession()->read('U2f.User');
        if (!isset($user['id'])) {
            return $data;
        }
        $data['user'] = $this->getUsersTable()->get($user['id']);
        $data['valid'] = $this->getU2fAuthenticationChecker()->isEnabled();
        $data['registration'] = $data['user']->u2f_registration;

        return $data;
    }
}
