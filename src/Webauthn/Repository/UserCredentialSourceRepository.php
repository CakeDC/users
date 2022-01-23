<?php
declare(strict_types=1);

namespace CakeDC\Users\Webauthn\Repository;

use Base64Url\Base64Url;
use Cake\Datasource\EntityInterface;
use CakeDC\Users\Model\Table\UsersTable;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

class UserCredentialSourceRepository implements PublicKeyCredentialSourceRepository
{
    /**
     * @var \Cake\Datasource\EntityInterface
     */
    private $user;
    /**
     * @var \CakeDC\Users\Model\Table\UsersTable|null
     */
    private $usersTable;

    /**
     * @param \Cake\Datasource\EntityInterface $user The user.
     * @param \CakeDC\Users\Model\Table\UsersTable|null $usersTable The table.
     */
    public function __construct(EntityInterface $user, ?UsersTable $usersTable = null)
    {
        $this->user = $user;
        $this->usersTable = $usersTable;
    }

    /**
     * @param string $publicKeyCredentialId  Public key credential id
     * @return \Webauthn\PublicKeyCredentialSource|null
     */
    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        $encodedId = Base64Url::encode($publicKeyCredentialId);
        $credential = $this->user['additional_data']['webauthn_credentials'][$encodedId] ?? null;

        return $credential
            ? PublicKeyCredentialSource::createFromArray($credential)
            : null;
    }

    /**
     * @inheritDoc
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        if ($publicKeyCredentialUserEntity->getId() != $this->user->id) {
            return [];
        }
        $credentials = $this->user['additional_data']['webauthn_credentials'] ?? [];
        $list = [];
        foreach ($credentials as $credential) {
            $list[] = PublicKeyCredentialSource::createFromArray($credential);
        }

        return $list;
    }

    /**
     * @param \Webauthn\PublicKeyCredentialSource $publicKeyCredentialSource Public key credential source
     */
    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        $credentials = $this->user['additional_data']['webauthn_credentials'] ?? [];
        $id = Base64Url::encode($publicKeyCredentialSource->getPublicKeyCredentialId());
        $credentials[$id] = json_decode(json_encode($publicKeyCredentialSource), true);
        $this->user['additional_data'] = $this->user['additional_data'] ?? [];
        $this->user['additional_data']['webauthn_credentials'] = $credentials;
        $this->usersTable->saveOrFail($this->user);
    }
}
