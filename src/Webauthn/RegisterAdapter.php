<?php

namespace CakeDC\Users\Webauthn;

use Webauthn\PublicKeyCredentialCreationOptions;


class RegisterAdapter extends BaseAdapter
{
    /**
     * @return PublicKeyCredentialCreationOptions
     */
    public function getOptions(): PublicKeyCredentialCreationOptions
    {
        $userEntity = $this->getUserEntity();
        $options = $this->server->generatePublicKeyCredentialCreationOptions(
            $userEntity,
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            []
        );
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
     * @param $options
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
}
