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
     * @return string
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * @param string $providerName
     */
    public function setProviderName(string $providerName)
    {
        $this->providerName = $providerName;
    }

}