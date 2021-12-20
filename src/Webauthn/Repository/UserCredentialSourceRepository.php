<?php

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
     * @var EntityInterface
     */
    private $user;
    /**
     * @var UsersTable|null
     */
    private $usersTable;

    /**
     * @param EntityInterface $user
     * @param UsersTable|null $usersTable
     */
    public function __construct(EntityInterface $user, ?UsersTable $usersTable = null)
    {
        $this->user = $user;
        $this->usersTable = $usersTable;
    }

    /**
     * @param string $publicKeyCredentialId
     * @return PublicKeyCredentialSource|null
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
     * @param PublicKeyCredentialSource $publicKeyCredentialSource
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
