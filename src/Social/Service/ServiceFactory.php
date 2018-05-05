<?php

namespace CakeDC\Users\Social\Service;

use Cake\Http\ServerRequest;
use Cake\Network\Exception\NotFoundException;
use CakeDC\Users\Social\ProviderConfig;

class ServiceFactory
{

    protected $redirectUriField = 'redirectUri';

    /**
     * @param string $redirectUriField
     *
     * @return self
     */
    public function setRedirectUriField(string $redirectUriField)
    {
        $this->redirectUriField = $redirectUriField;

        return $this;
    }

    /**
     * Create a new service based on provider alias
     *
     * @param string $provider provider alias
     *
     * @return ServiceInterface
     */
    public function createFromProvider($provider): ServiceInterface
    {
        $config = (new ProviderConfig())->getConfig($provider);

        if (!$provider || !$config) {
            throw new NotFoundException('Provider not found');
        }

        $config['options']['redirectUri'] = $config['options'][$this->redirectUriField];
        unset($config['options']['linkSocialUri'], $config['options']['callbackLinkSocialUri']);
        $service = new $config['service']($config);
        $service->setProviderName($provider);

        return $service;
    }

    /**
     * Create a new service based on request
     *
     * @param ServerRequest $request in use
     *
     * @return ServiceInterface
     */
    public function createFromRequest(ServerRequest $request): ServiceInterface
    {
        return $this->createFromProvider($request->getAttribute('params')['provider'] ?? null);
    }
}