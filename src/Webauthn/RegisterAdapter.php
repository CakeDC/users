<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2021, Cake Development Corporation (https://www.cakedc.com)
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
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;

class RegisterAdapter extends BaseAdapter
{
    /**
     * @return \Webauthn\PublicKeyCredentialCreationOptions
     */
    public function getOptions(): PublicKeyCredentialCreationOptions
    {
        $userEntity = $this->getUserEntity();
        $challenge = random_bytes(16);

        $options = PublicKeyCredentialCreationOptions::create(
            $this->rpEntity,
            $userEntity,
            $challenge,
            $this->getPubKeyCredParams(),
        );
        $options = $options
            ->setAuthenticatorSelection(new AuthenticatorSelectionCriteria())
            ->setAttestation(PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE)
            ->setExtensions(new AuthenticationExtensionsClientInputs());

        $this->request->getSession()->write('Webauthn2fa.registerOptions', $options);
        $this->request->getSession()->write('Webauthn2fa.userEntity', $userEntity);

        return $options;
    }

    /**
     * Verify the registration response
     *
     * @return \Webauthn\PublicKeyCredentialSource
     */
    public function verifyResponse(): \Webauthn\PublicKeyCredentialSource
    {
        $options = $this->request->getSession()->read('Webauthn2fa.registerOptions');
        $attestationStatementSupportManager = $this->getAttestationStatementSupportManager();
        $publicKeyCredentialLoader = $this->createPublicKeyCredentialLoader();

        $publicKeyCredential = $publicKeyCredentialLoader->loadArray((array)$this->request->getData());

        $authenticatorAttestationResponse = $publicKeyCredential->getResponse();
        if ($authenticatorAttestationResponse instanceof AuthenticatorAttestationResponse) {
            $extensionOutputCheckerHandler = $this->createExtensionOutputCheckerHandler();
            $authenticatorAttestationResponseValidator = new AuthenticatorAttestationResponseValidator(
                $attestationStatementSupportManager,
                $this->repository,
                null,//Token binding is deprecated
                $extensionOutputCheckerHandler
            );
            $credential = $authenticatorAttestationResponseValidator->check(
                $authenticatorAttestationResponse,
                $options,
                $this->request
            );

            $this->repository->saveCredentialSource($credential);

            return $credential;
        }
        throw new BadRequestException(__('Could not credential response for registration'));
    }

    /**
     * @return \Webauthn\PublicKeyCredentialParameters[]
     */
    protected function getPubKeyCredParams(): array
    {
        $list = [];
        foreach ($this->getAlgorithmManager()->all() as $algorithm) {
            $list[] = PublicKeyCredentialParameters::create(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                $algorithm::identifier()
            );
        }

        return $list;
    }
}
