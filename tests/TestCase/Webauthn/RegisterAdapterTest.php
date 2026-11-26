<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2023, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2023, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Webauthn;

use Cake\Core\Configure;
use Cake\Http\ServerRequestFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Webauthn\RegisterAdapter;
use Cose\Algorithms;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\CredentialRecord;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\Exception\AuthenticatorResponseVerificationException;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class RegisterAdapterTest extends TestCase
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
        $userId = '00000000-0000-0000-0000-000000000002';
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get($userId);
        $request = ServerRequestFactory::fromGlobals();
        $request->getSession()->write('Webauthn2fa.User', $user);
        $adapter = new RegisterAdapter($request, $UsersTable);
        $this->assertFalse($adapter->hasCredential());
        $options = $adapter->getOptions();
        $this->assertInstanceOf(PublicKeyCredentialCreationOptions::class, $options);
        $this->assertSame($adapter->serializeOptions($options), $request->getSession()->read('Webauthn2fa.registerOptions'));
        $userEntity = json_decode((string)$request->getSession()->read('Webauthn2fa.userEntity'), true);
        $this->assertSame($userId, base64_decode(strtr($userEntity['id'], '-_', '+/'), true));
    }

    /**
     * Only standard COSE algorithms are offered: no Ed256 (-260) or Ed512 (-261)
     *
     * @return void
     */
    public function testGetOptionsAlgorithms(): void
    {
        $adapter = $this->createAdapterForUser('00000000-0000-0000-0000-000000000002');
        $json = json_decode($adapter->serializeOptions($adapter->getOptions()), true);

        $this->assertSame(
            [-7, -47, -35, -36, -257, -258, -259, -37, -38, -39],
            array_column($json['pubKeyCredParams'], 'alg'),
        );
    }

    /**
     * Test verifyResponse
     *
     * @return void
     * @throws \Webauthn\Exception\InvalidDataException
     */
    public function testVerifyResponse(): void
    {
        $options = PublicKeyCredentialCreationOptions
            ::create(
                new PublicKeyCredentialRpEntity('My Application'),
                new PublicKeyCredentialUserEntity('test@foo.com', random_bytes(
                    64,
                ), 'Test PublicKeyCredentialUserEntity'),
                base64_decode(
                    '9WqgpRIYvGMCUYiFT20o1U7hSD193k11zu4tKP7wRcrE26zs1zc4LHyPinvPGS86wu6bDvpwbt8Xp2bQ3VBRSQ==',
                    true,
                ),
                [new PublicKeyCredentialParameters('public-key', Algorithms::COSE_ALGORITHM_ES256)],
            );

        $data = '{"id":"mMihuIx9LukswxBOMjMHDf6EAONOy7qdWhaQQ7dOtViR2cVB_MNbZxURi2cvgSvKSILb3mISe9lPNG9sYgojuY5iNinYOg6hRVxmm0VssuNG2pm1-RIuTF9DUtEJZEEK","type":"public-key","rawId":"mMihuIx9LukswxBOMjMHDf6EAONOy7qdWhaQQ7dOtViR2cVB/MNbZxURi2cvgSvKSILb3mISe9lPNG9sYgojuY5iNinYOg6hRVxmm0VssuNG2pm1+RIuTF9DUtEJZEEK","response":{"clientDataJSON":"eyJjaGFsbGVuZ2UiOiI5V3FncFJJWXZHTUNVWWlGVDIwbzFVN2hTRDE5M2sxMXp1NHRLUDd3UmNyRTI2enMxemM0TEh5UGludlBHUzg2d3U2YkR2cHdidDhYcDJiUTNWQlJTUSIsImNsaWVudEV4dGVuc2lvbnMiOnt9LCJoYXNoQWxnb3JpdGhtIjoiU0hBLTI1NiIsIm9yaWdpbiI6Imh0dHBzOi8vbG9jYWxob3N0Ojg0NDMiLCJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIn0","attestationObject":"o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVjkSZYN5YgOjGh0NBcPZHZgW4/krrmihjLHmVzzuoMdl2NBAAAAAAAAAAAAAAAAAAAAAAAAAAAAYJjIobiMfS7pLMMQTjIzBw3+hADjTsu6nVoWkEO3TrVYkdnFQfzDW2cVEYtnL4ErykiC295iEnvZTzRvbGIKI7mOYjYp2DoOoUVcZptFbLLjRtqZtfkSLkxfQ1LRCWRBCqUBAgMmIAEhWCAcPxwKyHADVjTgTsat4R/Jax6PWte50A8ZasMm4w6RxCJYILt0FCiGwC6rBrh3ySNy0yiUjZpNGAhW+aM9YYyYnUTJ"}}';
        $userId = '00000000-0000-0000-0000-000000000002';
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get($userId);
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withParsedBody(
            json_decode($data, true),
        );
        $request->getSession()->write('Webauthn2fa.User', $user);
        $request->getSession()->write('Webauthn2fa.registerOptions', $this->serializeForSession($options));
        $adapter = new RegisterAdapter($request, $UsersTable);
        $credentialsList = $adapter->getUser()->additional_data['webauthn_credentials'] ?? [];
        $this->assertCount(0, $credentialsList);
        $actual = $adapter->verifyResponse();
        $this->assertInstanceOf(CredentialRecord::class, $actual);
        $credentialsList = $adapter->getUser()->additional_data['webauthn_credentials'];
        $this->assertCount(1, $credentialsList);
        $key = key($credentialsList);
        $this->assertIsString($key);
        $this->assertTrue(isset($credentialsList[$key]['publicKeyCredentialId']));
        $this->assertTrue($adapter->hasCredential());
    }

    /**
     * Test verifyResponse
     *
     * @return void
     * @throws \Webauthn\Exception\InvalidDataException
     */
    public function testVerifyResponseOptionsDoesNotMatch(): void
    {
        $options = PublicKeyCredentialCreationOptions
            ::create(
                new PublicKeyCredentialRpEntity('My Application'),
                new PublicKeyCredentialUserEntity('test@foo.com', random_bytes(
                    64,
                ), 'Test PublicKeyCredentialUserEntity'),
                base64_decode(
                    '9WqgpRIYvGMCUYiFT20o1U7hSD193k11zu4tKP7wRcrE26zs1zc4LHyPinvPGS86wu6bDvpwbt8Xp2bQ3VBRSQ==',
                    true,
                ),
                [new PublicKeyCredentialParameters('public-key', Algorithms::COSE_ALGORITHM_ES256)],
            );

        $data = '{"id":"mMihuIx9LukswxBOMjMHDf6EAONOy7qdWhaQQ7dOtViR2cVB_MNbZxURi2cvgSvKSILb3mISe9lPNG9sYgojuY5iNinYOg6hRVxmm0VssuNG2pm1-RIuTF9DUtEJZEEK","type":"public-key","rawId":"mMihuIx9LukswxBOMjMHDf6EAONOy7qdWhaQQ7dOtViR2cVB/MNbZxURi2cvgSvKSILb3mISe9lPNG9sYgojuY5iNinYOg6hRVxmm0VssuNG2pm1+RIuTF9DUtEJZEEK","response":{"clientDataJSON":"eyJjaGFsbGVuZ2UiOiJNREF3TURBd01EQXRNREF3TUMwd01EQXdMVEF3TURBdE1EQXdNREF3TURBd01EQXoiLCJjbGllbnRFeHRlbnNpb25zIjp7fSwiaGFzaEFsZ29yaXRobSI6IlNIQS0yNTYiLCJvcmlnaW4iOiJodHRwczovL2xvY2FsaG9zdDo4NDQzIiwidHlwZSI6IndlYmF1dGhuLmNyZWF0ZSJ9","attestationObject":"o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVjkSZYN5YgOjGh0NBcPZHZgW4/krrmihjLHmVzzuoMdl2NBAAAAAAAAAAAAAAAAAAAAAAAAAAAAYJjIobiMfS7pLMMQTjIzBw3+hADjTsu6nVoWkEO3TrVYkdnFQfzDW2cVEYtnL4ErykiC295iEnvZTzRvbGIKI7mOYjYp2DoOoUVcZptFbLLjRtqZtfkSLkxfQ1LRCWRBCqUBAgMmIAEhWCAcPxwKyHADVjTgTsat4R/Jax6PWte50A8ZasMm4w6RxCJYILt0FCiGwC6rBrh3ySNy0yiUjZpNGAhW+aM9YYyYnUTJ"}}';
        //Mock success response
        $userId = '00000000-0000-0000-0000-000000000002';
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get($userId);
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withParsedBody(
            json_decode($data, true),
        );
        $request->getSession()->write('Webauthn2fa.User', $user);
        $request->getSession()->write('Webauthn2fa.registerOptions', $this->serializeForSession($options));
        $adapter = new RegisterAdapter($request, $UsersTable);

        $credentialsList = $adapter->getUser()->additional_data['webauthn_credentials'] ?? [];
        $this->assertCount(0, $credentialsList);
        $this->expectException(AuthenticatorResponseVerificationException::class);
        $this->expectExceptionMessage('Invalid challenge');
        $adapter->verifyResponse();
    }

    /**
     * The relying party sent to the browser uses Webauthn2fa.appName and Webauthn2fa.id
     *
     * @return void
     */
    public function testGetOptionsRpName(): void
    {
        $adapter = $this->createAdapterForUser('00000000-0000-0000-0000-000000000002');
        $json = json_decode($adapter->serializeOptions($adapter->getOptions()), true);

        $this->assertEquals(['name' => 'ACME Webauthn Server', 'id' => 'localhost'], $json['rp']);
    }

    /**
     * Without Webauthn2fa.appName the adapter fails, as in 17.x, instead of sending an empty RP name
     *
     * @return void
     */
    public function testConstructAppNameNotConfigured(): void
    {
        Configure::write('Webauthn2fa.appName', null);

        $this->expectException(\TypeError::class);
        $this->createAdapterForUser('00000000-0000-0000-0000-000000000002');
    }

    /**
     * Without Webauthn2fa.id the relying party id stays unset, so the current domain is used
     *
     * @return void
     */
    public function testGetOptionsRpIdNotConfigured(): void
    {
        Configure::write('Webauthn2fa.id', null);
        $adapter = $this->createAdapterForUser('00000000-0000-0000-0000-000000000002');
        $options = $adapter->getOptions();
        $json = json_decode($adapter->serializeOptions($options), true);

        $this->assertNull($options->rp->id);
        $this->assertArrayNotHasKey('id', $json['rp']);
    }

    /**
     * The options JSON keeps the shape that webroot/js/webauthn.js reads
     *
     * @return void
     */
    public function testSerializeOptionsShape(): void
    {
        $userId = '00000000-0000-0000-0000-000000000002';
        $adapter = $this->createAdapterForUser($userId);
        $json = json_decode($adapter->serializeOptions($adapter->getOptions()), true);

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $json['challenge']);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $json['user']['id']);
        $this->assertSame($userId, base64_decode(strtr($json['user']['id'], '-_', '+/'), true));
        $this->assertSame('none', $json['attestation']);
    }

    /**
     * A Host header with a port verifies the same as without it
     *
     * @return void
     */
    public function testVerifyResponseHostWithPort(): void
    {
        $options = PublicKeyCredentialCreationOptions
            ::create(
                new PublicKeyCredentialRpEntity('My Application'),
                new PublicKeyCredentialUserEntity('test@foo.com', random_bytes(
                    64,
                ), 'Test PublicKeyCredentialUserEntity'),
                base64_decode(
                    '9WqgpRIYvGMCUYiFT20o1U7hSD193k11zu4tKP7wRcrE26zs1zc4LHyPinvPGS86wu6bDvpwbt8Xp2bQ3VBRSQ==',
                    true,
                ),
                [new PublicKeyCredentialParameters('public-key', Algorithms::COSE_ALGORITHM_ES256)],
            );

        $data = '{"id":"mMihuIx9LukswxBOMjMHDf6EAONOy7qdWhaQQ7dOtViR2cVB_MNbZxURi2cvgSvKSILb3mISe9lPNG9sYgojuY5iNinYOg6hRVxmm0VssuNG2pm1-RIuTF9DUtEJZEEK","type":"public-key","rawId":"mMihuIx9LukswxBOMjMHDf6EAONOy7qdWhaQQ7dOtViR2cVB/MNbZxURi2cvgSvKSILb3mISe9lPNG9sYgojuY5iNinYOg6hRVxmm0VssuNG2pm1+RIuTF9DUtEJZEEK","response":{"clientDataJSON":"eyJjaGFsbGVuZ2UiOiI5V3FncFJJWXZHTUNVWWlGVDIwbzFVN2hTRDE5M2sxMXp1NHRLUDd3UmNyRTI2enMxemM0TEh5UGludlBHUzg2d3U2YkR2cHdidDhYcDJiUTNWQlJTUSIsImNsaWVudEV4dGVuc2lvbnMiOnt9LCJoYXNoQWxnb3JpdGhtIjoiU0hBLTI1NiIsIm9yaWdpbiI6Imh0dHBzOi8vbG9jYWxob3N0Ojg0NDMiLCJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIn0","attestationObject":"o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVjkSZYN5YgOjGh0NBcPZHZgW4/krrmihjLHmVzzuoMdl2NBAAAAAAAAAAAAAAAAAAAAAAAAAAAAYJjIobiMfS7pLMMQTjIzBw3+hADjTsu6nVoWkEO3TrVYkdnFQfzDW2cVEYtnL4ErykiC295iEnvZTzRvbGIKI7mOYjYp2DoOoUVcZptFbLLjRtqZtfkSLkxfQ1LRCWRBCqUBAgMmIAEhWCAcPxwKyHADVjTgTsat4R/Jax6PWte50A8ZasMm4w6RxCJYILt0FCiGwC6rBrh3ySNy0yiUjZpNGAhW+aM9YYyYnUTJ"}}';
        $userId = '00000000-0000-0000-0000-000000000002';
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get($userId);
        $request = ServerRequestFactory::fromGlobals(['HTTP_HOST' => 'localhost:8443', 'HTTPS' => 'on']);
        $request = $request->withParsedBody(
            json_decode($data, true),
        );
        $request->getSession()->write('Webauthn2fa.User', $user);
        $request->getSession()->write('Webauthn2fa.registerOptions', $this->serializeForSession($options));
        $adapter = new RegisterAdapter($request, $UsersTable);

        $this->assertInstanceOf(CredentialRecord::class, $adapter->verifyResponse());
    }

    /**
     * Options as RegisterAdapter::getOptions() stores them in the session
     *
     * @param \Webauthn\PublicKeyCredentialCreationOptions $options Options.
     * @return string
     */
    protected function serializeForSession(PublicKeyCredentialCreationOptions $options): string
    {
        $factory = new WebauthnSerializerFactory(
            new AttestationStatementSupportManager([new NoneAttestationStatementSupport()]),
        );

        return $factory->create()->serialize($options, 'json');
    }

    /**
     * @param string $userId User id.
     * @return \CakeDC\Users\Webauthn\RegisterAdapter
     */
    protected function createAdapterForUser(string $userId): RegisterAdapter
    {
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $request = ServerRequestFactory::fromGlobals();
        $request->getSession()->write('Webauthn2fa.User', $UsersTable->get($userId));

        return new RegisterAdapter($request, $UsersTable);
    }
}
