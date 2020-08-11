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

use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakeDC\Users\Model\Behavior\LinkSocialBehavior;

/**
 * App\Model\Behavior\LinkSocialBehavior Test Case
 */
class LinkSocialBehaviorTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Behavior\LinkSocialBehavior
     */
    public $Behavior;

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
        $this->Table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $this->Behavior = new LinkSocialBehavior($this->Table);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Table, $this->Behavior);
        parent::tearDown();
    }

    /**
     * Test linkSocialAccount with facebook and not existing social account
     *
     * @param array  $data   Test input data
     * @param string $userId User id to add social account
     * @param array  $result Expected result
     * @author Marcelo Rocha <marcelo@promosapiens.com.br>
     * @return void
     * @dataProvider providerFacebookLinkSocialAccount
     */
    public function testlinkSocialAccountFacebookProvider($data, $userId, $result)
    {
        $user = $this->Table->get($userId, [
            'contain' => 'SocialAccounts',
        ]);
        $resultUser = $this->Behavior->linkSocialAccount($user, $data);
        $this->assertInstanceOf('\CakeDC\Users\Model\Entity\User', $resultUser);
        $actual = $resultUser->social_accounts[2];

        $this->assertInstanceOf('\CakeDC\Users\Model\Entity\SocialAccount', $actual);
        $actual->token_expires = $actual->token_expires->format('Y-m-d H:i:s');

        foreach ($result as $property => $value) {
            $this->assertEquals($value, $actual->$property);
        }

        $result = $this->Table->SocialAccounts->exists(['id' => $actual->id]);
        $this->assertTrue($result);
    }

    /**
     * Provider for linkSocialAccount with facebook and not existing social account
     *
     * @author Marcelo Rocha <marcelo@promosapiens.com.br>
     * @return array
     */
    public function providerFacebookLinkSocialAccount()
    {
        $expiresTime = new Time();
        $tokenExpires = $expiresTime->setTimestamp(1458423682)->format('Y-m-d H:i:s');

        return [
                'provider' => [
                'data' => [
                    'id' => '9999911112255', //Reference existe mas provider google
                    'username' => null,
                    'full_name' => 'Full name',
                    'first_name' => 'First name',
                    'last_name' => 'Last name',
                    'email' => 'user-1@test.com',
                    'raw' => [
                        'id' => '9999911112255',
                        'name' => 'Ful Name.',
                        'first_name' => 'First Name',
                        'last_name' => 'Last name',
                        'email' => 'user-1@test.com',
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
                    'link' => 'facebook-link-15579',
                    'provider' => 'Facebook',
                ],
                'user' => '00000000-0000-0000-0000-000000000001',
                'result' => [
                    'provider' => 'Facebook',
                    'username' => null,
                    'reference' => '9999911112255',
                    'avatar' => '',
                    'link' => 'facebook-link-15579',
                    'description' => null,
                    'token' => 'token',
                    'token_secret' => null,
                    'token_expires' => $tokenExpires,
                    'user_id' => '00000000-0000-0000-0000-000000000001',
                    'active' => true,

                ],
                ],

        ];
    }

    /**
     * Test linkSocialAccount with facebook and could not save social account
     *
     * @param array  $data   Test input data
     * @param string $userId User id to add social account
     * @author Marcelo Rocha <marcelo@promosapiens.com.br>
     * @return void
     * @dataProvider providerFacebookLinkSocialAccountErrorSaving
     */
    public function testlinkSocialAccountErrorSavingFacebookProvider($data, $userId)
    {
        $user = $this->Table->get($userId);
        $resultUser = $this->Behavior->linkSocialAccount($user, $data);
        $this->assertInstanceOf('\CakeDC\Users\Model\Entity\User', $resultUser);
        $actual = $resultUser->social_accounts[0];
        $this->assertInstanceOf('\CakeDC\Users\Model\Entity\SocialAccount', $actual);

        $actual = $user->getErrors();

        $expected = [
            'social_accounts' => [
                [
                    'token' => [
                        '_empty' => 'This field cannot be left empty',
                        '_required' => 'This field is required',

                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Provider for linkSocialAccount with facebook and could not save social account
     *
     * @author Marcelo Rocha <marcelo@promosapiens.com.br>
     * @return array
     */
    public function providerFacebookLinkSocialAccountErrorSaving()
    {
        $expiresTime = new Time();
        $tokenExpires = $expiresTime->setTimestamp(1458423682)->format('Y-m-d H:i:s');

        return [
                'provider' => [
                'data' => [
                    'id' => '9999911112255', //Reference existe mas provider google
                    'username' => null,
                    'full_name' => 'Full name',
                    'first_name' => 'First name',
                    'last_name' => 'Last name',
                    'email' => 'user-1@test.com',
                    'raw' => [
                        'id' => '9999911112255',
                        'name' => 'Ful Name.',
                        'first_name' => 'First Name',
                        'last_name' => 'Last name',
                        'email' => 'user-1@test.com',
                        'picture' => [
                            'data' => [
                                'url' => 'data-url',
                            ],
                        ],
                    ],
                    'credentials' => [
                        'token' => '',
                        'secret' => null,
                        'expires' => 1458423682,
                    ],
                    'validated' => true,
                    'link' => 'facebook-link-15579',
                    'provider' => 'Facebook',
                ],
                'user' => '00000000-0000-0000-0000-000000000001',
                'result' => [
                    'provider' => 'Facebook',
                    'username' => null,
                    'reference' => '9999911112255',
                    'avatar' => '',
                    'link' => 'facebook-link-15579',
                    'description' => null,
                    'token' => 'token',
                    'token_secret' => null,
                    'token_expires' => $tokenExpires,
                    'user_id' => '00000000-0000-0000-0000-000000000001',
                    'active' => true,

                ],
                ],

        ];
    }

    /**
     * Test linkSocialAccount with facebook when account already exists
     *
     * @param array  $data   Test input data
     * @param string $userId User id to add social account
     * @param array  $result Expected result
     * @author Marcelo Rocha <marcelo@promosapiens.com.br>
     * @return void
     * @dataProvider providerFacebookLinkSocialAccountAccountExists
     */
    public function testlinkSocialAccountFacebookProviderAccountExists($data, $userId, $result)
    {
        $user = $this->Table->get($userId);
        $resultUser = $this->Behavior->linkSocialAccount($user, $data);
        $this->assertInstanceOf('\CakeDC\Users\Model\Entity\User', $resultUser);
        $this->assertFalse($resultUser->has('social_accounts'));
        $expected = [
            'social_accounts' => [
                '_existsIn' => __d('cake_d_c/users', 'Social account already associated to another user'),
            ],
        ];
        $actual = $user->getErrors();
        $this->assertEquals($expected, $actual);

        //Se for o usuário que já esta associado então okay
        $this->Table->SocialAccounts->find()->where([
            'reference' => $data['id'],
            'provider' => $data['provider'],
        ])->firstOrFail();

        $userBase = $this->Table->get('00000000-0000-0000-0000-000000000002', [
            'contain' => ['SocialAccounts'],
        ]);
        $resultUser = $this->Behavior->linkSocialAccount($userBase, $data);
        $this->assertInstanceOf('\CakeDC\Users\Model\Entity\User', $resultUser);
        $this->assertEquals([], $userBase->getErrors());

        $actual = $resultUser->social_accounts[0];

        $this->assertInstanceOf('\CakeDC\Users\Model\Entity\SocialAccount', $actual);

        $actual->token_expires = $actual->token_expires->format('Y-m-d H:i:s');
        foreach ($result as $property => $value) {
            $this->assertEquals($value, $actual->$property);
        }
    }

    /**
     * Provider for linkSocialAccount with facebook when account already exists
     *
     * @author Marcelo Rocha <marcelo@promosapiens.com.br>
     * @return array
     */
    public function providerFacebookLinkSocialAccountAccountExists()
    {
        $expiresTime = new Time();
        $tokenExpires = $expiresTime->setTimestamp(1458423682)->format('Y-m-d H:i:s');

        return [
                'provider' => [
                    'data' => [
                        'id' => 'reference-2-1',
                        'username' => null,
                        'full_name' => 'Full name',
                        'first_name' => 'First name',
                        'last_name' => 'Last name',
                        'email' => 'email@example.com',
                        'raw' => [
                            'id' => 'reference-2-1',
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
                        'link' => 'facebook-link-15579',
                        'provider' => 'Facebook',
                    ],
                    'user' => '00000000-0000-0000-0000-000000000001',
                    'result' => [
                        'id' => '00000000-0000-0000-0000-000000000003',
                        'provider' => 'Facebook',
                        'username' => null,
                        'reference' => 'reference-2-1',
                        'avatar' => '',
                        'link' => 'facebook-link-15579',
                        'description' => null,
                        'token' => 'token',
                        'token_secret' => null,
                        'token_expires' => $tokenExpires,
                        'user_id' => '00000000-0000-0000-0000-000000000002',
                        'active' => true,
                    ],
                ],

        ];
    }
}
