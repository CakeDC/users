<?php
/**
 * Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Social;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Auth\Exception\InvalidProviderException;
use CakeDC\Users\Auth\Exception\InvalidSettingsException;
use CakeDC\Users\Social\ProviderConfig;


/**
 * Users\Social\ProviderConfig Test Case
 */
class ProviderConfigTest extends TestCase
{
    /**
     * Test with invalid provider class
     *
     * @return void
     */
    public function testWithInvalidProviderClass()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', '10003030300303');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'secretpassword');
        Configure::write('OAuth.providers.facebook.className', 'League\OAuth2\Client\Provider\InvalidFacebook');

        $this->expectException(InvalidProviderException::class);
        new ProviderConfig();
    }

    /**
     * Test with invalid service class
     *
     * @return void
     */
    public function testWithInvalidServiceClass()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', '10003030300303');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'secretpassword');
        Configure::write('OAuth.providers.facebook.service', 'CakeDC\Users\Social\Service\InvalidOAuth2Service');

        $this->expectException(InvalidProviderException::class);
        new ProviderConfig();
    }

    /**
     * Test with invalid mapper class
     *
     * @return void
     */
    public function testWithInvalidMapperClass()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', '10003030300303');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'secretpassword');
        Configure::write('OAuth.providers.facebook.mapper', 'CakeDC\Users\Social\Mapper\InvalidFacebook');

        $this->expectException(InvalidProviderException::class);
        new ProviderConfig();
    }

    /**
     * Test with invalid settings options value type
     *
     * @return void
     */
    public function testWithInvalidOptionsValueType()
    {
        $this->expectException(InvalidSettingsException::class);
        $config = [
            'path' => [
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'socialLogin',
                'prefix' => null
            ],
            'providers' => [
                'facebook' => [
                    'service' => 'CakeDC\Users\Social\Service\OAuth2Service',
                    'className' => 'League\OAuth2\Client\Provider\Facebook',
                    'mapper' => 'CakeDC\Users\Social\Mapper\Facebook',
                    'options' => 'invalid options'
                ],
            ]
        ];
        (new ProviderConfig())->normalizeConfig($config);
    }

    /**
     * Test with invalid settings collaborators value type
     *
     * @return void
     */
    public function testWithInvalidCollaboratorsValueType()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', '10003030300303');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'secretpassword');
        Configure::write('OAuth.providers.facebook.collaborators', 'johndoe');

        $this->expectException(InvalidSettingsException::class);
        new ProviderConfig();
    }

    /**
     * Test with custom config
     *
     * @return void
     */
    public function testWithCustomConfig()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', '10003030300303');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'secretpassword');
        Configure::write('OAuth.providers.twitter.options.clientId', '20003030300303');
        Configure::write('OAuth.providers.twitter.options.clientSecret', 'weakpassword');
        Configure::write('OAuth.providers.amazon.options.clientId', '3003030300303');
        Configure::write('OAuth.providers.amazon.options.clientSecret', 'weaksecretpassword');

        $Config = new ProviderConfig([
            'options' => [
                'customOption' => 'hello'
            ],
        ]);
        $expected = [
            'service' => 'CakeDC\Users\Social\Service\OAuth2Service',
            'className' => 'League\OAuth2\Client\Provider\Facebook',
            'mapper' => 'CakeDC\Users\Social\Mapper\Facebook',
            'options' => [
                'customOption' => 'hello',
                'graphApiVersion' => 'v2.8',
                'redirectUri' => '/auth/facebook',
                'linkSocialUri' => '/link-social/facebook',
                'callbackLinkSocialUri' => '/callback-link-social/facebook',
                'clientId' => '10003030300303',
                'clientSecret' => 'secretpassword'
            ],
            'collaborators' => [],
            'signature' => null,
            'mapFields' => [],
            'path' => [
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'socialLogin',
                'prefix' => null
            ]
        ];
        $actual = $Config->getConfig('facebook');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test with providers enabled
     *
     * @return void
     */
    public function testWithProvidersEnabled()
    {
        Configure::write('OAuth.providers.facebook.options.clientId', '10003030300303');
        Configure::write('OAuth.providers.facebook.options.clientSecret', 'secretpassword');
        Configure::write('OAuth.providers.twitter.options.clientId', '20003030300303');
        Configure::write('OAuth.providers.twitter.options.clientSecret', 'weakpassword');
        Configure::write('OAuth.providers.amazon.options.clientId', '3003030300303');
        Configure::write('OAuth.providers.amazon.options.clientSecret', 'weaksecretpassword');

        $Config = new ProviderConfig();
        $expected = [
            'service' => 'CakeDC\Users\Social\Service\OAuth2Service',
            'className' => 'League\OAuth2\Client\Provider\Facebook',
            'mapper' => 'CakeDC\Users\Social\Mapper\Facebook',
            'options' => [
                'graphApiVersion' => 'v2.8',
                'redirectUri' => '/auth/facebook',
                'linkSocialUri' => '/link-social/facebook',
                'callbackLinkSocialUri' => '/callback-link-social/facebook',
                'clientId' => '10003030300303',
                'clientSecret' => 'secretpassword'
            ],
            'collaborators' => [],
            'signature' => null,
            'mapFields' => [],
            'path' => [
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'socialLogin',
                'prefix' => null
            ]
        ];
        $actual = $Config->getConfig('facebook');

        $this->assertEquals($expected, $actual);

        $expected = [
            'service' => 'CakeDC\Users\Social\Service\OAuth1Service',
            'className' => 'League\OAuth1\Client\Server\Twitter',
            'mapper' => 'CakeDC\Users\Social\Mapper\Twitter',
            'options' => [
                'redirectUri' => '/auth/twitter',
                'linkSocialUri' => '/link-social/twitter',
                'callbackLinkSocialUri' => '/callback-link-social/twitter',
                'clientId' => '20003030300303',
                'clientSecret' => 'weakpassword'
            ],
            'collaborators' => [],
            'signature' => null,
            'mapFields' => [],
            'path' => [
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'socialLogin',
                'prefix' => null
            ]
        ];
        $actual = $Config->getConfig('twitter');
        $this->assertEquals($expected, $actual);

        $expected = [
            'service' => 'CakeDC\Users\Social\Service\OAuth2Service',
            'className' => 'Luchianenco\OAuth2\Client\Provider\Amazon',
            'mapper' => 'CakeDC\Users\Social\Mapper\Amazon',
            'options' => [
                'redirectUri' => '/auth/amazon',
                'linkSocialUri' => '/link-social/amazon',
                'callbackLinkSocialUri' => '/callback-link-social/amazon',
                'clientId' => '3003030300303',
                'clientSecret' => 'weaksecretpassword'
            ],
            'collaborators' => [],
            'signature' => null,
            'mapFields' => [],
            'path' => [
                'plugin' => 'CakeDC/Users',
                'controller' => 'Users',
                'action' => 'socialLogin',
                'prefix' => null
            ]
        ];
        $actual = $Config->getConfig('amazon');
        $this->assertEquals($expected, $actual);

        $expected = [];
        $actual = $Config->getConfig('linkedIn');
        $this->assertEquals($expected, $actual);

        $expected = [];
        $actual = $Config->getConfig('instagram');
        $this->assertEquals($expected, $actual);

        $expected = [];
        $actual = $Config->getConfig('google');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test without providers enabled
     *
     * @return void
     */
    public function testWithoutProvidersEnabled()
    {
        $Config = new ProviderConfig();
        $expected = [];
        $actual = $Config->getConfig('facebook');
        $this->assertEquals($expected, $actual);

        $expected = [];
        $actual = $Config->getConfig('twitter');
        $this->assertEquals($expected, $actual);

        $expected = [];
        $actual = $Config->getConfig('amazon');
        $this->assertEquals($expected, $actual);

        $expected = [];
        $actual = $Config->getConfig('linkedIn');
        $this->assertEquals($expected, $actual);

        $expected = [];
        $actual = $Config->getConfig('instagram');
        $this->assertEquals($expected, $actual);

        $expected = [];
        $actual = $Config->getConfig('google');
        $this->assertEquals($expected, $actual);
    }
}