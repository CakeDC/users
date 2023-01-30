<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Webauthn;

use Cake\Http\Exception\BadRequestException;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;

class AuthenticateAdapter extends BaseAdapter
{
    /**
     * @return \Webauthn\PublicKeyCredentialRequestOptions
     */
    public function getOptions(): PublicKeyCredentialRequestOptions
    {
        $userEntity = $this->getUserEntity();
        $allowedCredentials = array_map(function (PublicKeyCredentialSource $credential) {
            return $credential->getPublicKeyCredentialDescriptor();
        }, $this->repository->findAllForUserEntity($userEntity));

        $options = (new PublicKeyCredentialRequestOptions(random_bytes(32)))
            ->setRpId($this->rpEntity->getId())
            ->setUserVerification(PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED)
            ->allowCredentials(...$allowedCredentials)
            ->setExtensions(new AuthenticationExtensionsClientInputs());

        $this->request->getSession()->write(
            'Webauthn2fa.authenticateOptions',
            $options
        );

        return $options;
    }

    /**
     * Verify the registration response
     *
     * @return \Webauthn\PublicKeyCredentialSource
     * @throws \Throwable
     */
    public function verifyResponse(): \Webauthn\PublicKeyCredentialSource
    {
        $options = $this->request->getSession()->read('Webauthn2fa.authenticateOptions');

        $publicKeyCredentialLoader = $this->createPublicKeyCredentialLoader();

        $publicKeyCredential = $publicKeyCredentialLoader->loadArray($this->request->getData());
        $authenticatorAssertionResponse = $publicKeyCredential->getResponse();
        if ($authenticatorAssertionResponse instanceof AuthenticatorAssertionResponse) {
            $authenticatorAssertionResponseValidator = $this->createAssertionResponseValidator();

            return $authenticatorAssertionResponseValidator->check(
                $publicKeyCredential->getRawId(),
                $authenticatorAssertionResponse,
                $options,
                $this->request,
                $this->getUserEntity()->getId(),
            );
        }

        throw new BadRequestException(__('Could not validate credential response for authentication'));
    }

    /**
     * @return \Webauthn\AuthenticatorAssertionResponseValidator
     */
    protected function createAssertionResponseValidator(): AuthenticatorAssertionResponseValidator
    {
        return new AuthenticatorAssertionResponseValidator(
            $this->repository,
            null,
            $this->createExtensionOutputCheckerHandler(),
            $this->getAlgorithmManager()
        );
    }
}
