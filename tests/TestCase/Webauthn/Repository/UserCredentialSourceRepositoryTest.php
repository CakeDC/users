<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Webauthn\Repository;

use Base64Url\Base64Url;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Webauthn\Repository\UserCredentialSourceRepository;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

class UserCredentialSourceRepositoryTest extends TestCase
{
    public $fixtures = [
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
        $this->assertInstanceOf(PublicKeyCredentialSource::class, $credential);

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
            'John Doe'
        );
        $repository = new UserCredentialSourceRepository($user, $UsersTable);
        $credentials = $repository->findAllForUserEntity($userEntity);
        $this->assertCount(1, $credentials);
        $this->assertInstanceOf(PublicKeyCredentialSource::class, $credentials[0]);

        //Not found id
        $userEntityInvalid = new PublicKeyCredentialUserEntity(
            'john.doe',
            '00000000-0000-0000-0000-000000000004',
            'John Doe'
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
            'credentialPublicKey' => Base64Url::encode('000000000000000000000000000000000000-9999999999999999999999999999999999999999-XXXXXXXXXXXXX-YYYYYYYYYYY'),
            'userHandle' => Base64Url::encode($userId),
            'counter' => 191,
            'otherUI' => null,
        ];
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get($userId);
        $firstKey = key($user->additional_data['webauthn_credentials']);
        $userEntity = new PublicKeyCredentialUserEntity(
            'john.doe',
            $userId,
            'John Doe'
        );
        $publicKey = PublicKeyCredentialSource::createFromArray($credentialData);
        $repository = new UserCredentialSourceRepository($user, $UsersTable);
        $repository->saveCredentialSource($publicKey);
        $credentials = $repository->findAllForUserEntity($userEntity);
        $this->assertCount(2, $credentials);
        $userAfter = $UsersTable->get($user->id);
        $this->assertArrayHasKey(
            '12b37486-9299-4331-ac33-85b2d985b6fe',
            $userAfter->additional_data['webauthn_credentials']
        );
        $this->assertArrayHasKey(
            $firstKey,
            $userAfter->additional_data['webauthn_credentials']
        );
        $this->assertCount(
            2,
            $userAfter->additional_data['webauthn_credentials']
        );
    }
}
