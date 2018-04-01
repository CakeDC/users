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

use CakeDC\Users\Auth\Social\Mapper\LinkedIn;
use Cake\TestSuite\TestCase;

class LinkedInTest extends TestCase
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
            'emailAddress' => 'test@gmail.com',
            'firstName' => 'Test',
            'headline' => 'The best test user in the world.',
            'id' => '1',
            'industry' => 'Computer Software',
            'lastName' => 'User',
            'location' => [
                'country' => [
                    'code' => 'es'
                ],
                'name' => 'Spain'
            ],
            'pictureUrl' => 'https://media.licdn.com/mpr/mprx/test.jpg',
            'publicProfileUrl' => 'https://www.linkedin.com/in/test'
        ];
        $providerMapper = new LinkedIn($rawData);
        $user = $providerMapper();
        $this->assertEquals([
            'id' => '1',
            'username' => null,
            'full_name' => null,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@gmail.com',
            'avatar' => 'https://media.licdn.com/mpr/mprx/test.jpg',
            'gender' => null,
            'link' => 'https://www.linkedin.com/in/test',
            'bio' => 'The best test user in the world.',
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
