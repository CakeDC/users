<?php
declare(strict_types=1);

namespace CakeDC\Users\Webauthn\Repository;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use CakeDC\Users\Webauthn\Base64Utility;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\CredentialRecord;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredentialUserEntity;

class UserCredentialSourceRepository
{
    /**
     * @var \Cake\Datasource\EntityInterface $user
     */
    private EntityInterface $user;
    /**
     * @var \Cake\ORM\Table|null
     */
    private ?Table $usersTable = null;

    private ?SerializerInterface $serializer = null;

    /**
     * @param \Cake\Datasource\EntityInterface $user The user.
     * @param \Cake\ORM\Table|null $usersTable The table.
     */
    public function __construct(EntityInterface $user, ?Table $usersTable = null)
    {
        $this->user = $user;
        $this->usersTable = $usersTable;
    }

    /**
     * @param string $publicKeyCredentialId Public key credential id
     * @return \Webauthn\CredentialRecord|null
     */
    public function findOneByCredentialId(string $publicKeyCredentialId): ?CredentialRecord
    {
        $encodedId = Base64Utility::basicEncode($publicKeyCredentialId);
        $credential = $this->user['additional_data']['webauthn_credentials'][$encodedId] ?? null;

        return $credential
            ? $this->createRecord($credential)
            : null;
    }

    /**
     * @param \Webauthn\PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity User entity.
     * @return array<\Webauthn\CredentialRecord>
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        if ($publicKeyCredentialUserEntity->id != $this->user->get('id')) {
            return [];
        }
        $credentials = $this->user['additional_data']['webauthn_credentials'] ?? [];
        $list = [];
        foreach ($credentials as $credential) {
            $list[] = $this->createRecord($credential);
        }

        return $list;
    }

    /**
     * @param \Webauthn\CredentialRecord $credentialRecord Credential record
     * @return void
     */
    public function saveCredentialSource(CredentialRecord $credentialRecord): void
    {
        $credentials = $this->user['additional_data']['webauthn_credentials'] ?? [];
        $id = Base64Utility::basicEncode($credentialRecord->publicKeyCredentialId);
        $credentials[$id] = json_decode(
            $this->getSerializer()->serialize($credentialRecord, 'json'),
            true,
        );
        $additionalData = $this->user->get('additional_data') ?? [];
        $additionalData['webauthn_credentials'] = $credentials;
        $this->user->set('additional_data', $additionalData);
        $this->usersTable->saveOrFail($this->user);
    }

    /**
     * @param array $data Stored credential data.
     * @return \Webauthn\CredentialRecord
     */
    protected function createRecord(array $data): CredentialRecord
    {
        return $this->getSerializer()->deserialize(
            (string)json_encode($data),
            CredentialRecord::class,
            'json',
        );
    }

    /**
     * @return \Symfony\Component\Serializer\SerializerInterface
     */
    protected function getSerializer(): SerializerInterface
    {
        if ($this->serializer === null) {
            $factory = new WebauthnSerializerFactory(
                new AttestationStatementSupportManager([
                    new NoneAttestationStatementSupport(),
                ]),
            );
            $this->serializer = $factory->create();
        }

        return $this->serializer;
    }
}
