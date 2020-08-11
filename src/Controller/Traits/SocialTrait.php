<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Controller\Traits;

use CakeDC\Users\Loader\LoginComponentLoader;

/**
 * Covers registration features and email token validation
 *
 * @property \Cake\Http\ServerRequest $request
 */
trait SocialTrait
{
    /**
     * Render the social email form
     *
     * @throws \Cake\Http\Exception\NotFoundException
     * @return mixed
     */
    public function socialEmail()
    {
        $Login = LoginComponentLoader::forSocial($this);

        return $Login->handleLogin(true, false);
    }
}
