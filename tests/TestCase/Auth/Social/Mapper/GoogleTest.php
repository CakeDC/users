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

namespace CakeDC\Users\Test\TestCase\Auth\Social\Mapper;

use CakeDC\Users\Auth\Social\Mapper\Google;
use Cake\TestSuite\TestCase;

class GoogleTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testMap()
    {
        $token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496
        ]);
        $rawData = [
            'token' => $token,
            'emails' => [['value' => 'test@gmail.com']],
            'id' => '1',
            'displayName' => 'Test User',
            'name' => [
                'familyName' => 'User',
                'givenName' => 'Test'
            ],
            'aboutMe' => '<span>I am the best test user in the world.</span>',
            'url' => 'https://plus.google.com/+TestUser',
            'image' => [
                'url' => 'https://lh3.googleusercontent.com/photo.jpg'
            ]
        ];
        $providerMapper = new Google($rawData);
        $user = $providerMapper();
        $this->assertEquals([
            'id' => '1',
            'username' => null,
            'full_name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@gmail.com',
            'avatar' => 'https://lh3.googleusercontent.com/photo.jpg',
            'gender' => null,
            'link' => 'https://plus.google.com/+TestUser',
            'bio' => '<span>I am the best test user in the world.</span>',
            'locale' => null,
            'validated' => true,
            'credentials' => [
                'token' => 'test-token',
                'secret' => null,
                'expires' => 1490988496
            ],
            'raw' => $rawData
        ], $user);
    }
}
