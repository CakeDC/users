<?php
declare(strict_types=1);

namespace CakeDC\Users\Model\Entity;

use Cake\ORM\Entity;

/**
 * OtpCode Entity
 *
 * @property int $id
 * @property string $user_id
 * @property string $code
 * @property int $tries
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $validated
 */
class OtpCode extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'user_id' => true,
        'code' => true,
        'tries' => true,
        'validated' => true,
        'created' => true,
    ];
}
