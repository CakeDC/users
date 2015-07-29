<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Users\Controller;

use Users\Controller\AppController;
use Users\Controller\Traits\LoginTrait;
use Users\Controller\Traits\ProfileTrait;
use Users\Controller\Traits\RegisterTrait;
use Users\Controller\Traits\SimpleCrudTrait;
use Users\Controller\Traits\SocialTrait;
use Users\Model\Table\UsersTable;

/**
 * Users Controller
 *
 * @property UsersTable $Users
 */
class UsersController extends AppController
{
    use LoginTrait;
    use ProfileTrait;
    use RegisterTrait;
    use SimpleCrudTrait;
    use SocialTrait;
}
