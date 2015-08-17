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

namespace Users\Test\TestCase\Controller\Traits;

use Cake\ORM\TableRegistry;
use Users\Test\BaseTraitTest;

class ProfileTraitTest extends BaseTraitTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.users.users',
        'plugin.users.social_accounts',
    ];

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->table = TableRegistry::get('Users.Users');
        $this->Trait = $this->getMockBuilder('Users\Controller\Traits\ProfileTrait')
                ->setMethods(['set', 'getUsersTable', 'redirect', 'validate'])
                ->getMockForTrait();
        $this->Trait->expects($this->any())
                ->method('getUsersTable')
                ->will($this->returnValue($this->table));
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->table, $this->Trait);
        parent::tearDown();
    }

    /**
     * test
     *
     * @return void
     */
    public function testProfileGetNotLoggedInUserNotFound()
    {
        $userId = 'not-found';
        $this->_mockRequestGet();
        $this->_mockAuth();
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
        $userId = 'not-found';
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
        $this->_mockAuth();
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
                    BaseTraitTest::assertEquals('Users.avatar_placeholder.png', $param2);
                } elseif (is_array($param1)) {
                    BaseTraitTest::assertEquals('user-1', $param1['user']->username);
                }
            }));
        $this->Trait->profile();
    }
}
