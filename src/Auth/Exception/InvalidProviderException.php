<?php
namespace CakeDC\Users\Auth\Exception;

use Cake\Core\Exception\Exception;

class InvalidProviderException extends Exception
{
    protected $_messageTemplate = 'Invalid provider or missing class (%s)';
    protected $code = 500;
}
