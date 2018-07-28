<?php
namespace CakeDC\Users\Social\Service;

use Cake\Http\ServerRequest;

interface ServiceInterface
{
    /**
     * Check if we are at getUserStep, meaning, we received a callback from provider.
     *
     * @param \Cake\Http\ServerRequest $request Request object.
     * @return bool
     */
    public function isGetUserStep(ServerRequest $request): bool;

    /**
     * Get a authentication url for user
     *
     * @param \Cake\Http\ServerRequest $request Request object.
     * @return string
     */
    public function getAuthorizationUrl(ServerRequest $request);

    /**
     * Get a user in social provider
     *
     * @param \Cake\Http\ServerRequest $request Request object.
     * @return array
     */
    public function getUser(ServerRequest $request): array;

    /**
     * Get the provider name
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Set the provider name
     *
     * @param string $name set name
     *
     * @return self
     */
    public function setProviderName(string $name);

    /**
     * Get current config
     *
     * @param string|null $key The key to get or null for the whole config.
     * @param mixed $default The return value when the key does not exist.
     * @return mixed Config value being read.
     */
    public function getConfig($key = null, $default = null);
}
