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

namespace Users\Model\Entity;

use Cake\ORM\Entity;

/**
 * Account Entity.
 */
class SocialAccount extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'user_id' => true,
        'provider' => true,
        'username' => true,
        'reference' => true,
        'avatar' => true,
        'description' => true,
        'link' => true,
        'token' => true,
        'token_secret' => true,
        'token_expires' => true,
        'active' => true,
        'data' => true,
        'user' => true,
    ];
}
