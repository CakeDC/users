<?php

namespace CakeDC\Users\Webauthn\Repository;


use Base64Url\Base64Url;
use Cake\Datasource\EntityInterface;
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
     * @param EntityInterface $user
     */
    public function __construct(EntityInterface $user)
    {
        $this->user = $user;
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
    }
}
