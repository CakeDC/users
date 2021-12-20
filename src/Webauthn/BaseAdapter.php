<?php

namespace CakeDC\Users\Webauthn;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use CakeDC\Users\Model\Table\UsersTable;
use CakeDC\Users\Webauthn\Repository\UserCredentialSourceRepository;
use Webauthn\PublicKeyCredentialRpEntity;
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
     * @var EntityInterface|\CakeDC\Users\Model\Entity\User
     */
    private $user;

    /**
     * @param ServerRequest $request
     * @param UsersTable|null $usersTable
     */
    public function __construct(ServerRequest $request, ?UsersTable $usersTable = null)
    {
        $this->request = $request;
        $rpEntity = new PublicKeyCredentialRpEntity(
            Configure::read('Webauthn2fa.appName'), // The application name
            Configure::read('Webauthn2fa.id')
        );
        /**
         * @var \Cake\ORM\Entity $userSession
         */
        $userSession = $request->getSession()->read('Webauthn2fa.User');
        $usersTable = $usersTable ?? TableRegistry::getTableLocator()
            ->get($userSession->getSource());
        $this->user = $usersTable->get($userSession->id);
        $this->repository = new UserCredentialSourceRepository(
            $this->user,
            $usersTable
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
        return $this->user;
    }
}
