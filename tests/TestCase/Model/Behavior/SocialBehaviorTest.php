<?php
declare(strict_types=1);

/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace CakeDC\Users\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use CakeDC\Users\Exception\AccountNotActiveException;
use CakeDC\Users\Exception\MissingEmailException;
use CakeDC\Users\Exception\UserNotActiveException;

/**
 * Test Case
 */
class SocialBehaviorTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.SocialAccounts',
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Table = $this->getMockForModel('CakeDC/Users.Users', ['save']);
        $this->Behavior = $this->getMockBuilder('CakeDC\Users\Model\Behavior\SocialBehavior')
            ->setMethods(['randomString', '_updateActive', 'generateUniqueUsername'])
            ->setConstructorArgs([$this->Table])
            ->getMock();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Table, $this->Behavior, $this->Email);
        parent::tearDown();
    }

    /**
     * Test socialLogin with facebook and not existing user
     *
     * @dataProvider providerFacebookSocialLogin
     */
    public function testSocialLoginFacebookProvider($data, $options, $dataUser)
    {
        $user = $this->Table->newEntity($dataUser, ['associated' => ['SocialAccounts']]);
        $user->password = '$2y$10$0QzszaIEpW1pYpoKJVf4DeqEAHtg9whiLTX/l3TcHAoOLF1bC9U.6';

        $this->Behavior->expects($this->once())
            ->method('generateUniqueUsername')
            ->with('email')
            ->will($this->returnValue('username'));

        $this->Behavior->expects($this->once())
            ->method('randomString')
            ->will($this->returnValue('password'));

        $this->Behavior->expects($this->once())
            ->method('_updateActive')
            ->will($this->returnValue($user));

        $this->Table->expects($this->once())
            ->method('save')
            ->with($user)
            ->will($this->returnValue($user));

        $result = $this->Behavior->socialLogin($data, $options);
        $this->assertEquals($result, $user);
    }

    /**
     * Test socialLogin with facebook and not existing user
     *
     * @dataProvider providerFacebookSocialLogin
     */
    public function testSocialLoginFacebookProviderUsingEmail($data, $options, $dataUser)
    {
        $user = $this->Table->newEntity($dataUser, ['associated' => ['SocialAccounts']]);
        $user->password = '$2y$10$0QzszaIEpW1pYpoKJVf4DeqEAHtg9whiLTX/l3TcHAoOLF1bC9U.6';

        $this->Behavior->expects($this->once())
            ->method('generateUniqueUsername')
            ->with('email')
            ->will($this->returnValue('username'));

        $this->Behavior->expects($this->once())
            ->method('randomString')
            ->will($this->returnValue('password'));

        $this->Behavior->expects($this->once())
            ->method('_updateActive')
            ->will($this->returnValue($user));

        $this->Table->expects($this->once())
            ->method('save')
            ->with($user)
            ->will($this->returnValue($user));

        $this->Behavior->initialize(['username' => 'email']);
        $result = $this->Behavior->socialLogin($data, $options);
        $this->assertEquals($result, $user);
    }

    /**
     * Provider for socialLogin with facebook and not existing user
     */
    public function providerFacebookSocialLogin()
    {
        return [
                'provider' => [
                'data' => [
                    'id' => 'facebook-id',
                    'username' => null,
                    'full_name' => 'Full name',
                    'first_name' => 'First name',
                    'last_name' => 'Last name',
                    'email' => 'email@example.com',
                    'raw' => [
                        'id' => '10153521527396318',
                        'name' => 'Ful Name.',
                        'first_name' => 'First Name',
                        'last_name' => 'Last name',
                        'email' => 'email@example.com',
                        'picture' => [
                            'data' => [
                                'url' => 'data-url',
                            ],
                        ],
                    ],
                    'credentials' => [
                        'token' => 'token',
                        'secret' => null,
                        'expires' => 1458423682,
                    ],
                    'validated' => true,
                    'link' => 'facebook-link',
                    'provider' => 'Facebook',
                ],
                'options' => [
                    'use_email' => true,
                    'validate_email' => true,
                    'token_expiration' => 3600,
                ],
                'result' => [
                    'first_name' => 'First name',
                    'last_name' => 'Last name',
                    'username' => 'username',
                    'email' => 'email@example.com',
                    'password' => '$2y$10$oLPxCkKJ1TUCR6xJ1t0Wj.7Fznx49Wn4NZB2aJCmVvRMucaHuNyyO',
                    'avatar' => null,
                    'tos_date' => '2016-01-20 15:45:09',
                    'gender' => null,
                    'social_accounts' => [
                        [
                            'provider' => 'Facebook',
                            'username' => null,
                            'reference' => '10153521527396318',
                            'avatar' => '',
                            'link' => 'facebook-link',
                            'description' => null,
                            'token' => 'token',
                            'token_secret' => null,
                            'token_expires' => '2016-03-19 21:41:22',
                            'data' => '-',
                            'active' => true,
                        ],
                    ],
                    'activation_date' => '2016-01-20 15:45:09',
                    'active' => true,
                ],
                ],

        ];
    }

    /**
     * Test socialLogin with facebook with existing and active user
     *
     * @dataProvider providerFacebookSocialLoginExistingReference
     */
    public function testSocialLoginExistingReferenceOkay($data, $options)
    {
        $this->Behavior->expects($this->never())
            ->method('generateUniqueUsername');

        $this->Behavior->expects($this->never())
            ->method('randomString');

        $this->Behavior->expects($this->never())
            ->method('_updateActive');

        $fullData = $data + [
            'credentials' => [
                'token' => 'aT0ken' . time(),
                'secret' => 'AS3crEt' . time(),
                'expires' => 1458423682,
            ],
            'avatar' => 'http://localhost/avatar.jpg' . time(),
            'link' => 'facebook-link' . time(),
            'bio' => 'This is a sample bio' . time(),
            'raw' => [
                'bio' => 'This is a raw bio',
                'extra' => 'value',
                'foo' => 'bar',
            ],
        ];
        $accountBefore = $this->Table->SocialAccounts->find()->where([
            'SocialAccounts.reference' => $data['id'],
            'SocialAccounts.provider' => $data['provider'],
        ])->firstOrFail();
        $result = $this->Behavior->socialLogin($fullData + [], $options);
        $this->assertEquals($result->id, '00000000-0000-0000-0000-000000000002');
        $this->assertTrue($result->active);

        $account = $this->Table->SocialAccounts->find()->where([
            'SocialAccounts.reference' => $data['id'],
            'SocialAccounts.provider' => $data['provider'],
        ])->firstOrFail();

        $this->assertEquals($fullData['avatar'], $account->avatar);
        $this->assertEquals($fullData['link'], $account->link);
        $this->assertEquals($fullData['bio'], $account->description);
        $this->assertEquals($fullData['raw'], unserialize($account->data));
        $this->assertEquals($fullData['credentials']['token'], $account->token);
        $this->assertEquals($fullData['credentials']['secret'], $account->token_secret);
        $this->assertNotEmpty($account->token_expires);
        $this->assertNotEquals($accountBefore->token_expires, $account->token_expires);
        $this->assertSame($accountBefore->id, $account->id);
        $this->assertSame($accountBefore->active, $account->active);
    }

    /**
     * Provider for socialLogin with facebook with existing and active user
     */
    public function providerFacebookSocialLoginExistingReference()
    {
        return [
            'provider' => [
                'data' => [
                    'id' => 'reference-2-1',
                    'provider' => 'Facebook',
                ],
                'options' => [
                    'use_email' => true,
                    'validate_email' => true,
                    'token_expiration' => 3600,
                ],
            ],

        ];
    }

    /**
     * Test socialLogin with existing and active user and not active social account
     *
     * @dataProvider providerSocialLoginExistingAndNotActiveAccount
     */
    public function testSocialLoginExistingNotActiveReference($data, $options)
    {
        $this->expectException(AccountNotActiveException::class);
        $this->Behavior->expects($this->never())
            ->method('generateUniqueUsername');

        $this->Behavior->expects($this->never())
            ->method('randomString');

        $this->Behavior->expects($this->never())
            ->method('_updateActive');
        $this->Behavior->socialLogin($data, $options);
    }

    /**
     * Provider for socialLogin with existing and active user and not active social account
     */
    public function providerSocialLoginExistingAndNotActiveAccount()
    {
        return [
            'provider' => [
                'data' => [
                    'id' => 'reference-1-1234',
                    'provider' => 'Facebook',
                ],
                'options' => [
                    'use_email' => true,
                    'validate_email' => true,
                    'token_expiration' => 3600,
                ],
            ],

        ];
    }

    /**
     * Test socialLogin with existing and active account but not active user
     *
     * @dataProvider providerSocialLoginExistingAccountNotActiveUser
     */
    public function testSocialLoginExistingReferenceNotActiveUser($data, $options)
    {
        $this->expectException(UserNotActiveException::class);
        $this->Behavior->expects($this->never())
            ->method('generateUniqueUsername');

        $this->Behavior->expects($this->never())
            ->method('randomString');

        $this->Behavior->expects($this->never())
            ->method('_updateActive');
        $this->Behavior->socialLogin($data, $options);
    }

    /**
     * Provider for socialLogin with existing and active account but not active user
     */
    public function providerSocialLoginExistingAccountNotActiveUser()
    {
        return [
            'provider' => [
                'data' => [
                    'id' => 'reference-1-1234',
                    'provider' => 'Twitter',
                ],
                'options' => [
                    'use_email' => true,
                    'validate_email' => true,
                    'token_expiration' => 3600,
                ],
            ],

        ];
    }

    /**
     * Test socialLogin with facebook and not existing user
     *
     * @dataProvider providerFacebookSocialLoginNoEmail
     */
    public function testSocialLoginNoEmail($data, $options)
    {
        $this->expectException(MissingEmailException::class);
        $this->Behavior->socialLogin($data, $options);
    }

    /**
     * Provider for socialLogin with facebook and not existing user
     */
    public function providerFacebookSocialLoginNoEmail()
    {
        return [
            'provider' => [
                'data' => [
                    'id' => 'facebook-id',
                    'username' => null,
                    'full_name' => 'Full name',
                    'first_name' => 'First name',
                    'last_name' => 'Last name',
                    'validated' => true,
                    'link' => 'facebook-link',
                    'provider' => 'Facebook',
                ],
                'options' => [
                    'use_email' => true,
                    'validate_email' => true,
                    'token_expiration' => 3600,
                ],
            ],

        ];
    }

    /**
     * Test socialLogin with facebook and not existing user
     *
     * @dataProvider providerGenerateUsername
     */
    public function testGenerateUniqueUsername($param, $expected)
    {
        $this->Behavior = $this->getMockBuilder('CakeDC\Users\Model\Behavior\SocialBehavior')
            ->setMethods(['randomString', '_updateActive'])
            ->setConstructorArgs([$this->Table])
            ->getMock();

        $result = $this->Behavior->generateUniqueUsername($param);
        $this->assertEquals($expected, $result);
    }

    /**
     * Provider for socialLogin with facebook and not existing user
     */
    public function providerGenerateUsername()
    {
        return [
            ['username', 'username'],
            ['user-1', 'user-10'],
            ['user-5', 'user-50'],

        ];
    }
}
