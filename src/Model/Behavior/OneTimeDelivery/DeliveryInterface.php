<?php
declare(strict_types=1);

namespace CakeDC\Users\Model\Behavior\OneTimeDelivery;

use Cake\Datasource\EntityInterface;

/**
 * Delivery interface.
 */
interface DeliveryInterface
{
    /**
     * Send a delivery.
     *
     * @param \Cake\Datasource\EntityInterface $user User.
     * @param string $token Token.
     * @return void
     */
    public function send(EntityInterface $user, string $token): void;
}
