<?php
namespace CakeDC\Users\Social;


use Cake\Core\Configure;
use Cake\Utility\Hash;
use CakeDC\Users\Auth\Exception\InvalidProviderException;
use CakeDC\Users\Auth\Exception\InvalidSettingsException;

class ProviderConfig
{
    /**
     * @var array
     */
    protected $providers;

    /**
     * ProviderConfig constructor.
     *
     * @param array $config additional data
     */
    public function __construct($config = [])
    {
        $oauthConfig = Configure::read('OAuth');

        $providers = [];
        foreach ($oauthConfig['providers'] as $provider => $options) {
            if ($this->_isProviderEnabled($options)) {
                $providers[$provider] = $options;
            }
        }
        $oauthConfig['providers'] = $providers;

        $this->providers = $this->normalizeConfig(Hash::merge($config, $oauthConfig))['providers'];

    }

    /**
     * Normalizes providers' configuration.
     *
     * @param array $config Array of config to normalize.
     * @return array
     * @throws \Exception
     */
    public function normalizeConfig(array $config)
    {
        if (!empty($config['providers'])) {
            array_walk($config['providers'], [$this, '_normalizeConfig'], $config);
        }

        return $config;
    }

    /**
     * Callback to loop through config values.
     *
     * @param array $config Configuration.
     * @param string $alias Provider's alias (key) in configuration.
     * @param array $parent Parent configuration.
     * @return void
     */
    protected function _normalizeConfig(&$config, $alias, $parent)
    {
        unset($parent['providers']);

        $defaults = [
                'className' => null,
                'service' => null,
                'mapper' => null,
                'options' => [],
                'collaborators' => [],
                'signature' => null,
                'mapFields' => [],
            ] + $parent;

        $config = array_intersect_key($config, $defaults);
        $config += $defaults;

        array_walk($config, [$this, '_validateConfig']);

        foreach (['options', 'collaborators', 'signature'] as $key) {
            if (empty($parent[$key]) || empty($config[$key])) {
                continue;
            }

            $config[$key] = array_merge($parent[$key], $config[$key]);
        }
    }

    /**
     * Validates the configuration.
     *
     * @param mixed $value Value.
     * @param string $key Key.
     * @return void
     * @throws \CakeDC\Users\Auth\Exception\InvalidProviderException
     * @throws \CakeDC\Users\Auth\Exception\InvalidSettingsException
     */
    protected function _validateConfig(&$value, $key)
    {
        if (in_array($key, ['className', 'service', 'mapper'], true) && !is_object($value) && !class_exists($value)) {
            throw new InvalidProviderException([$value]);
        } elseif (!is_array($value) && in_array($key, ['options', 'collaborators'])) {
            throw new InvalidSettingsException([$key]);
        }
    }

    /**
     * Returns when a provider has been enabled.
     *
     * @param array $options array of options by provider
     * @return bool
     */
    public function _isProviderEnabled($options)
    {
        return !empty($options['options']['redirectUri']) && !empty($options['options']['clientId']) &&
            !empty($options['options']['clientSecret']);
    }

    /**
     * Get provider config
     *
     * @param string $alias for provider
     * @return array
     */
    public function getConfig($alias): array
    {
        return Hash::get($this->providers, $alias, []);
    }

}