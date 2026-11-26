<?php
declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Webauthn;

use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\ServerRequest;
use Cake\Http\ServerRequestFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Webauthn\AuthenticateAdapter;
use Webauthn\PublicKeyCredentialRequestOptions;

class AuthenticateAdapterTest extends TestCase
{
    protected array $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        Configure::write('Webauthn2fa.appName', 'ACME Webauthn Server');
        Configure::write('Webauthn2fa.id', 'localhost');
    }

    /**
     * Test getRegisterOptions method
     *
     * @return void
     */
    public function testGetOptions()
    {
        $userId = '00000000-0000-0000-0000-000000000001';
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get($userId);
        $request = ServerRequestFactory::fromGlobals();
        $request->getSession()->write('Webauthn2fa.User', $user);
        $adapter = new AuthenticateAdapter($request);
        $options = $adapter->getOptions();
        $this->assertInstanceOf(PublicKeyCredentialRequestOptions::class, $options);
        $stored = $request->getSession()->read('Webauthn2fa.authenticateOptions');
        $this->assertIsString($stored);
        $this->assertSame($adapter->serializeOptions($options), $stored);
    }

    /**
     * The options JSON keeps the shape that webroot/js/webauthn.js reads
     *
     * @return void
     */
    public function testSerializeOptionsShape(): void
    {
        $adapter = $this->createAdapterForUser(ServerRequestFactory::fromGlobals());
        $json = json_decode($adapter->serializeOptions($adapter->getOptions()), true);

        $this->assertSame('localhost', $json['rpId']);
        $this->assertSame('preferred', $json['userVerification']);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $json['challenge']);
        $this->assertNotEmpty($json['allowCredentials']);
        foreach ($json['allowCredentials'] as $credential) {
            $this->assertSame('public-key', $credential['type']);
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $credential['id']);
        }
    }

    /**
     * An assertion for a credential the user does not have is rejected
     *
     * @return void
     */
    public function testVerifyResponseUnknownCredential(): void
    {
        $encode = fn(string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        $request = ServerRequestFactory::fromGlobals();
        $adapter = $this->createAdapterForUser($request);
        $options = $adapter->getOptions();
        $rawId = $encode(random_bytes(16));
        $request = $request->withParsedBody([
            'id' => $rawId,
            'rawId' => $rawId,
            'type' => 'public-key',
            'response' => [
                'clientDataJSON' => $encode((string)json_encode([
                    'type' => 'webauthn.get',
                    'challenge' => $encode($options->challenge),
                    'origin' => 'https://localhost',
                ])),
                'authenticatorData' => $encode(hash('sha256', 'localhost', true) . "\x01" . "\x00\x00\x00\x01"),
                'signature' => $encode(random_bytes(64)),
                'userHandle' => $encode('00000000-0000-0000-0000-000000000001'),
            ],
        ]);
        $adapter = new AuthenticateAdapter($request);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Could not validate credential response for authentication');
        $adapter->verifyResponse();
    }

    /**
     * A valid assertion logs in and the new signature counter is saved to the user
     *
     * @return void
     */
    public function testVerifyResponseSavesCounter(): void
    {
        $encode = fn(string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        $userId = '00000000-0000-0000-0000-000000000001';
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');

        // An ES256 credential stored as plugin 17 stored it, with counter 190
        $key = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        $details = openssl_pkey_get_details($key);
        $x = str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT);
        $y = str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);
        // COSE_Key as CBOR: {1: 2 (EC2), 3: -7 (ES256), -1: 1 (P-256), -2: x, -3: y}
        $coseKey = "\xa5\x01\x02\x03\x26\x20\x01\x21\x58\x20" . $x . "\x22\x58\x20" . $y;
        $rawId = random_bytes(16);
        $user = $UsersTable->get($userId);
        $user->set('additional_data', ['webauthn_credentials' => [
            $encode($rawId) => [
                'publicKeyCredentialId' => $encode($rawId),
                'type' => 'public-key',
                'transports' => [],
                'attestationType' => 'none',
                'trustPath' => ['type' => 'Webauthn\TrustPath\EmptyTrustPath'],
                'aaguid' => '00000000-0000-0000-0000-000000000000',
                'credentialPublicKey' => $encode($coseKey),
                'userHandle' => $encode($userId),
                'counter' => 190,
                'otherUI' => null,
            ],
        ]]);
        $UsersTable->saveOrFail($user);

        $request = ServerRequestFactory::fromGlobals(['HTTP_HOST' => 'localhost', 'HTTPS' => 'on']);
        $adapter = $this->createAdapterForUser($request);
        $options = $adapter->getOptions();

        $clientDataJSON = (string)json_encode([
            'type' => 'webauthn.get',
            'challenge' => $encode($options->challenge),
            'origin' => 'https://localhost',
        ]);
        $authenticatorData = hash('sha256', 'localhost', true) . "\x01" . pack('N', 191);
        openssl_sign($authenticatorData . hash('sha256', $clientDataJSON, true), $signature, $key, OPENSSL_ALGO_SHA256);
        $request = $request->withParsedBody([
            'id' => $encode($rawId),
            'rawId' => $encode($rawId),
            'type' => 'public-key',
            'response' => [
                'clientDataJSON' => base64_encode($clientDataJSON),
                'authenticatorData' => base64_encode($authenticatorData),
                'signature' => base64_encode($signature),
                'userHandle' => base64_encode($userId),
            ],
        ]);

        $credential = (new AuthenticateAdapter($request))->verifyResponse();

        $this->assertSame(191, $credential->counter);
        $stored = $UsersTable->get($userId)->additional_data['webauthn_credentials'][$encode($rawId)];
        $this->assertSame(191, $stored['counter']);
    }

    /**
     * webauthn.js sends signature and userHandle as standard base64 (btoa), with "+", "/" and "="
     *
     * @return void
     */
    public function testVerifyResponseStandardBase64Fields(): void
    {
        $encode = fn(string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        $request = ServerRequestFactory::fromGlobals();
        $adapter = $this->createAdapterForUser($request);
        $options = $adapter->getOptions();
        $rawId = $encode(random_bytes(16));
        $signature = base64_encode(str_repeat("\xfb\xff\xbf", 23) . "\x01");
        $this->assertMatchesRegularExpression('#[+/=]#', $signature);
        $request = $request->withParsedBody([
            'id' => $rawId,
            'rawId' => $rawId,
            'type' => 'public-key',
            'response' => [
                'clientDataJSON' => base64_encode((string)json_encode([
                    'type' => 'webauthn.get',
                    'challenge' => $encode($options->challenge),
                    'origin' => 'https://localhost',
                ])),
                'authenticatorData' => base64_encode(hash('sha256', 'localhost', true) . "\x01" . "\x00\x00\x00\x01"),
                'signature' => $signature,
                'userHandle' => base64_encode('00000000-0000-0000-0000-000000000001'),
            ],
        ]);
        $adapter = new AuthenticateAdapter($request);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Could not validate credential response for authentication');
        $adapter->verifyResponse();
    }

    /**
     * @param \Cake\Http\ServerRequest $request The request.
     * @return \CakeDC\Users\Webauthn\AuthenticateAdapter
     */
    protected function createAdapterForUser(ServerRequest $request): AuthenticateAdapter
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $request->getSession()->write('Webauthn2fa.User', $UsersTable->get('00000000-0000-0000-0000-000000000001'));

        return new AuthenticateAdapter($request);
    }
}
