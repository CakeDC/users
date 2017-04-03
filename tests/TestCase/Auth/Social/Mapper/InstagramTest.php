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

use CakeDC\Users\Auth\Social\Mapper\Instagram;
use Cake\TestSuite\TestCase;

class InstagramTest extends TestCase
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
            'profile_picture' => 'https://scontent-lax3-2.cdninstagram.com/test.jpg',
            'username' => 'test',
            'id' => '1',
            'full_name' => '',
            'website' => '',
            'counts' => [
                'followed_by' => 35,
                'media' => 1,
                'follows' => 44
            ],
            'bio' => ''
        ];
        $providerMapper = new Instagram($rawData);
        $user = $providerMapper();
        $this->assertEquals([
            'id' => '1',
            'username' => 'test',
            'full_name' => '',
            'first_name' => null,
            'last_name' => null,
            'email' => null,
            'avatar' => 'https://scontent-lax3-2.cdninstagram.com/test.jpg',
            'gender' => null,
            'link' => 'https://instagram.com/test',
            'bio' => '',
            'locale' => null,
            'validated' => false,
            'credentials' => [
                'token' => 'test-token',
                'secret' => null,
                'expires' => 1490988496
            ],
            'raw' => $rawData
        ], $user);
    }
}
