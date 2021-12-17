<?php

namespace CakeDC\Users\Webauthn;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Http\ServerRequest;
use CakeDC\Users\Webauthn\Repository\UserCredentialSourceRepository;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\Server;

class BaseAdapter
{
    /**
     * @var ServerRequest
     */
    protected $request;
    /**
     * @var UserCredentialSourceRepository
     */
    protected $repository;
    /**
     * @var Server
     */
    protected $server;

    /**
     * @param ServerRequest $request
     */
    public function __construct(ServerRequest $request)
    {
        $this->request = $request;
        $rpEntity = new PublicKeyCredentialRpEntity(
            Configure::read('Webauthn2fa.appName'), // The application name
            Configure::read('Webauthn2fa.id')
        );
        $this->repository = new UserCredentialSourceRepository(
            $request->getSession()->read('Webauthn2fa.User')
        );
        $this->server = new Server(
            $rpEntity,
            $this->repository
        );
    }

    /**
     * @return PublicKeyCredentialUserEntity
     */
    protected function getUserEntity(): PublicKeyCredentialUserEntity
    {
        $user = $this->request->getSession()->read('Webauthn2fa.User');

        return new PublicKeyCredentialUserEntity(
            $user->webauthn_username ?? $user->username,
            (string)$user->id,
            (string)$user->first_name
        );
    }

    /**
     * @return array|mixed|null
     */
    public function getUser()
    {
        return $this->request->getSession()->read('Webauthn2fa.User');
    }
}
