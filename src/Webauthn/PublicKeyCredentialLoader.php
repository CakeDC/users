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

namespace CakeDC\Users\Webauthn;

use Webauthn\PublicKeyCredential;

class PublicKeyCredentialLoader extends \Webauthn\PublicKeyCredentialLoader
{
    /**
     * @inheritDoc
     */
    public function loadArray(array $json): PublicKeyCredential
    {
        if (isset($json['response']['clientDataJSON'])
            && is_string($json['response']['clientDataJSON'])
        ) {
            $json['response']['clientDataJSON'] = Base64Utility::complyEncodedNoPadding($json['response']['clientDataJSON']);
        }

        return parent::loadArray($json);
    }
}
