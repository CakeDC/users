<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Webauthn;

use Base64Url\Base64Url;
use Cake\Core\Configure;
use Cake\Http\ServerRequestFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Webauthn\RegisterAdapter;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialSource;

class RegisterAdapterTest extends TestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    /**
     * Test getRegisterOptions method
     *
     * @return void
     */
    public function testGetOptions()
    {
        Configure::write('Webauthn2fa.appName', 'ACME Webauthn Server');
        Configure::write('Webauthn2fa.id', 'localhost');
        $userId = '00000000-0000-0000-0000-000000000002';
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get($userId);
        $request = ServerRequestFactory::fromGlobals();
        $request->getSession()->write('Webauthn2fa.User', $user);
        $adapter = new RegisterAdapter($request, $UsersTable);
        $this->assertFalse($adapter->hasCredential());
        $options = $adapter->getOptions();
        $this->assertInstanceOf(PublicKeyCredentialCreationOptions::class, $options);
        $this->assertSame($options, $request->getSession()->read('Webauthn2fa.registerOptions'));

        $data = '{"id":"LFdoCFJTyB82ZzSJUHc-c72yraRc_1mPvGX8ToE8su39xX26Jcqd31LUkKOS36FIAWgWl6itMKqmDvruha6ywA","rawId":"LFdoCFJTyB82ZzSJUHc-c72yraRc_1mPvGX8ToE8su39xX26Jcqd31LUkKOS36FIAWgWl6itMKqmDvruha6ywA","response":{"clientDataJSON":"eyJjaGFsbGVuZ2UiOiJOeHlab3B3VktiRmw3RW5uTWFlXzVGbmlyN1FKN1FXcDFVRlVLakZIbGZrIiwiY2xpZW50RXh0ZW5zaW9ucyI6e30sImhhc2hBbGdvcml0aG0iOiJTSEEtMjU2Iiwib3JpZ2luIjoiaHR0cDovL2xvY2FsaG9zdDozMDAwIiwidHlwZSI6IndlYmF1dGhuLmNyZWF0ZSJ9","attestationObject":"o2NmbXRoZmlkby11MmZnYXR0U3RtdKJjc2lnWEcwRQIgVzzvX3Nyp_g9j9f2B-tPWy6puW01aZHI8RXjwqfDjtQCIQDLsdniGPO9iKr7tdgVV-FnBYhvzlZLG3u28rVt10YXfGN4NWOBWQJOMIICSjCCATKgAwIBAgIEVxb3wDANBgkqhkiG9w0BAQsFADAuMSwwKgYDVQQDEyNZdWJpY28gVTJGIFJvb3QgQ0EgU2VyaWFsIDQ1NzIwMDYzMTAgFw0xNDA4MDEwMDAwMDBaGA8yMDUwMDkwNDAwMDAwMFowLDEqMCgGA1UEAwwhWXViaWNvIFUyRiBFRSBTZXJpYWwgMjUwNTY5MjI2MTc2MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEZNkcVNbZV43TsGB4TEY21UijmDqvNSfO6y3G4ytnnjP86ehjFK28-FdSGy9MSZ-Ur3BVZb4iGVsptk5NrQ3QYqM7MDkwIgYJKwYBBAGCxAoCBBUxLjMuNi4xLjQuMS40MTQ4Mi4xLjUwEwYLKwYBBAGC5RwCAQEEBAMCBSAwDQYJKoZIhvcNAQELBQADggEBAHibGMqbpNt2IOL4i4z96VEmbSoid9Xj--m2jJqg6RpqSOp1TO8L3lmEA22uf4uj_eZLUXYEw6EbLm11TUo3Ge-odpMPoODzBj9aTKC8oDFPfwWj6l1O3ZHTSma1XVyPqG4A579f3YAjfrPbgj404xJns0mqx5wkpxKlnoBKqo1rqSUmonencd4xanO_PHEfxU0iZif615Xk9E4bcANPCfz-OLfeKXiT-1msixwzz8XGvl2OTMJ_Sh9G9vhE-HjAcovcHfumcdoQh_WM445Za6Pyn9BZQV3FCqMviRR809sIATfU5lu86wu_5UGIGI7MFDEYeVGSqzpzh6mlcn8QSIZoYXV0aERhdGFYxEmWDeWIDoxodDQXD2R2YFuP5K65ooYyx5lc87qDHZdjQQAAAAAAAAAAAAAAAAAAAAAAAAAAAEAsV2gIUlPIHzZnNIlQdz5zvbKtpFz_WY-8ZfxOgTyy7f3Ffbolyp3fUtSQo5LfoUgBaBaXqK0wqqYO-u6FrrLApQECAyYgASFYIPr9-YH8DuBsOnaI3KJa0a39hyxh9LDtHErNvfQSyxQsIlgg4rAuQQ5uy4VXGFbkiAt0uwgJJodp-DymkoBcrGsLtkI"},"type":"public-key"}';
        $request = $request->withParsedBody(
            json_decode($data, true)
        );
        //Mock success response
        $adapter = $this->getMockBuilder(RegisterAdapter::class)
            ->onlyMethods(['loadAndCheckAttestationResponse'])
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
            'credentialPublicKey' => Base64Url::encode('000000000000000000000000000000000000-9999999999999999999999999999999999999999-XXXXXXXXXXXXX-YYYYYYYYYYY'),
            'userHandle' => Base64Url::encode($userId),
            'counter' => 191,
            'otherUI' => null,
        ];
        $credential = PublicKeyCredentialSource::createFromArray($credentialData);
        $adapter->expects($this->once())
            ->method('loadAndCheckAttestationResponse')
            ->with(
                $this->equalTo($options)
            )
            ->willReturn($credential);
        $credentialsList = $adapter->getUser()->additional_data['webauthn_credentials'] ?? [];
        $this->assertCount(0, $credentialsList);
        $actual = $adapter->verifyResponse();
        $this->assertEquals($credential, $actual);
        $credentialsList = $adapter->getUser()->additional_data['webauthn_credentials'];
        $this->assertCount(1, $credentialsList);
        $key = key($credentialsList);
        $this->assertIsString($key);
        $this->assertTrue(isset($credentialsList[$key]['publicKeyCredentialId']));
        $this->assertTrue($adapter->hasCredential());
        //Invalid challenge without mock
        $adapter = new RegisterAdapter($request);
        $this->expectException(\Assert\InvalidArgumentException::class);
        $this->getExpectedExceptionMessage('Invalid challenge.');
        $adapter->verifyResponse();
    }
}
