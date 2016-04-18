<?php
namespace CakeDC\Users\Auth\Exception;

use Cake\Core\Exception\Exception;

class InvalidSettingsException extends Exception
{
    protected $_messageTemplate = 'Invalid settings for key (%s)';
    protected $code = 500;
}
