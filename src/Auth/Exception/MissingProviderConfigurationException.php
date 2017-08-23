<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Auth\Exception;

use Exception;

class MissingProviderConfigurationException extends Exception
{
    protected $_messageTemplate = 'No OAuth providers configured.';
    protected $code = 500;

    /**
     * MissingProviderConfigurationException constructor.
     * @param string $message message
     * @param int $code code
     * @param null $previous previous
     */
    public function __construct($message = null, $code = 500, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
