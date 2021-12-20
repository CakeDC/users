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

use Webauthn\PublicKeyCredentialCreationOptions;

class RegisterAdapter extends BaseAdapter
{
    /**
     * @return \Webauthn\PublicKeyCredentialCreationOptions
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
}
