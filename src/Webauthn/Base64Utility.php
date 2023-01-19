<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2023, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Webauthn;

use InvalidArgumentException;
use ParagonIE\ConstantTime\Base64UrlSafe;

class Base64Utility
{
    /**
     * @param string $data
     * @return string
     */
    public static function complyEncodedNoPadding(string $data): string
    {
        return Base64UrlSafe::encodeUnpadded(static::basicDecode($data));
    }

    /**
     * @param string $data The data to encode
     * @param bool   $usePadding If true, the "=" padding at end of the encoded value are kept, else it is removed
     * @return string The data encoded
     */
    public static function basicEncode(string $data, bool $usePadding = false): string
    {
        $encoded = strtr(base64_encode($data), '+/', '-_');

        return $usePadding === true ? $encoded : rtrim($encoded, '=');
    }

    /**
     * @param string $data The data to decode
     * @throws \InvalidArgumentException
     * @return string The data decoded
     */
    public static function basicDecode(string $data): string
    {
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        if ($decoded === false) {
            throw new InvalidArgumentException('Invalid data provided');
        }

        return $decoded;
    }
}
