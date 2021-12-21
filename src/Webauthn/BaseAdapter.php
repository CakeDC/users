<?php
declare(strict_types=1);

namespace CakeDC\Users\Webauthn;

use Cake\Core\Configure;
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
     * @var \Cake\Http\ServerRequest
     */
    protected $request;
    /**
     * @var \CakeDC\Users\Webauthn\Repository\UserCredentialSourceRepository
     */
    protected $repository;
    /**
     * @var \Webauthn\Server
     */
    protected $server;
    /**
     * @var \Cake\Datasource\EntityInterface|\CakeDC\Users\Model\Entity\User
     */
    private $user;

    /**
     * @param \Cake\Http\ServerRequest $request The request.
     * @param \CakeDC\Users\Model\Table\UsersTable|null $usersTable The users table.
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
     * @return \Webauthn\PublicKeyCredentialUserEntity
     */
    protected function getUserEntity(): PublicKeyCredentialUserEntity
    {
        $user = $this->getUser();

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

    /**
     * @return bool
     */
    public function hasCredential(): bool
    {
        return (bool)$this->repository->findAllForUserEntity(
            $this->getUserEntity()
        );
    }
}
