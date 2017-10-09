<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Console;

use Cake\Database\Schema\Table;
use Cake\Filesystem\File;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use CakeDC\Users\Console\Traits\CommonQuestionsTrait;
use CakeDC\Users\Traits\RandomStringTrait;
use Composer\Script\Event;

/**
 * Installer methods for the plugin
 */
class Installer
{
    use CommonQuestionsTrait;
    use RandomStringTrait;

    /**
     * Configure users plugin
     *
     * @param string $rootDir root folder
     * @param \Composer\IO\IOInterface $io io interface
     * @return void
     */
    public static function configureUsers($rootDir, $io)
    {
        $usersFilePath = $rootDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'users.php';
        if ((new File($usersFilePath))->exists()) {
            return;
        }

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
        static::yesNo(
            $io,
            '[Users]: Add superuser [y/N]? ',
            'N',
            ['\CakeDC\Users\Console\Installer::addSuperuser', [$io]]
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
     * Generates a random password.
     *
     * @return string
     */
    protected static function _generateRandomPassword()
    {
        return self::randomString(20);
    }

    public static function addSuperuser($io)
    {
        $Users = TableRegistry::get('Users');
        $username = $Users->generateUniqueUsername('superadmin');
        $password = self::_generateRandomPassword();
        $user = [
            'username' => $username,
            'email' => $username . '@example.com',
            'password' => $password,
            'active' => 1,
        ];

        $userEntity = $Users->newEntity($user);
        $userEntity->is_superuser = true;
        $userEntity->role = 'superuser';
        $savedUser = $Users->save($userEntity);
        if (!empty($savedUser)) {
            $io->write(__d('CakeDC/Users', 'Superuser added:'));
            $io->write(__d('CakeDC/Users', 'Id: {0}', $savedUser->id));
            $io->write(__d('CakeDC/Users', 'Username: {0}', $username));
            $io->write(__d('CakeDC/Users', 'Email: {0}', $savedUser->email));
            $io->write(__d('CakeDC/Users', 'Password: {0}', $password));
        } else {
            $io->write(__d('CakeDC/Users', 'Superuser could not be added:'));

            collection($userEntity->errors())->each(function ($error, $field) use ($io) {
                $io->write(__d('CakeDC/Users', 'Field: {0} Error: {1}', $field, implode(',', $error)));
            });
        }
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
