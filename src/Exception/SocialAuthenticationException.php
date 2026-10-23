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
namespace CakeDC\Users\Exception;

use Cake\Core\Exception\CakeException;

class SocialAuthenticationException extends CakeException
{
    protected string $messageTemplate = 'Could not autheticate user';
    protected int $defaultCode = 400;
}
