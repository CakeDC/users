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

namespace CakeDC\Users\Model\Entity;

use Cake\ORM\Entity;

/**
 * Account Entity.
 *
 * @property string $id
 * @property string $user_id
 * @property string $provider
 * @property string|null $username
 * @property string $reference
 * @property string|null $avatar
 * @property string|null $description
 * @property string|null $link
 * @property string $token
 * @property string|null $token_secret
 * @property \Cake\I18n\DateTime|null $token_expires
 * @property bool $active
 * @property string $data
 * @property \CakeDC\Users\Model\Entity\User $user
 */
class SocialAccount extends Entity
{
    /**
     * @inheritDoc
     */
    protected array $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     * @inheritDoc
     */
    protected array $_hidden = [
        'token',
        'token_secret',
        'token_expires',
    ];
}
