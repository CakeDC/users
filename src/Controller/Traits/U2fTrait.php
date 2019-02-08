<?php
namespace CakeDC\Users\Controller\Traits;

use CakeDC\Users\Auth\U2fAuthenticationCheckerFactory;
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
     * U2f entry point
     *
     * @return \Cake\Http\Response|null
     */
    public function u2f()
    {
        $user = $this->request->getSession()->read('U2f.User');
        if (!isset($user['id'])) {
            return $this->redirect([
                'action' => 'login'
            ]);
        }
        $user = $this->getUsersTable()->get($user['id']);
        $hasRegistration = $this->getUsersTable()->U2fRegistrations->exists([
            'user_id' => $user['id']
        ]);

        if (!$hasRegistration) {
            return $this->redirect([
                'action' => 'u2fRegister'
            ]);
        }

        return $this->redirect([
            'action' => 'u2fAuthenticate'
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
            return $this->redirect([
                'action' => 'login'
            ]);
        }

        if (!$data['registration']) {
            list($registerRequest, $signs) = $this->createU2fLib()->getRegisterData();
            $this->request->getSession()->write('U2f.registerRequest', json_encode($registerRequest));
            $this->set(compact('registerRequest', 'signs'));

            return null;
        }

        return $this->redirect([
            'action' => 'u2fAuthenticate'
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
            $registration = $this->createU2fLib()->doRegister($request, $response);
            $registration = json_decode(json_encode($registration), true);
            $registration['user_id'] = $data['user']['id'];
            $table = $this->getUsersTable()->U2fRegistrations;
            $entity = $table->newEntity($registration);
            $table->saveOrFail($entity);
            $this->request->getSession()->delete('U2f.registerRequest');

            return $this->redirect([
                'action' => 'u2fAuthenticate'
            ]);
        } catch (\Exception $e) {
            $this->request->getSession()->delete('U2f.registerRequest');

            return $this->redirect([
                'action' => 'u2fRegister'
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
            return $this->redirect([
                'action' => 'login'
            ]);
        }

        if (!$data['registration']) {
            return $this->redirect([
                'action' => 'u2fRegister'
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
            $Model = $this->getUsersTable()->U2fRegistrations;
            $registration = $Model->find('all')->where([
                'user_id' => $data['user']['id']
            ])->first();

            $result = $this->createU2fLib()->doAuthenticate($request, [$registration], $response);
            $registration->counter = $result->counter;
            $Model->saveOrFail($registration);
            $this->request->getSession()->delete('U2f');
            $this->Auth->setUser($data['user']);

            return $this->redirect($this->Auth->redirectUrl());
        } catch (\Exception $e) {
            $this->request->getSession()->delete('U2f.authenticateRequest');

            return $this->redirect([
                'action' => 'u2fAuthenticate'
            ]);
        }
    }

    /**
     * Create a u2f lib
     *
     * @return U2F
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
            'registration' => null
        ];
        $data['user'] = $this->request->getSession()->read('U2f.User');
        if (!isset($data['user']['id'])) {
            return $data;
        }
        $data['valid'] = $this->getU2fAuthenticationChecker()->isEnabled();
        $data['registration'] = $this->getUsersTable()->U2fRegistrations->find('all')->where([
            'user_id' => $data['user']['id']
        ])->first();

        return $data;
    }

    /**
     * Get the configured two factory authentication
     *
     * @return \CakeDC\Users\Auth\U2fAuthenticationCheckerInterface
     */
    protected function getU2fAuthenticationChecker()
    {
        return (new U2fAuthenticationCheckerFactory())->build();
    }
}
