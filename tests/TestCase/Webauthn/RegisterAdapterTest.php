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
use Webauthn\Exception\AuthenticatorResponseVerificationException;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
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
        $this->assertSame($options, $request->getSession()->read('Webauthn2fa.registerOptions'));
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
                    64
                ), 'Test PublicKeyCredentialUserEntity'),
                base64_decode(
                    '9WqgpRIYvGMCUYiFT20o1U7hSD193k11zu4tKP7wRcrE26zs1zc4LHyPinvPGS86wu6bDvpwbt8Xp2bQ3VBRSQ==',
                    true
                ),
                [new PublicKeyCredentialParameters('public-key', Algorithms::COSE_ALGORITHM_ES256)]
            );

        $data = '{"id":"mMihuIx9LukswxBOMjMHDf6EAONOy7qdWhaQQ7dOtViR2cVB_MNbZxURi2cvgSvKSILb3mISe9lPNG9sYgojuY5iNinYOg6hRVxmm0VssuNG2pm1-RIuTF9DUtEJZEEK","type":"public-key","rawId":"mMihuIx9LukswxBOMjMHDf6EAONOy7qdWhaQQ7dOtViR2cVB/MNbZxURi2cvgSvKSILb3mISe9lPNG9sYgojuY5iNinYOg6hRVxmm0VssuNG2pm1+RIuTF9DUtEJZEEK","response":{"clientDataJSON":"eyJjaGFsbGVuZ2UiOiI5V3FncFJJWXZHTUNVWWlGVDIwbzFVN2hTRDE5M2sxMXp1NHRLUDd3UmNyRTI2enMxemM0TEh5UGludlBHUzg2d3U2YkR2cHdidDhYcDJiUTNWQlJTUSIsImNsaWVudEV4dGVuc2lvbnMiOnt9LCJoYXNoQWxnb3JpdGhtIjoiU0hBLTI1NiIsIm9yaWdpbiI6Imh0dHBzOi8vbG9jYWxob3N0Ojg0NDMiLCJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIn0","attestationObject":"o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVjkSZYN5YgOjGh0NBcPZHZgW4/krrmihjLHmVzzuoMdl2NBAAAAAAAAAAAAAAAAAAAAAAAAAAAAYJjIobiMfS7pLMMQTjIzBw3+hADjTsu6nVoWkEO3TrVYkdnFQfzDW2cVEYtnL4ErykiC295iEnvZTzRvbGIKI7mOYjYp2DoOoUVcZptFbLLjRtqZtfkSLkxfQ1LRCWRBCqUBAgMmIAEhWCAcPxwKyHADVjTgTsat4R/Jax6PWte50A8ZasMm4w6RxCJYILt0FCiGwC6rBrh3ySNy0yiUjZpNGAhW+aM9YYyYnUTJ"}}';
        $userId = '00000000-0000-0000-0000-000000000002';
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get($userId);
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withParsedBody(
            json_decode($data, true)
        );
        $request->getSession()->write('Webauthn2fa.User', $user);
        $request->getSession()->write('Webauthn2fa.registerOptions', $options);
        $adapter = new RegisterAdapter($request, $UsersTable);
        $credentialsList = $adapter->getUser()->additional_data['webauthn_credentials'] ?? [];
        $this->assertCount(0, $credentialsList);
        $actual = $adapter->verifyResponse();
        $this->assertInstanceOf(PublicKeyCredentialSource::class, $actual);
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
                    64
                ), 'Test PublicKeyCredentialUserEntity'),
                base64_decode(
                    '9WqgpRIYvGMCUYiFT20o1U7hSD193k11zu4tKP7wRcrE26zs1zc4LHyPinvPGS86wu6bDvpwbt8Xp2bQ3VBRSQ==',
                    true
                ),
                [new PublicKeyCredentialParameters('public-key', Algorithms::COSE_ALGORITHM_ES256)]
            );

        $data = '{"id":"mMihuIx9LukswxBOMjMHDf6EAONOy7qdWhaQQ7dOtViR2cVB_MNbZxURi2cvgSvKSILb3mISe9lPNG9sYgojuY5iNinYOg6hRVxmm0VssuNG2pm1-RIuTF9DUtEJZEEK","type":"public-key","rawId":"mMihuIx9LukswxBOMjMHDf6EAONOy7qdWhaQQ7dOtViR2cVB/MNbZxURi2cvgSvKSILb3mISe9lPNG9sYgojuY5iNinYOg6hRVxmm0VssuNG2pm1+RIuTF9DUtEJZEEK","response":{"clientDataJSON":"eyJjaGFsbGVuZ2UiOiJNREF3TURBd01EQXRNREF3TUMwd01EQXdMVEF3TURBdE1EQXdNREF3TURBd01EQXoiLCJjbGllbnRFeHRlbnNpb25zIjp7fSwiaGFzaEFsZ29yaXRobSI6IlNIQS0yNTYiLCJvcmlnaW4iOiJodHRwczovL2xvY2FsaG9zdDo4NDQzIiwidHlwZSI6IndlYmF1dGhuLmNyZWF0ZSJ9","attestationObject":"o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YVjkSZYN5YgOjGh0NBcPZHZgW4/krrmihjLHmVzzuoMdl2NBAAAAAAAAAAAAAAAAAAAAAAAAAAAAYJjIobiMfS7pLMMQTjIzBw3+hADjTsu6nVoWkEO3TrVYkdnFQfzDW2cVEYtnL4ErykiC295iEnvZTzRvbGIKI7mOYjYp2DoOoUVcZptFbLLjRtqZtfkSLkxfQ1LRCWRBCqUBAgMmIAEhWCAcPxwKyHADVjTgTsat4R/Jax6PWte50A8ZasMm4w6RxCJYILt0FCiGwC6rBrh3ySNy0yiUjZpNGAhW+aM9YYyYnUTJ"}}';
        //Mock success response
        $userId = '00000000-0000-0000-0000-000000000002';
        $UsersTable = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $user = $UsersTable->get($userId);
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withParsedBody(
            json_decode($data, true)
        );
        $request->getSession()->write('Webauthn2fa.User', $user);
        $request->getSession()->write('Webauthn2fa.registerOptions', $options);
        $adapter = new RegisterAdapter($request, $UsersTable);

        $credentialsList = $adapter->getUser()->additional_data['webauthn_credentials'] ?? [];
        $this->assertCount(0, $credentialsList);
        $this->expectException(AuthenticatorResponseVerificationException::class);
        $this->expectExceptionMessage('Invalid challenge');
        $adapter->verifyResponse();
    }
}
