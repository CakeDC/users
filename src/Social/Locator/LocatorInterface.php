<?php

namespace CakeDC\Users\Social\Locator;

use CakeDC\Users\Model\Entity\User;

interface LocatorInterface
{
    /**
     * Get or create the user based on the $rawData
     *
     * @param array $rawData mapped social user data
     * @return User
     */
    public function getOrCreate(array $rawData): User;
}
