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

namespace CakeDC\Users\Test\TestCase\Controller\Traits;

class ProfileTraitTest extends BaseTraitTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.CakeDC/Users.SocialAccounts',
    ];

    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->traitClassName = 'CakeDC\Users\Controller\UsersController';
        $this->traitMockMethods = ['set', 'getUsersTable', 'redirect', 'validate'];
        parent::setUp();
    }

    /**
     * test
     *
     * @return void
     */
    public function testProfileGetNotLoggedInUserNotFound()
    {
        $userId = '00000000-0000-0000-0000-000000000000'; //not found
        $this->_mockRequestGet();
        $this->_mockAuthentication();
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('User was not found');
        $this->Trait->profile($userId);
    }

    /**
     * test
     *
     * @return void
     */
    public function testProfileGetLoggedInUserNotFound()
    {
        $userId = '00000000-0000-0000-0000-000000000000'; //not found
        $this->_mockRequestGet();
        $this->_mockAuthLoggedIn();
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('User was not found');
        $this->Trait->profile($userId);
    }

    /**
     * test
     *
     * @return void
     */
    public function testProfileGetNotLoggedInEmptyId()
    {
        $this->_mockRequestGet();
        $this->_mockAuthentication();
        $this->_mockFlash();
        $this->Trait->Flash->expects($this->once())
            ->method('error')
            ->with('Not authorized, please login first');
        $this->Trait->profile();
    }

    /**
     * test
     *
     * @return void
     */
    public function testProfileGetLoggedInMyProfile()
    {
        $this->_mockRequestGet();
        $this->_mockAuthLoggedIn();
        $this->_mockFlash();
        $this->Trait->expects($this->any())
            ->method('set')
            ->will($this->returnCallback(function ($param1, $param2 = null) {
                if ($param1 === 'avatarPlaceholder') {
                    BaseTraitTest::assertEquals('CakeDC/Users.avatar_placeholder.png', $param2);
                } elseif (is_array($param1)) {
                    BaseTraitTest::assertEquals('user-1', $param1['user']->username);
                }
            }));
        $this->Trait->profile();
    }
}
