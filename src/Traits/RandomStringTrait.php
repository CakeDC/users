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

namespace CakeDC\Users\Traits;

trait RandomStringTrait
{
    /**
     * Generates random string
     *
     * @param string|int $length String size.
     * @return string
     */
    public function randomString($length = 10)
    {
        if (!is_numeric($length) || $length <= 0) {
            $length = 10;
        }
        $length = (int)$length;
        $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphabetLength = strlen($alphabet);
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $alphabet[random_int(0, $alphabetLength - 1)];
        }

        return $result;
    }
}
