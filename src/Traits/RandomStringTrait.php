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
        return substr(bin2hex(random_bytes((int)ceil($length / 2))), 0, $length);
    }
}
