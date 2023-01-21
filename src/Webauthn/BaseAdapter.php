<?php
declare(strict_types=1);

namespace CakeDC\Users\Webauthn;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use CakeDC\Users\Model\Table\UsersTable;
use CakeDC\Users\Webauthn\Repository\UserCredentialSourceRepository;
use Cose\Algorithm\Manager;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\ECDSA\ES256K;
use Cose\Algorithm\Signature\ECDSA\ES384;
use Cose\Algorithm\Signature\ECDSA\ES512;
use Cose\Algorithm\Signature\EdDSA\Ed256;
use Cose\Algorithm\Signature\EdDSA\Ed512;
use Cose\Algorithm\Signature\RSA\PS256;
use Cose\Algorithm\Signature\RSA\PS384;
use Cose\Algorithm\Signature\RSA\PS512;
use Cose\Algorithm\Signature\RSA\RS256;
use Cose\Algorithm\Signature\RSA\RS384;
use Cose\Algorithm\Signature\RSA\RS512;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

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
     * @var \Cake\Datasource\EntityInterface|\CakeDC\Users\Model\Entity\User
     */
    private $user;
    /**
     * @var \Webauthn\PublicKeyCredentialRpEntity
     */
    protected PublicKeyCredentialRpEntity $rpEntity;
    /**
     * @var \Webauthn\AttestationStatement\AttestationStatementSupportManager|null
     */
    protected ?AttestationStatementSupportManager $attestationStatementSupportManager = null;
    /**
     * @var \Cose\Algorithm\Manager
     */
    protected ?Manager $algorithmManager = null;

    /**
     * @param \Cake\Http\ServerRequest $request The request.
     * @param \CakeDC\Users\Model\Table\UsersTable|null $usersTable The users table.
     */
    public function __construct(ServerRequest $request, ?UsersTable $usersTable = null)
    {
        $this->request = $request;
        $this->rpEntity = new PublicKeyCredentialRpEntity(
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
     * @return mixed|array|null
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

    /**
     * @param \Webauthn\AttestationStatement\AttestationStatementSupportManager $attestationStatementSupportManager
     */
    public function setAttestationStatementSupportManager(AttestationStatementSupportManager $attestationStatementSupportManager): void
    {
        $this->attestationStatementSupportManager = $attestationStatementSupportManager;
    }

    /**
     * @return \Webauthn\AttestationStatement\AttestationStatementSupportManager
     */
    protected function getAttestationStatementSupportManager(): AttestationStatementSupportManager
    {
        if ($this->attestationStatementSupportManager === null) {
            $this->attestationStatementSupportManager = new AttestationStatementSupportManager();
            $this->attestationStatementSupportManager
                ->add(new NoneAttestationStatementSupport());
        }

        return $this->attestationStatementSupportManager;
    }

    /**
     * @return \CakeDC\Users\Webauthn\PublicKeyCredentialLoader
     */
    protected function createPublicKeyCredentialLoader(): PublicKeyCredentialLoader
    {
        $attestationObjectLoader = new AttestationObjectLoader(
            $this->getAttestationStatementSupportManager()
        );

        return new PublicKeyCredentialLoader(
            $attestationObjectLoader
        );
    }

    /**
     * @return \Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler
     */
    protected function createExtensionOutputCheckerHandler(): ExtensionOutputCheckerHandler
    {
        return new ExtensionOutputCheckerHandler();
    }

    /**
     * @return \Cose\Algorithm\Manager
     */
    protected function getAlgorithmManager(): Manager
    {
        if ($this->algorithmManager === null) {
            $this->algorithmManager = Manager::create()->add(
                ES256::create(),
                ES256K::create(),
                ES384::create(),
                ES512::create(),
                RS256::create(),
                RS384::create(),
                RS512::create(),
                PS256::create(),
                PS384::create(),
                PS512::create(),
                Ed256::create(),
                Ed512::create(),
            );
        }

        return $this->algorithmManager;
    }
}
