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

use Cose\Algorithms;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
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
        $credential = $this->loadAndCheckAttestationResponse($options);
        $this->repository->saveCredentialSource($credential);

        return $credential;
    }

    /**
     * @param \Webauthn\PublicKeyCredentialCreationOptions $options creation options
     * @return \Webauthn\PublicKeyCredentialSource
     */
    protected function loadAndCheckAttestationResponse($options): \Webauthn\PublicKeyCredentialSource
    {
        $credential = $this->server->loadAndCheckAttestationResponse(
            json_encode($this->request->getData()),
            $options,
            $this->request
        );

        return $credential;
    }

    /**
     * @return array|\Webauthn\PublicKeyCredentialParameters[]
     */
    protected function getPubKeyCredParams(): array
    {
        $algos = [
            Algorithms::COSE_ALGORITHM_ES256,
            Algorithms::COSE_ALGORITHM_ES256K,
            Algorithms::COSE_ALGORITHM_ES384,
            Algorithms::COSE_ALGORITHM_ES512,
            Algorithms::COSE_ALGORITHM_RS256,
            Algorithms::COSE_ALGORITHM_RS384,
            Algorithms::COSE_ALGORITHM_RS512,
            Algorithms::COSE_ALGORITHM_PS256,
            Algorithms::COSE_ALGORITHM_PS384,
            Algorithms::COSE_ALGORITHM_PS512,
            Algorithms::COSE_ALGORITHM_ED256,
            Algorithms::COSE_ALGORITHM_ED512,
        ];

        return array_map(function ($algo) {
            return PublicKeyCredentialParameters::create(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                $algo,
            );
        }, $algos);
    }
}
