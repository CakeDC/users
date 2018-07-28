<?php

namespace CakeDC\Users\Social\Service;

use Cake\Core\InstanceConfigTrait;

abstract class OAuthServiceAbstract implements ServiceInterface
{
    use InstanceConfigTrait;

    protected $_defaultConfig = [];

    /**
     * @var string
     */
    protected $providerName;

    /**
     * Get the social provider name
     *
     * @return string
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * Set the social provider name
     *
     * @param string $providerName social provider
     * @return void
     */
    public function setProviderName(string $providerName)
    {
        $this->providerName = $providerName;
    }
}
