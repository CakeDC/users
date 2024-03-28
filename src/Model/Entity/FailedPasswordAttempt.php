<?php
declare(strict_types=1);

namespace CakeDC\Users\Model\Entity;

use Cake\ORM\Entity;

/**
 * FailedPasswordAttempt Entity
 *
 * @property string $id
 * @property string $user_id
 * @property \Cake\I18n\DateTime $created
 *
 * @property \CakeDC\Users\Model\Entity\User $user
 */
class FailedPasswordAttempt extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'created' => true,
        'user' => true,
    ];
}
