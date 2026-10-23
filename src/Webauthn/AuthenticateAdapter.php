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

namespace CakeDC\Users\Webauthn;

use Cake\Http\Exception\BadRequestException;
use Webauthn\AuthenticationExtensions\AuthenticationExtensions;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialRequestOptions;

class AuthenticateAdapter extends BaseAdapter
{
    /**
     * @return \Webauthn\PublicKeyCredentialRequestOptions
     */
    public function getOptions(): PublicKeyCredentialRequestOptions
    {
        $userEntity = $this->getUserEntity();
        $allowedCredentials = array_map(function (CredentialRecord $credential) {
            return $credential->getPublicKeyCredentialDescriptor();
        }, $this->repository->findAllForUserEntity($userEntity));

        $options = PublicKeyCredentialRequestOptions::create(
            random_bytes(32),
            rpId: $this->rpEntity->id,
            allowCredentials: $allowedCredentials,
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            extensions: AuthenticationExtensions::create(),
        );

        $this->request->getSession()->write(
            'Webauthn2fa.authenticateOptions',
            json_encode($options),
        );

        return $options;
    }

    /**
     * Verify the registration response
     *
     * @return \Webauthn\CredentialRecord
     * @throws \Throwable
     */
    public function verifyResponse(): CredentialRecord
    {
        /** @var \Webauthn\PublicKeyCredentialRequestOptions $options */
        $options = $this->createSerializer()->deserialize(
            (string)$this->request->getSession()->read('Webauthn2fa.authenticateOptions'),
            PublicKeyCredentialRequestOptions::class,
            'json',
        );

        $publicKeyCredentialLoader = $this->createPublicKeyCredentialLoader();

        $publicKeyCredential = $publicKeyCredentialLoader->loadArray($this->request->getData());
        $authenticatorAssertionResponse = $publicKeyCredential->response;
        if ($authenticatorAssertionResponse instanceof AuthenticatorAssertionResponse) {
            $storedCredential = $this->repository->findOneByCredentialId($publicKeyCredential->rawId);
            if (!$storedCredential) {
                throw new BadRequestException(
                    __d(
                        'cake_d_c/users',
                        'Could not validate credential response for authentication',
                    ),
                );
            }
            $authenticatorAssertionResponseValidator = AuthenticatorAssertionResponseValidator::create(
                $this->createCeremonyStepManagerFactory()->requestCeremony(),
            );

            $credential = $authenticatorAssertionResponseValidator->check(
                $storedCredential,
                $authenticatorAssertionResponse,
                $options,
                (string)$this->request->host(),
                $this->getUserEntity()->id,
            );

            $this->repository->saveCredentialSource($credential);

            return $credential;
        }

        throw new BadRequestException(
            __d(
                'cake_d_c/users',
                'Could not validate credential response for authentication',
            ),
        );
    }
}
