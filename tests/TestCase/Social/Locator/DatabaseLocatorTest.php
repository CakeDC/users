<?php

namespace CakeDC\Users\Test\TestCase\Social\Locator;

use CakeDC\Users\Auth\Exception\InvalidSettingsException;
use CakeDC\Users\Social\Locator\DatabaseLocator;
use CakeDC\Users\Social\Mapper\Facebook;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\TestSuite\TestCase;

class DatabaseLocatorTest extends TestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.users',
        'plugin.CakeDC/Users.social_accounts'
    ];

    /**
     * @var DatabaseLocator
     */
    public $Locator;

    /**
     * Test getOrCreate method
     */
    public function testGetOrCreate()
    {
        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496
        ]);

        $data = [
            'token' => $Token,
            'id' => '1',
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@gmail.com',
            'hometown' => [
                'id' => '108226049197930',
                'name' => 'Madrid'
            ],
            'picture' => [
                'data' => [
                    'url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
                    'is_silhouette' => false
                ]
            ],
            'cover' => [
                'source' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
                'id' => '1'
            ],
            'gender' => 'male',
            'locale' => 'en_US',
            'link' => 'https://www.facebook.com/app_scoped_user_id/1/',
            'timezone' => -5,
            'age_range' => [
                'min' => 21
            ],
            'bio' => 'I am the best test user in the world.',
            'picture_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
            'is_silhouette' => false,
            'cover_photo_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg'
        ];

        $user = (new Facebook($data))();
        $user['provider'] = 'facebook';

        $this->Locator = new DatabaseLocator();
        $result = $this->Locator->getOrCreate($user);
        $this->assertInstanceOf('CakeDC\Users\Model\Entity\User', $result);
        $this->assertNotEmpty($result->id);
        $this->assertEquals('test@gmail.com', $result->email);
        $this->assertEquals('test', $result->username);
    }

    /**
     * Test getOrCreate method error in social login
     *
     * @return void
     */
    public function testGetOrCreateErrorSocialLogin()
    {
        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496
        ]);
        $this->Locator = $this->getMockBuilder(DatabaseLocator::class)->setMethods([
            '_socialLogin'
        ])->getMock();
        $this->Locator->expects($this->once())
            ->method('_socialLogin')
            ->will($this->returnValue(false));

        $data = [
            'token' => $Token,
            'id' => '1',
            'name' => '',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@gmail.com',
            'hometown' => [
                'id' => '108226049197930',
                'name' => 'Madrid'
            ],
            'locale' => 'en_US',
            'link' => 'https://www.facebook.com/app_scoped_user_id/1/',
            'timezone' => -5,
            'age_range' => [
                'min' => 21
            ],
            'bio' => 'I am the best test user in the world.',
            'picture_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
            'is_silhouette' => false,
            'cover_photo_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg'
        ];

        $user = (new Facebook($data))();
        $user['provider'] = 'facebook';

        $this->expectException(RecordNotFoundException::class);
        $this->Locator->getOrCreate($user);
    }

    /**
     * Test getOrCreate method invalid user model
     *
     * @return void
     */
    public function testGetOrCreateInvalidUserModel()
    {
        $Token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'test-token',
            'expires' => 1490988496
        ]);

        $data = [
            'token' => $Token,
            'id' => '1',
            'name' => '',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@gmail.com',
            'hometown' => [
                'id' => '108226049197930',
                'name' => 'Madrid'
            ],
            'locale' => 'en_US',
            'link' => 'https://www.facebook.com/app_scoped_user_id/1/',
            'timezone' => -5,
            'age_range' => [
                'min' => 21
            ],
            'bio' => 'I am the best test user in the world.',
            'picture_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg',
            'is_silhouette' => false,
            'cover_photo_url' => 'https://scontent.xx.fbcdn.net/v/test.jpg'
        ];

        $this->Locator = new DatabaseLocator([
            'userModel' => false
        ]);
        $user = (new Facebook($data))();
        $user['provider'] = 'facebook';

        $this->expectException(InvalidSettingsException::class);
        $this->Locator->getOrCreate($user);
    }
}
