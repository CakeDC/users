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
        $allowed = array_map(function (PublicKeyCredentialSource $credential) {
            return $credential->getPublicKeyCredentialDescriptor();
        }, $this->repository->findAllForUserEntity($userEntity));

        $options = $this->server->generatePublicKeyCredentialRequestOptions(
            PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED, // Default value
            $allowed
        );
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
     */
    public function verifyResponse(): \Webauthn\PublicKeyCredentialSource
    {
        $options = $this->request->getSession()->read('Webauthn2fa.authenticateOptions');

        return $this->loadAndCheckAssertionResponse($options);
    }

    /**
     * @param \Webauthn\PublicKeyCredentialRequestOptions $options request options
     * @return \Webauthn\PublicKeyCredentialSource
     */
    protected function loadAndCheckAssertionResponse($options): PublicKeyCredentialSource
    {
        return $this->server->loadAndCheckAssertionResponse(
            json_encode($this->request->getData()),
            $options,
            $this->getUserEntity(),
            $this->request
        );
    }
}
