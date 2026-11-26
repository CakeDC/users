<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Webauthn\Repository;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Webauthn\Base64Utility;
use CakeDC\Users\Webauthn\Repository\UserCredentialSourceRepository;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\CredentialRecord;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredentialUserEntity;

class UserCredentialSourceRepositoryTest extends TestCase
{
    protected array $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    /**
     * Test findOneByCredentialId method
     *
     * @return void
     */
    public function testFindOneByCredentialId()
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get('00000000-0000-0000-0000-000000000001');
        $repository = new UserCredentialSourceRepository($user, $UsersTable);
        $credential = $repository->findOneByCredentialId('12b37486-9299-4331-ac33-85b2d985b6fe');
        $this->assertInstanceOf(CredentialRecord::class, $credential);

        //Not found id
        $repository = new UserCredentialSourceRepository($user, $UsersTable);
        $credential = $repository->findOneByCredentialId('some-testing-value');
        $this->assertNull($credential);
    }

    /**
     * Test findAllForUserEntity method
     *
     * @return void
     */
    public function testFindAllForUserEntity()
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userId = '00000000-0000-0000-0000-000000000001';
        $user = $UsersTable->get($userId);
        $userEntity = new PublicKeyCredentialUserEntity(
            'john.doe',
            $userId,
            'John Doe',
        );
        $repository = new UserCredentialSourceRepository($user, $UsersTable);
        $credentials = $repository->findAllForUserEntity($userEntity);
        $this->assertCount(1, $credentials);
        $this->assertInstanceOf(CredentialRecord::class, $credentials[0]);

        //Not found id
        $userEntityInvalid = new PublicKeyCredentialUserEntity(
            'john.doe',
            '00000000-0000-0000-0000-000000000004',
            'John Doe',
        );
        $repository = new UserCredentialSourceRepository($user, $UsersTable);
        $credentials = $repository->findAllForUserEntity($userEntityInvalid);
        $this->assertEmpty($credentials);
    }

    /**
     * Test saveCredentialSource method
     *
     * @return void
     */
    public function testSaveCredentialSource()
    {
        \Cake\Database\TypeFactory::map('json', \Cake\Database\Type\JsonType::class);

        $publicKeyCredentialId = '12b37486-9299-4331-ac33-85b2d985b6fe';
        $userId = '00000000-0000-0000-0000-000000000001';
        $credentialData = [
            'publicKeyCredentialId' => $publicKeyCredentialId,
            'type' => 'public-key',
            'transports' => [],
            'attestationType' => 'none',
            'trustPath' => [
                'type' => 'Webauthn\TrustPath\EmptyTrustPath',
            ],
            'aaguid' => '00000000-0000-0000-0000-000000000000',
            'credentialPublicKey' => Base64Utility::basicEncode('000000000000000000000000000000000000-9999999999999999999999999999999999999999-XXXXXXXXXXXXX-YYYYYYYYYYY'),
            'userHandle' => Base64Utility::basicEncode($userId),
            'counter' => 191,
            'otherUI' => null,
        ];
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get($userId);
        $firstKey = key($user->additional_data['webauthn_credentials']);
        $userEntity = new PublicKeyCredentialUserEntity(
            'john.doe',
            $userId,
            'John Doe',
        );
        $publicKey = self::createCredentialRecord($credentialData);
        $repository = new UserCredentialSourceRepository($user, $UsersTable);
        $repository->saveCredentialSource($publicKey);
        $credentials = $repository->findAllForUserEntity($userEntity);
        $this->assertCount(2, $credentials);
        $userAfter = $UsersTable->get($user->id);
        $this->assertArrayHasKey(
            '12b37486-9299-4331-ac33-85b2d985b6fe',
            $userAfter->additional_data['webauthn_credentials'],
        );
        $this->assertArrayHasKey(
            $firstKey,
            $userAfter->additional_data['webauthn_credentials'],
        );
        $this->assertCount(
            2,
            $userAfter->additional_data['webauthn_credentials'],
        );
    }

    /**
     * A credential stored by plugin 17 (webauthn-lib 4) still loads
     *
     * @return void
     */
    public function testFindOneByCredentialIdLegacyFormat(): void
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get('00000000-0000-0000-0000-000000000001');
        $repository = new UserCredentialSourceRepository($user, $UsersTable);
        $credential = $repository->findOneByCredentialId('12b37486-9299-4331-ac33-85b2d985b6fe');

        $this->assertInstanceOf(CredentialRecord::class, $credential);
        $this->assertSame(190, $credential->counter);
        $this->assertSame('00000000-0000-0000-0000-000000000001', $credential->userHandle);
        $this->assertSame('public-key', $credential->type);
    }

    /**
     * Saving a loaded credential writes back the same stored data as plugin 17 did
     *
     * @return void
     */
    public function testSaveCredentialSourceRoundTrip(): void
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $userId = '00000000-0000-0000-0000-000000000001';
        $user = $UsersTable->get($userId);
        // As plugin 17 wrote it: the key and publicKeyCredentialId are both the base64url raw id
        $key = Base64Utility::basicEncode('12b37486-9299-4331-ac33-85b2d985b6fe');
        $additionalData = $user->additional_data;
        $additionalData['webauthn_credentials'][$key]['publicKeyCredentialId'] = $key;
        $user->set('additional_data', $additionalData);
        $UsersTable->saveOrFail($user);
        $user = $UsersTable->get($userId);
        $storedBefore = $user->additional_data['webauthn_credentials'];
        $repository = new UserCredentialSourceRepository($user, $UsersTable);
        $credential = $repository->findOneByCredentialId('12b37486-9299-4331-ac33-85b2d985b6fe');
        $repository->saveCredentialSource($credential);

        $storedAfter = $UsersTable->get($userId)->additional_data['webauthn_credentials'];
        $this->assertSame([$key], array_keys($storedAfter));
        foreach (['publicKeyCredentialId', 'credentialPublicKey', 'userHandle', 'counter', 'aaguid', 'attestationType'] as $field) {
            $this->assertSame($storedBefore[$key][$field], $storedAfter[$key][$field], $field);
        }
        $reloaded = (new UserCredentialSourceRepository($UsersTable->get($userId), $UsersTable))
            ->findOneByCredentialId('12b37486-9299-4331-ac33-85b2d985b6fe');
        $this->assertSame($credential->publicKeyCredentialId, $reloaded->publicKeyCredentialId);
        $this->assertSame($credential->credentialPublicKey, $reloaded->credentialPublicKey);
        $this->assertSame(190, $reloaded->counter);
    }

    /**
     * @param array $data Credential data.
     * @return \Webauthn\CredentialRecord
     */
    protected static function createCredentialRecord(array $data): CredentialRecord
    {
        $factory = new WebauthnSerializerFactory(
            new AttestationStatementSupportManager([
                new NoneAttestationStatementSupport(),
            ]),
        );

        return $factory->create()->deserialize(
            (string)json_encode($data),
            CredentialRecord::class,
            'json',
        );
    }
}
