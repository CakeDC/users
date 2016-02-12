<?php
namespace CakeDC\Users\Auth\Exception;

use Cake\Core\Exception\Exception;

class MissingEventListenerException extends Exception
{
    protected $_messageTemplate = 'Missing listener to the `%s` event.';
}
