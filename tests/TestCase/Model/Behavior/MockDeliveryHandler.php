<?php

declare(strict_types=1);

namespace CakeDC\Users\Test\TestCase\Model\Behavior;

use Cake\Datasource\EntityInterface;

/**
 * Mock Delivery Handler
 */
class MockDeliveryHandler
{
    /**
     * @var array
     */
    public static $sent = [];

    /**
     * @param array $options Options
     */
    public function __construct(array $options = [])
    {
    }

    /**
     * @param \Cake\Datasource\EntityInterface $user User
     * @param string $token Token
     * @return void
     */
    public function send(EntityInterface $user, string $token): void
    {
        static::$sent[] = ['user' => $user, 'token' => $token];
    }
}
