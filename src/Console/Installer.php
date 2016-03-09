<?php
/**
 * Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2015, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace CakeDC\Users\Console;

use CakeDC\Users\Console\Traits\CommonQuestionsTrait;
use Composer\Script\Event;

/**
 * Installer methods for the plugin
 */
class Installer
{
    use CommonQuestionsTrait;

    /**
     * Configure users plugin
     *
     * @param string $rootDir root folder
     * @param \Composer\IO\IOInterface $io io interface
     * @return void
     */
    public static function configureUsers($rootDir, $io)
    {
        $usersConfig = [
            'Users.Registration.active' => false,
            'Users.Social.login' => false,
            'OAuth.providers.facebook.options.clientId' => false,
            'OAuth.providers.facebook.options.clientSecret' => false,
            'OAuth.providers.google.options.clientId' => false,
            'OAuth.providers.google.options.clientSecret' => false,
            'OAuth.providers.twitter.options.clientId' => false,
            'OAuth.providers.twitter.options.clientSecret' => false,
            'OAuth.providers.linkedIn.options.clientId' => false,
            'OAuth.providers.linkedIn.options.clientSecret' => false,
            'OAuth.providers.instagram.options.clientId' => false,
            'OAuth.providers.instagram.options.clientSecret' => false,
        ];
        static::yesNo(
            $io,
            '[Users]: Allow user registration [y/N]? ',
            'N',
            ['\CakeDC\Users\Console\Installer::enableConfigOptions', [&$usersConfig, ['Users.Registration.active']]]
        );
        $providers = ['facebook', 'google', 'twitter', 'linkedIn', 'instagram'];
        foreach ($providers as $provider) {
            static::yesNo(
                $io,
                sprintf('[Users]: Enable social login using provider: %s [y/N]? ', $provider),
                'N',
                ['\CakeDC\Users\Console\Installer::askSocialProviderData', [$io, &$usersConfig, $provider]]
            );
        }

        static::generateUsersConfigOverride($rootDir, $io, $usersConfig);
    }

    /**
     * Enable flags in configuration
     *
     * @param array $config config array
     * @param array $flags flags array
     * @return void
     */
    public static function enableConfigOptions(&$config, $flags = [])
    {
        foreach ($flags as $flag) {
            if (isset($config[$flag])) {
                $config[$flag] = true;
            }
        }
    }

    /**
     * Ask for clientId & secret
     *
     * @param \Composer\IO\IOInterface $io io interface
     * @param array $config config array
     * @param string $provider provider, from available social providers list
     * @return void
     */
    public static function askSocialProviderData($io, &$config, $provider)
    {
        $questions = ['clientId', 'clientSecret'];
        foreach ($questions as $question) {
            $value = $io->askAndValidate(
                sprintf('[Users]: Please enter %s %s? ', $provider, $question),
                null,
                10
            );
            if (isset($config["OAuth.providers.$provider.options.$question"])) {
                $config["OAuth.providers.$provider.options.$question"] = $value;
            }
        }
        static::enableConfigOptions($config, ['Users.Social.login']);
    }

    /**
     * Apply the configuration keys to the users config file and save the file
     *
     * @param string $dir root folder
     * @param \Composer\IO\IOInterface $io io interface
     * @param array $usersConfig config array
     * @return void
     */
    public static function generateUsersConfigOverride($dir, $io, $usersConfig)
    {
        $config = $dir . '/config/users.php';
        $content = file_get_contents($config);
        $override = [];
        foreach ($usersConfig as $configKey => $value) {
            if (!empty($value)) {
                $override[$configKey] = $value;
            }
        }

        //get rid of default array() output from var_export
        $formatted = '[' . substr(var_export($override, true), 7, -1) . ']';

        $content = str_replace('//__$CONFIG_OVERRIDE__', '$config = array_merge($config, ' . $formatted . ');', $content, $count);

        if ($count == 0) {
            $io->write('No changes made to Users configuration, project was already configured.');
            return;
        }

        $result = file_put_contents($config, $content);
        if ($result) {
            $io->write('Updated config/users.php.');
            return;
        }
        $io->write('Unable to update config/users.php.');
    }
}
