<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Webauthn;

use Base64Url\Base64Url;
use Cake\Core\Configure;
use Cake\Http\ServerRequestFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Webauthn\AuthenticateAdapter;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;

class AuthenticateAdapterTest extends TestCase
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
        $userId = '00000000-0000-0000-0000-000000000001';
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get($userId);
        $request = ServerRequestFactory::fromGlobals();
        $request->getSession()->write('Webauthn2fa.User', $user);
        $adapter = new AuthenticateAdapter($request);
        $options = $adapter->getOptions();
        $this->assertInstanceOf(PublicKeyCredentialRequestOptions::class, $options);
        $this->assertSame($options, $request->getSession()->read('Webauthn2fa.authenticateOptions'));
        $data = json_decode('{"id":"LFdoCFJTyB82ZzSJUHc-c72yraRc_1mPvGX8ToE8su39xX26Jcqd31LUkKOS36FIAWgWl6itMKqmDvruha6ywA","rawId":"LFdoCFJTyB82ZzSJUHc-c72yraRc_1mPvGX8ToE8su39xX26Jcqd31LUkKOS36FIAWgWl6itMKqmDvruha6ywA","response":{"authenticatorData":"SZYN5YgOjGh0NBcPZHZgW4_krrmihjLHmVzzuoMdl2MBAAAAAA","signature":"MEYCIQCv7EqsBRtf2E4o_BjzZfBwNpP8fLjd5y6TUOLWt5l9DQIhANiYig9newAJZYTzG1i5lwP-YQk9uXFnnDaHnr2yCKXL","userHandle":"","clientDataJSON":"eyJjaGFsbGVuZ2UiOiJ4ZGowQ0JmWDY5MnFzQVRweTBrTmM4NTMzSmR2ZExVcHFZUDh3RFRYX1pFIiwiY2xpZW50RXh0ZW5zaW9ucyI6e30sImhhc2hBbGdvcml0aG0iOiJTSEEtMjU2Iiwib3JpZ2luIjoiaHR0cDovL2xvY2FsaG9zdDozMDAwIiwidHlwZSI6IndlYmF1dGhuLmdldCJ9"},"type":"public-key"}', true);
        $request = $request->withParsedBody($data);

        $adapter = $this->getMockBuilder(AuthenticateAdapter::class)
            ->onlyMethods(['loadAndCheckAssertionResponse'])
            ->setConstructorArgs([$request])
            ->getMock();
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
        $credential = PublicKeyCredentialSource::createFromArray($credentialData);
        $adapter->expects($this->once())
            ->method('loadAndCheckAssertionResponse')
            ->with(
                $this->equalTo($options)
            )
            ->willReturn($credential);
        $actual = $adapter->verifyResponse();
        $this->assertEquals($credential, $actual);

        $adapter = new AuthenticateAdapter($request);

        $this->expectException(\Assert\InvalidArgumentException::class);
        $this->getExpectedExceptionMessage('The credential ID is not allowed.');
        $adapter->verifyResponse();
    }
}
