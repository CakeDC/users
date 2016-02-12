<?php
namespace CakeDC\Users\Auth\Exception;

use Exception;

class MissingProviderConfigurationException extends Exception
{
    protected $_messageTemplate = 'No OAuth providers configured.';
    protected $code = 500;
}
