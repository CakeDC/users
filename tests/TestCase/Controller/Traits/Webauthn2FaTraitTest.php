<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2026, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\ServerRequest;
use Cake\Http\ServerRequestFactory;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use CakeDC\Users\Model\Entity\User;
use CakeDC\Users\Webauthn\AuthenticateAdapter;
use CakeDC\Users\Webauthn\Base64Utility;
use CakeDC\Users\Webauthn\RegisterAdapter;
use Exception;
use Webauthn\PublicKeyCredentialSource;

/**
 * Class Webauthn2faTraitTest
 *
 * @package App\Test\TestCase\Controller\Traits
 */
class Webauthn2FaTraitTest extends BaseTrait
{
    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = [
            'dispatchEvent',
            'redirect',
            'getUsersTable',
            'set',
            'getWebauthn2faAuthenticateAdapter',
        ];

        parent::setUp();

        $request = new ServerRequest();
        $this->Trait->setRequest($request);
        Configure::write('Webauthn2fa.enabled', true);
        Configure::write('Webauthn2fa.appName', 'ACME Webauthn Server');
        Configure::write('Webauthn2fa.id', 'localhost');
    }

    /**
     * Mock session and mock session attributes
     *
     * @param array $attributes session attributes
     * @return \Cake\Http\Session
     */
    protected function mockSession(array $attributes): Session
    {
        $session = new Session();

        foreach ($attributes as $field => $value) {
            $session->write($field, $value);
        }

        $this->Trait
            ->getRequest()
            ->expects($this->atLeastOnce())
            ->method('getSession')
            ->willReturn($session);

        return $session;
    }

    /**
     * Test webauthn2fa method when requires register
     *
     * @return void
     */
    public function testWebauthn2faIsRegister(): void
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['getSession', 'is'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->never())
            ->method('is')
            ->with(
                $this->equalTo('ssl'),
            );

        $table = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users');
        $user = $table->get('00000000-0000-0000-0000-000000000002');
        $this->mockSession([
            'Webauthn2fa.User' => $user,
        ]);
        $this->Trait
            ->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($name, $value) {
                match ([$name, $value]) {
                    ['isRegister', true] => null,
                    ['username', 'user-2'] => null,
                    default => null,
                };

                return $this->Trait;
            });
        $this->Trait->webauthn2fa();
        $this->assertSame(
            $user,
            $this->Trait->getRequest()->getSession()->read('Webauthn2fa.User'),
        );
    }

    /**
     * Test webauthn2fa method when DON'T require register
     *
     * @return void
     */
    public function testWebauthn2faDontRequireRegister(): void
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['getSession', 'is'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->never())
            ->method('is')
            ->with(
                $this->equalTo('ssl'),
            );

        $table = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users');
        $user = $table->get('00000000-0000-0000-0000-000000000001');
        $this->mockSession([
            'Webauthn2fa.User' => $user,
        ]);
        $this->Trait
            ->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($name, $value) {
                match ([$name, $value]) {
                    ['isRegister', false] => null,
                    ['username', 'user-1'] => null,
                    default => null,
                };

                return $this->Trait;
            });
        $this->Trait->webauthn2fa();
        $this->assertSame(
            $user,
            $this->Trait->getRequest()->getSession()->read('Webauthn2fa.User'),
        );
    }

    /**
     * Test webauthn2faRegisterOptions method when DON'T require register
     *
     * @return void
     */
    public function testWebauthn2faRegisterOptionsDontRequireRegister(): void
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['getSession', 'is'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->never())
            ->method('is')
            ->with(
                $this->equalTo('ssl'),
            );

        $table = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users');
        $user = $table->get('00000000-0000-0000-0000-000000000001');
        $this->mockSession([
            'Webauthn2fa.User' => $user,
        ]);
        $this->Trait->expects($this->never())
            ->method('redirect');
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('User already has configured webauthn2fa');
        $this->Trait->webauthn2faRegisterOptions();
    }

    /**
     * Test webauthn2faRegisterOptions method
     *
     * @return void
     */
    public function testWebauthn2faRegisterOptions(): void
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['getSession', 'is'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->never())
            ->method('is')
            ->with(
                $this->equalTo('ssl'),
            );

        $table = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users');
        $user = $table->get('00000000-0000-0000-0000-000000000002');
        $this->mockSession([
            'Webauthn2fa.User' => $user,
        ]);
        $this->Trait->expects($this->never())
            ->method('redirect');

        $response = $this->Trait->webauthn2faRegisterOptions();
        $data = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('rp', $data);
        $this->assertArrayHasKey('user', $data);
        $this->assertSame('user-2', $data['user']['name']);
        $this->assertSame(
            $user,
            $this->Trait->getRequest()->getSession()->read('Webauthn2fa.User'),
        );
    }

    /**
     * Test webauthn2faRegister method when DON'T require register
     *
     * @return void
     */
    public function testWebauthn2faRegisterDontRequireRegister(): void
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['getSession', 'is'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->never())
            ->method('is')
            ->with(
                $this->equalTo('ssl'),
            );

        $table = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users');
        $user = $table->get('00000000-0000-0000-0000-000000000001');
        $this->mockSession([
            'Webauthn2fa.User' => $user,
        ]);
        $this->Trait->expects($this->never())
            ->method('redirect');
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('User already has configured webauthn2fa');
        $this->Trait->webauthn2faRegister();
    }

    /**
     *  Test webauthn2faRegisterOptions method
     *
     * @return void
     */
    public function testWebauthn2faRegister(): void
    {
        $table = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users');
        $user = $table->get('00000000-0000-0000-0000-000000000002');
        $request = ServerRequestFactory::fromGlobals();
        $request->getSession()->write('Webauthn2fa.User', $user);

        $this->Trait->setRequest($request);
        $adapter = $this->getMockBuilder(RegisterAdapter::class)
            ->onlyMethods(['verifyResponse'])
            ->setConstructorArgs([$request])
            ->getMock();
        $publicKeyCredentialId = '12b37486-9299-4331-ac33-85b2d985b6fe';
        $userId = '00000000-0000-0000-0000-000000000002';
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
        $credential = PublicKeyCredentialSource::createFromArray($credentialData);
        $adapter->expects($this->once())
            ->method('verifyResponse')
            ->willReturn($credential);

        $this->Trait->expects($this->never())
            ->method('redirect');
        $data = '{"id":"LFdoCFJTyB82ZzSJUHc-c72yraRc_1mPvGX8ToE8su39xX26Jcqd31LUkKOS36FIAWgWl6itMKqmDvruha6ywA","rawId":"LFdoCFJTyB82ZzSJUHc-c72yraRc_1mPvGX8ToE8su39xX26Jcqd31LUkKOS36FIAWgWl6itMKqmDvruha6ywA","response":{"clientDataJSON":"eyJjaGFsbGVuZ2UiOiJOeHlab3B3VktiRmw3RW5uTWFlXzVGbmlyN1FKN1FXcDFVRlVLakZIbGZrIiwiY2xpZW50RXh0ZW5zaW9ucyI6e30sImhhc2hBbGdvcml0aG0iOiJTSEEtMjU2Iiwib3JpZ2luIjoiaHR0cDovL2xvY2FsaG9zdDozMDAwIiwidHlwZSI6IndlYmF1dGhuLmNyZWF0ZSJ9","attestationObject":"o2NmbXRoZmlkby11MmZnYXR0U3RtdKJjc2lnWEcwRQIgVzzvX3Nyp_g9j9f2B-tPWy6puW01aZHI8RXjwqfDjtQCIQDLsdniGPO9iKr7tdgVV-FnBYhvzlZLG3u28rVt10YXfGN4NWOBWQJOMIICSjCCATKgAwIBAgIEVxb3wDANBgkqhkiG9w0BAQsFADAuMSwwKgYDVQQDEyNZdWJpY28gVTJGIFJvb3QgQ0EgU2VyaWFsIDQ1NzIwMDYzMTAgFw0xNDA4MDEwMDAwMDBaGA8yMDUwMDkwNDAwMDAwMFowLDEqMCgGA1UEAwwhWXViaWNvIFUyRiBFRSBTZXJpYWwgMjUwNTY5MjI2MTc2MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEZNkcVNbZV43TsGB4TEY21UijmDqvNSfO6y3G4ytnnjP86ehjFK28-FdSGy9MSZ-Ur3BVZb4iGVsptk5NrQ3QYqM7MDkwIgYJKwYBBAGCxAoCBBUxLjMuNi4xLjQuMS40MTQ4Mi4xLjUwEwYLKwYBBAGC5RwCAQEEBAMCBSAwDQYJKoZIhvcNAQELBQADggEBAHibGMqbpNt2IOL4i4z96VEmbSoid9Xj--m2jJqg6RpqSOp1TO8L3lmEA22uf4uj_eZLUXYEw6EbLm11TUo3Ge-odpMPoODzBj9aTKC8oDFPfwWj6l1O3ZHTSma1XVyPqG4A579f3YAjfrPbgj404xJns0mqx5wkpxKlnoBKqo1rqSUmonencd4xanO_PHEfxU0iZif615Xk9E4bcANPCfz-OLfeKXiT-1msixwzz8XGvl2OTMJ_Sh9G9vhE-HjAcovcHfumcdoQh_WM445Za6Pyn9BZQV3FCqMviRR809sIATfU5lu86wu_5UGIGI7MFDEYeVGSqzpzh6mlcn8QSIZoYXV0aERhdGFYxEmWDeWIDoxodDQXD2R2YFuP5K65ooYyx5lc87qDHZdjQQAAAAAAAAAAAAAAAAAAAAAAAAAAAEAsV2gIUlPIHzZnNIlQdz5zvbKtpFz_WY-8ZfxOgTyy7f3Ffbolyp3fUtSQo5LfoUgBaBaXqK0wqqYO-u6FrrLApQECAyYgASFYIPr9-YH8DuBsOnaI3KJa0a39hyxh9LDtHErNvfQSyxQsIlgg4rAuQQ5uy4VXGFbkiAt0uwgJJodp-DymkoBcrGsLtkI"},"type":"public-key"}';
        $request = $request->withParsedBody(
            json_decode($data, true),
        );
        $this->Trait->setRequest($request);
        $response = $this->Trait->webauthn2faRegister($adapter);
        $this->assertEquals('{"success":true}', (string)$response->getBody());
    }

    /**
     *  Test webauthn2faRegisterOptions method
     *
     * @return void
     */
    public function testWebauthn2faRegisterError(): void
    {
        $table = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users');
        $user = $table->get('00000000-0000-0000-0000-000000000002');
        $request = ServerRequestFactory::fromGlobals();
        $request->getSession()->write('Webauthn2fa.User', $user);

        $this->Trait->setRequest($request);
        $adapter = $this->getMockBuilder(RegisterAdapter::class)
            ->onlyMethods(['verifyResponse'])
            ->setConstructorArgs([$request])
            ->getMock();

        $adapter->expects($this->once())
            ->method('verifyResponse')
            ->willThrowException(new Exception('Testing error exception for webauthn2faRegister'));

        $this->Trait->expects($this->never())
            ->method('redirect');
        $data = '{"id":"LFdoCFJTyB82ZzSJUHc-c72yraRc_1mPvGX8ToE8su39xX26Jcqd31LUkKOS36FIAWgWl6itMKqmDvruha6ywA","rawId":"LFdoCFJTyB82ZzSJUHc-c72yraRc_1mPvGX8ToE8su39xX26Jcqd31LUkKOS36FIAWgWl6itMKqmDvruha6ywA","response":{"clientDataJSON":"eyJjaGFsbGVuZ2UiOiJOeHlab3B3VktiRmw3RW5uTWFlXzVGbmlyN1FKN1FXcDFVRlVLakZIbGZrIiwiY2xpZW50RXh0ZW5zaW9ucyI6e30sImhhc2hBbGdvcml0aG0iOiJTSEEtMjU2Iiwib3JpZ2luIjoiaHR0cDovL2xvY2FsaG9zdDozMDAwIiwidHlwZSI6IndlYmF1dGhuLmNyZWF0ZSJ9","attestationObject":"o2NmbXRoZmlkby11MmZnYXR0U3RtdKJjc2lnWEcwRQIgVzzvX3Nyp_g9j9f2B-tPWy6puW01aZHI8RXjwqfDjtQCIQDLsdniGPO9iKr7tdgVV-FnBYhvzlZLG3u28rVt10YXfGN4NWOBWQJOMIICSjCCATKgAwIBAgIEVxb3wDANBgkqhkiG9w0BAQsFADAuMSwwKgYDVQQDEyNZdWJpY28gVTJGIFJvb3QgQ0EgU2VyaWFsIDQ1NzIwMDYzMTAgFw0xNDA4MDEwMDAwMDBaGA8yMDUwMDkwNDAwMDAwMFowLDEqMCgGA1UEAwwhWXViaWNvIFUyRiBFRSBTZXJpYWwgMjUwNTY5MjI2MTc2MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEZNkcVNbZV43TsGB4TEY21UijmDqvNSfO6y3G4ytnnjP86ehjFK28-FdSGy9MSZ-Ur3BVZb4iGVsptk5NrQ3QYqM7MDkwIgYJKwYBBAGCxAoCBBUxLjMuNi4xLjQuMS40MTQ4Mi4xLjUwEwYLKwYBBAGC5RwCAQEEBAMCBSAwDQYJKoZIhvcNAQELBQADggEBAHibGMqbpNt2IOL4i4z96VEmbSoid9Xj--m2jJqg6RpqSOp1TO8L3lmEA22uf4uj_eZLUXYEw6EbLm11TUo3Ge-odpMPoODzBj9aTKC8oDFPfwWj6l1O3ZHTSma1XVyPqG4A579f3YAjfrPbgj404xJns0mqx5wkpxKlnoBKqo1rqSUmonencd4xanO_PHEfxU0iZif615Xk9E4bcANPCfz-OLfeKXiT-1msixwzz8XGvl2OTMJ_Sh9G9vhE-HjAcovcHfumcdoQh_WM445Za6Pyn9BZQV3FCqMviRR809sIATfU5lu86wu_5UGIGI7MFDEYeVGSqzpzh6mlcn8QSIZoYXV0aERhdGFYxEmWDeWIDoxodDQXD2R2YFuP5K65ooYyx5lc87qDHZdjQQAAAAAAAAAAAAAAAAAAAAAAAAAAAEAsV2gIUlPIHzZnNIlQdz5zvbKtpFz_WY-8ZfxOgTyy7f3Ffbolyp3fUtSQo5LfoUgBaBaXqK0wqqYO-u6FrrLApQECAyYgASFYIPr9-YH8DuBsOnaI3KJa0a39hyxh9LDtHErNvfQSyxQsIlgg4rAuQQ5uy4VXGFbkiAt0uwgJJodp-DymkoBcrGsLtkI"},"type":"public-key"}';
        $request = $request->withParsedBody(
            json_decode($data, true),
        );
        $this->Trait->setRequest($request);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Testing error exception for webauthn2faRegister');
        $this->Trait->webauthn2faRegister($adapter);
    }

    /**
     * Test webauthn2faAuthenticateOptions
     *
     * @return void
     */
    public function testWebauthn2faAuthenticateOptions(): void
    {
        $request = $this->getMockBuilder('Cake\Http\ServerRequest')
            ->onlyMethods(['getSession', 'is'])
            ->getMock();
        $this->Trait->setRequest($request);
        $request->expects($this->never())
            ->method('is')
            ->with(
                $this->equalTo('ssl'),
            );

        $table = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users');
        $user = $table->get('00000000-0000-0000-0000-000000000001');
        $this->mockSession([
            'Webauthn2fa.User' => $user,
        ]);
        $this->Trait->expects($this->once())
            ->method('getWebauthn2faAuthenticateAdapter')
            ->willReturn(new AuthenticateAdapter($this->Trait->getRequest()));
        $this->Trait->expects($this->never())
            ->method('redirect');

        $response = $this->Trait->webauthn2faAuthenticateOptions();
        $data = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('challenge', $data);
        $this->assertArrayHasKey('userVerification', $data);
        $this->assertArrayHasKey('allowCredentials', $data);
        $expectedCredentials = [
            [
                'type' => 'public-key',
                'id' => '12b37486-9299-4331-ac33-85b2d985b6fe',
            ],
        ];
        $this->assertEquals($expectedCredentials, $data['allowCredentials']);
        $this->assertSame(
            $user,
            $this->Trait->getRequest()->getSession()->read('Webauthn2fa.User'),
        );
    }

    /**
     *  Test webauthn2faAuthenticateOptions method
     *
     * @return void
     */
    public function testWebauthn2faAuthenticate(): void
    {
        $table = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users');
        $user = $table->get('00000000-0000-0000-0000-000000000002');
        $request = ServerRequestFactory::fromGlobals();
        $request->getSession()->write('Webauthn2fa.User', $user);

        $this->Trait->setRequest($request);
        $adapter = $this->getMockBuilder(AuthenticateAdapter::class)
            ->onlyMethods(['verifyResponse'])
            ->setConstructorArgs([$request])
            ->getMock();
        $publicKeyCredentialId = '12b37486-9299-4331-ac33-85b2d985b6fe';
        $userId = '00000000-0000-0000-0000-000000000002';
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
        $credential = PublicKeyCredentialSource::createFromArray($credentialData);
        $adapter->expects($this->once())
            ->method('verifyResponse')
            ->willReturn($credential);

        $this->Trait->expects($this->once())
            ->method('getWebauthn2faAuthenticateAdapter')
            ->willReturn($adapter);
        $this->Trait->setRequest($request);
        $response = $this->Trait->webauthn2faAuthenticate();
        $expected = [
            'success' => true,
            'redirectUrl' => '/login',
        ];
        $actual = json_decode((string)$response->getBody(), true);
        $this->assertEquals($expected, $actual);

        $this->assertNull(
            $this->Trait->getRequest()->getSession()->read('Webauthn2fa.User'),
        );
        $userSession = $this->Trait->getRequest()->getSession()->read('TwoFactorAuthenticator.User');
        $this->assertInstanceOf(
            User::class,
            $userSession,
        );
        $this->assertEquals($userSession->toArray(), $user->toArray());
    }

    /**
     *  Test webauthn2faAuthenticateOptions method
     *
     * @return void
     */
    public function testWebauthn2faAuthenticateError(): void
    {
        $table = TableRegistry::getTableLocator()
            ->get('CakeDC/Users.Users');
        $user = $table->get('00000000-0000-0000-0000-000000000002');
        $request = ServerRequestFactory::fromGlobals();
        $request->getSession()->write('Webauthn2fa.User', $user);

        $this->Trait->setRequest($request);
        $adapter = $this->getMockBuilder(AuthenticateAdapter::class)
            ->onlyMethods(['verifyResponse'])
            ->setConstructorArgs([$request])
            ->getMock();

        $adapter->expects($this->once())
            ->method('verifyResponse')
            ->willThrowException(new Exception('Test exception error for webauthn2faAuthenticate'));

        $this->Trait->expects($this->once())
            ->method('getWebauthn2faAuthenticateAdapter')
            ->willReturn($adapter);
        $this->Trait->setRequest($request);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception error for webauthn2faAuthenticate');
        $this->Trait->webauthn2faAuthenticate();
    }
}
